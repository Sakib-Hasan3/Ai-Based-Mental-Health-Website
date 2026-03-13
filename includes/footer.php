<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <h5>About Mentora</h5>
                <p>Bangladesh's first integrated platform combining AI-powered mental health support, professional mentorship, and medical consultation.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 mb-4">
                <h5>Quick Links</h5>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>/pages/about.php">About Us</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/services.php">Services</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/pricing.php">Pricing</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/blog.php">Blog</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/contact.php">Contact</a></li>
                </ul>
            </div>
            
            <div class="col-lg-2 col-md-4 mb-4">
                <h5>For Users</h5>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>/auth/login.php">Login</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/auth/register.php">Register</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/faq.php">FAQ</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/privacy-policy.php">Privacy Policy</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/terms.php">Terms of Use</a></li>
                </ul>
            </div>
            
            <div class="col-lg-4 col-md-4 mb-4">
                <h5>Contact Info</h5>
                <ul class="footer-links">
                    <li><i class="fas fa-map-marker-alt me-2"></i> Dhaka, Bangladesh</li>
                    <li><i class="fas fa-phone me-2"></i> <?php echo SITE_PHONE; ?></li>
                    <li><i class="fas fa-envelope me-2"></i> <?php echo SITE_EMAIL; ?></li>
                </ul>
                
                <h5 class="mt-4">Emergency Support</h5>
                <p class="text-danger fw-bold">24/7 Helpline: 12345</p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Custom JS -->
<script src="<?php echo ASSETS_PATH; ?>js/main.js"></script>

</body>
</html>