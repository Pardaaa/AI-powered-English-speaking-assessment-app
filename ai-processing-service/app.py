import os
import uuid
import shutil
import subprocess
import time
from urllib.parse import urlparse

import ffmpeg
import azure.cognitiveservices.speech as speechsdk
from flask import Flask, request, jsonify, send_from_directory
import google.generativeai as genai

# =========================
# HARD CONFIG (TANPA ENV)
# =========================
SPEECH_REGION = "eastasia"
SPEECH_KEY = "5tg9JYtb5Y9rQ0ZD8EQeo2WSIezzEy9tFdSxcfUA9leZLvTpnQCJJQQJ99BJAC3pKaRXJ3w3AAAYACOGMPJl"
GEMINI_API_KEY = "AIzaSyAIPHWeFLRg_3RgAuaVzoFS3TXGPFY306I"

UPLOAD_DIR = "uploads"
TMP_DIR = "tmp"
CHUNK_SECONDS = 20  # internal segmentation supaya stabil

app = Flask(__name__)
os.makedirs(UPLOAD_DIR, exist_ok=True)
os.makedirs(TMP_DIR, exist_ok=True)


# =========================
# UTIL
# =========================
def safe_error(message: str, status: int = 500, **extra):
    payload = {"error": message, **extra}
    return jsonify(payload), status


def ensure_speech_key():
    if not SPEECH_KEY or not str(SPEECH_KEY).strip():
        return False, "SPEECH_KEY not configured"
    return True, ""


def configure_gemini_optional():
    """Gemini optional: kalau key kosong/invalid/kuota habis, skip tanpa bikin endpoint gagal."""
    if not GEMINI_API_KEY or not str(GEMINI_API_KEY).strip():
        return False, "GEMINI_API_KEY not configured (skip feedback)"
    try:
        genai.configure(api_key=str(GEMINI_API_KEY).strip())
        return True, ""
    except Exception as e:
        return False, f"GEMINI configure failed: {str(e)}"


def convert_to_wav(input_path: str) -> str:
    """Convert audio/video -> wav 16khz mono pcm."""
    wav_path = os.path.join(TMP_DIR, f"{uuid.uuid4()}.wav")
    (
        ffmpeg
        .input(input_path)
        .output(
            wav_path,
            format="wav",
            acodec="pcm_s16le",
            ac=1,
            ar="16000",
            af="aresample=async=1"
        )
        .overwrite_output()
        .run(quiet=True)
    )
    return wav_path


def split_wav(wav_path: str, out_dir: str, chunk_seconds: int):
    """Split wav into segments (re-encode each) biar reliable."""
    os.makedirs(out_dir, exist_ok=True)
    out_pattern = os.path.join(out_dir, "seg_%03d.wav")

    cmd = [
        "ffmpeg", "-y",
        "-i", wav_path,
        "-vn",
        "-acodec", "pcm_s16le",
        "-ar", "16000",
        "-ac", "1",
        "-f", "segment",
        "-segment_time", str(chunk_seconds),
        "-reset_timestamps", "1",
        out_pattern
    ]
    subprocess.run(cmd, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, check=True)

    files = sorted(
        os.path.join(out_dir, f) for f in os.listdir(out_dir)
        if f.lower().endswith(".wav")
    )
    return files


def build_speech_config():
    speech_config = speechsdk.SpeechConfig(subscription=SPEECH_KEY, region=SPEECH_REGION)
    speech_config.speech_recognition_language = "en-US"
    # biar cepat selesai kalau hening
    speech_config.set_property(
        speechsdk.PropertyId.SpeechServiceConnection_EndSilenceTimeoutMs, "2000"
    )
    return speech_config


