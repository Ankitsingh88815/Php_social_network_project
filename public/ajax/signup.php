<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';

header('Content-Type: application/json');
// किसी accidental echo/notice को हटाने के लिए
if (ob_get_length()) {
    ob_clean();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    // ---- collect & sanitize ----
    $fullname  = trim($_POST['fullname'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    // re-password किसी भी नाम से आ सकता है
    $repass    = $_POST['re_password'] ?? ($_POST['password2'] ?? '');

    // age या dob (yyyy-mm-dd) में से कुछ
    // $age       = null;
    // $dobRaw    = trim($_POST['dob'] ?? '');
    // if ($dobRaw !== '') {
    //     // dob -> age
    //     $ts = strtotime($dobRaw);
    //     if ($ts !== false) {
    //         $dob = new DateTime(date('Y-m-d', $ts));
    //         $now = new DateTime();
    //         $age = abs((int)$dob->diff($now)->y);
    //     }
    // }
    $age = null;
    $dobRaw = trim($_POST['dob'] ?? '');

    if ($dobRaw !== '') {
        $dob = DateTime::createFromFormat('Y-m-d', $dobRaw);
        $errors = DateTime::getLastErrors();

        if ($dob === false || !empty($errors['warning_count']) || !empty($errors['error_count'])) {
            $errorMsg = "Invalid date format. Please use YYYY-MM-DD.";
        } else {
            $now = new DateTime();

            // 🚫 Future DOB not allowed
            if ($dob > $now) {
                $errorMsg = "Date of birth cannot be in the future.";
                $age = -1;
                //exit;
            } else {
                $age = $dob->diff($now)->y;
            }
        }
    } else {
        // plain age field (number)
        if (isset($_POST['age']) && $_POST['age'] !== '') {
            $age = (int)$_POST['age'];
        }
    }
    // if ($errorMsg !== null) {
    //     // echo "<p style='color:red;'>$errorMsg</p>";
    //     $age =-1;
    // } 

    $file = $_FILES['profile_pic'] ?? null;

    // ---- server-side validation ----
    if (strlen($fullname) < 2) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid full name.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
        exit;
    }

    if ($repass !== '' && $password !== $repass) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        exit;
    }

    if ($age !== null && ($age < 0 || $age > 120)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid age.']);
        exit;
    }

    // ---- file validation (optional but safer) ----
    if ($file && isset($file['error']) && $file['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Profile picture upload error.']);
            exit;
        }
        if (!empty($file['size']) && defined('MAX_FILE_SIZE') && $file['size'] > MAX_FILE_SIZE) {
            echo json_encode(['success' => false, 'message' => 'Profile picture too large.']);
            exit;
        }
        if (!empty($file['tmp_name'])) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($file['tmp_name']);
            if (isset($ALLOWED_MIME) && is_array($ALLOWED_MIME) && !in_array($mime, $ALLOWED_MIME, true)) {
                echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, GIF images are allowed.']);
                exit;
            }
        }
    } else {
        // no file uploaded: keep $file as null so User::create can handle default
        $file = null;
    }

    // ---- create user via model ----
    // Note: User::__construct($allowedMime = null) — अगर तुम्हारा constructor ऐसे है,
    // तो नीचे $ALLOWED_MIME pass करना ठीक है. नहीं है तो बिना param के instantiate करें।
    $u = new User($ALLOWED_MIME ?? null);

    $res = $u->create($fullname, $email, $password, $age, $file);

    // Expecting ['success'=>bool, 'message'=>..., 'user_id'=>...]
    echo json_encode($res);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Exception: ' . $e->getMessage()]);
}
exit;
