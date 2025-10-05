<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Auth.php';
require_once __DIR__ . '/../../classes/Like.php';
header('Content-Type: application/json');

$auth = new Auth();
$userId = $auth->check();
if (!$userId) { echo json_encode(['success'=>false,'message'=>'Not logged in']); exit; }

$postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
$value = isset($_POST['value']) ? intval($_POST['value']) : 0; // 1 or -1

$l = new Like();
$res = $l->toggle($userId, $postId, $value);
echo json_encode($res);
