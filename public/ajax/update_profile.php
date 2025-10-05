<?php
// require_once __DIR__ . '/../../config.php';
// require_once __DIR__ . '/../../classes/Database.php';
// require_once __DIR__ . '/../../classes/Auth.php';
// require_once __DIR__ . '/../../classes/User.php';
// header('Content-Type: application/json');
//
// $auth = new Auth();
// $userId = $auth->check();
// if (!$userId) { echo json_encode(['success'=>false,'message'=>'Not logged in']); exit; }
//
// $fullname = $_POST['fullname'] ?? '';
// $age = isset($_POST['age']) ? intval($_POST['age']) : null;
// $file = $_FILES['profile_pic'] ?? null;
//
// $user = new User($ALLOWED_MIME);
// $res = $user->updateProfile($userId, $fullname, $age, $file);
// echo json_encode($res);


require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Auth.php';

header('Content-Type: application/json');
if (ob_get_length()) {
    ob_clean();
}

try {
    $auth = new Auth();
    $userId = $auth->check();
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }

    $fullname = trim($_POST['fullname'] ?? '');
    $dobRaw   = trim($_POST['dob'] ?? '');   // optional
    $file     = $_FILES['profile_pic'] ?? null;

    if (strlen($fullname) < 2) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid full name.']);
        exit;
    }

    // dob → age (if provided)
    // $age = null;
    // if ($dobRaw !== '') {
    //     $ts = strtotime($dobRaw);
    //     if ($ts !== false) {
    //         $dob = new DateTime(date('Y-m-d', $ts));
    //         $now = new DateTime();
    //         $age = (int)$dob->diff($now)->y;
    //     }
    // }
    $age = null;
    if ($dobRaw !== '') {
        $ts = strtotime($dobRaw);
        if ($ts !== false) {
            $dob = new DateTime(date('Y-m-d', $ts));
            $now = new DateTime();

            if ($dob > $now) {
                // 🚫 Future DOB not allowed
                $errorMsg = "Date of birth cannot be in the future.";
            } else {
                $age = (int)$dob->diff($now)->y;
            }
        } else {
            $errorMsg = "Invalid date format.";
        }
    }
    // Later you can use:
if ($errorMsg !== null) {
    echo $errorMsg;   // Or return as JSON
}


    // validate file if present
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
            $mime = $finfo->file($file['tmp_name']);
            if (isset($ALLOWED_MIME) && is_array($ALLOWED_MIME) && !in_array($mime, $ALLOWED_MIME, true)) {
                echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, GIF images are allowed.']);
                exit;
            }
        }
    } else {
        $file = null;
    }

    // call User model
    $userModel = new User($ALLOWED_MIME ?? null);
    $res = $userModel->updateProfile($userId, $fullname, $age, $file);

    if (!isset($res['success']) || !$res['success']) {
        echo json_encode(['success' => false, 'message' => $res['message'] ?? 'Update failed']);
        exit;
    }

    // build response
    $out = [
        'success' => true,
        'message' => 'Profile updated',
        'fullname' => $res['fullname'] ?? $fullname
    ];
    if (array_key_exists('age', $res)) $out['age'] = $res['age'];
    if (!empty($res['profile_pic'])) {
        $out['profile_pic_url'] = UPLOAD_URL . $res['profile_pic'];
    }

    echo json_encode($out);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Exception: ' . $e->getMessage()]);
}
exit;
