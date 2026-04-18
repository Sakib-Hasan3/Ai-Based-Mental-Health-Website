# ML Server Architecture & Technical Documentation

## System Architecture

### High-Level Architecture

```
┌────────────────────────────────────────────────────────────────┐
│                   CLIENT TIER (Browser)                        │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │  assessment.php (JavaScript/jQuery)                       │ │
│  │  - Form validation                                        │ │
│  │  - AJAX submission to predict.php                        │ │
│  └──────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────┘
                              │ (HTTP POST)
                              │ JSON payload
                              ↓
┌────────────────────────────────────────────────────────────────┐
│                  SERVER TIER (Apache/PHP)                      │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │  predict.php                                              │ │
│  │  - Session validation                                     │ │
│  │  - Data extraction & mapping                              │ │
│  │  - Error handling                                         │ │
│  │  - Fallback logic                                         │ │
│  └──────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────┘
                              │ (HTTP POST JSON)
                              │ cURL request
                              ↓
┌────────────────────────────────────────────────────────────────┐
│            ML SERVER TIER (Python/Flask - Port 5000)           │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │  ml_server.py (Flask Application)                        │ │
│  │                                                           │ │
│  │  1. Request Handler (Flask Routes)                       │ │
│  │     - /api/predict (main endpoint)                       │ │
│  │     - /api/health (status check)                         │ │
│  │     - /api/info (documentation)                          │ │
│  │                                                           │ │
│  │  2. Data Processing Pipeline                            │ │
│  │     - Input validation                                   │ │
│  │     - Feature encoding (LabelEncoder)                    │ │
│  │     - Feature ordering                                   │ │
│  │     - Array conversion                                   │ │
│  │                                                           │ │
│  │  3. Model Inference                                      │ │
│  │     - Prediction generation                              │ │
│  │     - Probability calculation                            │ │
│  │     - Risk scoring                                       │ │
│  │                                                           │ │
│  │  4. Post-Processing                                      │ │
│  │     - Risk level determination                           │ │
│  │     - Recommendation generation                          │ │
│  │     - Response formatting                                │ │
│  │                                                           │ │
│  │  5. Logging & Monitoring                                │ │
│  │     - Console logging                                    │ │
│  │     - File logging (ml_server.log)                       │ │
│  │     - Request/response tracking                          │ │
│  └──────────────────────────────────────────────────────────┘ │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │  ML Model & Feature Management                           │ │
│  │                                                           │ │
│  │  - mental_health_model_optimized.pkl                     │ │
│  │    └─ Trained classifier (Random Forest/XGBoost/etc)    │ │
│  │                                                           │ │
│  │  - encoders.pkl                                          │ │
│  │    └─ LabelEncoders for 14 categorical features         │ │
│  │                                                           │ │
│  │  - FEATURE_ORDER (14 features)                           │ │
│  │    1. Gender                                             │ │
│  │    2. Occupation                                         │ │
│  │    3. self_employed                                      │ │
│  │    4. family_history                                     │ │
│  │    5. Days_Indoors                                       │ │
│  │    6. Growing_Stress                                     │ │
│  │    7. Changes_Habits                                     │ │
│  │    8. Mental_Health_History                              │ │
│  │    9. Mood_Swings                                        │ │
│  │   10. Coping_Struggles                                   │ │
│  │   11. Work_Interest                                      │ │
│  │   12. Social_Weakness                                    │ │
│  │   13. mental_health_interview                            │ │
│  │   14. care_options                                       │ │
│  └──────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────┘
                              │ (HTTP 200 + JSON)
                              │ prediction response
                              ↓
┌────────────────────────────────────────────────────────────────┐
│                  SERVER TIER (Apache/PHP)                      │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │  predict.php (Response Handler)                          │ │
│  │  - Response parsing                                      │ │
│  │  - Error handling                                        │ │
│  │  - JSON formatting                                       │ │
│  │  - Fallback activation (if needed)                       │ │
│  └──────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────┘
                              │ (JSON Response)
                              │ HTTP 200 + result
                              ↓
┌────────────────────────────────────────────────────────────────┐
│                   CLIENT TIER (Browser)                        │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │  assessment.php (JavaScript/jQuery)                       │ │
│  │  - Response parsing                                      │ │
│  │  - Result visualization                                  │ │
│  │  - Gauge chart display                                   │ │
│  │  - Recommendations display                               │ │
│  │  - Scroll to results                                     │ │
│  └──────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────┘
```

