import os
import uuid
import time
import ffmpeg
import azure.cognitiveservices.speech as speechsdk
from flask import Flask, request, jsonify
import google.generativeai as genai
import threading

# --- KONFIGURASI ---
# GANTI dengan nilai yang valid di lingkungan produksi!
SPEECH_REGION = "eastasia"
SPEECH_KEY = ""
GEMINI_API_KEY = ""

# Konfigurasi Gemini API
genai.configure(api_key=GEMINI_API_KEY)

app = Flask(__name__)

# --- FUNGSI GEMINI ---

def generate_gemini_feedback(
    transcript: str,
    accuracy: float,
    fluency: float,
    completeness: float,
    pronunciation: float,
):
    """
    Kirim transcript + skor ke Gemini untuk dianalisis (CEFR, penjelasan skor, saran).
    Format dijamin rapi, tanpa markdown, tanpa tabel, tanpa simbol aneh.
    """

    model = genai.GenerativeModel("gemini-flash-latest")

    prompt = f"""
Tolong analisis hasil speaking berikut menggunakan CEFR (A1–C2).

TRANSKRIP:
\"\"\"{transcript}\"\"\"

SKOR:
Accuracy: {accuracy}
Fluency: {fluency}
Completeness: {completeness}
Pronunciation: {pronunciation}

TOLONG JAWAB DALAM FORMAT TEKS RAPIH BERIKUT (tanpa markdown, tanpa tanda bintang, tanpa tanda pagar, tanpa garis tabel):

CEFR Level: <tulis level CEFR, misalnya A2, B1, B2>
<jelaskan alasan level berdasarkan grammar, vocabulary, fluency, dan coherence dalam 2-3 kalimat>

1. Accuracy:
   <penjelasan singkat 1-2 kalimat>

2. Fluency:
   <penjelasan singkat 1-2 kalimat>

3. Completeness:
   <penjelasan singkat 1-2 kalimat>

4. Pronunciation:
   <penjelasan singkat 1-2 kalimat>

Suggestion:
- <saran singkat 1 kalimat>
- <saran singkat 1 kalimat>
- <saran singkat 1 kalimat>

Catatan Penting:
- Tidak boleh ada markdown.
- Tidak boleh ada simbol seperti #, *, |.
- Format harus persis seperti contoh di atas.
- Bahasa Inggris yang jelas dan mudah dimengerti mahasiswa.
"""

    response = model.generate_content(prompt)
    return response.text

# --- ENDPOINT UTAMA ---

@app.route("/health", methods=["GET"])
def health():
    if not SPEECH_KEY:
        return jsonify({"status": "error", "message": "SPEECH_KEY not configured"}), 500
    if not GEMINI_API_KEY:
        return jsonify({"status": "error", "message": "GEMINI_API_KEY not configured"}), 500

    return jsonify({"status": "ok"})

