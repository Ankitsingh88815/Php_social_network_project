<?php
// require_once __DIR__ . '/../../config.php';
// require_once __DIR__ . '/../../classes/Database.php';
// require_once __DIR__ . '/../../classes/Auth.php';
// header('Content-Type: application/json');
//
// $auth = new Auth();
// $res = $auth->logout();
// echo json_encode($res);

require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json');
if (ob_get_length()) { ob_clean(); }

try {
    // Session kill
    if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

    // Unset all session variables
    $_SESSION = [];

    // Delete session cookie
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    // Destroy session
    session_destroy();

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Exception: '.$e->getMessage()]);
}
exit;
