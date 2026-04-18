<?php
// assessment.php
// Path: mental health/dashboard/assessment.php
session_start();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>মানসিক স্বাস্থ্য মূল্যায়ন</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"/>
  <style>
    :root { --accent: #6c63ff; }
    body  { background: #f4f6fb; font-family: 'Segoe UI', sans-serif; }

    .page-header {
      background: linear-gradient(135deg, #6c63ff 0%, #3d35b5 100%);
      color: #fff; border-radius: 0 0 24px 24px;
      padding: 36px 24px 28px; margin-bottom: 32px;
    }
    .card { border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,.07); }
    .form-label { font-weight: 600; font-size: .9rem; color: #444; }
    .form-select, .form-control {
      border-radius: 10px; border: 1.5px solid #dde1f0;
      transition: border-color .2s;
    }
    .form-select:focus, .form-control:focus {
      border-color: var(--accent); box-shadow: 0 0 0 3px rgba(108,99,255,.15);
    }
    .btn-predict {
      background: linear-gradient(135deg, #6c63ff, #3d35b5);
      border: none; border-radius: 12px; color: #fff;
      padding: 12px 40px; font-size: 1.05rem; font-weight: 600;
      width: 100%; transition: opacity .2s;
    }
    .btn-predict:hover { opacity: .88; color: #fff; }

    /* Result box */
    #resultBox { display: none; }
    .result-card {
      border-radius: 16px; padding: 28px 24px;
      animation: fadeIn .4s ease;
    }
    .result-icon { font-size: 3.5rem; line-height: 1; }
    .confidence-bar .progress { height: 10px; border-radius: 8px; }
    .advice-item {
      background: rgba(255,255,255,.5);
      border-radius: 10px; padding: 10px 14px;
      margin-bottom: 8px; font-size: .92rem;
    }
    @keyframes fadeIn { from { opacity:0; transform: translateY(16px); } to { opacity:1; transform: translateY(0); } }

    /* Spinner */
    #loadingSpinner { display: none; text-align: center; padding: 20px; }

    .section-title {
      font-size: .75rem; font-weight: 700; letter-spacing: .08em;
      text-transform: uppercase; color: var(--accent); margin-bottom: 14px;
    }
  </style>
</head>
<body>

<div class="page-header text-center">
  <h2 class="fw-bold mb-1">🧠 মানসিক স্বাস্থ্য মূল্যায়ন</h2>
  <p class="mb-0 opacity-75">আপনার তথ্য সম্পূর্ণ গোপন থাকবে — AI বিশ্লেষণ করে ফলাফল জানাবে</p>
</div>

<div class="container pb-5" style="max-width:720px">

  <!-- ── FORM CARD ─────────────────────────────────────────── -->
  <div class="card p-4 mb-4">

    <!-- Personal -->
    <p class="section-title">👤 ব্যক্তিগত তথ্য</p>
    <div class="row g-3 mb-4">

      <div class="col-md-6">
        <label class="form-label">লিঙ্গ</label>
        <select id="Gender" class="form-select">
          <option value="">-- বেছে নিন --</option>
          <option value="Male">পুরুষ</option>
          <option value="Female">মহিলা</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">পেশা</label>
        <select id="Occupation" class="form-select">
          <option value="">-- বেছে নিন --</option>
          <option value="Corporate">কর্পোরেট চাকরি</option>
          <option value="Student">শিক্ষার্থী</option>
          <option value="Business">ব্যবসা</option>
          <option value="Housewife">গৃহিণী</option>
          <option value="Others">অন্যান্য</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">আপনি কি স্ব-নিযুক্ত (Self-employed)?</label>
        <select id="self_employed" class="form-select">
          <option value="No">না</option>
          <option value="Yes">হ্যাঁ</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">পরিবারে মানসিক সমস্যার ইতিহাস আছে?</label>
        <select id="family_history" class="form-select">
          <option value="No">না</option>
          <option value="Yes">হ্যাঁ</option>
        </select>
      </div>

    </div>

    <!-- Lifestyle -->
    <p class="section-title">🏠 জীবনযাপন</p>
    <div class="row g-3 mb-4">

      <div class="col-md-6">
        <label class="form-label">গত কতদিন বাড়িতেই ছিলেন?</label>
        <select id="Days_Indoors" class="form-select">
          <option value="1-14 days">১-১৪ দিন</option>
          <option value="15-30 days">১৫-৩০ দিন</option>
          <option value="30+ days">৩০+ দিন</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">মানসিক চাপ বাড়ছে?</label>
        <select id="Growing_Stress" class="form-select">
          <option value="No">না</option>
          <option value="Yes">হ্যাঁ</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">অভ্যাসে পরিবর্তন এসেছে?</label>
        <select id="Changes_Habits" class="form-select">
          <option value="No">না</option>
          <option value="Yes">হ্যাঁ</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">আগে মানসিক স্বাস্থ্য সমস্যা ছিল?</label>
        <select id="Mental_Health_History" class="form-select">
          <option value="No">না</option>
          <option value="Yes">হ্যাঁ</option>
        </select>
      </div>

    </div>

    <!-- Mental symptoms -->
    <p class="section-title">💭 মানসিক লক্ষণ</p>
    <div class="row g-3 mb-4">

      <div class="col-md-6">
        <label class="form-label">মেজাজের ওঠানামা কেমন?</label>
        <select id="Mood_Swings" class="form-select">
          <option value="Low">কম</option>
          <option value="Medium">মাঝারি</option>
          <option value="High">বেশি</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">মোকাবেলায় সমস্যা হচ্ছে?</label>
        <select id="Coping_Struggles" class="form-select">
          <option value="No">না</option>
          <option value="Yes">হ্যাঁ</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">কাজে আগ্রহ কমে গেছে?</label>
        <select id="Work_Interest" class="form-select">
          <option value="Yes">না, আগ্রহ আছে</option>
          <option value="No">হ্যাঁ, কমে গেছে</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">সামাজিকভাবে দুর্বল অনুভব করছেন?</label>
        <select id="Social_Weakness" class="form-select">
          <option value="No">না</option>
          <option value="Yes">হ্যাঁ</option>
        </select>
      </div>

    </div>

    <!-- Support -->
    <p class="section-title">🤝 সহায়তা ও সম্পদ</p>
    <div class="row g-3 mb-4">

      <div class="col-md-6">
        <label class="form-label">মানসিক সাক্ষাৎকারে যেতে রাজি?</label>
        <select id="mental_health_interview" class="form-select">
          <option value="No">না</option>
          <option value="Maybe">হয়তো</option>
          <option value="Yes">হ্যাঁ</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">মানসিক স্বাস্থ্য সেবা পাওয়ার সুযোগ আছে?</label>
        <select id="care_options" class="form-select">
          <option value="No">না</option>
          <option value="Yes">হ্যাঁ</option>
        </select>
      </div>

    </div>

    <!-- Submit -->
    <div id="loadingSpinner">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-2 text-muted">AI বিশ্লেষণ করছে…</p>
    </div>

    <button class="btn btn-predict mt-2" onclick="submitAssessment()">
      🔍 মূল্যায়ন শুরু করুন
    </button>
  </div>

  <!-- ── RESULT BOX ─────────────────────────────────────────── -->
  <div id="resultBox">
    <div id="resultCard" class="result-card">

      <div class="d-flex align-items-center gap-3 mb-3">
        <div class="result-icon" id="resIcon"></div>
        <div>
          <h4 class="mb-0 fw-bold" id="resLabelBn"></h4>
          <small class="text-muted" id="resLabelEn"></small>
        </div>
      </div>

      <p id="resMessage" class="mb-3"></p>

      <div class="confidence-bar mb-4">
        <div class="d-flex justify-content-between mb-1">
          <small class="fw-semibold">AI আস্থার মাত্রা</small>
          <small id="resConf"></small>
        </div>
        <div class="progress">
          <div id="resConfBar" class="progress-bar" role="progressbar"></div>
        </div>
      </div>

      <p class="fw-bold mb-2">📋 পরামর্শ:</p>
      <div id="resAdvice"></div>

      <button class="btn btn-outline-secondary w-100 mt-4 rounded-pill"
              onclick="resetForm()">🔄 আবার মূল্যায়ন করুন</button>
    </div>
  </div>

</div><!-- /container -->

<script>
const API = "http://localhost:5000";

async function submitAssessment() {
  // Collect form data
  const fields = [
    "Gender","Occupation","self_employed","family_history",
    "Days_Indoors","Growing_Stress","Changes_Habits","Mental_Health_History",
    "Mood_Swings","Coping_Struggles","Work_Interest","Social_Weakness",
    "mental_health_interview","care_options"
  ];

  const payload = {};
  for (const f of fields) {
    const el = document.getElementById(f);
    if (!el || !el.value) {
      alert("অনুগ্রহ করে সব প্রশ্নের উত্তর দিন।");
      return;
    }
    payload[f] = el.value;
  }

  // Show spinner
  document.getElementById("loadingSpinner").style.display = "block";
  document.getElementById("resultBox").style.display = "none";

  try {
    const res  = await fetch(`${API}/predict`, {
      method:  "POST",
      headers: { "Content-Type": "application/json" },
      body:    JSON.stringify(payload)
    });
    const data = await res.json();

    document.getElementById("loadingSpinner").style.display = "none";

    if (!data.success) {
      alert("সার্ভার ত্রুটি: " + (data.error || "অজানা সমস্যা"));
      return;
    }

    showResult(data);

  } catch (err) {
    document.getElementById("loadingSpinner").style.display = "none";
    alert("⚠️ Flask সার্ভারে সংযোগ হচ্ছে না।\nনিশ্চিত করুন app.py চালু আছে (python app.py)");
    console.error(err);
  }
}

function showResult(data) {
  const colorMap = {
    success: { bg: "#d1fae5", border: "#10b981", text: "#065f46", bar: "bg-success" },
    danger:  { bg: "#fee2e2", border: "#ef4444", text: "#7f1d1d", bar: "bg-danger"  },
    warning: { bg: "#fef3c7", border: "#f59e0b", text: "#78350f", bar: "bg-warning" },
  };
  const theme = colorMap[data.color] || colorMap["warning"];

  const card = document.getElementById("resultCard");
  card.style.background    = theme.bg;
  card.style.border        = `2px solid ${theme.border}`;
  card.style.color         = theme.text;

  document.getElementById("resIcon").textContent    = data.icon;
  document.getElementById("resLabelBn").textContent = data.label_bn;
  document.getElementById("resLabelEn").textContent = data.label_en;
  document.getElementById("resMessage").textContent = data.message;

  // Confidence
  const conf = data.confidence || 0;
  document.getElementById("resConf").textContent    = conf + "%";
  const bar = document.getElementById("resConfBar");
  bar.style.width     = conf + "%";
  bar.className       = `progress-bar ${theme.bar}`;

  // Advice
  const advDiv = document.getElementById("resAdvice");
  advDiv.innerHTML = "";
  (data.advice || []).forEach(tip => {
    const d = document.createElement("div");
    d.className   = "advice-item";
    d.textContent = "✔ " + tip;
    advDiv.appendChild(d);
  });

  document.getElementById("resultBox").style.display = "block";
  document.getElementById("resultBox").scrollIntoView({ behavior: "smooth" });
}

function resetForm() {
  document.getElementById("resultBox").style.display = "none";
  window.scrollTo({ top: 0, behavior: "smooth" });
}
</script>
</body>
</html>