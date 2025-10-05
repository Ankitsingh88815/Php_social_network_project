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
  <title>Join Social Network</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    .signup-wrap {
      max-width: 720px;
      margin: 40px auto;
      background: #fff;
      border-radius: 16px;
      padding: 32px 28px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    }

    .signup-title {
      text-align: center;
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 24px;
      color: #1a237e;
      letter-spacing: 0.05em;
    }

    .avatar-uploader {
      text-align: center;
      margin-bottom: 24px;
    }

    .avatar-uploader .avatar-ring {
      width: 110px;
      height: 110px;
      border-radius: 50%;
      background: #e6e9f4;
      margin: 0 auto 14px;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 3px solid #3949ab;
      transition: box-shadow 0.3s ease;
    }

    .avatar-uploader .avatar-ring:hover {
      box-shadow: 0 0 16px #3949ab;
    }

    .avatar-uploader img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 50%;
    }

    .btn-outline {
      display: inline-block;
      padding: 10px 16px;
      font-size: 15px;
      border: 2px solid #3949ab;
      color: #3949ab;
      border-radius: 10px;
      cursor: pointer;
      background: #fff;
      font-weight: 600;
      transition: background-color 0.2s ease, color 0.2s ease;
    }

    .btn-outline:hover {
      background: #3949ab;
      color: #fff;
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 18px;
    }

    .form-grid .full {
      grid-column: 1 / -1;
    }

    .form-field label {
      display: block;
      font-size: 14px;
      color: #444;
      margin: 8px 4px 6px 2px;
      font-weight: 600;
    }

    .form-field input,
    .form-field textarea {
      width: 100%;
      padding: 14px 16px;
      border: 2px solid #d1d7e0;
      border-radius: 12px;
      background: #f9fbfe;
      outline: none;
      font-family: inherit;
      font-size: 15px;
      transition: border-color 0.25s ease, background-color 0.25s ease;
      box-sizing: border-box;
    }

    .form-field input:focus,
    .form-field textarea:focus {
      border-color: #3949ab;
      background: #fff;
    }

    .hint {
      font-size: 13px;
      color: #6f7287;
      margin-top: 6px;
      font-style: italic;
    }

    .actions {
      text-align: center;
      margin-top: 28px;
    }

    .btn-primary {
      width: 100%;
      padding: 16px 0;
      background: #3949ab;
      color: #fff;
      border: none;
      border-radius: 14px;
      cursor: pointer;
      font-weight: 700;
      font-size: 17px;
      letter-spacing: 0.04em;
      transition: background-color 0.3s ease, transform 0.1s ease;
    }

    .btn-primary:hover {
      background: #2c387e;
    }

    .btn-primary:active {
      transform: translateY(2px);
    }

    #signupMsg {
      text-align: center;
      margin-top: 18px;
      font-weight: 700;
      font-size: 15px;
      min-height: 1.4em;
    }

    .err {
      color: #d32f2f;
    }

    .ok {
      color: #388e3c;
    }

    @media (max-width: 720px) {
      .form-grid {
        grid-template-columns: 1fr;
      }

      .signup-wrap {
        max-width: 100%;
        padding: 28px 24px;
        border-radius: 14px;
        margin: 24px 12px;
      }
    }
  </style>
</head>