def recognize_text_continuous(speech_config, wav_file: str) -> str:
    """
    Continuous STT untuk satu segmen.
    Tidak print per-segmen. (kamu maunya output final saja)
    """
    audio_input = speechsdk.AudioConfig(filename=wav_file)
    recognizer = speechsdk.SpeechRecognizer(speech_config=speech_config, audio_config=audio_input)

    parts = []
    done = False
    err = None

    def recognized_cb(evt):
        nonlocal parts
        if evt.result.reason == speechsdk.ResultReason.RecognizedSpeech:
            t = (evt.result.text or "").strip()
            if t:
                parts.append(t)

    def canceled_cb(evt):
        nonlocal done, err
        try:
            cd = evt.result.cancellation_details
            if cd and getattr(cd, "error_details", None):
                err = f"{cd.reason} | {cd.error_details}"
            else:
                err = "canceled"
        except Exception as e:
            err = f"canceled (no details): {e}"
        done = True

    def stopped_cb(evt):
        nonlocal done
        done = True

    recognizer.recognized.connect(recognized_cb)
    recognizer.canceled.connect(canceled_cb)
    recognizer.session_stopped.connect(stopped_cb)

    recognizer.start_continuous_recognition()

    start = time.time()
    while not done:
        time.sleep(0.05)
        # safety: max 90s per segment
        if time.time() - start > 90:
            break

    recognizer.stop_continuous_recognition()

    text = " ".join(parts).strip()
    if not text and err:
        raise RuntimeError(f"STT canceled: {err}")
    return text


def get_wav_duration_seconds(wav_file: str) -> float:
    cmd = [
        "ffprobe", "-v", "error",
        "-show_entries", "format=duration",
        "-of", "default=noprint_wrappers=1:nokey=1",
        wav_file
    ]
    out = subprocess.check_output(cmd).decode().strip()
    try:
        return float(out)
    except:
        return 0.0


def pronunciation_assess_once(speech_config, wav_file: str, reference_text: str):
    """Pronunciation assessment 1 segmen (unscripted: pakai recognized text)."""
    if not reference_text:
        return None

    audio_input = speechsdk.AudioConfig(filename=wav_file)
    recognizer = speechsdk.SpeechRecognizer(speech_config=speech_config, audio_config=audio_input)

    pron_config = speechsdk.PronunciationAssessmentConfig(
        reference_text=reference_text,
        grading_system=speechsdk.PronunciationAssessmentGradingSystem.HundredMark,
        granularity=speechsdk.PronunciationAssessmentGranularity.Word,
        enable_miscue=True
    )
    pron_config.apply_to(recognizer)

    res = recognizer.recognize_once_async().get()
    if res.reason != speechsdk.ResultReason.RecognizedSpeech:
        return None

    pa = speechsdk.PronunciationAssessmentResult(res)
    return {
        "accuracy_score": float(pa.accuracy_score or 0.0),
        "fluency_score": float(pa.fluency_score or 0.0),
        "completeness_score": float(pa.completeness_score or 0.0),
        "pronunciation_score": float(pa.pronunciation_score or 0.0),
    }


