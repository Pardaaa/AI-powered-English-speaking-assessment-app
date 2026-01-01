import os
import uuid
import ffmpeg
import azure.cognitiveservices.speech as speechsdk
from flask import Flask, request, jsonify
import google.generativeai as genai
import threading

# --- KONFIGURASI (PAKAI ENV VAR DI PRODUKSI) ---
SPEECH_REGION = os.getenv("SPEECH_REGION", "eastasia")
SPEECH_KEY = os.getenv("5tg9JYtb5Y9rQ0ZD8EQeo2WSIezzEy9tFdSxcfUA9leZLvTpnQCJJQQJ99BJAC3pKaRXJ3w3AAAYACOGMPJl", "")
GEMINI_API_KEY = os.getenv("AIzaSyCm4et9o-g5NX5Cn7xrmAB5aZej-eHEpewAIzaSyCm4et9o-g5NX5Cn7xrmAB5aZej-eHEpew", "")

genai.configure(api_key=GEMINI_API_KEY)
app = Flask(__name__)

def generate_gemini_feedback(transcript, accuracy, fluency, completeness, pronunciation):
    model = genai.GenerativeModel("gemini-flash-latest")
    prompt = f"""
Tolong analisis hasil speaking berikut menggunakan CEFR (A1–C2).

TRANSKRIP:
\"\"\"{transcript}\"\"\"

SKOR:
Accuracy: {accuracy:.2f}
Fluency: {fluency:.2f}
Completeness: {completeness:.2f}
Pronunciation: {pronunciation:.2f}

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
    return model.generate_content(prompt).text

def convert_to_wav_16k_mono(input_path: str, wav_path: str):
    # lebih aman untuk speech: 16k mono PCM
    (
        ffmpeg
        .input(input_path)
        .output(
            wav_path,
            format="wav",
            acodec="pcm_s16le",
            ac=1,
            ar="16000"
        )
        .overwrite_output()
        .run(quiet=True)
    )

def continuous_stt(speech_config, wav_path: str):
    audio_input = speechsdk.AudioConfig(filename=wav_path)
    recognizer = speechsdk.SpeechRecognizer(speech_config=speech_config, audio_config=audio_input)

    done = threading.Event()
    stt_error = {"is_error": False, "reason": None}
    segments = []

    def recognized_cb(evt):
        if evt.result.reason == speechsdk.ResultReason.RecognizedSpeech and evt.result.text:
            segments.append(evt.result.text)

    def canceled_cb(evt):
        # end of stream itu normal
        details = evt.result.cancellation_details
        if details and details.reason != speechsdk.CancellationReason.EndOfStream:
            stt_error["is_error"] = True
            stt_error["reason"] = f"{details.reason} | {details.error_details}"
        done.set()

    def session_stopped_cb(evt):
        done.set()

    recognizer.recognized.connect(recognized_cb)
    recognizer.canceled.connect(canceled_cb)
    recognizer.session_stopped.connect(session_stopped_cb)

    recognizer.start_continuous_recognition()
    done.wait()
    recognizer.stop_continuous_recognition()

    text = " ".join(segments).strip()
    return text, stt_error

def continuous_pronunciation_assessment(speech_config, wav_path: str, reference_text: str):
    """
    Pronunciation Assessment continuous untuk audio panjang.
    Skor di-average berbasis jumlah kata (lebih fair).
    """
    audio_input = speechsdk.AudioConfig(filename=wav_path)
    recognizer = speechsdk.SpeechRecognizer(speech_config=speech_config, audio_config=audio_input)

    # Untuk stabilitas: Word granularity dulu.
    # enable_miscue paling cocok kalau reference text memang "target reading".
    pron_config = speechsdk.PronunciationAssessmentConfig(
        reference_text=reference_text,
        grading_system=speechsdk.PronunciationAssessmentGradingSystem.HundredMark,
        granularity=speechsdk.PronunciationAssessmentGranularity.Word,
        enable_miscue=False
    )
    pron_config.apply_to(recognizer)

    done = threading.Event()
    pa_error = {"is_error": False, "reason": None}

    # Akumulasi skor berbobot kata
    total_words = 0
    sum_acc = 0.0
    sum_flu = 0.0
    sum_com = 0.0
    sum_pro = 0.0

    # optionally kumpulin word details untuk debugging
    word_details_all = []

    def recognized_cb(evt):
        nonlocal total_words, sum_acc, sum_flu, sum_com, sum_pro
        if evt.result.reason == speechsdk.ResultReason.RecognizedSpeech:
            pa_res = speechsdk.PronunciationAssessmentResult(evt.result)

            # Ambil jumlah kata dari JSON detail (lebih akurat daripada split text biasa)
            j = evt.result.properties.get_property(speechsdk.PropertyId.SpeechServiceResponse_JsonResult)
            # fallback kalau json kosong
            word_count = 0

            try:
                import json
                data = json.loads(j)
                nbest = data.get("NBest", [])
                if nbest:
                    words = nbest[0].get("Words", []) or []
                    word_count = len(words)
                    # simpan word details (optional)
                    for w in words:
                        word_details_all.append({
                            "word": w.get("Word"),
                            "accuracy": w.get("PronunciationAssessment", {}).get("AccuracyScore"),
                            "errorType": w.get("PronunciationAssessment", {}).get("ErrorType")
                        })
            except Exception:
                word_count = max(1, len(evt.result.text.split()))

            if word_count <= 0:
                return

            total_words += word_count
            sum_acc += pa_res.accuracy_score * word_count
            sum_flu += pa_res.fluency_score * word_count
            sum_com += pa_res.completeness_score * word_count
            sum_pro += pa_res.pronunciation_score * word_count

    def canceled_cb(evt):
        details = evt.result.cancellation_details
        if details and details.reason != speechsdk.CancellationReason.EndOfStream:
            pa_error["is_error"] = True
            pa_error["reason"] = f"{details.reason} | {details.error_details}"
        done.set()

    def session_stopped_cb(evt):
        done.set()

    recognizer.recognized.connect(recognized_cb)
    recognizer.canceled.connect(canceled_cb)
    recognizer.session_stopped.connect(session_stopped_cb)

    recognizer.start_continuous_recognition()
    done.wait()
    recognizer.stop_continuous_recognition()

    if pa_error["is_error"]:
        return None, pa_error, word_details_all

    if total_words == 0:
        return None, {"is_error": True, "reason": "No speech recognized in PA"}, word_details_all

    scores = {
        "accuracy_score": sum_acc / total_words,
        "fluency_score": sum_flu / total_words,
        "completeness_score": sum_com / total_words,
        "pronunciation_score": sum_pro / total_words,
    }
    return scores, pa_error, word_details_all

@app.route("/stt", methods=["POST"])
def stt_route():
    input_path = None
    wav_path = None
    try:
        if not SPEECH_KEY:
            return jsonify({"error": "SPEECH_KEY not configured"}), 500

        if "audio" not in request.files:
            return jsonify({"error": "audio not found"}), 400

        # OPTIONAL: kalau kamu punya reading prompt dari frontend, kirim sebagai form field "reference_text"
        # Kalau tidak ada, kita fallback ke hasil STT (tapi ini bukan setup ideal untuk PA).
        reference_text_from_client = (request.form.get("reference_text") or "").strip()

        audio_file = request.files["audio"]
        os.makedirs("uploads", exist_ok=True)

        input_path = f"uploads/{uuid.uuid4()}.m4a"
        wav_path = f"uploads/{uuid.uuid4()}.wav"
        audio_file.save(input_path)

        convert_to_wav_16k_mono(input_path, wav_path)

        speech_config = speechsdk.SpeechConfig(subscription=SPEECH_KEY, region=SPEECH_REGION)
        speech_config.speech_recognition_language = "en-US"

        # 1) STT continuous
        recognized_text, stt_error = continuous_stt(speech_config, wav_path)
        if stt_error["is_error"]:
            return jsonify({"error": "STT canceled", "details": stt_error["reason"]}), 500
        if not recognized_text:
            return jsonify({"error": "No speech recognized"}), 400

        # 2) Reference text terbaik:
        # - Reading test: pakai reference_text dari client (WAJIB supaya skor meaningful)
        # - Spontaneous: Azure PA bukan yang paling tepat; tapi fallback pakai recognized_text biar tetap jalan
        reference_text = reference_text_from_client if reference_text_from_client else recognized_text

        # 3) PA continuous (lebih stabil daripada recognize_once)
        pa_scores, pa_error, word_details = continuous_pronunciation_assessment(speech_config, wav_path, reference_text)
        if pa_error["is_error"] or not pa_scores:
            return jsonify({"error": "Pronunciation Assessment failed", "details": pa_error["reason"]}), 500

        gemini_feedback = generate_gemini_feedback(
            recognized_text,
            pa_scores["accuracy_score"],
            pa_scores["fluency_score"],
            pa_scores["completeness_score"],
            pa_scores["pronunciation_score"]
        )

        return jsonify({
            "recognized_text": recognized_text,
            "reference_text_used": reference_text,
            **pa_scores,
            "word_details_sample": word_details[:30],  # sample biar ga kebanyakan
            "gpt_feedback": gemini_feedback
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 500
    finally:
        try:
            if input_path and os.path.exists(input_path):
                os.remove(input_path)
            if wav_path and os.path.exists(wav_path):
                os.remove(wav_path)
        except Exception:
            pass

if __name__ == "__main__":
    app.run(debug=True, host="127.0.0.1", port=5000)
