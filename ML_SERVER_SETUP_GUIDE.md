# Mental Health ML Prediction Server Setup Guide

## Overview

This guide will help you set up and run the Mental Health ML Prediction Server, which processes mental health assessment data using trained machine learning models.

### What is the ML Server?

The ML Server is a Python-based backend service that:
- Loads your trained `.pkl` model files (encoders, model predictions)
- Receives assessment data from your PHP frontend
- Uses machine learning to predict mental health risk levels
- Returns risk scores and recommendations to users

### System Components

```
┌─────────────────────────────────────────────────────────────┐
│                   Browser (Frontend)                         │
│              (assessment.php form)                           │
└────────────────────┬────────────────────────────────────────┘
                     │ Assessment Data (JSON)
                     ↓
┌─────────────────────────────────────────────────────────────┐
│          Apache/PHP Backend (predict.php)                   │
│    └─ Validates input                                       │
│    └─ Sends to ML Server                                    │
└────────────────────┬────────────────────────────────────────┘
                     │ HTTP POST to localhost:5000
                     ↓
┌─────────────────────────────────────────────────────────────┐
│         ML Prediction Server (ml_server.py)                 │
│    ✓ Loads: mental_health_model_optimized.pkl              │
│    ✓ Loads: encoders.pkl                                    │
│    ✓ Encodes input features                                │
│    ✓ Makes predictions                                      │
│    ✓ Generates recommendations                             │
└────────────────────┬────────────────────────────────────────┘
                     │ Response (JSON with prediction)
                     ↓
┌─────────────────────────────────────────────────────────────┐
│         Apache/PHP Backend (predict.php)                    │
│    └─ Receives & formats response                           │
└────────────────────┬────────────────────────────────────────┘
                     │ JSON Response
                     ↓
┌─────────────────────────────────────────────────────────────┐
│              Browser (Shows Results)                        │
│         Risk Score, Recommendations, etc.                   │
└─────────────────────────────────────────────────────────────┘
```

---

## Prerequisites

### System Requirements
- Windows 7 or later
- Python 3.7 or higher (Python 3.9+ recommended)
- At least 2GB RAM
- Internet connection (for first-time package installation)

### Install Python

1. **Download Python** from https://www.python.org/downloads/
   - Choose Python 3.9, 3.10, or 3.11 (latest versions)
   
2. **Install Python**
   - ✅ **IMPORTANT**: Check the box "Add Python to PATH" during installation
   - Complete the installation

3. **Verify Installation**
   - Open Command Prompt (cmd) or PowerShell
   - Type: `python --version`
   - You should see: `Python 3.X.X`

---

## Installation Steps

### Step 1: Navigate to Project Directory

**Option A: Using Command Prompt (cmd)**
```bash
cd C:\xampp\htdocs\mental health
```

**Option B: Using PowerShell**
```powershell
cd "C:\xampp\htdocs\mental health"
```

### Step 2: Install Required Python Packages

Run this command to install all dependencies:

```bash
pip install flask flask-cors numpy pandas scikit-learn
```

**What these packages do:**
- `flask` - Web framework for the ML server
- `flask-cors` - Allows PHP to communicate with Python server
- `numpy` - Numerical computing (used by the ML model)
- `pandas` - Data processing
- `scikit-learn` - Machine learning library

### Step 3: Verify Installation

Check that all packages are installed:

```bash
pip list
```

Look for: flask, flask-cors, numpy, pandas, scikit-learn

---

## Running the ML Server

### Method 1: Batch File (Easy - Recommended for Windows)

1. Open the "mental health" folder in File Explorer
2. **Double-click** `START_ML_SERVER.bat`
3. A command window will open and show:
   ```
   ===================================
     Mental Health ML Prediction Server
   ===================================
   
   [OK] Python found
   Python 3.9.x
   
   [OK] Flask installed
   [OK] Flask-CORS installed
   ...
   
   ===================================
     Starting ML Server...
   ===================================
   
   Server will run at: http://localhost:5000
   API endpoint: http://localhost:5000/api/predict
   Health check: http://localhost:5000/api/health
   
   Press Ctrl+C to stop the server
   ```

4. **The server is now running!** Keep this window open.

### Method 2: PowerShell Script

1. Right-click on `START_ML_SERVER.ps1`
2. Select "Run with PowerShell"
3. If you get an error about script execution policy:
   - Open PowerShell as Administrator
   - Run: `Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser`
   - Then run the script again

### Method 3: Manual Command Line

Open Command Prompt or PowerShell:

```bash
cd C:\xampp\htdocs\mental health\api
python ml_server.py
```

---

## Testing the Server

### Check Server Status (HTTP 200 = Good)

1. **In Browser**, open: `http://localhost:5000/api/health`
   
   You should see:
   ```json
   {
     "status": "healthy",
     "model_loaded": true,
     "encoders_loaded": true,
     "timestamp": "2024-04-18T10:30:00.123456"
   }
   ```

2. **If you see errors**, check:
   - Is the server window still open?
   - Can you see the loading messages?
   - Are there any red error lines?

### Test the Assessment Form

1. Open your Mental Health website
2. Go to Dashboard → Mental Health Assessment (মানসিক স্বাস্থ্য যাচাই)
3. Fill in the assessment form
4. Click "Predict" button
5. Check if you see results with risk percentage

---

## File Locations

```
C:\xampp\htdocs\mental health\
├── api/
│   ├── ml_server.py                    ← Main ML server (Python)
│   ├── predict.php                     ← PHP endpoint that calls ML server
│   └── mental_health_api.py           ← Old Flask API (not used)
│
├── assets/ml_model/
│   ├── mental_health_model_optimized.pkl   ← Main ML model
│   ├── encoders.pkl                        ← Feature encoders
│   └── mental_health_model.pkl             ← Backup model
│
├── dashboard/
│   └── assessment.php                  ← Frontend form
│
├── START_ML_SERVER.bat                 ← Batch startup script (Windows)
└── START_ML_SERVER.ps1                 ← PowerShell startup script
```

