<?php
// Start session
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: ../user/dashboard.php");
    exit;
}

$error = '';
$success = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user'; // user, mentor, doctor
    
    // Additional fields based on role
    $specialization = trim($_POST['specialization'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $degree = trim($_POST['degree'] ?? '');

    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Database Connection
        $db_connected = false;
        if (file_exists('../db.php')) {
            include_once '../db.php';
            if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
                $db_connected = true;
            }
        }

        if ($db_connected) {
            // Check if email exists
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $error = "Email already registered. Please login.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Begin Transaction
                $conn->begin_transaction();

                try {
                    // 1. Insert into Users Table
                    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                    $stmt->bind_param("sssss", $name, $email, $phone, $hashed_password, $role);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to create user account.");
                    }

                    $user_id = $conn->insert_id;

                    // 2. Insert into Role-Specific Tables
                    if ($role === 'mentor') {
                        $m_stmt = $conn->prepare("INSERT INTO mentors (user_id, specialization, experience_years, verification_status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
                        $m_stmt->bind_param("iss", $user_id, $specialization, $experience);
                        if (!$m_stmt->execute()) throw new Exception("Failed to create mentor profile.");
                    } elseif ($role === 'doctor') {
                        $d_stmt = $conn->prepare("INSERT INTO doctors (user_id, specialization, degree, verification_status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
                        $d_stmt->bind_param("iss", $user_id, $specialization, $degree);
                        if (!$d_stmt->execute()) throw new Exception("Failed to create doctor profile.");
                    }

                    // Commit Transaction
                    $conn->commit();

                    // Set success message and redirect
                    $_SESSION['success_message'] = "Registration successful! Please login to continue.";
                    header("Location: login.php");
                    exit;

                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Registration failed: " . $e->getMessage();
                }
            }
            $check_stmt->close();
            $stmt->close();
        } else {
            // Demo mode if DB is not connected
            $_SESSION['success_message'] = "Demo Mode: Registration successful! (Database not connected)";
            header("Location: login.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Mentora</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --who-blue-dark: #003366;
            --who-blue-light: #0056b3;
            --who-accent: #fdb913;
            --bg-light: #f0f4f8;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--bg-light);
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Bar */
        .top-bar {
            background-color: var(--who-blue-dark);
            color: white;
            padding: 0.5rem 0;
            font-size: 0.9rem;
        }
        .top-bar a { color: white; text-decoration: none; margin-left: 15px; }

        /* Header */
        .main-header {
            background: white;
            padding: 1rem 0;
            border-bottom: 1px solid #dee2e6;
        }
        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--who-blue-light);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Register Container */
        .register-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem;
        }
        .register-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 600px;
            overflow: hidden;
        }
        .card-header-custom {
            background: var(--who-blue-dark);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        .card-header-custom h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
        }
        .card-body-custom {
            padding: 2rem;
        }

        /* Form Styles */
        .form-label {
            font-weight: 500;
            color: var(--who-blue-dark);
            margin-bottom: 0.5rem;
        }
        .form-control, .form-select {
            border-radius: 4px;
            border: 1px solid #ced4da;
            padding: 0.6rem 0.8rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--who-blue-light);
            box-shadow: 0 0 0 0.2rem rgba(0, 86, 179, 0.25);
        }
        .btn-register {
            background-color: var(--who-blue-light);
            color: white;
            font-weight: 600;
            padding: 0.7rem;
            border-radius: 4px;
            border: none;
            width: 100%;
            transition: background 0.3s;
        }
        .btn-register:hover {
            background-color: var(--who-blue-dark);
            color: white;
        }
        .alert-custom {
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .role-info {
            background: #eef2ff;
            border-left: 4px solid var(--who-blue-light);
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: #555;
        }
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }
        .login-link a {
            color: var(--who-blue-light);
            font-weight: 600;
            text-decoration: none;
        }

        /* Dynamic Field Animation */
        .dynamic-fields {
            display: none;
            animation: fadeIn 0.4s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        footer {
            background: #f8f9fa;
            padding: 1.5rem 0;
            text-align: center;
            font-size: 0.85rem;
            color: #666;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>

    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container d-flex justify-content-between">
            <span><i class="fas fa-globe me-2"></i>Global</span>
            <div>
                <a href="../index.php"><i class="fas fa-home me-1"></i> Home</a>
                <a href="login.php"><i class="fas fa-sign-in-alt me-1"></i> Login</a>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="main-header">
        <div class="container">
            <a href="../index.php" class="logo-text">
                <i class="fas fa-brain"></i> MENTORA
            </a>
        </div>
    </header>

    <!-- Register Section -->
    <section class="register-container">
        <div class="register-card">
            <div class="card-header-custom">
                <h2>Create Account</h2>
                <p class="mb-0 opacity-75 small">Join our mental wellness community</p>
            </div>
            <div class="card-body-custom">
                
                <?php if($error): ?>
                    <div class="alert alert-danger alert-custom" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="registerForm">
                    <!-- Full Name -->
                    <div class="mb-3">
                        <label for="fullname" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="fullname" name="fullname" required placeholder="e.g. Rahim Ahmed">
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="name@example.com">
                    </div>

                    <!-- Phone -->
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="+880 1XXX XXXXXX">
                    </div>

                    <!-- Role Selection -->
                    <div class="mb-3">
                        <label for="role" class="form-label">I am registering as a <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required onchange="toggleFields()">
                            <option value="user">Service User (Patient/Student)</option>
                            <option value="mentor">Mentor (Career/Life Coach)</option>
                            <option value="doctor">Doctor (Psychiatrist/Psychologist)</option>
                        </select>
                    </div>

                    <!-- Dynamic Info Box -->
                    <div id="roleInfo" class="role-info">
                        <i class="fas fa-info-circle me-2"></i> <span id="roleText">As a user, you can access AI chat, book sessions, and track your progress.</span>
                    </div>

                    <!-- Dynamic Fields: Mentor -->
                    <div id="mentorFields" class="dynamic-fields">
                        <div class="mb-3">
                            <label for="m_specialization" class="form-label">Area of Expertise</label>
                            <input type="text" class="form-control" id="m_specialization" name="specialization" placeholder="e.g. Career Counseling, Academic Guidance">
                        </div>
                        <div class="mb-3">
                            <label for="m_experience" class="form-label">Years of Experience</label>
                            <input type="number" class="form-control" id="m_experience" name="experience" placeholder="e.g. 5">
                        </div>
                    </div>

                    <!-- Dynamic Fields: Doctor -->
                    <div id="doctorFields" class="dynamic-fields">
                        <div class="mb-3">
                            <label for="d_specialization" class="form-label">Medical Specialization</label>
                            <input type="text" class="form-control" id="d_specialization" name="specialization" placeholder="e.g. Psychiatry, Clinical Psychology">
                        </div>
                        <div class="mb-3">
                            <label for="d_degree" class="form-label">Highest Degree</label>
                            <input type="text" class="form-control" id="d_degree" name="degree" placeholder="e.g. MBBS, FCPS, PhD">
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="6">
                        <div class="form-text">Must be at least 6 characters long.</div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn btn-register">Create Account</button>

                    <div class="login-link">
                        Already have an account? <a href="login.php">Login here</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Mentora. All rights reserved.</p>
            <small class="text-muted">Dedicated to improving global mental health.</small>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleFields() {
            const role = document.getElementById('role').value;
            const mentorFields = document.getElementById('mentorFields');
            const doctorFields = document.getElementById('doctorFields');
            const roleText = document.getElementById('roleText');

            // Reset
            mentorFields.style.display = 'none';
            doctorFields.style.display = 'none';
            
            // Remove required attributes from hidden fields to prevent validation errors
            document.querySelectorAll('#mentorFields input, #doctorFields input').forEach(input => {
                input.removeAttribute('required');
            });

            if (role === 'mentor') {
                mentorFields.style.display = 'block';
                document.querySelector('#m_specialization').setAttribute('required', 'true');
                roleText.textContent = "As a mentor, you will guide students and professionals. Your profile will be verified by our team before going live.";
            } else if (role === 'doctor') {
                doctorFields.style.display = 'block';
                document.querySelector('#d_specialization').setAttribute('required', 'true');
                document.querySelector('#d_degree').setAttribute('required', 'true');
                roleText.textContent = "As a doctor, you will provide medical consultations. Please ensure your credentials are accurate for verification.";
            } else {
                roleText.textContent = "As a user, you can access AI chat, book sessions with mentors/doctors, and track your mental wellness progress.";
            }
        }

        // Initialize on load
        window.onload = toggleFields;
    </script>
</body>
</html>