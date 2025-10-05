<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Auth.php';
require_once __DIR__ . '/../../classes/Post.php';

header('Content-Type: application/json');
ob_clean();

$auth = new Auth();
$userId = $auth->check();
if (!$userId) { echo json_encode(['success'=>false,'message'=>'Not logged in']); exit; }

$description = $_POST['description'] ?? null;
$file = $_FILES['image'] ?? null;

$postModel = new Post();
$res = $postModel->create($userId, $description, $file);

if (!$res['success']) {
    echo json_encode($res);
    exit;
}

// fetch the inserted post with user info and counts
$pdo = Database::getInstance()->getConnection();
$stmt = $pdo->prepare("
  SELECT p.*, u.fullname, u.profile_pic,
    (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = p.id AND pl.value = 1) AS likes,
    (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = p.id AND pl.value = -1) AS dislikes
  FROM posts p
  JOIN users u ON p.user_id = u.id
  WHERE p.id = ?
");
$stmt->execute([ $res['post_id'] ]);
$post = $stmt->fetch();

if (!$post) {
    echo json_encode(['success'=>false,'message'=>'Post created but failed to retrieve it']);
    exit;
}

// sanitize paths: prefix images with UPLOAD_URL
if (!empty($post['image'])) $post['image_url'] = UPLOAD_URL . $post['image'];
else $post['image_url'] = null;
if (!empty($post['profile_pic'])) $post['profile_pic_url'] = UPLOAD_URL . $post['profile_pic'];
else $post['profile_pic_url'] = null;

echo json_encode(['success'=>true, 'post' => $post]);
exit;
