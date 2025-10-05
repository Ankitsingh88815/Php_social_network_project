<?php
// classes/Like.php
require_once __DIR__ . '/Database.php';

class Like {
    private $pdo;
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // value = 1 (like) or -1 (dislike)
    public function toggle($userId, $postId, $value) {
        if (!in_array($value, [1, -1])) return ['success'=>false,'message'=>'Invalid value'];

        // check existing
        $stmt = $this->pdo->prepare("SELECT * FROM post_likes WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$userId, $postId]);
        $row = $stmt->fetch();

        if ($row) {
            if ((int)$row['value'] === $value) {
                // same reaction, remove (toggle off)
                $del = $this->pdo->prepare("DELETE FROM post_likes WHERE id = ?");
                $del->execute([$row['id']]);
            } else {
                // different reaction, update
                $upd = $this->pdo->prepare("UPDATE post_likes SET value = ? WHERE id = ?");
                $upd->execute([$value, $row['id']]);
            }
        } else {
            $ins = $this->pdo->prepare("INSERT INTO post_likes (user_id, post_id, value) VALUES (?,?,?)");
            $ins->execute([$userId, $postId, $value]);
        }

        // return updated counts
        $stmtLike = $this->pdo->prepare("SELECT COUNT(*) FROM post_likes WHERE post_id = ? AND value = 1");
        $stmtLike->execute([$postId]);
        $likes = $stmtLike->fetchColumn();

        $stmtDis = $this->pdo->prepare("SELECT COUNT(*) FROM post_likes WHERE post_id = ? AND value = -1");
        $stmtDis->execute([$postId]);
        $dislikes = $stmtDis->fetchColumn();

        // check what current user now has (0 none, 1 or -1)
        $stmtNow = $this->pdo->prepare("SELECT value FROM post_likes WHERE user_id = ? AND post_id = ?");
        $stmtNow->execute([$userId, $postId]);
        $now = $stmtNow->fetch();

        return [
            'success'=>true,
            'likes' => intval($likes),
            'dislikes' => intval($dislikes),
            'current' => $now ? intval($now['value']) : 0
        ];
    }
}
