from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import pandas as pd
import numpy as np
import json
import os

app = Flask(__name__)
CORS(app)  # PHP থেকে API কল করার অনুমতি

# মডেল এবং এনকোডার লোড করুন
MODEL_PATH = os.path.join(os.path.dirname(__file__), '..', 'assets', 'ml_model', 'mental_health_model_optimized.pkl')
ENCODER_PATH = os.path.join(os.path.dirname(__file__), '..', 'assets', 'ml_model', 'encoders.pkl')

print("🔄 Loading model and encoders...")
model = joblib.load(MODEL_PATH)
encoders = joblib.load(ENCODER_PATH)
print("✅ Model and encoders loaded successfully!")

# ফিচারের ক্রম (Training এর সময় যে অর্ডার ছিল)
FEATURE_ORDER = [
    'Gender_encoded', 'Occupation_encoded', 'self_employed_encoded',
    'family_history_encoded', 'Days_Indoors_encoded', 'Growing_Stress_encoded',
    'Changes_Habits_encoded', 'Mental_Health_History_encoded',
    'Mood_Swings_encoded', 'Coping_Struggles_encoded', 'Work_Interest_encoded',
    'Social_Weakness_encoded', 'mental_health_interview_encoded',
    'care_options_encoded'
]

@app.route('/api/predict', methods=['POST'])
def predict():
    """
    Mental Health Prediction API
    Accepts POST request with user data and returns prediction
    """
    try:
        # JSON ডেটা পান
        data = request.get_json()
        
        if not data:
            return jsonify({'error': 'No data provided'}), 400
        
        # এনকোডিং করুন
        encoded_input = {}
        for col, encoder in encoders.items():
            if col != 'treatment' and col in data:
                try:
                    value = data[col]
                    encoded_input[col + '_encoded'] = int(encoder.transform([value])[0])
                except Exception as e:
                    # যদি ভ্যালু না পাওয়া যায়, ডিফল্ট ব্যবহার করুন
                    default_value = encoder.classes_[0]
                    encoded_input[col + '_encoded'] = int(encoder.transform([default_value])[0])
        
        # ফিচার অ্যারে তৈরি করুন
        features = []
        for feat in FEATURE_ORDER:
            features.append(encoded_input.get(feat, 0))
        
        features_array = np.array([features])
        
        # প্রেডিকশন করুন
        prediction = model.predict(features_array)[0]
        probabilities = model.predict_proba(features_array)[0]
        
        # ফলাফল প্রস্তুত করুন
        result = {
            'success': True,
            'prediction': 'Treatment Recommended' if prediction == 1 else 'No Treatment Needed',
            'prediction_code': int(prediction),
            'risk_percentage': round(float(probabilities[1]) * 100, 2),
            'probability_no_treatment': round(float(probabilities[0]) * 100, 2),
            'probability_treatment': round(float(probabilities[1]) * 100, 2),
            'risk_level': 'High' if probabilities[1] > 0.6 else 'Moderate' if probabilities[1] > 0.3 else 'Low',
            'recommendation': get_recommendation(probabilities[1])
        }
        
        return jsonify(result)
        
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

def get_recommendation(risk_score):
    """Risk score based recommendation"""
    if risk_score > 0.6:
        return {
            'message': '⚠️ আপনার মানসিক স্বাস্থ্যের ঝুঁকি বেশি। দয়া করে একজন বিশেষজ্ঞের সাথে কথা বলুন।',
            'helpline': '01977-855055',
            'action': 'consult_doctor',
            'tips': [
                'নিয়মিত ঘুমান',
                'পরিবার ও বন্ধুদের সাথে কথা বলুন',
                'প্রফেশনাল হেল্প নিন'
            ]
        }
    elif risk_score > 0.3:
        return {
            'message': '🟡 আপনার মানসিক স্বাস্থ্যের মাঝারি ঝুঁকি রয়েছে। নিয়মিত সেলফ-কেয়ার অনুশীলন করুন।',
            'action': 'self_care',
            'tips': [
                'নিয়মিত ব্যায়াম করুন',
                'মেডিটেশন করুন',
                'জার্নাল লিখুন',
                'মনের বন্ধু চ্যাটবট ব্যবহার করুন'
            ]
        }
    else:
        return {
            'message': '✅ আপনার মানসিক স্বাস্থ্য ভালো অবস্থায় আছে। সুস্থ থাকার অভ্যাস বজায় রাখুন।',
            'action': 'maintain',
            'tips': [
                'নিয়মিত মুড ট্র্যাক করুন',
                'সুস্থ খাদ্যাভ্যাস বজায় রাখুন',
                'সামাজিক যোগাযোগ বজায় রাখুন'
            ]
        }

@app.route('/api/health', methods=['GET'])
def health_check():
    """API health check endpoint"""
    return jsonify({'status': 'healthy', 'model_loaded': model is not None})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)