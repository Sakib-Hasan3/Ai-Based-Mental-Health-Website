<?php

require_once("../db.php");

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

$insert = $conn->prepare("INSERT INTO users(full_name,email,phone,password) VALUES(?,?,?,?)");

$insert->bind_param("ssss",$name,$email,$phone,$password_hash);

if($insert->execute()){
    header("Location: login.php?success=Account created successfully");
    exit();
} else {
    header("Location: register.php?error=Registration failed");
    exit();
}

?>