<?php

require_once("../db.php");
require_once("../includes/functions.php");

$name = trim($_POST['full_name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$password = trim($_POST['password']);

if(empty($name) || empty($email) || empty($password)){
    header("Location: register.php?error=Please fill required fields");
    exit();
}

$stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
$stmt->bind_param("s",$email);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows > 0){
    header("Location: register.php?error=Email already exists");
    exit();
}

$password_hash = password_hash($password,PASSWORD_DEFAULT);

// Debug: Log the data being inserted
error_log("Attempting to insert user: $name, $email, $phone");

// Insert user (is_verified defaults to 0 in database)
$insert = $conn->prepare("INSERT INTO users(full_name,email,phone,password) VALUES(?,?,?,?)");

if(!$insert) {
    error_log("Prepare failed: " . $conn->error);
    header("Location: register.php?error=Database error: " . urlencode($conn->error));
    exit();
}

$insert->bind_param("ssss",$name,$email,$phone,$password_hash);

if(!$insert->execute()){
    error_log("Execute failed: " . $insert->error);
    header("Location: register.php?error=Insert failed: " . urlencode($insert->error));
    exit();
}

// Get the newly created user ID
$user_id = $insert->insert_id;

// Generate email verification token
$token = generateEmailToken($user_id);

if($token) {
    // Send verification email
    $email_sent = sendVerificationEmail($email, $token, $name);
    
    if($email_sent) {
        // Redirect to verification sent page
        header("Location: verify-email-sent.php?email=" . urlencode($email));
        exit();
    } else {
        header("Location: register.php?error=Could not send verification email. Please try again.");
        exit();
    }
} else {
    error_log("Token generation failed for user: $user_id");
    header("Location: register.php?error=Registration failed. Please try again.");
    exit();
}

?>