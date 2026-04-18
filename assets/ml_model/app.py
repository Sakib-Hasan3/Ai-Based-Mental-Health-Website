"""
Mental Health Prediction - Flask Backend
Run: python app.py
API runs at: http://localhost:5000
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import numpy as np
import warnings
import os

warnings.filterwarnings('ignore')

app = Flask(__name__)
CORS(app)  # Allow PHP frontend at localhost to call this API

# ── Load models from same folder as app.py ───────────────────────────────────
BASE = os.path.dirname(os.path.abspath(__file__))

encoders = joblib.load(os.path.join(BASE, "encoders.pkl"))
model    = joblib.load(os.path.join(BASE, "mental_health_model.pkl"))

print("✅ Model loaded | Features:", model.n_features_in_, "| Classes:", model.classes_)

# ── Feature order MUST match training order ───────────────────────────────────
FEATURE_ORDER = [
    "Gender",
    "Occupation",
    "self_employed",
    "family_history",
    "Days_Indoors",
    "Growing_Stress",
    "Changes_Habits",
    "Mental_Health_History",
    "Mood_Swings",
    "Coping_Struggles",
    "Work_Interest",
    "Social_Weakness",
    "mental_health_interview",
    "care_options",
]

def encode(feature_name: str, raw_value: str) -> int:
    """Encode a single categorical value using saved LabelEncoder."""
    if feature_name not in encoders:
        return 0
    enc = encoders[feature_name]
    try:
        return int(enc.transform([raw_value])[0])
    except Exception:
        # Unseen label → return most common class (0)
        return 0


# ── Routes ────────────────────────────────────────────────────────────────────

@app.route("/", methods=["GET"])
def home():
    return jsonify({
        "status":  "running",
        "message": "Mental Health API is live 🟢",
        "routes":  ["/predict (POST)", "/options (GET)"]
    })


@app.route("/options", methods=["GET"])
def options():
    """Return all valid choices for each feature — used to build the form."""
    result = {}
    for key, enc in encoders.items():
        result[key] = [str(c) for c in enc.classes_]
    return jsonify(result)


@app.route("/predict", methods=["POST"])
def predict():
    try:
        data = request.get_json(force=True)
        if not data:
            return jsonify({"success": False, "error": "No JSON received"}), 400

        # Build feature vector in correct order
        features = []
        for feat in FEATURE_ORDER:
            raw = data.get(feat, "")
            features.append(encode(feat, str(raw)))

        X = np.array(features, dtype=float).reshape(1, -1)

        # Predict
        pred   = int(model.predict(X)[0])          # 0 or 1
        proba  = model.predict_proba(X)[0]
        conf   = round(float(np.max(proba)) * 100, 1)

        # Build human-readable result
        if pred == 1:
            result = {
                "needs_treatment": True,
                "label_en":  "Needs Mental Health Support",
                "label_bn":  "মানসিক স্বাস্থ্য সহায়তা প্রয়োজন",
                "color":     "danger",
                "icon":      "⚠️",
                "message":   "আপনার মানসিক স্বাস্থ্যের দিকে মনোযোগ দেওয়া দরকার। একজন মানসিক স্বাস্থ্য বিশেষজ্ঞের সাথে পরামর্শ করুন।",
                "advice": [
                    "একজন থেরাপিস্ট বা মনোরোগ বিশেষজ্ঞের সাথে কথা বলুন",
                    "পরিবার বা বিশ্বস্ত বন্ধুর সাথে মনের কথা শেয়ার করুন",
                    "নিয়মিত হালকা ব্যায়াম ও পর্যাপ্ত ঘুমের অভ্যাস করুন",
                    "সামাজিক যোগাযোগ বজায় রাখুন — একা থাকা এড়িয়ে চলুন",
                    "প্রয়োজনে জাতীয় মানসিক স্বাস্থ্য হেল্পলাইনে (16789) কল করুন",
                ]
            }
        else:
            result = {
                "needs_treatment": False,
                "label_en":  "Mental Health Looks Fine",
                "label_bn":  "মানসিক স্বাস্থ্য স্বাভাবিক",
                "color":     "success",
                "icon":      "✅",
                "message":   "আপনার মানসিক স্বাস্থ্য এখন ভালো আছে। ভবিষ্যতেও সুস্থ থাকতে নিচের পরামর্শগুলো মেনে চলুন।",
                "advice": [
                    "নিয়মিত ব্যায়াম ও পর্যাপ্ত ঘুম (৭-৮ ঘণ্টা) বজায় রাখুন",
                    "পরিবার ও বন্ধুদের সাথে সময় কাটান",
                    "মানসিক চাপ তৈরি হলে দ্রুত সাহায্য নিন",
                    "নিজেকে ভালোবাসুন ও শখের কাজে সময় দিন",
                ]
            }

        return jsonify({
            "success":    True,
            "prediction": pred,
            "confidence": conf,
            **result
        })

    except Exception as e:
        return jsonify({"success": False, "error": str(e)}), 500


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True)