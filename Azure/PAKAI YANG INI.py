import os
import uuid
import time
import ffmpeg
import azure.cognitiveservices.speech as speechsdk
from flask import Flask, render_template, request, jsonify

app = Flask(__name__)

SPEECH_KEY = ""
SPEECH_REGION = "eastasia"

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/stt', methods=['POST'])
def stt():
    try:
        # save file
        audio_file = request.files['audio']
        input_path = f"uploads/{uuid.uuid4()}.m4a"
        wav_path = f"uploads/{uuid.uuid4()}.wav"
        audio_file.save(input_path)

        # convert
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

        # proses convert
        for _ in range(10):
            if os.path.exists(wav_path) and os.path.getsize(wav_path) > 1000:
                break
            time.sleep(0.2)

        speech_config = speechsdk.SpeechConfig(subscription=SPEECH_KEY, region=SPEECH_REGION)
        speech_config.speech_recognition_language = "en-US"  # untuk tetap bahasa inggris
        audio_config = speechsdk.AudioConfig(filename=wav_path)
        recognizer = speechsdk.SpeechRecognizer(speech_config=speech_config, audio_config=audio_config)

        print("🎧 Recognizing speech...")
        stt_result = recognizer.recognize_once_async().get()

        if stt_result.reason != speechsdk.ResultReason.RecognizedSpeech:
            return jsonify({
                "error": "Speech not recognized",
                "details": str(stt_result.no_match_details)
            })

        recognized_text = stt_result.text.strip()

        print(f"🗣️ Assessing pronunciation for: {recognized_text}")

        pron_speech_config = speechsdk.SpeechConfig(subscription=SPEECH_KEY, region=SPEECH_REGION)
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

        try:
            os.remove(input_path)
            os.remove(wav_path)
        except Exception:
            pass

        return jsonify({
            "recognized_text": recognized_text,
            "accuracy_score": pron_assessment.accuracy_score,
            "fluency_score": pron_assessment.fluency_score,
            "completeness_score": pron_assessment.completeness_score,
            "pronunciation_score": pron_assessment.pronunciation_score
        })

    except Exception as e:
        return jsonify({"error": str(e)})

if __name__ == "__main__":
    os.makedirs("uploads", exist_ok=True)
    app.run(debug=True)
