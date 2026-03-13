<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Meta Tags -->
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'Mentora - Your Companion in Mental Wellness Journey'; ?>">
    <meta name="keywords" content="<?php echo isset($page_keywords) ? $page_keywords : 'mental health, mentorship, medical consultation, AI chatbot, Bangladesh, wellness'; ?>">
    <meta name="author" content="Mentora">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts for Bengali -->
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/style.css">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo ASSETS_PATH; ?>images/favicon.ico" type="image/x-icon">
</head>
<body>

<!-- Navigation -->
<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
            <img src="<?php echo ASSETS_PATH; ?>images/logo.png" alt="<?php echo SITE_NAME; ?>" height="40">
        </a>
        
        <button class="navbar-toggler" type="button" onclick="toggleMenu()">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="navbar-nav" id="mainNav">
            <a href="<?php echo SITE_URL; ?>" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a>
            <a href="<?php echo SITE_URL; ?>/pages/about.php" class="nav-link">About</a>
            <a href="<?php echo SITE_URL; ?>/pages/services.php" class="nav-link">Services</a>
            <a href="<?php echo SITE_URL; ?>/pages/pricing.php" class="nav-link">Pricing</a>
            <a href="<?php echo SITE_URL; ?>/pages/blog.php" class="nav-link">Blog</a>
            <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="nav-link">Contact</a>
            
            <?php if(isLoggedIn()): ?>
                <div class="dropdown d-inline-block">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> <?php echo $_SESSION['user_name'] ?? 'Account'; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <?php if(isAdmin()): ?>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/dashboard.php">Admin Dashboard</a></li>
                        <?php elseif(isMentor()): ?>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/mentor/dashboard.php">Mentor Dashboard</a></li>
                        <?php elseif(isDoctor()): ?>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/doctor/dashboard.php">Doctor Dashboard</a></li>
                        <?php else: ?>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/user/dashboard.php">Dashboard</a></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/auth/logout.php">Logout</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/auth/login.php" class="nav-link">Login</a>
                <a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn btn-primary btn-sm">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Flash Message -->
<div class="container mt-3">
    <?php displayFlashMessage(); ?>
</div>

<script>
function toggleMenu() {
    document.getElementById('mainNav').classList.toggle('show');
}
</script>