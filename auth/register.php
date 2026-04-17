<!DOCTYPE html>
<html>
<head>

<title>Register | Mentora</title>

<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/css/register.css">

</head>

<body>

<div class="register-container">

<div class="register-card">

<!-- Left Side - Welcome Section -->
<div class="welcome-side">
<div class="particle particle-1"></div>
<div class="particle particle-2"></div>
<div class="particle particle-3"></div>

<div class="welcome-content">
<div class="brand-section">
<div class="brand-icon">🌿</div>
<h1 class="brand-name">Mentora</h1>
<p class="brand-tagline">Your Mental Health Companion</p>
</div>

<div class="welcome-message">
<h2 class="welcome-title">Join Our Community</h2>
<p class="welcome-text">Take the first step towards better mental health. Connect with mentors, track your mood, and access valuable resources.</p>

<div class="benefits-list">
<div class="benefit-item">
<div class="benefit-icon">✓</div>
<div class="benefit-text">
Expert Mentors
<small>Get guidance from professionals</small>
</div>
</div>

<div class="benefit-item">
<div class="benefit-icon">📊</div>
<div class="benefit-text">
Mood Tracking
<small>Monitor your mental health progress</small>
</div>
</div>

<div class="benefit-item">
<div class="benefit-icon">🎯</div>
<div class="benefit-text">
Personalized Resources
<small>Content tailored to your needs</small>
</div>
</div>
</div>
</div>

<div class="testimonial-card">
<p class="testimonial-text">"Mentora helped me understand myself better and connect with supportive people. Highly recommended!"</p>
<div class="testimonial-author">
<div class="author-avatar">SR</div>
<div class="author-info">
<h5>Sarah Rahman</h5>
<p>Mental Health Advocate</p>
</div>
</div>
</div>
</div>
</div>

<!-- Right Side - Registration Form -->
<div class="form-side">
<div class="form-header">
<h2>Create Account</h2>
<p>Join thousands of people improving their mental health</p>
</div>

<!-- Progress Steps -->
<div class="progress-steps">
<div class="progress-step active completed">
<div class="step-number">✓</div>
<div class="step-label">Account</div>
</div>
<div class="progress-step active">
<div class="step-number">2</div>
<div class="step-label">Verification</div>
</div>
<div class="progress-step">
<div class="step-number">3</div>
<div class="step-label">Profile</div>
</div>
</div>

<!-- Form Sections -->
<div class="form-section active">

<?php
if(isset($_GET['error'])){
echo "<div class='alert alert-danger'>".$_GET['error']."</div>";
}
?>

<form method="POST" action="register-process.php">

<!-- Full Name -->
<div class="form-group">
<label class="form-label"><span class="label-icon">👤</span>Full Name</label>
<div class="input-wrapper">
<i class="input-icon">👤</i>
<input type="text" name="full_name" class="form-input" placeholder="Enter your full name" required>
</div>
</div>

<!-- Email -->
<div class="form-group">
<label class="form-label"><span class="label-icon">✉️</span>Email Address</label>
<div class="input-wrapper">
<i class="input-icon">✉️</i>
<input type="email" name="email" class="form-input" placeholder="Enter your email" required>
</div>
</div>

<!-- Phone -->
<div class="form-group">
<label class="form-label"><span class="label-icon">📱</span>Phone Number</label>
<div class="input-wrapper">
<i class="input-icon">📱</i>
<input type="text" name="phone" class="form-input" placeholder="Enter your phone number">
</div>
</div>

<!-- Password -->
<div class="form-group">
<label class="form-label"><span class="label-icon">🔐</span>Password</label>
<div class="input-wrapper">
<i class="input-icon">🔐</i>
<input type="password" name="password" class="form-input" placeholder="Create a strong password" required>
</div>
</div>

<!-- Password Strength Indicator -->
<div class="password-strength">
<div class="strength-bar">
<div class="strength-indicator" id="strengthIndicator"></div>
</div>
<small class="strength-text" id="strengthText">Password strength: Weak</small>
</div>

<!-- Terms & Conditions -->
<div class="form-group checkbox-group">
<label class="checkbox-label">
<input type="checkbox" required>
<span>I agree to the <a href="#" class="link">Terms & Conditions</a></span>
</label>
</div>

<!-- Submit Button -->
<button type="submit" class="btn-primary btn-submit">Create Account</button>

</form>

</div>

<!-- Login Link -->
<div class="form-footer">
<p>Already have an account? <a href="login.php" class="link">Sign In</a></p>
</div>

</div>

</div>

</div>

</body>
</html>