---

## Data Flow

### Input Data

**From Frontend (assessment.php)**
```json
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
```

### Processing Steps in ml_server.py

1. **Receive Request**
   - Extract JSON/form data from HTTP request
   - Validate content type

2. **Clean Data**
   - Convert keys to lowercase
   - Trim whitespace
   - Handle missing values

3. **Encode Features**
   ```python
   # Input: "Male" for Gender
   # Lookup in encoders['Gender']
   # Output: 0 or 1 (integer representation)
   ```

4. **Create Feature Array**
   ```python
   # [Gender_encoded, Occupation_encoded, ..., care_options_encoded]
   # Shape: (1, 14) - 1 sample, 14 features
   ```

5. **Make Prediction**
   ```python
   # prediction = model.predict(features_array)[0]
   # probabilities = model.predict_proba(features_array)[0]
   # Result: (0 or 1), [prob_class_0, prob_class_1]
   ```

6. **Calculate Risk Score**
   ```python
   risk_percentage = probabilities[1] * 100
   # 0% = No treatment needed
   # 100% = Definitely needs treatment
   ```

7. **Determine Risk Level**
   ```python
   if risk_percentage > 60:
       risk_level = "High"
   elif risk_percentage > 30:
       risk_level = "Moderate"
   else:
       risk_level = "Low"
   ```

8. **Generate Recommendation**
   - Personalized message (Bengali)
   - Action suggestions
   - Helpful tips
   - Helpline number for high-risk cases

9. **Format Response**
   ```json
   {
     "success": true,
     "data": {
       "prediction": "Treatment Recommended",
       "risk_percentage": 75.5,
       "risk_level": "High",
       "recommendation": {...},
       ...
     },
     "timestamp": "2024-04-18T10:30:00.123456"
   }
   ```

### Output Data

**To Frontend (JavaScript)**
```javascript
{
  success: true,
  data: {
    prediction: "Treatment Recommended",
    prediction_code: 1,
    risk_percentage: 75.5,
    probability_no_treatment: 24.5,
    probability_treatment: 75.5,
    risk_level: "High",
    recommendation: {
      status: "high_risk",
      message: "⚠️ আপনার মানসিক স্বাস্থ্যের ঝুঁকি বেশি...",
      helpline: "01977-855055",
      action: "consult_doctor",
      tips: [...]
    }
  }
}
```

---

## File Descriptions

### ml_server.py (Main Server)

**Size**: ~300 lines
**Purpose**: Flask-based ML prediction server

**Key Functions**:
- `load_models()` - Load .pkl files on startup
- `encode_input(data)` - Convert string features to integers
- `make_prediction(features_array)` - Run model inference
- `get_recommendation(risk_score)` - Generate personalized advice
- Route handlers: `/api/predict`, `/api/health`, `/api/info`

**Dependencies**:
- Flask
- Flask-CORS
- NumPy
- Pandas
- Pickle (standard library)

### predict.php (PHP Endpoint)

**Size**: ~200 lines
**Purpose**: Bridge between frontend and ML server

**Key Functions**:
- Session validation
- `callFlaskAPI($input)` - Sends request to ml_server.py using cURL
- `calculateFallbackRisk($input)` - Fallback if ML server unavailable
- Error handling and logging

**Fallback Logic**:
- If ML server fails to respond → uses rule-based calculation
- Point system based on domain knowledge
- Returns similar format as ML predictions

### assessment.php (Frontend)

**Size**: ~1000+ lines (HTML + CSS + JavaScript)
**Purpose**: User interface for mental health assessment

**JavaScript Functions**:
- Form validation
- AJAX submission to predict.php
- Response handling
- Results visualization (gauge charts, recommendations)
- Error handling

