# ML Server Quick Start Guide

## For Impatient People (5 Minutes)

### What You Need
- Python 3.7+ installed (download from https://www.python.org/)
  - **CHECK** "Add Python to PATH" when installing

### Step 1: Install Packages (1 minute)
Open Command Prompt and run:
```bash
pip install flask flask-cors numpy pandas scikit-learn
```

### Step 2: Start Server (30 seconds)
Go to `C:\xampp\htdocs\mental health` and double-click:
```
START_ML_SERVER.bat
```

A command window opens. You should see:
```
✅ Model and encoders loaded successfully!
Server will run at: http://localhost:5000
```

### Step 3: Test (1 minute)
Open your browser and go to:
```
http://localhost:5000/api/health
```

You should see:
```json
{
  "status": "healthy",
  "model_loaded": true,
  "encoders_loaded": true
}
```

### Step 4: Use It (2 minutes)
1. Go to your Mentora website
2. Go to "মানসিক স্বাস্থ্য যাচাই" (Mental Health Assessment)
3. Fill the form
4. Click "আমার মানসিক স্বাস্থ্য যাচাই করুন"
5. See your risk score! ✨

---

## Troubleshooting (If Something Goes Wrong)

| Problem | Solution |
|---------|----------|
| Python not found | Reinstall Python, check "Add to PATH" |
| Flask not found | Run: `pip install flask flask-cors` |
| Port 5000 in use | Another program is using it. Kill it or restart computer |
| Model not found | Check `assets/ml_model/` has the `.pkl` files |
| Assessment hangs | Make sure server window is still open and running |

---

## How It Works (The Flow)

```
You fill form → PHP sends data → Python server processes → Results shown
```

That's it! The backend does all the ML work. You just need the server running.

---

## Key Files

| File | Purpose |
|------|---------|
| `START_ML_SERVER.bat` | Click this to start server |
| `api/ml_server.py` | The actual server code |
| `api/predict.php` | Talks to Python server |
| `assets/ml_model/*.pkl` | Your trained ML models |

---

## One-Liner Start (PowerShell)

```powershell
cd "C:\xampp\htdocs\mental health\api"; python ml_server.py
```

---

## Stop the Server

Just close the command window. Or press `Ctrl+C` in the window.

---

That's all! Happy assessing! 🎉
