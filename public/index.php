<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth = new Auth();
if ($auth->check()) {
    header('Location: profile.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- OPTION A (recommended): relative path -->
  <link rel="stylesheet" href="assets/css/style.css">

  <!-- OPTION B: absolute path (use only if A fails on your setup) -->
  <!-- <link rel="stylesheet" href="/social_network_project/public/assets/css/style.css"> -->
</head>
<body>
  <div class="card login-card" style="max-width:420px;margin:40px auto;">
    <h2>Social Network Login</h2>

    <!-- No action/onsubmit (JS handles it) -->
    <form id="loginForm" onsubmit="return false;" action="javascript:void(0)">
  <label>Email</label>
  <input type="email" name="email" required>
  <label>Password</label>
  <input type="password" name="password" required>
  <button id="loginBtn" type="button">Login</button>
  <!-- <p>Don't have account? <a href="signup.php">Sign up</a></p> -->
  <p>Don't have account? <a href="signup.php" id="signupLink">Sign up</a></p>

</form>
<div id="loginMsg"></div>

  </div>

  <!-- jQuery first, then your main.js -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="assets/js/main.js"></script>
</body>
</html>