def generate_gemini_feedback_optional(transcript, accuracy, fluency, completeness, pronunciation):
    """
    Gemini OPTIONAL: pakai prompt kamu yang rapi (tanpa markdown).
    Boleh pakai gemini-flash-latest.
    Kalau quota/404/error => return None (endpoint tetap sukses).
    """
    ok, msg = configure_gemini_optional()
    if not ok:
        print("[Gemini] SKIP:", msg, flush=True)
        return None

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
""".strip()

    try:
        model = genai.GenerativeModel("gemini-flash-latest")
        resp = model.generate_content(prompt)
        text = (getattr(resp, "text", "") or "").strip()
        if not text:
            print("[Gemini] empty response -> skip", flush=True)
            return None
        return text
    except Exception as e:
        print("[Gemini] ERROR:", str(e), flush=True)
        return None


# =========================
# ROUTES
# =========================
@app.route("/health", methods=["GET"])
def health():
    ok, msg = ensure_speech_key()
    if not ok:
        return safe_error(msg, 500)
    gem_ok, gem_msg = configure_gemini_optional()
    return jsonify({
        "status": "ok",
        "speech": "ok",
        "gemini": "ok" if gem_ok else "skip",
        "gemini_note": "" if gem_ok else gem_msg
    })


@app.route("/uploads/<path:filename>")
def serve_upload(filename):
    return send_from_directory(UPLOAD_DIR, filename, as_attachment=False)


@app.route("/stt_by_url", methods=["POST"])
def stt_by_url():
    ok, msg = ensure_speech_key()
    if not ok:
        return safe_error(msg, 500)

    seg_dir = None
    wav_path = None

    try:
        data = request.get_json(silent=True) or {}
        file_url = (data.get("file_url") or "").strip()
        if not file_url:
            return safe_error("file_url required", 400)

        parsed = urlparse(file_url)
        filename = os.path.basename(parsed.path)
        if not filename:
            return safe_error("invalid file_url", 400)

        input_path = os.path.join(UPLOAD_DIR, filename)
        if not os.path.exists(input_path):
            return safe_error("file not found on server", 404, filename=filename, expected_path=input_path)

        # 1) convert
        wav_path = convert_to_wav(input_path)

        # 2) split
        seg_dir = os.path.join(TMP_DIR, f"seg_{uuid.uuid4()}")
        seg_files = split_wav(wav_path, seg_dir, CHUNK_SECONDS)
        if not seg_files:
            return safe_error("failed to split audio", 500)

        speech_config = build_speech_config()

        transcript_parts = []
        total_weight = 0.0
        sum_acc = sum_flu = sum_com = sum_pro = 0.0

        # 3) process segments (NO per-segment print)
        for seg in seg_files:
            seg_dur = get_wav_duration_seconds(seg) or float(CHUNK_SECONDS)

            text = recognize_text_continuous(speech_config, seg)
            if text:
                transcript_parts.append(text)

            pa = pronunciation_assess_once(speech_config, seg, text)
            if pa:
                total_weight += seg_dur
                sum_acc += pa["accuracy_score"] * seg_dur
                sum_flu += pa["fluency_score"] * seg_dur
                sum_com += pa["completeness_score"] * seg_dur
                sum_pro += pa["pronunciation_score"] * seg_dur

        recognized_text = " ".join(transcript_parts).strip()
        if not recognized_text:
            return safe_error("No speech recognized", 400)

        # scores weighted by duration
        if total_weight <= 0:
            scores = {
                "accuracy_score": 0.0,
                "fluency_score": 0.0,
                "completeness_score": 0.0,
                "pronunciation_score": 0.0,
            }
        else:
            scores = {
                "accuracy_score": round(sum_acc / total_weight, 2),
                "fluency_score": round(sum_flu / total_weight, 2),
                "completeness_score": round(sum_com / total_weight, 2),
                "pronunciation_score": round(sum_pro / total_weight, 2),
            }

        # ✅ PRINT FINAL TEXT SAJA (yang kamu minta)
        print("\n=========== FINAL STT ===========\n", flush=True)
        print(recognized_text, flush=True)
        print("\n===============================\n", flush=True)

        # 4) gemini optional
        gpt_feedback = generate_gemini_feedback_optional(
            recognized_text,
            scores["accuracy_score"],
            scores["fluency_score"],
            scores["completeness_score"],
            scores["pronunciation_score"],
        )

        if gpt_feedback:
            print("\n=========== GEMINI FEEDBACK ===========\n", flush=True)
            print(gpt_feedback, flush=True)
            print("\n=====================================\n", flush=True)

        payload = {
            "recognized_text": recognized_text,
            **scores,
        }
        if gpt_feedback:
            payload["gpt_feedback"] = gpt_feedback

        return jsonify(payload)

    except subprocess.CalledProcessError:
        return safe_error("ffmpeg/ffprobe failed (check ffmpeg installed and file has audio)", 500)

    except Exception as e:
        return safe_error("Internal error", 500, details=str(e))

    finally:
        try:
            if wav_path and os.path.exists(wav_path):
                os.remove(wav_path)
        except:
            pass
        try:
            if seg_dir and os.path.isdir(seg_dir):
                shutil.rmtree(seg_dir, ignore_errors=True)
        except:
            pass


if __name__ == "__main__":
    print("🚀 Flask STT + Gemini Service Running at http://127.0.0.1:5000", flush=True)
    app.run(debug=True, host="127.0.0.1", port=5000)