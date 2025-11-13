import os
import uuid
import time
import ffmpeg
import azure.cognitiveservices.speech as speechsdk
from flask import Flask, request, jsonify

app = Flask(__name__)

SPEECH_REGION = "eastasia"
SPEECH_KEY = "CRfSRjia2fWRkBB6zZJg2znS7acIrc9m7CVssmeZFg6H8kofbYnyJQQJ99BJAC3pKaRXJ3w3AAAYACOGXAUq"

@app.route("/health", methods=["GET"])
def health():
    """
    Endpoint sederhana untuk ngecek service jalan.
    """
    if not SPEECH_KEY:
        return jsonify({"status": "error", "message": "SPEECH_KEY not set"}), 500

    return jsonify({"status": "ok"}), 200

@app.route('/stt', methods=['POST'])
def stt():
    try:
        if not SPEECH_KEY:
            return jsonify({"error": "SPEECH_KEY not configured"}), 500

        # pastikan ada file
        if 'audio' not in request.files:
            return jsonify({"error": "No 'audio' file in request"}), 400

        # save file upload
        audio_file = request.files['audio']
        input_path = f"uploads/{uuid.uuid4()}.m4a"
        wav_path = f"uploads/{uuid.uuid4()}.wav"
        audio_file.save(input_path)

        # convert ke WAV
        (
            ffmpeg
            .input(input_path)
            .output(
                wav_path,
                format='wav',
                acodec='pcm_s16le',
                ac=1,
                ar='16000'
            )
            .overwrite_output()
            .run(quiet=True)
        )

        # tunggu file hasil convert siap
        for _ in range(10):
            if os.path.exists(wav_path) and os.path.getsize(wav_path) > 1000:
                break
            time.sleep(0.2)

        # --- Speech to Text ---
        speech_config = speechsdk.SpeechConfig(
            subscription=SPEECH_KEY,
            region=SPEECH_REGION
        )
        speech_config.speech_recognition_language = "en-US"
        audio_config = speechsdk.AudioConfig(filename=wav_path)
        recognizer = speechsdk.SpeechRecognizer(
            speech_config=speech_config,
            audio_config=audio_config
        )

        print("🎧 Recognizing speech...")
        stt_result = recognizer.recognize_once_async().get()

        if stt_result.reason != speechsdk.ResultReason.RecognizedSpeech:
            try:
                os.remove(input_path)
                os.remove(wav_path)
            except Exception:
                pass

            return jsonify({
                "error": "Speech not recognized",
                "details": str(stt_result.no_match_details)
            }), 400

        recognized_text = stt_result.text.strip()
        print(f"🗣️ Assessing pronunciation for: {recognized_text}")

        # --- Pronunciation Assessment ---
        pron_speech_config = speechsdk.SpeechConfig(
            subscription=SPEECH_KEY,
            region=SPEECH_REGION
        )
        pron_audio_config = speechsdk.AudioConfig(filename=wav_path)
        pron_recognizer = speechsdk.SpeechRecognizer(
            speech_config=pron_speech_config,
            audio_config=pron_audio_config
        )

        pron_config = speechsdk.PronunciationAssessmentConfig(
            reference_text=recognized_text,
            grading_system=speechsdk.PronunciationAssessmentGradingSystem.HundredMark,
            granularity=speechsdk.PronunciationAssessmentGranularity.Phoneme,
            enable_miscue=True
        )
        pron_config.apply_to(pron_recognizer)

        pron_result = pron_recognizer.recognize_once_async().get()
        pron_assessment = speechsdk.PronunciationAssessmentResult(pron_result)

        # bersihin file sementara
        try:
            os.remove(input_path)
            os.remove(wav_path)
        except Exception:
            pass

        # respon JSON ke Laravel
        return jsonify({
            "recognized_text": recognized_text,
            "accuracy_score": pron_assessment.accuracy_score,
            "fluency_score": pron_assessment.fluency_score,
            "completeness_score": pron_assessment.completeness_score,
            "pronunciation_score": pron_assessment.pronunciation_score
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 500


if __name__ == "__main__":
    os.makedirs("uploads", exist_ok=True)
    print("🚀 Starting Flask server on http://127.0.0.1:5000")
    app.run(debug=True, host="127.0.0.1", port=5000)
