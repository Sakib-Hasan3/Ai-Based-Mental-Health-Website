<?php
require_once 'db.php';
session_start();

// Get featured mentors
$mentors = fetchAll("SELECT * FROM users u JOIN mentors m ON u.id = m.user_id WHERE m.verification_status = 'verified' LIMIT 4");

// Get featured doctors
$doctors = fetchAll("SELECT * FROM users u JOIN doctors d ON u.id = d.user_id WHERE d.verification_status = 'verified' LIMIT 4");

// Get blog posts
$blogs = fetchAll("SELECT * FROM articles ORDER BY created_at DESC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentora - Your Companion in Mental Wellness Journey</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="assets/images/logo.png" alt="Mentora" height="40">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pages/about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pages/services.php">Services</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pages/mentors.php">Mentors</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pages/doctors.php">Doctors</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pages/blog.php">Blog</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pages/contact.php">Contact</a>
                </li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="btn btn-outline-primary ms-2" href="user/dashboard.php">Dashboard</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-outline-primary ms-2" href="auth/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="auth/register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">
                    Your Companion in <span class="text-primary">Mental Wellness</span> Journey
                </h1>
                <p class="lead mb-4">
                    Connect with verified mentors, doctors, and AI-powered support for your mental wellness journey. Available 24/7 in Bengali.
                </p>
                <div class="d-flex gap-3">
                    <a href="auth/register.php" class="btn btn-primary btn-lg">Get Started Free</a>
                    <a href="#how-it-works" class="btn btn-outline-primary btn-lg">Learn More</a>
                </div>
                <div class="row mt-5">
                    <div class="col-4">
                        <h3 class="h2 fw-bold">500+</h3>
                        <p class="text-muted">Verified Mentors</p>
                    </div>
                    <div class="col-4">
                        <h3 class="h2 fw-bold">200+</h3>
                        <p class="text-muted">Qualified Doctors</p>
                    </div>
                    <div class="col-4">
                        <h3 class="h2 fw-bold">10k+</h3>
                        <p class="text-muted">Happy Users</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="assets/images/illustrations/hero-image.svg" alt="Hero" class="img-fluid">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section py-5 bg-light">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Why Choose Mentora?</h2>
            <p class="lead text-muted">Comprehensive mental wellness platform tailored for Bangladesh</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-primary bg-opacity-10 rounded-circle p-3 mb-3">
                            <i class="fas fa-robot fa-2x text-primary"></i>
                        </div>
                        <h4>AI Companion</h4>
                        <p class="text-muted">24/7 AI chatbot "মনের বন্ধু" for immediate support in Bengali</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-success bg-opacity-10 rounded-circle p-3 mb-3">
                            <i class="fas fa-chalkboard-teacher fa-2x text-success"></i>
                        </div>
                        <h4>Expert Mentors</h4>
                        <p class="text-muted">Verified mentors for career, academic, and life guidance</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-info bg-opacity-10 rounded-circle p-3 mb-3">
                            <i class="fas fa-user-md fa-2x text-info"></i>
                        </div>
                        <h4>Medical Consultation</h4>
                        <p class="text-muted">Connect with qualified doctors for professional medical advice</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section id="how-it-works" class="how-it-works py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">How It Works</h2>
            <p class="lead text-muted">Start your wellness journey in three simple steps</p>
        </div>
        <div class="row">
            <div class="col-md-4 text-center">
                <div class="step-number bg-primary text-white rounded-circle mb-3">1</div>
                <h4>Create Account</h4>
                <p class="text-muted">Sign up free and complete your wellness assessment</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="step-number bg-primary text-white rounded-circle mb-3">2</div>
                <h4>Choose Your Support</h4>
                <p class="text-muted">Select AI chat, mentor, or doctor based on your needs</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="step-number bg-primary text-white rounded-circle mb-3">3</div>
                <h4>Start Your Journey</h4>
                <p class="text-muted">Begin sessions and track your wellness progress</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Mentors -->
<section class="featured-mentors py-5 bg-light">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2 class="display-5 fw-bold">Top Mentors</h2>
            <a href="pages/mentors.php" class="btn btn-outline-primary">View All <i class="fas fa-arrow-right ms-2"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach($mentors as $mentor): ?>
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm mentor-card">
                    <img src="assets/uploads/profiles/<?php echo $mentor['profile_image'] ?? 'default-avatar.png'; ?>" 
                         class="card-img-top rounded-circle mx-auto mt-4" style="width: 100px; height: 100px; object-fit: cover;" 
                         alt="<?php echo $mentor['name']; ?>">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo $mentor['name']; ?></h5>
                        <p class="text-primary mb-2"><?php echo $mentor['specialization']; ?></p>
                        <div class="mb-2">
                            <i class="fas fa-star text-warning"></i>
                            <span><?php echo number_format($mentor['rating'], 1); ?></span>
                            <span class="text-muted ms-2">(<?php echo $mentor['total_sessions']; ?> sessions)</span>
                        </div>
                        <p class="card-text text-muted small"><?php echo substr($mentor['bio'], 0, 80); ?>...</p>
                        <a href="pages/mentor-profile.php?id=<?php echo $mentor['id']; ?>" class="btn btn-outline-primary btn-sm">View Profile</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Doctors -->
<section class="featured-doctors py-5">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2 class="display-5 fw-bold">Expert Doctors</h2>
            <a href="pages/doctors.php" class="btn btn-outline-primary">View All <i class="fas fa-arrow-right ms-2"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach($doctors as $doctor): ?>
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm doctor-card">
                    <img src="assets/uploads/profiles/<?php echo $doctor['profile_image'] ?? 'default-avatar.png'; ?>" 
                         class="card-img-top rounded-circle mx-auto mt-4" style="width: 100px; height: 100px; object-fit: cover;" 
                         alt="<?php echo $doctor['name']; ?>">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo $doctor['name']; ?></h5>
                        <p class="text-info mb-2"><?php echo $doctor['specialization']; ?></p>
                        <p class="small text-muted"><?php echo $doctor['degree']; ?></p>
                        <div class="mb-2">
                            <i class="fas fa-star text-warning"></i>
                            <span><?php echo number_format($doctor['rating'], 1); ?></span>
                            <span class="text-muted ms-2">Fee: ৳<?php echo $doctor['consultation_fee']; ?></span>
                        </div>
                        <a href="pages/doctor-profile.php?id=<?php echo $doctor['id']; ?>" class="btn btn-outline-info btn-sm">View Profile</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- AI Chatbot Preview -->
<section class="chatbot-preview py-5 bg-primary bg-opacity-10">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="display-5 fw-bold mb-4">Meet "মনের বন্ধু"</h2>
                <p class="lead mb-4">Our AI companion available 24/7 to listen, understand, and support you in Bengali. No judgement, just support.</p>
                <ul class="list-unstyled mb-4">
                    <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i> Bengali language support</li>
                    <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i> Mood tracking & analysis</li>
                    <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i> Crisis detection & support</li>
                    <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i> Weekly wellness reports</li>
                </ul>
                <a href="auth/register.php" class="btn btn-primary btn-lg">Try AI Chat Now</a>
            </div>
            <div class="col-lg-6">
                <img src="assets/images/illustrations/chatbot-preview.svg" alt="Chatbot Preview" class="img-fluid">
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">What Our Users Say</h2>
            <p class="lead text-muted">Real stories from real people</p>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-4">"মনের বন্ধু আমাকে আমার উদ্বেগ কাটিয়ে উঠতে অনেক সাহায্য করেছে। ২৪/৭ সাপোর্ট পাচ্ছি।"</p>
                        <div class="d-flex align-items-center">
                            <img src="assets/images/avatars/female-1.png" class="rounded-circle me-3" width="50" alt="User">
                            <div>
                                <h6 class="mb-0">রহিমা খাতুন</h6>
                                <small class="text-muted">Student, Dhaka</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-4">"The mentors here are amazing! Got career guidance that changed my life."</p>
                        <div class="d-flex align-items-center">
                            <img src="assets/images/avatars/male-1.png" class="rounded-circle me-3" width="50" alt="User">
                            <div>
                                <h6 class="mb-0">Karim Ahmed</h6>
                                <small class="text-muted">Professional, Chittagong</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-4">"Easy appointment with doctors, digital prescriptions, and medicine reminders."</p>
                        <div class="d-flex align-items-center">
                            <img src="assets/images/avatars/female-2.png" class="rounded-circle me-3" width="50" alt="User">
                            <div>
                                <h6 class="mb-0">Nasrin Sultana</h6>
                                <small class="text-muted">Homemaker, Sylhet</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Blog Section -->
<section class="blog-section py-5 bg-light">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2 class="display-5 fw-bold">Latest from Blog</h2>
            <a href="pages/blog.php" class="btn btn-outline-primary">View All <i class="fas fa-arrow-right ms-2"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach($blogs as $blog): ?>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <img src="assets/images/blog/<?php echo $blog['image']; ?>" class="card-img-top" alt="<?php echo $blog['title']; ?>">
                    <div class="card-body">
                        <div class="text-muted small mb-2"><?php echo date('M d, Y', strtotime($blog['created_at'])); ?></div>
                        <h5 class="card-title"><?php echo $blog['title']; ?></h5>
                        <p class="card-text text-muted"><?php echo substr($blog['content'], 0, 100); ?>...</p>
                        <a href="pages/blog-single.php?id=<?php echo $blog['id']; ?>" class="btn btn-link p-0">Read More <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5 bg-primary text-white">
    <div class="container py-5 text-center">
        <h2 class="display-5 fw-bold mb-4">Ready to Start Your Wellness Journey?</h2>
        <p class="lead mb-4">Join thousands of Bangladeshis who are taking care of their mental health</p>
        <a href="auth/register.php" class="btn btn-light btn-lg px-5">Sign Up Free</a>
    </div>
</section>

<!-- Footer -->
<footer class="footer bg-dark text-white pt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <img src="assets/images/logo-white.png" alt="Mentora" height="40" class="mb-3">
                <p>Your companion in mental wellness journey. Available 24/7 for all Bangladeshis.</p>
                <div class="social-links">
                    <a href="#" class="text-white me-2"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="text-white me-2"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-white me-2"><i class="fab fa-linkedin fa-lg"></i></a>
                    <a href="#" class="text-white me-2"><i class="fab fa-instagram fa-lg"></i></a>
                </div>
            </div>
            <div class="col-md-2 mb-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="pages/about.php" class="text-white-50">About Us</a></li>
                    <li><a href="pages/services.php" class="text-white-50">Services</a></li>
                    <li><a href="pages/mentors.php" class="text-white-50">Mentors</a></li>
                    <li><a href="pages/doctors.php" class="text-white-50">Doctors</a></li>
                    <li><a href="pages/blog.php" class="text-white-50">Blog</a></li>
                </ul>
            </div>
            <div class="col-md-2 mb-4">
                <h5>Support</h5>
                <ul class="list-unstyled">
                    <li><a href="pages/faq.php" class="text-white-50">FAQ</a></li>
                    <li><a href="pages/contact.php" class="text-white-50">Contact</a></li>
                    <li><a href="pages/privacy-policy.php" class="text-white-50">Privacy Policy</a></li>
                    <li><a href="pages/terms.php" class="text-white-50">Terms of Use</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Contact Info</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> Dhaka, Bangladesh</li>
                    <li class="mb-2"><i class="fas fa-phone me-2"></i> +880 1234-567890</li>
                    <li class="mb-2"><i class="fas fa-envelope me-2"></i> info@mentora.com</li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary">
        <div class="row pb-3">
            <div class="col text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Mentora. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>

</body>
</html>