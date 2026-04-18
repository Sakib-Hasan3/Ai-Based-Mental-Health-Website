#!/usr/bin/env python
# Direct model prediction script - can be called from PHP

import sys
import json
import joblib
import numpy as np
import os

# Add the directory to path
sys.path.insert(0, os.path.dirname(__file__))

try:
    # Load model and encoders
    MODEL_PATH = os.path.join(os.path.dirname(__file__), '..', 'assets', 'ml_model', 'mental_health_model_optimized.pkl')
    ENCODER_PATH = os.path.join(os.path.dirname(__file__), '..', 'assets', 'ml_model', 'encoders.pkl')
    
    model = joblib.load(MODEL_PATH)
    encoders = joblib.load(ENCODER_PATH)
    
    # Feature order
    FEATURE_ORDER = [
        'Gender_encoded', 'Occupation_encoded', 'self_employed_encoded',
        'family_history_encoded', 'Days_Indoors_encoded', 'Growing_Stress_encoded',
        'Changes_Habits_encoded', 'Mental_Health_History_encoded',
        'Mood_Swings_encoded', 'Coping_Struggles_encoded', 'Work_Interest_encoded',
        'Social_Weakness_encoded', 'mental_health_interview_encoded',
        'care_options_encoded'
    ]
    
    # Read input from command line argument (JSON string)
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'No input provided'}))
        sys.exit(1)
    
    input_data = json.loads(sys.argv[1])
    
    # Encode input
    encoded_input = {}
    for col, encoder in encoders.items():
        if col != 'treatment':
            # Map field names to encoder column names
            field_mapping = {
                'Gender': 'Gender',
                'gender': 'Gender',
                'Occupation': 'Occupation',
                'occupation': 'Occupation',
                'self_employed': 'self_employed',
                'family_history': 'family_history',
                'days_indoors': 'Days_Indoors',
                'Days_Indoors': 'Days_Indoors',
                'growing_stress': 'Growing_Stress',
                'Growing_Stress': 'Growing_Stress',
                'changes_habits': 'Changes_Habits',
                'Changes_Habits': 'Changes_Habits',
                'mental_health_history': 'Mental_Health_History',
                'Mental_Health_History': 'Mental_Health_History',
                'mood_swings': 'Mood_Swings',
                'Mood_Swings': 'Mood_Swings',
                'coping_struggles': 'Coping_Struggles',
                'Coping_Struggles': 'Coping_Struggles',
                'work_interest': 'Work_Interest',
                'Work_Interest': 'Work_Interest',
                'social_weakness': 'Social_Weakness',
                'Social_Weakness': 'Social_Weakness',
                'mental_health_interview': 'mental_health_interview',
                'care_options': 'care_options'
            }
            
            field_name = field_mapping.get(col, col)
            
            if field_name in input_data:
                try:
                    value = input_data[field_name]
                    encoded_input[col + '_encoded'] = int(encoder.transform([value])[0])
                except Exception as e:
                    # Use default value
                    default_value = encoder.classes_[0]
                    encoded_input[col + '_encoded'] = int(encoder.transform([default_value])[0])
            else:
                # Use default value
                default_value = encoder.classes_[0]
                encoded_input[col + '_encoded'] = int(encoder.transform([default_value])[0])
    
    # Create feature array
    features = []
    for feat in FEATURE_ORDER:
        features.append(encoded_input.get(feat, 0))
    
    features_array = np.array([features])
    
    # Make prediction
    prediction = model.predict(features_array)[0]
    probabilities = model.predict_proba(features_array)[0]
    
    # Prepare result
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
    
    print(json.dumps(result))

except Exception as e:
    print(json.dumps({'error': str(e)}))
    sys.exit(1)

def get_recommendation(risk_score):
    if risk_score > 0.6:
        return {
            'message': '⚠️ Your mental health has high risk. Please consult a specialist.',
            'action': 'consult_doctor'
        }
    elif risk_score > 0.3:
        return {
            'message': '🟡 Your mental health has moderate risk. Practice regular self-care.',
            'action': 'self_care'
        }
    else:
        return {
            'message': '✅ Your mental health is in good condition. Keep maintaining healthy habits.',
            'action': 'maintain'
        }
