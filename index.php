<?php
// index.php - Fixed for folder with space
session_start();

// Get the correct base path
$base_path = '/mental%20health/';  // স্পেস এনকোডেড
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>মেন্টোরা · বাংলায় মানসিক স্বাস্থ্য</title>
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Noto Sans Bengali', 'Segoe UI', sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background-color: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
        }

        h1, h2, h3 {
            font-weight: 700;
            line-height: 1.4;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* header / navbar */
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 30, 30, 0.05);
            position: sticky;
            top: 0;
            z-index: 50;
            padding: 12px 0;
        }

        .nav-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo i {
            font-size: 28px;
            color: #0d9488;
        }

        .logo span {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #115e59;
        }

        .logo span small {
            font-size: 16px;
            font-weight: 400;
            color: #2c3e50;
            margin-left: 6px;
        }

        .nav-links {
            display: flex;
            gap: 32px;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-links a {
            text-decoration: none;
            font-weight: 500;
            color: #1e293b;
            font-size: 18px;
            transition: 0.2s;
            border-bottom: 2px solid transparent;
            padding-bottom: 4px;
        }

        .nav-links a:hover {
            color: #0f766e;
            border-bottom-color: #14b8a6;
        }

        .auth-buttons {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn-login {
            background: transparent;
            border: 2px solid #14b8a6;
            color: #0f766e;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
        }

        .btn-login:hover {
            background: #14b8a6;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(20, 184, 166, 0.3);
        }

        .btn-signup {
            background: #14b8a6;
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
        }

        .btn-signup:hover {
            background: #0f766e;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(20, 184, 166, 0.4);
        }

        .btn {
            background: #14b8a6;
            color: white !important;
            padding: 10px 22px;
            border-radius: 40px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: 0.2s;
            display: inline-block;
            text-decoration: none;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #14b8a6;
            color: #115e59 !important;
        }

        .btn:hover {
            background: #0f766e;
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(20, 184, 166, 0.2);
        }

        /* hero */
        .hero {
            background: linear-gradient(145deg, #f0fdfa 0%, #ffffff 100%);
            padding: 60px 0 40px 0;
        }

        .hero-grid {
            display: flex;
            align-items: center;
            gap: 40px;
            flex-wrap: wrap;
        }

        .hero-content {
            flex: 1 1 400px;
        }

        .hero-badge {
            background: #ccf0e9;
            color: #0d5e5a;
            padding: 6px 14px;
            border-radius: 30px;
            display: inline-block;
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 20px;
        }

        .hero-content h1 {
            font-size: 48px;
            color: #0f172a;
            line-height: 1.2;
            margin-bottom: 20px;
        }

        .hero-highlight {
            color: #0d9488;
            border-bottom: 3px solid #99f6e4;
        }

        .hero-content p {
            font-size: 20px;
            color: #334155;
            margin-bottom: 30px;
            max-width: 550px;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .hero-image {
            flex: 1 1 300px;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 400"><path fill="%23b2f0e6" d="M250,50C140,50,50,140,50,250s90,200,200,200s200-90,200-200S360,50,250,50z M250,400c-82.7,0-150-67.3-150-150s67.3-150,150-150s150,67.3,150,150S332.7,400,250,400z"/><circle fill="%2314b8a6" cx="250" cy="200" r="40"/><circle fill="%23f59e0b" cx="180" cy="260" r="30"/><circle fill="%233b82f6" cx="330" cy="280" r="35"/><path fill="%230f766e" d="M210,310c20,25,60,25,80,0"/></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            min-height: 300px;
        }

        .section-title {
            text-align: center;
            margin: 50px 0 30px;
        }

        .section-title h2 {
            font-size: 36px;
            color: #0f172a;
        }

        .section-title p {
            color: #475569;
            font-size: 18px;
            max-width: 600px;
            margin: 10px auto 0;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }

        .card {
            background: white;
            border-radius: 28px;
            padding: 32px 20px;
            box-shadow: 0 10px 30px -5px rgba(0, 100, 90, 0.1);
            transition: 0.3s ease;
            text-align: center;
            border: 1px solid #e2e8f0;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 40px -15px #14b8a680;
        }

        .card i {
            font-size: 44px;
            color: #14b8a6;
            margin-bottom: 20px;
        }

        .card h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .card p {
            color: #475569;
            font-size: 16px;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            margin: 50px auto;
            max-width: 1000px;
        }

        .step-card {
            text-align: center;
        }

        .step-number {
            width: 100px;
            height: 100px;
            background: #14b8a6;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 44px;
            font-weight: 700;
            margin: 0 auto 25px;
            box-shadow: 0 10px 30px -5px rgba(20, 184, 166, 0.3);
        }

        .step-card h3 {
            font-size: 24px;
            color: #0f172a;
            margin-bottom: 15px;
        }

        .step-card p {
            color: #475569;
            font-size: 16px;
            line-height: 1.6;
        }

        .pstu-highlight {
            background: #e6fffa;
            border-radius: 60px;
            padding: 50px 40px;
            margin: 60px 0;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 30px;
        }

        .pstu-content {
            flex: 2 1 300px;
        }

        .pstu-content h3 {
            font-size: 32px;
            color: #0b4f4a;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pstu-map {
            flex: 1 1 200px;
            background: #d9f2ee;
            border-radius: 40px;
            padding: 30px;
            text-align: center;
            color: #0b4f4a;
        }

        .pstu-map i {
            font-size: 70px;
            opacity: 0.9;
        }

        .pill-group {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 20px;
        }

        .pill {
            background: white;
            color: #115e59;
            font-weight: 600;
            padding: 8px 18px;
            border-radius: 50px;
            border: 1px solid #5eead4;
        }

        .experts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }

        .expert-card {
            background: white;
            border-radius: 30px;
            padding: 25px 15px;
            text-align: center;
            box-shadow: 0 8px 18px rgba(0,0,0,0.02);
            border: 1px solid #f1f5f9;
        }

        .expert-avatar {
            background: #ccf0e9;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #115e59;
        }

        .testimonial {
            background: white;
            border-radius: 40px;
            padding: 40px;
            margin: 40px 0;
            box-shadow: 0 10px 25px -8px #99f6e4;
        }

        .testimonial-flex {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            align-items: center;
        }

        .testimonial blockquote {
            font-size: 22px;
            font-weight: 500;
            color: #0f172a;
            border-left: 5px solid #14b8a6;
            padding-left: 30px;
            flex: 3;
        }

        .testimonial-author {
            flex: 1;
            text-align: center;
        }

        .author-name {
            font-weight: 700;
            font-size: 22px;
        }

        .cta-section {
            background: linear-gradient(130deg, #115e59, #0d9488);
            border-radius: 50px;
            padding: 60px 40px;
            color: white;
            text-align: center;
            margin: 60px 0;
        }

        .cta-section h2 {
            font-size: 38px;
            margin-bottom: 15px;
        }

        .cta-section .btn {
            background: white;
            color: #115e59 !important;
            font-size: 20px;
            padding: 16px 44px;
            margin-top: 20px;
        }

        .footer {
            background: #0f172a;
            color: #cbd5e1;
            padding: 50px 0 30px;
            border-radius: 40px 40px 0 0;
            margin-top: 60px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 40px;
        }

        .footer-logo span {
            font-size: 28px;
            font-weight: 700;
            color: white;
        }

        .footer-links a {
            display: block;
            color: #cbd5e1;
            text-decoration: none;
            margin: 8px 0;
        }

        .footer-links a:hover {
            color: #99f6e4;
        }

        .copyright {
            text-align: center;
            border-top: 1px solid #334155;
            margin-top: 40px;
            padding-top: 30px;
            color: #94a3b8;
        }

        @media (max-width: 700px) {
            .nav-container {
                flex-direction: column;
                gap: 15px;
            }
            .hero-content h1 {
                font-size: 36px;
            }
            .auth-buttons {
                width: 100%;
                justify-content: center;
            }
            .steps-grid {
                grid-template-columns: 1fr;
            }
            .step-number {
                width: 80px;
                height: 80px;
                font-size: 36px;
            }
        }
    </style>
</head>
<body>

<!-- ======== Header ======== -->
<header class="navbar">
    <div class="container nav-container">
        <div class="logo">
            <i class="fas fa-brain"></i>
            <span>মেন্টোরা <small>Mentora</small></span>
        </div>
        <div class="nav-links">
            <a href="#home">মূলপাতা</a>
            <a href="#services">সেবাসমূহ</a>
            <a href="<?php echo $base_path; ?>auth/login.php">বিশেষজ্ঞ</a>
            <a href="<?php echo $base_path; ?>auth/login.php">ব্লগ</a>
            <a href="<?php echo $base_path; ?>auth/login.php">যোগাযোগ</a>
        </div>
        <div class="auth-buttons">
            <a href="<?php echo $base_path; ?>auth/login.php" class="btn-login">লগইন</a>
            <a href="<?php echo $base_path; ?>auth/register.php" class="btn-signup">সাইন আপ</a>
        </div>
    </div>
</header>

<!-- Hero Section -->
<section class="hero" id="home">
    <div class="container hero-grid">
        <div class="hero-content">
            <span class="hero-badge"><i class="fas fa-brain"></i> বাংলাদেশের প্রথম AI মানসিক স্বাস্থ্য প্ল্যাটফর্ম</span>
            <h1>আপনার মানসিক সুস্থতার <span class="hero-highlight">বিশ্বস্ত সঙ্গী</span></h1>
            <p>AI চ্যাটবট, বিশেষজ্ঞ মেন্টর এবং আত্ম-উন্নয়ন কোর্সের মাধ্যমে আপনার মানসিক স্বাস্থ্যের যত্ন নিন — সম্পূর্ণ বাংলায়, সম্পূর্ণ গোপনীয়।</p>
            <div class="hero-buttons">
                <a href="<?php echo $base_path; ?>auth/login.php" class="btn">মন খুলে বলুন <i class="fas fa-arrow-right"></i></a>
                <a href="#services" class="btn-outline btn">আমাদের সম্পর্কে</a>
            </div>
        </div>
        <div class="hero-image"></div>
    </div>
</section>

<!-- Services Section -->
<div class="container" id="services">
    <div class="section-title">
        <h2>আমাদের সুবিধাসমূহ</h2>
        <p>মানসিক স্বাস্থ্য সেবা এখন আপনার হাতের মুঠোয়। সবকিছু এক জায়গায়।</p>
    </div>
    <div class="cards-grid">
        <div class="card">
            <i class="fas fa-comments"></i>
            <h3>মনের বন্ধু AI চ্যাটবট</h3>
            <p>২৪/৭ বাংলায় কথা বলুন আমাদের AI চ্যাটবটের সাথে। আপনার মানসিক স্বাস্থ্যের যত্ন নিন যেকোনো সময়।</p>
        </div>
        <div class="card">
            <i class="fas fa-users"></i>
            <h3>বিশেষজ্ঞ মেন্টর</h3>
            <p>যাচাইকৃত মানসিক স্বাস্থ্য বিশেষজ্ঞ ও ক্যারিয়ার মেন্টরদের সাথে ভিডিও সেশন নিন।</p>
        </div>
        <div class="card">
            <i class="fas fa-book-open"></i>
            <h3>আত্ম-উন্নয়ন কোর্স</h3>
            <p>স্ট্রেস ম্যানেজমেন্ট, মেডিটেশন, ক্যারিয়ার গাইডেন্স সহ বিভিন্ন কোর্সে ভর্তি হন।</p>
        </div>
        <div class="card">
            <i class="fas fa-shield"></i>
            <h3>সম্পূর্ণ গোপনীয়তা</h3>
            <p>আপনার সব তথ্য এন্ড-টু-এন্ড এনক্রিপ্টেড। সম্পূর্ণ গোপনীয়তার নিশ্চয়তা।</p>
        </div>
    </div>
</div>

<!-- Steps Section -->
<div class="container">
    <div class="section-title">
        <h2>কীভাবে কাজ করে?</h2>
        <p>মাত্র তিনটি পদক্ষেপে শুরু করুন আপনার সুস্থতার যাত্রা।</p>
    </div>
    <div class="steps-grid">
        <div class="step-card">
            <div class="step-number">০१</div>
            <h3>আকাউন্ট তৈরি করুন</h3>
            <p>ইমেইল বা ফোন নম্বর দিয়ে সহজে নিবন্ধন করুন</p>
        </div>
        <div class="step-card">
            <div class="step-number">०२</div>
            <h3>মেজাজ ট্র্যাক করুন</h3>
            <p>প্রতিদিন আপনার মানসিক অবস্থা ট্র্যাক করুন</p>
        </div>
        <div class="step-card">
            <div class="step-number">०३</div>
            <h3>সাহায্য নিন</h3>
            <p>AI চ্যাটবট বা বিশেষজ্ঞ মেন্টরের কাছ থেকে সাহায্য নিন</p>
        </div>
    </div>
</div>

<!-- PSTU Section -->
<div class="container">
    <div class="pstu-highlight">
        <div class="pstu-content">
            <h3><i class="fas fa-university"></i> পটুয়াখালী বিজ্ঞান ও প্রযুক্তি বিশ্ববিদ্যালয়</h3>
            <p>মেন্টোরা শুরু হয়েছে একঝাঁক উদ্যমী শিক্ষার্থীর হাত ধরে। সাইকোলজি ও সিএসই বিভাগের সম্মিলিত প্রচেষ্টায় তৈরি এই প্ল্যাটফর্ম দেশীয় প্রেক্ষাপটে মানসিক স্বাস্থ্য সচেতনতা বাড়াতে কাজ করছে।</p>
            <div class="pill-group">
                <span class="pill"><i class="far fa-check-circle"></i> গবেষণা-সমর্থিত</span>
                <span class="pill"><i class="far fa-check-circle"></i> দেশীয় বিশেষজ্ঞ</span>
                <span class="pill"><i class="far fa-check-circle"></i> বিনামূল্যের বেসিক সেবা</span>
            </div>
        </div>
        <div class="pstu-map">
            <i class="fas fa-location-dot"></i>
            <h4>পটুয়াখালী, বাংলাদেশ</h4>
            <p>ক্যাম্পাস থেকে শুরু, সারা দেশে</p>
        </div>
    </div>
</div>

<!-- Experts Section -->
<div class="container">
    <div class="section-title">
        <h2>আমাদের উপদেষ্টা ও থেরাপিস্ট</h2>
        <p>বিশ্ববিদ্যালয়ের শিক্ষক ও পেশাদার সাইকোলজিস্ট</p>
    </div>
    <div class="experts-grid">
        <div class="expert-card">
            <div class="expert-avatar"><i class="fas fa-user-md"></i></div>
            <h3>ড. নাফিজা হক</h3>
            <p>ক্লিনিক্যাল সাইকোলজিস্ট</p>
        </div>
        <div class="expert-card">
            <div class="expert-avatar"><i class="fas fa-user-tie"></i></div>
            <h3>অধ্যাপক মোঃ আনিস</h3>
            <p>সাইকিয়াট্রি</p>
        </div>
        <div class="expert-card">
            <div class="expert-avatar"><i class="fas fa-user-nurse"></i></div>
            <h3>সাদিয়া ইসলাম</h3>
            <p>কাউন্সেলর</p>
        </div>
        <div class="expert-card">
            <div class="expert-avatar"><i class="fas fa-users"></i></div>
            <h3>ছাত্র স্বেচ্ছাসেবক</h3>
            <p>পিয়ার সাপোর্ট গ্রুপ</p>
        </div>
    </div>
</div>

<!-- Testimonials -->
<div class="container">
    <div class="testimonial">
        <div class="testimonial-flex">
            <blockquote>“মেন্টোরার গ্রুপ থেরাপিতে আমি প্রথম বুঝতে পারি আমার অনুভূতি অস্বাভাবিক না। বাংলায়, নিজের ভাষায় এত যত্ন পাওয়া সত্যিই অভাবনীয়।”</blockquote>
            <div class="testimonial-author">
                <i class="fas fa-user-circle" style="font-size: 60px; color: #14b8a6;"></i>
                <div class="author-name">তানভীর আনজুম রাহাত</div>
                <div style="color: #475569;">পটুয়াখালী</div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="testimonial">
        <div class="testimonial-flex">
            <blockquote>“মেন্টোরার সাহায্যে আমি খুব সহজে আমার ডিপ্রেশন লেভেল কতটুকু প্রেডিক্ট করতে পারি এবং মনের বন্ধু চ্যাটবটের সাথে কথা বলে সাজেশন নিতে পারে”</blockquote>
            <div class="testimonial-author">
                <i class="fas fa-user-circle" style="font-size: 60px; color: #14b8a6;"></i>
                <div class="author-name">রাহিনা আকতার</div>
                <div style="color: #475569;">ঢাকা</div>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="container">
    <div class="cta-section">
        <h2>আপনি একা নন, আমরা আছি পাশে</h2>
        <p style="font-size: 20px; max-width: 600px; margin: 0 auto;">বিনামূল্যে প্রথম সেশন বুক করুন অথবা চ্যাট শুরু করুন। সম্পূর্ণ গোপনীয়।</p>
        <a href="<?php echo $base_path; ?>auth/login.php" class="btn">এখনই কথা বলুন <i class="fas fa-arrow-right"></i></a>
    </div>
</div>

<!-- Footer -->
<footer class="footer">
    <div class="container footer-grid">
        <div class="footer-logo">
            <span>মেন্টোরা</span>
            <p style="margin-top: 15px;">বাংলায় মানসিক সুস্থতা<br>পটুয়াখালী বিজ্ঞান ও প্রযুক্তি বিশ্ববিদ্যালয়</p>
        </div>
        <div class="footer-links">
            <h4 style="color: white;">সহায়তা</h4>
            <a href="#">আমাদের সম্পর্কে</a>
            <a href="#">গোপনীয়তা নীতি</a>
            <a href="#">সেবার শর্তাবলী</a>
        </div>
        <div class="footer-links">
            <h4 style="color: white;">যোগাযোগ</h4>
            <a href="#"><i class="fas fa-envelope"></i> mentora@pstu.ac.bd</a>
            <a href="#"><i class="fas fa-phone"></i> ০১৮৬৯৭৯৩১৩৯</a>
            <div style="margin-top: 10px;">
                <i class="fab fa-facebook" style="font-size: 24px; margin-right: 12px;"></i>
                <i class="fab fa-instagram" style="font-size: 24px; margin-right: 12px;"></i>
                <i class="fab fa-linkedin" style="font-size: 24px;"></i>
            </div>
        </div>
    </div>
    <div class="container copyright">
        © ২০২৬ মেন্টোরা · পটুয়াখালী বিজ্ঞান ও প্রযুক্তি বিশ্ববিদ্যালয়ের শিক্ষার্থীদের উদ্যোগ
    </div>
</footer>

<script>
    (function() {
        console.log("✅ মেন্টোরা — বাংলায় মানসিক স্বাস্থ্য সবার জন্য।");
        console.log("🔗 Base path: <?php echo $base_path; ?>");
        
        // Check all auth links
        const links = document.querySelectorAll('a[href*="login"], a[href*="register"]');
        console.log("📌 Found " + links.length + " auth links:");
        links.forEach((link, i) => {
            console.log(`${i}: ${link.href}`);
        });
    })();
</script>

</body>
</html>