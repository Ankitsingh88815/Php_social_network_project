<?php
// classes/Post.php
require_once __DIR__ . '/Database.php';

class Post {
    private $pdo;
    private $allowedMime;
    public function __construct($allowedMime = ['image/jpeg','image/png','image/gif']) {
        $this->pdo = Database::getInstance()->getConnection();
        $this->allowedMime = $allowedMime;
    }

    private function sanitizeFilename($name){
        $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
        return time() . '_' . bin2hex(random_bytes(4)) . '_' . $name;
    }

    private function saveFile($file){
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception('File too large (max 2MB).');
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $this->allowedMime)) {
            throw new Exception('Invalid image type.');
        }
        $filename = $this->sanitizeFilename($file['name']);
        $destination = UPLOAD_DIR . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Failed to move uploaded file.');
        }
        return $filename;
    }

    public function create($userId, $description = null, $file = null) {
        $image = null;
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            try {
                $image = $this->saveFile($file);
            } catch (Exception $e) {
                return ['success'=>false,'message'=>$e->getMessage()];
            }
        }
        $stmt = $this->pdo->prepare("INSERT INTO posts (user_id, description, image) VALUES (?,?,?)");
        $stmt->execute([$userId, $description, $image]);
        return ['success'=>true,'message'=>'Post created','post_id'=>$this->pdo->lastInsertId()];
    }
    
    public function delete($postId, $userId) {
        // ensure owner
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
        $stmt->execute([$postId, $userId]);
        $post = $stmt->fetch();
        if (!$post) return ['success'=>false,'message'=>'Post not found or unauthorized'];

        // delete image file if exists
        if (!empty($post['image'])) {
            $file = UPLOAD_DIR . $post['image'];
            if (file_exists($file)) @unlink($file);
        }

        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$postId]);
        return ['success'=>true,'message'=>'Post deleted'];
    }

    public function getByUser($userId) {
        $stmt = $this->pdo->prepare("SELECT p.*, u.fullname, u.profile_pic FROM posts p JOIN users u ON p.user_id = u.id WHERE p.user_id = ? ORDER BY p.created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }


public function getRecentAll($limit = 50) {
    $stmt = $this->pdo->prepare("
        SELECT p.*, u.fullname, u.profile_pic,
            (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = p.id AND pl.value = 1) AS likes,
            (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = p.id AND pl.value = -1) AS dislikes
        FROM posts p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
        LIMIT ?
    ");
    $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

    // get single post with counts
    public function getWithCounts($postId) {
        $stmt = $this->pdo->prepare("
            SELECT p.*,
                (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = p.id AND pl.value = 1) AS likes,
                (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = p.id AND pl.value = -1) AS dislikes
            FROM posts p WHERE p.id = ?
        ");
        $stmt->execute([$postId]);
        return $stmt->fetch();
    }
}
