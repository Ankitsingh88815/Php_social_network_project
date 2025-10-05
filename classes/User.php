<?php

// classes/User.php
require_once __DIR__ . '/Database.php';

class User {
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

        if (!empty($file['size']) && defined('MAX_FILE_SIZE') && $file['size'] > MAX_FILE_SIZE) {
            throw new Exception('File too large (max 2MB).');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $this->allowedMime, true)) {
            throw new Exception('Invalid image type.');
        }

        $filename = $this->sanitizeFilename($file['name']);
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }
        $destination = UPLOAD_DIR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Failed to move uploaded file.');
        }

        return $filename;
    }

    public function create($fullname, $email, $password, $age = null, $file = null) {
        // Server-side validations
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success'=>false,'message'=>'Invalid email'];
        }
        if (strlen($password) < 6) {
            return ['success'=>false,'message'=>'Password must be at least 6 characters'];
        }

        // Unique email
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success'=>false,'message'=>'Email already registered'];
        }

        // Hash password
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $profilePic = null;
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            try {
                $profilePic = $this->saveFile($file);
            } catch (Exception $e) {
                return ['success'=>false,'message'=>$e->getMessage()];
            }
        }

        $stmt = $this->pdo->prepare("INSERT INTO users (fullname,email,password,age,profile_pic) VALUES (?,?,?,?,?)");
        $stmt->execute([$fullname, $email, $hash, $age, $profilePic]);

        return ['success'=>true,'message'=>'User created'];
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Update profile: only fullname, age, profile_pic
     * - Email/Password untouched
     * - If new image provided: save it and delete old one
     * - Returns fields needed for instant UI update
     */
    public function updateProfile($id, $fullname, $age = null, $file = null) {
        // Fetch current to know old picture (for deletion) and current values
        $current = $this->findById($id);
        if (!$current) {
            return ['success'=>false,'message'=>'User not found'];
        }

        $newPic = null;
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            try {
                $newPic = $this->saveFile($file);
            } catch (Exception $e) {
                return ['success'=>false,'message'=>$e->getMessage()];
            }
        }

        // If you want to KEEP old age when $age is null, uncomment next two lines:
        // if ($age === null) {
        //     $age = $current['age']; // keep existing
        // }

        // Build dynamic update
        $set = ['fullname = ?'];
        $params = [$fullname];

        // If you want to write NULL when no age provided, keep as is:
        $set[] = 'age = ?';
        $params[] = $age;

        if ($newPic) {
            $set[] = 'profile_pic = ?';
            $params[] = $newPic;
        }

        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $set) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        // Delete old picture if replaced
        if ($newPic && !empty($current['profile_pic'])) {
            $oldFile = UPLOAD_DIR . $current['profile_pic'];
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        return [
            'success'       => true,
            'message'       => 'Profile updated',
            'fullname'      => $fullname,
            'age'           => $age,
            'profile_pic'   => $newPic ?: $current['profile_pic'] // return final filename
        ];
    }
}