<body style="background:#e2e7ee;">

  <div class="signup-wrap">
    <div class="signup-title">Join Social Network</div>

    <form id="signupForm" enctype="multipart/form-data" onsubmit="return false;">
      <!-- Avatar -->
      <div class="avatar-uploader">
        <div class="avatar-ring">
          <img id="avatarPreview"
            alt="profile preview"
            src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='108' height='108'><rect width='108' height='108' rx='54' fill='%23e9edf3'/><circle cx='54' cy='44' r='20' fill='%23c7cfdb'/><rect x='22' y='68' width='64' height='26' rx='13' fill='%23c7cfdb'/></svg>">
        </div>
        <label class="btn-outline" for="profilePicInput">Upload Profile Pic</label>
        <input id="profilePicInput" type="file" name="profile_pic" accept="image/*" style="display:none">
      </div>

      <!-- Fields -->
      <div class="form-grid">
        <div class="form-field full">
          <label>Full Name</label>
          <input type="text" name="fullname" placeholder="John Doe" required>
        </div>

        <div class="form-field full">
          <label>Date of Birth</label>
          <input type="date" name="dob" placeholder="dd/mm/yyyy">
        </div>

        <div class="form-field full">
          <label>Email Address</label>
          <input type="email" name="email" placeholder="john@doe.com" required>
        </div>

        <div class="form-field">
          <label>Password</label>
          <input type="password" name="password" id="pw1" minlength="6" required>
          <div class="hint">Use A-Z, a-z, 0-9, !@#$%^&*</div>
        </div>

        <div class="form-field">
          <label>Re-Password</label>
          <input type="password" id="pw2" minlength="6" required>
          <div class="hint" id="pwHint"></div>
        </div>
      </div>

      <div class="actions">
        <button id="signupBtn" class="btn-primary" type="submit">Sign Up</button>
      </div>

      <div id="signupMsg"></div>
    </form>
  </div>

  <!-- jQuery then your JS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- Live preview + client-side checks (small inline helper) -->
  <script>
    // live avatar preview
    document.getElementById('profilePicInput').addEventListener('change', function(e) {
      const file = e.target.files && e.target.files[0];
      if (!file) return;
      const url = URL.createObjectURL(file);
      const img = document.getElementById('avatarPreview');
      img.src = url;
      img.onload = () => URL.revokeObjectURL(url); // free memory
    });

    // confirm password live hint
    const pw1 = document.getElementById('pw1');
    const pw2 = document.getElementById('pw2');
    const hint = document.getElementById('pwHint');

    function checkPw() {
      if (!pw2.value) {
        hint.textContent = '';
        return;
      }
      if (pw1.value !== pw2.value) {
        hint.textContent = "Passwords do not match";
        hint.style.color = "#d62828";
      } else {
        hint.textContent = "Passwords match ✓";
        hint.style.color = "#2a9d8f";
      }
    }
    pw1.addEventListener('input', checkPw);
    pw2.addEventListener('input', checkPw);
  </script>

  <!-- Your global main.js (has AJAX for #signupForm) -->
  <script src="assets/js/main.js"></script>

  <!-- Ensure main.js validates re-password too -->
  <script>
    // augment existing signup handler in main.js: block submit if pw mismatch
    $(document).on('submit', '#signupForm', function(e){
      var p1 = $('#pw1').val().trim();
      var p2 = $('#pw2').val().trim();
      if (p1 !== p2) {
        e.preventDefault();
        $('#signupMsg').removeClass('ok').addClass('err').text('Passwords do not match.');
        return false;
      }
      // also ensure either name/email present (HTML5 required already handles)
      $('#signupMsg').text('');
    });
    // $(document).on('submit', '#signupForm', function(e) {
    //   var p1 = $('#pw1').val().trim();
    //   var p2 = $('#pw2').val().trim();
    //   var dob = $('input[name="dob"]').val();

    //   // Password mismatch
    //   if (p1 !== p2) {
    //     e.preventDefault();
    //     $('#signupMsg').removeClass('ok').addClass('err').text('Passwords do not match.');
    //     return false;
    //   }

    //   //Future DOB validation
    //   if (dob) {
    //     var dobDate = new Date(dob);
    //     var today = new Date();

    //     if (dobDate > today) {
    //       e.preventDefault();
    //       $('#signupMsg').removeClass('ok').addClass('err').text('❌ Date of birth cannot be in the future.');
    //       return false;
    //     }
    //   }

    //   $('#signupMsg').text('');
    // });
  </script>
</body>

</html>