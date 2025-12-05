import os
import uuid
import time
import ffmpeg
import azure.cognitiveservices.speech as speechsdk
from flask import Flask, request, jsonify
import google.generativeai as genai

SPEECH_REGION = "eastasia"   
SPEECH_KEY = "5tg9JYtb5Y9rQ0ZD8EQeo2WSIezzEy9tFdSxcfUA9leZLvTpnQCJJQQJ99BJAC3pKaRXJ3w3AAAYACOGMPJl"
GEMINI_API_KEY = "AIzaSyBAnu4lNfQGUABcB9NF4ZVIL86qE0LYRmk"

# Konfigurasi Gemini API
genai.configure(api_key=GEMINI_API_KEY)

app = Flask(__name__)

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
Penjelasan CEFR: <jelaskan alasan level berdasarkan grammar, vocabulary, fluency, dan coherence dalam 2-3 kalimat>

1. Accuracy:
   <penjelasan singkat 1-2 kalimat>

2. Fluency:
   <penjelasan singkat 1-2 kalimat>

3. Completeness:
   <penjelasan singkat 1-2 kalimat>

4. Pronunciation:
   <penjelasan singkat 1-2 kalimat>

Saran Perbaikan:
- <saran singkat 1 kalimat>
- <saran singkat 1 kalimat>
- <saran singkat 1 kalimat>

Catatan Penting:
- Tidak boleh ada markdown.
- Tidak boleh ada simbol seperti #, *, |.
- Format harus persis seperti contoh di atas.
- Bahasa Indonesia yang jelas dan mudah dimengerti mahasiswa.
"""

    response = model.generate_content(prompt)
    return response.text
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
Penjelasan CEFR: <jelaskan alasan level berdasarkan grammar, vocabulary, fluency, dan coherence dalam 2-3 kalimat>

1. Accuracy:
   <penjelasan singkat 1-2 kalimat>

2. Fluency:
   <penjelasan singkat 1-2 kalimat>

3. Completeness:
   <penjelasan singkat 1-2 kalimat>

4. Pronunciation:
   <penjelasan singkat 1-2 kalimat>

Saran Perbaikan:
- <saran singkat 1 kalimat>
- <saran singkat 1 kalimat>
- <saran singkat 1 kalimat>

Catatan Penting:
- Tidak boleh ada markdown.
- Tidak boleh ada simbol seperti #, *, |.
- Format harus persis seperti contoh di atas.
- Bahasa Indonesia yang jelas dan mudah dimengerti mahasiswa.
"""

    response = model.generate_content(prompt)
    return response.text

@app.route("/health", methods=["GET"])
def health():
    if not SPEECH_KEY:
        return jsonify({"status": "error", "message": "SPEECH_KEY not configured"}), 500
    if not GEMINI_API_KEY:
        return jsonify({"status": "error", "message": "GEMINI_API_KEY not configured"}), 500

    return jsonify({"status": "ok"})

@app.route("/stt", methods=["POST"])
def stt_route():
    try:
        if "audio" not in request.files:
            return jsonify({"error": "audio not found"}), 400

        audio_file = request.files["audio"]

        os.makedirs("uploads", exist_ok=True)

        input_path = f"uploads/{uuid.uuid4()}.m4a"
        wav_path = f"uploads/{uuid.uuid4()}.wav"

        audio_file.save(input_path)

        ffmpeg.input(input_path).output(
            wav_path,
            format="wav",
            acodec="pcm_s16le",
            ac=1,
            ar="16000"
        ).overwrite_output().run(quiet=True)

        time.sleep(0.2)

        speech_config = speechsdk.SpeechConfig(
            subscription=SPEECH_KEY,
            region=SPEECH_REGION
        )
        speech_config.speech_recognition_language = "en-US"

        recognizer = speechsdk.SpeechRecognizer(
            speech_config=speech_config,
            audio_config=speechsdk.AudioConfig(filename=wav_path)
        )

        print("🎧 Recognizing speech...")
        stt_result = recognizer.recognize_once_async().get()

        print("STT Reason:", stt_result.reason)

        if stt_result.reason == speechsdk.ResultReason.RecognizedSpeech:
            recognized_text = stt_result.text.strip()
            print("✅ Recognized:", recognized_text)

        elif stt_result.reason == speechsdk.ResultReason.NoMatch:
            print("❌ No speech recognized")
            return jsonify({"error": "No speech recognized"}), 400

        elif stt_result.reason == speechsdk.ResultReason.Canceled:
            details = stt_result.cancellation_details
            print("❌ Canceled:", details.reason, details.error_details)
            return jsonify({
                "error": "Speech canceled",
                "details": details.error_details
            }), 500

        else:
            print("❌ Unknown STT error")
            return jsonify({"error": "Unknown STT error"}), 500

        pron_config = speechsdk.PronunciationAssessmentConfig(
            reference_text=recognized_text,
            grading_system=speechsdk.PronunciationAssessmentGradingSystem.HundredMark,
            granularity=speechsdk.PronunciationAssessmentGranularity.Phoneme,
            enable_miscue=True
        )

        pron_recognizer = speechsdk.SpeechRecognizer(
            speech_config=speech_config,
            audio_config=speechsdk.AudioConfig(filename=wav_path)
        )

        pron_config.apply_to(pron_recognizer)

        pron_result = pron_recognizer.recognize_once_async().get()
        pron_assessment = speechsdk.PronunciationAssessmentResult(pron_result)

        print("🎯 Scores:",
              pron_assessment.accuracy_score,
              pron_assessment.fluency_score,
              pron_assessment.completeness_score,
              pron_assessment.pronunciation_score)

        gemini_feedback = generate_gemini_feedback(
            recognized_text,
            pron_assessment.accuracy_score,
            pron_assessment.fluency_score,
            pron_assessment.completeness_score,
            pron_assessment.pronunciation_score
        )

        return jsonify({
            "recognized_text": recognized_text,
            "accuracy_score": pron_assessment.accuracy_score,
            "fluency_score": pron_assessment.fluency_score,
            "completeness_score": pron_assessment.completeness_score,
            "pronunciation_score": pron_assessment.pronunciation_score,
            "gpt_feedback": gemini_feedback
        })

    except Exception as e:
        print("🔥 Exception:", e)
        return jsonify({"error": str(e)}), 500

    finally:
        try:
            os.remove(input_path)
            os.remove(wav_path)
        except:
            pass

if __name__ == "__main__":
    print("🚀 Flask STT + Gemini Service Running at http://127.0.0.1:5000")
    app.run(debug=True, host="127.0.0.1", port=5000)