---

## Model Information

### Model File: mental_health_model_optimized.pkl

- **Type**: Machine Learning Classifier
- **Input**: 14 categorical features (encoded as integers)
- **Output**: Binary classification (0 or 1) + probabilities
- **Size**: Typically 1-5 MB
- **Possible Algorithms**:
  - Random Forest
  - XGBoost
  - Gradient Boosting
  - Logistic Regression
  - Ensemble methods

### Encoder File: encoders.pkl

- **Type**: Dictionary of LabelEncoders
- **Format**: `{feature_name: LabelEncoder_object}`
- **14 Encoders** for:
  1. Gender: Male, Female
  2. Occupation: Corporate, Business, Student, Housewife, Others
  3. self_employed: Yes, No
  4. family_history: Yes, No
  5. Days_Indoors: Multiple categories
  6. Growing_Stress: Yes, No, Maybe
  7. Changes_Habits: Yes, No, Maybe
  8. Mental_Health_History: Yes, No, Maybe
  9. Mood_Swings: Low, Medium, High
  10. Coping_Struggles: Yes, No
  11. Work_Interest: Yes, No, Maybe
  12. Social_Weakness: Yes, No, Maybe
  13. mental_health_interview: Yes, No, Maybe
  14. care_options: Yes, No, Not sure

---

## Request-Response Examples

### Successful Prediction

**Request (HTTP POST to localhost:5000/api/predict)**
```json
{
  "Gender": "Female",
  "Occupation": "Student",
  "self_employed": "No",
  "family_history": "No",
  "Days_Indoors": "1-14 days",
  "Growing_Stress": "Maybe",
  "Changes_Habits": "Maybe",
  "Mental_Health_History": "No",
  "Mood_Swings": "Low",
  "Coping_Struggles": "No",
  "Work_Interest": "Yes",
  "Social_Weakness": "No",
  "mental_health_interview": "No",
  "care_options": "Yes"
}
```

**Response (HTTP 200)**
```json
{
  "success": true,
  "data": {
    "prediction": "No Treatment Needed",
    "prediction_code": 0,
    "risk_percentage": 15.3,
    "probability_no_treatment": 84.7,
    "probability_treatment": 15.3,
    "risk_level": "Low",
    "recommendation": {
      "status": "low_risk",
      "message": "✅ আপনার মানসিক স্বাস্থ্য ভালো অবস্থায় আছে...",
      "action": "maintain",
      "tips": [...]
    }
  },
  "timestamp": "2024-04-18T10:30:15.123456"
}
```

### Error Responses

**Models Not Loaded (HTTP 503)**
```json
{
  "success": false,
  "error": "Models are not loaded. Server may not be properly initialized."
}
```

**No Data Provided (HTTP 400)**
```json
{
  "success": false,
  "error": "No data provided"
}
```

**Server Error (HTTP 500)**
```json
{
  "success": false,
  "error": "Detailed error message here"
}
```

---

## Feature Importance (Expected)

Based on domain knowledge, these features likely have highest impact:

1. **Mood_Swings** - High/Medium indicates instability
2. **Coping_Struggles** - Inability to cope is critical
3. **family_history** - Genetic predisposition matters
4. **Mental_Health_History** - Past issues predict future risk
5. **Growing_Stress** - Current stress level
6. **Days_Indoors** - Isolation is concerning
7. **Work_Interest** - Loss of interest is warning sign

---

## Logging

### Server Log File: ml_server.log