---

## Server Endpoints

Once the server is running, it provides these endpoints:

### 1. **GET /api/health**
Check if the server is running and models are loaded
```
http://localhost:5000/api/health
```

### 2. **GET /api/info**
Get information about the API
```
http://localhost:5000/api/info
```

### 3. **GET /**
API documentation
```
http://localhost:5000/
```

### 4. **POST /api/predict**
Make a prediction (called by predict.php automatically)

**Request (JSON):**
```json
{
  "Gender": "Male",
  "Occupation": "Student",
  "self_employed": "No",
  "family_history": "Yes",
  "Days_Indoors": "1-14 days",
  "Growing_Stress": "Yes",
  "Changes_Habits": "No",
  "Mental_Health_History": "No",
  "Mood_Swings": "Medium",
  "Coping_Struggles": "No",
  "Work_Interest": "Yes",
  "Social_Weakness": "No",
  "mental_health_interview": "Maybe",
  "care_options": "Yes"
}
```

**Response (JSON):**
```json
{
  "success": true,
  "data": {
    "prediction": "Treatment Recommended",
    "prediction_code": 1,
    "risk_percentage": 75.5,
    "probability_no_treatment": 24.5,
    "probability_treatment": 75.5,
    "risk_level": "High",
    "recommendation": {
      "status": "high_risk",
      "message": "⚠️ আপনার মানসিক স্বাস্থ্যের ঝুঁকি বেশি...",
      "helpline": "01977-855055",
      "action": "consult_doctor",
      "tips": [...]
    }
  },
  "timestamp": "2024-04-18T10:30:00.123456"
}
```

---

## Troubleshooting

### Problem: Python not found

**Error:**
```
'python' is not recognized as an internal or external command
```

**Solution:**
1. Reinstall Python
2. ✅ Make sure "Add Python to PATH" is checked
3. Restart your computer
4. Try again

### Problem: Flask not installed

**Error:**
```
ModuleNotFoundError: No module named 'flask'
```

**Solution:**
```bash
pip install flask flask-cors
```

### Problem: Model files not found

**Error:**
```
File not found: C:\xampp\htdocs\mental health\assets\ml_model\mental_health_model_optimized.pkl
```

**Solution:**
1. Check that the `.pkl` files exist in `assets/ml_model/`
2. Make sure file names match exactly (case-sensitive on some systems)
3. Verify files were not deleted or moved

### Problem: Port 5000 already in use

**Error:**
```
Address already in use
```

**Solution:**
1. Another program is using port 5000
2. Kill the process:
   ```bash
   netstat -ano | findstr :5000
   taskkill /PID [PID_NUMBER] /F
   ```
3. Or change port in `ml_server.py` line 210 (change 5000 to 5001)

### Problem: Assessment form hangs when clicking Predict

**Possible causes:**
1. ML Server is not running
2. Server failed to load models
3. Network issue between PHP and Python

**Solution:**
1. Check if command window for ML Server is still open
2. Look for red error messages in the server window
3. Go to `http://localhost:5000/api/health` in browser
4. If not accessible, restart the server

### Problem: Seeing "fallback" in response

**What it means:**
ML Server is not available, using rule-based calculation instead

**Solution:**
1. Start the ML Server using START_ML_SERVER.bat
2. Verify it's running with `http://localhost:5000/api/health`
3. Try the assessment again

---

## Logging

The ML server creates a log file: `ml_server.log`

Location: `C:\xampp\htdocs\mental health\api\ml_server.log`

View the log to debug issues:
- Each request is logged
- Errors are highlighted with ❌
- Success messages have ✅

---

## Performance Tips

1. **Keep the server running** while users are making assessments
2. **Check memory usage** - Server typically uses 50-100MB RAM
3. **Scale up** if you have many concurrent users:
   - Use a process manager like `gunicorn` or `waitress`
   - Run multiple instances on different ports

---

## Next Steps

1. ✅ Install Python (if not done)
2. ✅ Run `START_ML_SERVER.bat` to start the server
3. ✅ Test with `http://localhost:5000/api/health`
4. ✅ Go to assessment form and test a prediction
5. ✅ Check results display

---

## Support & Debugging

### Enable Debug Mode

For detailed logging, edit `ml_server.py` line 206:
```python
debug=True  # Change False to True
```

Then restart the server.

### Check Server Logs

Open the command window where the server is running - you'll see logs like:

```
2024-04-18 10:30:15,123 - INFO - 📨 REQUEST: POST at 2024-04-18 10:30:15.123456
2024-04-18 10:30:15,145 - INFO - ✅ JSON data received
2024-04-18 10:30:15,200 - INFO - 🔄 Encoding input...
2024-04-18 10:30:15,210 - INFO - 🤖 Making prediction...
2024-04-18 10:30:15,215 - INFO - ✅ Response sent: Treatment Recommended
```

---

## Advanced: Running on Startup

To run the ML server automatically when Windows starts:

1. Create a shortcut to `START_ML_SERVER.bat`
2. Press `Win + R`, type: `shell:startup`
3. Paste the shortcut into the folder

---

## Version History

- **v1.0** (April 2024) - Initial production-ready server
  - Improved error handling
  - Better logging
  - Flask CORS support
  - Feature encoding validation
  - Risk assessment and recommendations

---

## Questions or Issues?

1. Check the troubleshooting section above
2. Review the log file: `api/ml_server.log`
3. Check browser console (F12) for frontend errors
4. Test server directly: `http://localhost:5000/api/health`

---

**Last Updated:** April 18, 2024
