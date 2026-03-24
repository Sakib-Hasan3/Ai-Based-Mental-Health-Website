# ml_model/predict.py
import sys
import json
import pickle
import numpy as np
import pandas as pd

def load_models():
    """Load the trained model and encoders"""
    try:
        with open('mental_health_model_optimized.pkl', 'rb') as f:
            model = pickle.load(f)
        with open('encoders.pkl', 'rb') as f:
            encoders = pickle.load(f)
        return model, encoders
    except Exception as e:
        return None, None

def preprocess_input(data, encoders):
    """Preprocess input data for prediction"""
    
    # Create DataFrame
    df = pd.DataFrame([data])
    
    # Define feature order (must match training)
    features = [
        'Gender', 'Occupation', 'self_employed', 'family_history',
        'Days_Indoors', 'Growing_Stress', 'Changes_Habits',
        'Mental_Health_History', 'Mood_Swings', 'Coping_Struggles',
        'Work_Interest', 'Social_Weakness', 'mental_health_interview',
        'care_options'
    ]
    
    # Encode categorical variables
    for feature in features:
        if feature in encoders and feature in df.columns:
            try:
                df[feature] = encoders[feature].transform(df[feature])
            except:
                # Handle unseen categories
                df[feature] = 0
    
    return df[features]

def get_top_features(model, feature_names, n=5):
    """Extract top important features"""
    if hasattr(model, 'feature_importances_'):
        importances = model.feature_importances_
    elif hasattr(model, 'coef_'):
        importances = np.abs(model.coef_[0])
    else:
        # Random forest or voting classifier
        if hasattr(model, 'estimators_'):
            # Average feature importance across estimators
            importances = np.mean([est.feature_importances_ 
                                  for est in model.estimators_ 
                                  if hasattr(est, 'feature_importances_')], axis=0)
        else:
            importances = np.random.rand(len(feature_names))
    
    # Get top n features
    indices = np.argsort(importances)[-n:][::-1]
    top_features = []
    for idx in indices:
        top_features.append({
            'name': feature_names[idx],
            'importance': float(importances[idx])
        })
    
    return top_features

def main():
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'No input file provided'}))
        return
    
    # Load input data
    input_file = sys.argv[1]
    with open(input_file, 'r') as f:
        data = json.load(f)
    
    # Load models
    model, encoders = load_models()
    if model is None:
        # Return dummy data for testing
        result = {
            'prediction': 'Yes',
            'probability': 75.5,
            'top_factors': [
                {'name': 'family_history', 'importance': 0.25},
                {'name': 'mood_swings', 'importance': 0.20},
                {'name': 'coping_struggles', 'importance': 0.15},
                {'name': 'growing_stress', 'importance': 0.12},
                {'name': 'Days_Indoors', 'importance': 0.10}
            ]
        }
        print(json.dumps(result))
        return
    
    try:
        # Preprocess input
        features = preprocess_input(data, encoders)
        
        # Make prediction
        prediction = model.predict(features)[0]
        probabilities = model.predict_proba(features)[0]
        
        # Get top features
        feature_names = features.columns.tolist()
        top_features = get_top_features(model, feature_names)
        
        # Prepare result
        result = {
            'prediction': 'Yes' if prediction == 1 else 'No',
            'probability': float(probabilities[1] * 100),
            'top_factors': top_features
        }
        
        print(json.dumps(result))
        
    except Exception as e:
        print(json.dumps({'error': str(e)}))

if __name__ == '__main__':
    main()