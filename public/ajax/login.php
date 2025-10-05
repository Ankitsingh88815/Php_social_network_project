<?php
// require_once __DIR__ . '/../../classes/Database.php';
// require_once __DIR__ . '/../../classes/Auth.php';
// header('Content-Type: application/json');
//
// $email = $_POST['email'] ?? '';
// $password = $_POST['password'] ?? '';
//
// $auth = new Auth();
// $res = $auth->login($email, $password);
// echo json_encode($res);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Auth.php';

header('Content-Type: application/json');
if (ob_get_length()) { ob_clean(); }

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Invalid method']); exit;
  }
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  $auth  = new Auth();
  $res   = $auth->login($email, $pass); // ['success'=>bool,'message'=>...]
  echo json_encode($res);
} catch (Throwable $e) {
  echo json_encode(['success'=>false,'message'=>'Exception: '.$e->getMessage()]);
}
exit;