```
2024-04-18 10:30:15,123 - INFO - ======================================================================
2024-04-18 10:30:15,145 - INFO - 📨 REQUEST: POST at 2024-04-18 10:30:15.145000
2024-04-18 10:30:15,167 - INFO - Content-Type: application/json
2024-04-18 10:30:15,189 - INFO - ======================================================================
2024-04-18 10:30:15,210 - INFO - ✅ JSON data received
2024-04-18 10:30:15,232 - INFO - 📝 Input data: ['gender', 'occupation', ...]
2024-04-18 10:30:15,254 - INFO - 🔄 Encoding input...
2024-04-18 10:30:15,276 - INFO -   ✓ Gender: 'Male' → 0
2024-04-18 10:30:15,298 - INFO -   ✓ Occupation: 'Student' → 2
2024-04-18 10:30:15,320 - INFO - 📊 Feature array shape: (1, 14)
2024-04-18 10:30:15,342 - INFO - 📊 Feature values: [0, 2, 0, 0, 1, 1, 0, 0, 1, 0, 1, 0, 1, 1]
2024-04-18 10:30:15,364 - INFO - 🤖 Making prediction...
2024-04-18 10:30:15,386 - INFO - ✅ Prediction made: No Treatment Needed (15.3%)
2024-04-18 10:30:15,408 - INFO - ✅ Response sent: No Treatment Needed
2024-04-18 10:30:15,430 - INFO - ======================================================================
```

---

## Performance Characteristics

### Typical Metrics

- **Request-to-Response Time**: 50-200ms
- **Model Inference Time**: 5-10ms
- **Encoding Time**: 10-20ms
- **Memory Usage**: 50-150 MB per instance
- **Maximum Concurrent Users** (single instance): 10-20
- **CPU Usage**: Minimal (<5%) when idle

### Bottlenecks

1. **Model Loading** (on startup): 1-2 seconds
2. **Network Latency**: PHP → Python communication
3. **Encoding Overhead**: String-to-integer conversion

### Optimization Opportunities

1. **Cache encoded values** - Store frequently used encodings
2. **Batch predictions** - Process multiple requests together
3. **Load balancing** - Run multiple ml_server instances
4. **Use faster frameworks** - FastAPI instead of Flask
5. **Compile model** - Use ONNX or TensorFlow Lite

---

## Security Considerations

### Current Implementation

- ✅ Session validation in PHP (user must be logged in)
- ✅ Input validation in ml_server.py
- ✅ CORS headers set appropriately
- ⚠️ No API key authentication (open to localhost)

### Recommended Additions

1. Add API key authentication between PHP and Python
2. Rate limiting to prevent abuse
3. Input size limits
4. SQL injection prevention (already using prepared statements)
5. HTTPS/TLS for production (if server exposed)

---

## Scaling Strategy

### For Small Deployments (< 100 users)
- Single ml_server instance on port 5000
- Adequate for concurrent usage

### For Medium Deployments (100-1000 users)
- Multiple instances on ports 5000, 5001, 5002
- Load balancer (HAProxy/Nginx) distributing requests
- Uptime monitoring

### For Large Deployments (> 1000 users)
- Production WSGI server (Gunicorn/uWSGI)
- Multiple worker processes
- Caching layer (Redis)
- Database logging of predictions
- Model serving framework (TensorFlow Serving/Seldon Core)

---

## Troubleshooting Guide

### Server Won't Start

**Check**:
1. Is Python installed? `python --version`
2. Are dependencies installed? `pip list`
3. Are model files present? `ls assets/ml_model/*.pkl`
4. Is port 5000 free? `netstat -ano | findstr :5000`

**Fix**:
```bash
pip install -r requirements.txt
python ml_server.py
```

### Slow Predictions

**Causes**:
- Large model file (> 500MB)
- Complex encoding operations
- Network latency

**Solutions**:
- Check model file size
- Optimize encoders
- Use faster Python version
- Run server on same machine as Apache

### Encoding Errors

**Cause**: Unknown value for a categorical feature

**Solution**: Add that value to encoder during training or handle gracefully (use default value)

---

## References

- [Flask Documentation](https://flask.palletsprojects.com/)
- [Scikit-learn LabelEncoder](https://scikit-learn.org/stable/modules/generated/sklearn.preprocessing.LabelEncoder.html)
- [NumPy Array Documentation](https://numpy.org/doc/stable/reference/arrays.html)
- [Python pickle Module](https://docs.python.org/3/library/pickle.html)

---

**Document Version**: 1.0
**Last Updated**: April 18, 2024
**Author**: Mentora Development Team