@app.route("/stt", methods=["POST"])
def stt_route():
    # Inisialisasi path di awal untuk penggunaan di blok finally
    input_path = None
    wav_path = None
    recognizer = None # Deklarasi agar bisa dihapus di finally
    pron_recognizer = None # Deklarasi agar bisa dihapus di finally

    try:
        if "audio" not in request.files:
            return jsonify({"error": "audio not found"}), 400

        audio_file = request.files["audio"]

        os.makedirs("uploads", exist_ok=True)

        input_path = f"uploads/{uuid.uuid4()}.m4a"
        wav_path = f"uploads/{uuid.uuid4()}.wav"

        # 1. Simpan dan Konversi Audio ke WAV 16kHz, mono
        audio_file.save(input_path)

        ffmpeg.input(input_path).output(
            wav_path,
            format="wav",
            acodec="pcm_s16le",
            ac=1,
            ar="16000"
        ).overwrite_output().run(quiet=True)

        time.sleep(0.2) 

        # Konfigurasi umum Azure Speech
        speech_config = speechsdk.SpeechConfig(
            subscription=SPEECH_KEY,
            region=SPEECH_REGION
        )
        speech_config.speech_recognition_language = "en-US"
        audio_input = speechsdk.AudioConfig(filename=wav_path)

        # 2. REKOGNISI UCAPAN (STT) BERKELANJUTAN
        
        recognized_text = ""
        stt_done = threading.Event()
        stt_error = False

        recognizer = speechsdk.SpeechRecognizer(
            speech_config=speech_config,
            audio_config=audio_input
        )

        def stt_recognized_cb(evt: speechsdk.SpeechRecognitionEventArgs):
            nonlocal recognized_text
            if evt.result.reason == speechsdk.ResultReason.RecognizedSpeech:
                # LOGGING BARU untuk membantu mendiagnosis STT failed
                print(f"Segment Recognized: {evt.result.text}") 
                recognized_text += evt.result.text + " "

        def stt_canceled_cb(evt: speechsdk.SessionEventArgs):
            nonlocal stt_error
            cancellation_reason = evt.result.cancellation_details.reason
            print(f"❌ STT Canceled: {cancellation_reason}")
            
            # PERBAIKAN KRUSIAL: Hanya set stt_error=True jika BUKAN EndOfStream.
            if cancellation_reason != speechsdk.CancellationReason.EndOfStream:
                stt_error = True
            
            stt_done.set()

        def stt_session_stopped_cb(evt: speechsdk.SessionEventArgs):
            print(f"🛑 STT Session Stopped: {evt}")
            stt_done.set()

        recognizer.recognized.connect(stt_recognized_cb)
        recognizer.session_stopped.connect(stt_session_stopped_cb)
        recognizer.canceled.connect(stt_canceled_cb)

        print("🎧 Recognizing speech continuously (STT)...")
        recognizer.start_continuous_recognition_async()
        stt_done.wait() # Tunggu hingga sesi selesai
        recognizer.stop_continuous_recognition_async()

        # PENTING: Hapus objek recognizer untuk melepaskan file WAV
        del recognizer 
        recognizer = None # Set ke None untuk penggunaan finally

        recognized_text = recognized_text.strip()
        
        # LOGGING STATUS AKHIR (Perbaikan #1)
        print(f"DEBUG: stt_error={stt_error}, recognized_text length={len(recognized_text)}")

        if stt_error or not recognized_text:
            print("❌ STT failed or no speech recognized")
            if stt_error:
                return jsonify({"error": "Speech recognition was canceled due to a genuine error"}), 500
            else:
                return jsonify({"error": "No speech recognized"}), 400
        
        print("✅ Recognized:", recognized_text)

        # 3. PENILAIAN PELAFALAN (PRONUNCIATION ASSESSMENT)
        
        # Menggunakan recognized_text sebagai reference_text untuk penilaian
        pron_config = speechsdk.PronunciationAssessmentConfig(
            reference_text=recognized_text,
            grading_system=speechsdk.PronunciationAssessmentGradingSystem.HundredMark,
            granularity=speechsdk.PronunciationAssessmentGranularity.Phoneme,
            enable_miscue=True
        )
        
        # Inisialisasi Recognizer baru untuk PA
        pron_recognizer = speechsdk.SpeechRecognizer(
            speech_config=speech_config,
            audio_config=audio_input
        )
        
        pron_config.apply_to(pron_recognizer)

        print("🎯 Starting Pronunciation Assessment (Single-Shot)...")
        pron_result = pron_recognizer.recognize_once_async().get()
        
        # PENTING: Hapus objek pron_recognizer untuk melepaskan file WAV
        del pron_recognizer
        pron_recognizer = None # Set ke None untuk penggunaan finally

        if pron_result.reason != speechsdk.ResultReason.RecognizedSpeech:
            print("❌ Pronunciation Assessment failed or no match.")
            return jsonify({"error": "Pronunciation Assessment failed"}), 500

        pron_assessment = speechsdk.PronunciationAssessmentResult(pron_result)

        accuracy_score = pron_assessment.accuracy_score
        fluency_score = pron_assessment.fluency_score
        completeness_score = pron_assessment.completeness_score
        pronunciation_score = pron_assessment.pronunciation_score

        print("🎯 Scores:",
              accuracy_score,
              fluency_score,
              completeness_score,
              pronunciation_score)

        # 4. FEEDBACK GEMINI
        gemini_feedback = generate_gemini_feedback(
            recognized_text,
            accuracy_score,
            fluency_score,
            completeness_score,
            pronunciation_score
        )

        return jsonify({
            "recognized_text": recognized_text,
            "accuracy_score": accuracy_score,
            "fluency_score": fluency_score,
            "completeness_score": completeness_score,
            "pronunciation_score": pronunciation_score,
            "gpt_feedback": gemini_feedback
        })

    except Exception as e:
        print("🔥 Exception:", e)
        return jsonify({"error": str(e)}), 500

    finally:
        # Menghapus time.sleep dari finally karena sudah ada del di try/except
        
        try:
            # Membersihkan file sementara
            if input_path and os.path.exists(input_path):
                os.remove(input_path)
            if wav_path and os.path.exists(wav_path):
                os.remove(wav_path)
        except Exception as clean_e:
            # Jika WinError 32 tetap terjadi di sini, itu karena sistem operasi belum sempat membersihkan handle.
            print(f"⚠️ Error cleaning up files: {clean_e}")
            pass

if __name__ == "__main__":
    print("🚀 Flask STT + Gemini Service Running at http://127.0.0.1:5000")
    app.run(debug=True, host="127.0.0.1", port=5000)