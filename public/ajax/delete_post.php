
// require_once __DIR__ . '/../../config.php';
// require_once __DIR__ . '/../../classes/Database.php';
// require_once __DIR__ . '/../../classes/Auth.php';
// require_once __DIR__ . '/../../classes/Post.php';
// header('Content-Type: application/json');
//
// $auth = new Auth();
// $userId = $auth->check();
// if (!$userId) { echo json_encode(['success'=>false,'message'=>'Not logged in']); exit; }
//
// $postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
// $post = new Post();
// $res = $post->delete($postId, $userId);
// echo json_encode($res); -->
//

<!--
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Post.php';

header('Content-Type: application/json');

// Check login
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

// Validate input
if (empty($_POST['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'Post ID required']);
    exit;
}
$postId = (int)$_POST['post_id'];

try {
    $postModel = new Post();

    // Find post and ensure ownership
    $stmt = $postModel->pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$postId, $userId]);
    $post = $stmt->fetch();

    if (!$post) {
        echo json_encode(['success' => false, 'message' => 'Post not found or unauthorized']);
        exit;
    }

    // Delete image if exists
    if (!empty($post['image'])) {
        $filePath = UPLOAD_DIR . $post['image'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // Delete post record
    $del = $postModel->pdo->prepare("DELETE FROM posts WHERE id = ?");
    $del->execute([$postId]);

    echo json_encode(['success' => true, 'message' => 'Post deleted']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} -->


<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Auth.php';
require_once __DIR__ . '/../../classes/Post.php';

header('Content-Type: application/json');
ob_clean();

$auth = new Auth();
$userId = $auth->check();
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
if ($postId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Post ID']);
    exit;
}

try {
    $postModel = new Post();
    $res = $postModel->delete($postId, $userId);

    // 👇 यहीं add करो — server ka JSON log file me bhi save hoga
    file_put_contents(__DIR__ . '/debug_delete.log',
        date('H:i:s') . " RESPONSE: " . json_encode($res) . PHP_EOL,
        FILE_APPEND
    );

    echo json_encode($res);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
