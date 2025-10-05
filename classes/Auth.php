<?php
// classes/Auth.php
require_once __DIR__ . '/User.php';

class Auth {
    private $userModel;
    public function __construct() {
        $this->userModel = new User();
    }

    public function login($email, $password) {
        $user = $this->userModel->findByEmail($email);
        if (!$user) return ['success'=>false,'message'=>'Invalid credentials'];
        if (!password_verify($password, $user['password'])) return ['success'=>false,'message'=>'Invalid credentials'];

        // login
        $_SESSION['user_id'] = $user['id'];
        return ['success'=>true,'message'=>'Logged in'];
    }

    public function logout() {
        session_unset();
        session_destroy();
        return ['success'=>true];
    }

    public function check() {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : false;
    }

    public function user() {
        if ($this->check()) {
            return $this->userModel->findById($_SESSION['user_id']);
        }
        return null;
    }
}
