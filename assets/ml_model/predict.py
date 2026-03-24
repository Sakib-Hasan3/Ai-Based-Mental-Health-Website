# assets/ml_model/predict.py
import sys
import json
import pickle
import numpy as np
import pandas as pd
import os
import warnings
warnings.filterwarnings('ignore')

# Get the directory where this script is located
script_dir = os.path.dirname(os.path.abspath(__file__))

def load_models():
    """Load the trained model and encoders"""
    try:
        # Load the optimized model
        model_path = os.path.join(script_dir, 'mental_health_model_optimized.pkl')
        encoders_path = os.path.join(script_dir, 'encoders.pkl')
        
        print(f"Loading model from: {model_path}", file=sys.stderr)
        print(f"Loading encoders from: {encoders_path}", file=sys.stderr)
        
        with open(model_path, 'rb') as f:
            model = pickle.load(f)
        
        with open(encoders_path, 'rb') as f:
            encoders = pickle.load(f)
        
        print("✅ Model and encoders loaded successfully!", file=sys.stderr)
        return model, encoders
        
    except FileNotFoundError as e:
        print(f"❌ File not found: {e}", file=sys.stderr)
        return None, None
    except Exception as e:
        print(f"❌ Error loading models: {e}", file=sys.stderr)
        return None, None

def preprocess_input(data, encoders):
    """Preprocess input data for prediction"""
    
    # Create DataFrame with the input data
    df = pd.DataFrame([data])
    
    # Define feature order (must match training order)
    # These are the 14 features used in the model
    features = [
        'Gender', 'Occupation', 'self_employed', 'family_history',
        'Days_Indoors', 'Growing_Stress', 'Changes_Habits',
        'Mental_Health_History', 'Mood_Swings', 'Coping_Struggles',
        'Work_Interest', 'Social_Weakness', 'mental_health_interview',
        'care_options'
    ]
    
    # Ensure all features are present
    for feature in features:
        if feature not in df.columns:
            df[feature] = ''
    
    # Encode categorical variables using the saved encoders
    for feature in features:
        if feature in encoders:
            try:
                # Get the value and encode it
                value = str(df[feature].iloc[0])
                # Check if value exists in encoder classes
                if value in encoders[feature].classes_:
                    df[feature] = encoders[feature].transform([value])[0]
                else:
                    # Use the most common value (first class) as fallback
                    df[feature] = 0
                    print(f"⚠️ Unknown value '{value}' for {feature}, using default", file=sys.stderr)
            except Exception as e:
                print(f"⚠️ Error encoding {feature}: {e}", file=sys.stderr)
                df[feature] = 0
        else:
            print(f"⚠️ No encoder found for {feature}", file=sys.stderr)
            df[feature] = 0
    
    # Return only the features in correct order
    return df[features].values

def get_feature_importance(model, feature_names, n=5):
    """Extract top important features from the model"""
    
    importances = None
    
    # Check model type and extract feature importance
    if hasattr(model, 'feature_importances_'):
        # For RandomForest, XGBoost, etc.
        importances = model.feature_importances_
    elif hasattr(model, 'coef_'):
        # For Logistic Regression, SVM, etc.
        importances = np.abs(model.coef_[0])
    elif hasattr(model, 'estimators_'):
        # For VotingClassifier or Ensemble
        # Average feature importance across all estimators
        all_importances = []
        for est in model.estimators_:
            if hasattr(est, 'feature_importances_'):
                all_importances.append(est.feature_importances_)
            elif hasattr(est, 'coef_'):
                all_importances.append(np.abs(est.coef_[0]))
        
        if all_importances:
            importances = np.mean(all_importances, axis=0)
    
    # If still no importances, use default values
    if importances is None:
        # Default importance based on domain knowledge
        default_importance = {
            'family_history': 0.25,
            'Mood_Swings': 0.20,
            'Coping_Struggles': 0.18,
            'Growing_Stress': 0.15,
            'Days_Indoors': 0.12,
            'Mental_Health_History': 0.10
        }
        
        top_features = []
        for name, imp in default_importance.items():
            if name in feature_names:
                top_features.append({
                    'name': name,
                    'importance': imp
                })
        return sorted(top_features, key=lambda x: x['importance'], reverse=True)[:n]
    
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
    try:
        with open(input_file, 'r', encoding='utf-8') as f:
            data = json.load(f)
        print(f"✅ Input data loaded: {data}", file=sys.stderr)
    except Exception as e:
        print(json.dumps({'error': f'Failed to read input file: {e}'}))
        return
    
    # Load models
    model, encoders = load_models()
    
    # If model loading failed, use rule-based fallback
    if model is None or encoders is None:
        print("⚠️ Using fallback prediction", file=sys.stderr)
        
        # Simple rule-based calculation
        score = 0
        risk_factors = {
            'family_history': {'Yes': 25, 'No': 0},
            'Mental_Health_History': {'Yes': 30, 'Maybe': 10, 'No': 0},
            'Mood_Swings': {'High': 35, 'Medium': 15, 'Low': 0},
            'Coping_Struggles': {'Yes': 30, 'No': 0},
            'Growing_Stress': {'Yes': 25, 'Maybe': 10, 'No': 0},
            'Days_Indoors': {'More than 2 months': 25, '31-60 days': 15, '15-30 days': 10, '1-14 days': 5, 'Go out Every day': 0},
            'Work_Interest': {'No': 20, 'Maybe': 8, 'Yes': 0},
            'Social_Weakness': {'Yes': 20, 'Maybe': 5, 'No': 0}
        }
        
        for field, points in risk_factors.items():
            value = data.get(field, '')
            if value in points:
                score += points[value]
        
        score = min(100, score)
        prediction = 'Yes' if score >= 50 else 'No'
        
        result = {
            'prediction': prediction,
            'probability': float(score),
            'top_factors': [
                {'name': 'family_history', 'importance': 0.25},
                {'name': 'Mood_Swings', 'importance': 0.20},
                {'name': 'Coping_Struggles', 'importance': 0.18},
                {'name': 'Growing_Stress', 'importance': 0.15},
                {'name': 'Days_Indoors', 'importance': 0.12}
            ]
        }
        print(json.dumps(result))
        return
    
    try:
        # Preprocess input
        features = preprocess_input(data, encoders)
        print(f"✅ Features shape: {features.shape}", file=sys.stderr)
        print(f"✅ Features: {features[0]}", file=sys.stderr)
        
        # Make prediction
        prediction = model.predict(features)[0]
        probabilities = model.predict_proba(features)[0]
        
        # Get probability of positive class (Yes)
        prob_positive = probabilities[1] * 100
        
        print(f"✅ Prediction: {prediction}", file=sys.stderr)
        print(f"✅ Probability: {prob_positive:.2f}%", file=sys.stderr)
        
        # Get feature names
        feature_names = [
            'Gender', 'Occupation', 'self_employed', 'family_history',
            'Days_Indoors', 'Growing_Stress', 'Changes_Habits',
            'Mental_Health_History', 'Mood_Swings', 'Coping_Struggles',
            'Work_Interest', 'Social_Weakness', 'mental_health_interview',
            'care_options'
        ]
        
        # Get top features
        top_features = get_feature_importance(model, feature_names)
        
        # Prepare result
        result = {
            'prediction': 'Yes' if prediction == 1 else 'No',
            'probability': float(prob_positive),
            'top_factors': top_features
        }
        
        print(json.dumps(result))
        
    except Exception as e:
        print(json.dumps({'error': str(e)}), file=sys.stderr)
        print(f"❌ Error: {e}", file=sys.stderr)

if __name__ == '__main__':
    main()