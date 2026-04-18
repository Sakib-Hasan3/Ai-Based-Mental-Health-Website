"""
Mental Health ML Model Prediction Server
Runs on port 5000 and handles predictions for the Mentora application
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import pickle
import numpy as np
import pandas as pd
import json
import os
import sys
import logging
from datetime import datetime

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('ml_server.log'),
        logging.StreamHandler(sys.stdout)
    ]
)
logger = logging.getLogger(__name__)

app = Flask(__name__)
CORS(app)

# ====================
# Model Loading
# ====================

# Determine paths
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
PROJECT_ROOT = os.path.dirname(BASE_DIR)  # Go up to mental health folder
ML_MODEL_DIR = os.path.join(PROJECT_ROOT, 'assets', 'ml_model')

MODEL_PATH = os.path.join(ML_MODEL_DIR, 'mental_health_model_optimized.pkl')
ENCODER_PATH = os.path.join(ML_MODEL_DIR, 'encoders.pkl')

logger.info(f"Base Directory: {BASE_DIR}")
logger.info(f"Project Root: {PROJECT_ROOT}")
logger.info(f"ML Model Dir: {ML_MODEL_DIR}")
logger.info(f"Model Path: {MODEL_PATH}")
logger.info(f"Encoder Path: {ENCODER_PATH}")

# Global variables for models
model = None
encoders = None

def load_models():
    """Load the trained model and encoders"""
    global model, encoders
    try:
        if not os.path.exists(MODEL_PATH):
            logger.error(f"Model file not found: {MODEL_PATH}")
            return False
        
        if not os.path.exists(ENCODER_PATH):
            logger.error(f"Encoder file not found: {ENCODER_PATH}")
            return False
        
        logger.info("🔄 Loading model and encoders...")
        
        with open(MODEL_PATH, 'rb') as f:
            model = pickle.load(f)
        logger.info(f"✅ Model loaded successfully from {MODEL_PATH}")
        
        with open(ENCODER_PATH, 'rb') as f:
            encoders = pickle.load(f)
        logger.info(f"✅ Encoders loaded successfully from {ENCODER_PATH}")
        
        logger.info(f"📊 Encoder keys: {list(encoders.keys())}")
        return True
        
    except FileNotFoundError as e:
        logger.error(f"❌ File not found: {e}")
        return False
    except pickle.UnpicklingError as e:
        logger.error(f"❌ Error unpickling file: {e}")
        return False
    except Exception as e:
        logger.error(f"❌ Unexpected error loading models: {e}")
        return False

# Feature order (must match training)
FEATURE_ORDER = [
    'Gender', 'Occupation', 'self_employed', 'family_history',
    'Days_Indoors', 'Growing_Stress', 'Changes_Habits',
    'Mental_Health_History', 'Mood_Swings', 'Coping_Struggles',
    'Work_Interest', 'Social_Weakness', 'mental_health_interview',
    'care_options'
]

# ====================
# Prediction Logic
# ====================

def encode_input(data):
    """Encode input data using saved encoders"""
    try:
        encoded_data = {}
        
        for feature in FEATURE_ORDER:
            if feature not in data:
                logger.warning(f"⚠️ Missing feature: {feature}")
                # Use default encoding (0)
                encoded_data[feature] = 0
                continue
            
            value = str(data[feature]).strip()
            
            # Find matching encoder
            encoder_key = None
            for key in encoders.keys():
                if key.lower() == feature.lower():
                    encoder_key = key
                    break
            
            if encoder_key is None:
                logger.warning(f"⚠️ No encoder found for {feature}")
                encoded_data[feature] = 0
                continue
            
            try:
                encoder = encoders[encoder_key]
                
                # Check if value is in encoder classes
                if value in encoder.classes_:
                    encoded_value = int(encoder.transform([value])[0])
                    encoded_data[feature] = encoded_value
                    logger.info(f"  ✓ {feature}: '{value}' → {encoded_value}")
                else:
                    # Use first class as default
                    default_value = encoder.classes_[0]
                    encoded_data[feature] = int(encoder.transform([default_value])[0])
                    logger.warning(
                        f"  ⚠️ {feature}: '{value}' not in encoder, "
                        f"using default '{default_value}'"
                    )
            except Exception as e:
                logger.error(f"  ❌ Error encoding {feature}: {e}")
                encoded_data[feature] = 0
        
        return encoded_data
    
    except Exception as e:
        logger.error(f"❌ Error in encode_input: {e}")
        return None

def make_prediction(features_array):
    """Make prediction using the loaded model"""
    try:
        if model is None:
            raise ValueError("Model not loaded")
        
        # Make prediction
        prediction = model.predict(features_array)[0]
        probabilities = model.predict_proba(features_array)[0]
        
        result = {
            'prediction': 'Treatment Recommended' if prediction == 1 else 'No Treatment Needed',
            'prediction_code': int(prediction),
            'risk_percentage': round(float(probabilities[1]) * 100, 2),
            'probability_no_treatment': round(float(probabilities[0]) * 100, 2),
            'probability_treatment': round(float(probabilities[1]) * 100, 2),
        }
        
        # Determine risk level
        if probabilities[1] > 0.6:
            result['risk_level'] = 'High'
        elif probabilities[1] > 0.3:
            result['risk_level'] = 'Moderate'
        else:
            result['risk_level'] = 'Low'
        
        # Get recommendation
        result['recommendation'] = get_recommendation(probabilities[1])
        
        logger.info(f"✅ Prediction made: {result['prediction']} ({result['risk_percentage']}%)")
        
        return result
    
    except Exception as e:
        logger.error(f"❌ Error making prediction: {e}")
        return None

def get_recommendation(risk_score):
    """Generate recommendation based on risk score"""
    if risk_score > 0.6:
        return {
            'status': 'high_risk',
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
            'status': 'moderate_risk',
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
            'status': 'low_risk',
            'message': '✅ আপনার মানসিক স্বাস্থ্য ভালো অবস্থায় আছে। সুস্থ থাকার অভ্যাস বজায় রাখুন।',
            'action': 'maintain',
            'tips': [
                'নিয়মিত মুড ট্র্যাক করুন',
                'সুস্থ খাদ্যাভ্যাস বজায় রাখুন',
                'সামাজিক যোগাযোগ বজায় রাখুন'
            ]
        }

# ====================
# API Routes
# ====================

@app.route('/api/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    status = {
        'status': 'healthy',
        'model_loaded': model is not None,
        'encoders_loaded': encoders is not None,
        'timestamp': datetime.now().isoformat()
    }
    
    if model is None or encoders is None:
        return jsonify(status), 503  # Service Unavailable
    
    return jsonify(status), 200

@app.route('/api/predict', methods=['POST', 'OPTIONS'])
def predict():
    """
    Mental Health Prediction API
    Accepts POST request with assessment data and returns prediction
    
    Expected JSON format:
    {
        "gender": "Male",
        "occupation": "Student",
        "self_employed": "No",
        "family_history": "Yes",
        "days_indoors": "1-14 days",
        "growing_stress": "Yes",
        "changes_habits": "No",
        "mental_health_history": "No",
        "mood_swings": "Medium",
        "coping_struggles": "No",
        "work_interest": "Yes",
        "social_weakness": "No",
        "mental_health_interview": "Maybe",
        "care_options": "Yes"
    }
    """
    
    logger.info(f"\n{'='*70}")
    logger.info(f"📨 REQUEST: {request.method} at {datetime.now()}")
    logger.info(f"Content-Type: {request.content_type}")
    logger.info(f"Remote Address: {request.remote_addr}")
    logger.info(f"{'='*70}")
    
    # Handle CORS preflight
    if request.method == 'OPTIONS':
        logger.info("✅ CORS preflight request")
        return jsonify({}), 200
    
    # Check if models are loaded
    if model is None or encoders is None:
        logger.error("❌ Models not loaded")
        return jsonify({
            'success': False,
            'error': 'Models are not loaded. Server may not be properly initialized.'
        }), 503
    
    try:
        # Get input data (JSON or form data)
        data = None
        
        if request.is_json:
            data = request.get_json(force=True, silent=True)
            logger.info(f"✅ JSON data received")
        elif request.form:
            data = request.form.to_dict()
            logger.info(f"✅ Form data received")
        else:
            # Try raw input
            raw_data = request.get_data(as_text=True)
            if raw_data:
                try:
                    data = json.loads(raw_data)
                    logger.info(f"✅ Raw JSON data received")
                except:
                    logger.warning("⚠️ Could not parse raw data as JSON")
        
        if not data:
            logger.error("❌ No data provided")
            return jsonify({'success': False, 'error': 'No data provided'}), 400
        
        # Convert keys to lowercase and clean
        data_clean = {}
        for key, value in data.items():
            data_clean[key.lower()] = str(value).strip()
        
        logger.info(f"📝 Input data: {list(data_clean.keys())}")
        
        # Encode input
        logger.info("🔄 Encoding input...")
        encoded_data = encode_input(data_clean)
        
        if encoded_data is None:
            logger.error("❌ Failed to encode input")
            return jsonify({'success': False, 'error': 'Failed to encode input'}), 400
        
        # Create feature array
        features = [encoded_data.get(feature, 0) for feature in FEATURE_ORDER]
        features_array = np.array([features])
        
        logger.info(f"📊 Feature array shape: {features_array.shape}")
        logger.info(f"📊 Feature values: {features}")
        
        # Make prediction
        logger.info("🤖 Making prediction...")
        result = make_prediction(features_array)
        
        if result is None:
            logger.error("❌ Prediction failed")
            return jsonify({'success': False, 'error': 'Prediction failed'}), 500
        
        # Return successful response
        response = {
            'success': True,
            'data': result,
            'timestamp': datetime.now().isoformat()
        }
        
        logger.info(f"✅ Response sent: {result['prediction']}")
        logger.info(f"{'='*70}\n")
        
        return jsonify(response), 200
    
    except Exception as e:
        logger.error(f"❌ Exception in predict: {str(e)}", exc_info=True)
        logger.info(f"{'='*70}\n")
        
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/info', methods=['GET'])
def info():
    """Get API and model information"""
    return jsonify({
        'name': 'Mental Health ML Prediction API',
        'version': '1.0',
        'description': 'Provides mental health assessment predictions',
        'features': FEATURE_ORDER,
        'model_loaded': model is not None,
        'encoders_loaded': encoders is not None,
        'timestamp': datetime.now().isoformat()
    }), 200

@app.route('/', methods=['GET'])
def index():
    """API documentation"""
    return jsonify({
        'message': 'Mental Health ML Prediction API',
        'version': '1.0',
        'endpoints': {
            'GET /': 'This documentation',
            'GET /api/health': 'Health check',
            'GET /api/info': 'API information',
            'POST /api/predict': 'Make a prediction',
        },
        'model_status': {
            'model_loaded': model is not None,
            'encoders_loaded': encoders is not None
        }
    }), 200

# ====================
# Error Handlers
# ====================

@app.errorhandler(404)
def not_found(error):
    logger.warning(f"⚠️ 404 Not Found: {request.path}")
    return jsonify({'error': 'Not found'}), 404

@app.errorhandler(500)
def internal_error(error):
    logger.error(f"❌ 500 Internal Server Error: {str(error)}")
    return jsonify({'error': 'Internal server error'}), 500

# ====================
# Main
# ====================

if __name__ == '__main__':
    logger.info("="*70)
    logger.info("🚀 Starting Mental Health ML Prediction Server")
    logger.info("="*70)
    
    # Load models on startup
    if not load_models():
        logger.error("❌ Failed to load models. Server will not function properly.")
        logger.error("Please check the model and encoder file paths.")
        sys.exit(1)
    
    logger.info("\n✅ Server initialization successful!")
    logger.info(f"📧 API will be available at: http://localhost:5000")
    logger.info(f"📧 Health check: http://localhost:5000/api/health")
    logger.info(f"📧 Documentation: http://localhost:5000/")
    logger.info("="*70 + "\n")
    
    # Start Flask server
    app.run(
        host='0.0.0.0',
        port=5000,
        debug=False,  # Set to True only for development
        use_reloader=False
    )
