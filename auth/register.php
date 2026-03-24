<!DOCTYPE html>
<html>
<head>

<title>Register | Mentora</title>

<link rel="stylesheet" href="../assets/css/bootstrap.min.css">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-6">

<div class="card shadow">

<div class="card-body">

<h4 class="text-center mb-4">Create Mentora Account</h4>

<?php
if(isset($_GET['error'])){
echo "<div class='alert alert-danger'>".$_GET['error']."</div>";
}
?>

<form method="POST" action="register-process.php">

<div class="mb-3">
<label>Full Name</label>
<input type="text" name="full_name" class="form-control" required>
</div>

<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control" required>
</div>

<div class="mb-3">
<label>Phone</label>
<input type="text" name="phone" class="form-control">
</div>

<div class="mb-3">
<label>Password</label>
<input type="password" name="password" class="form-control" required>
</div>

<button class="btn btn-success w-100">Register</button>

</form>

<p class="text-center mt-3">
Already have account? <a href="login.php">Login</a>
</p>

</div>
</div>

</div>
</div>
</div>

</body>
</html>