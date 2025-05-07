<?php
require_once __DIR__ . '/BaseDao.php';

class UserDao extends BaseDao {
    
    public function __construct() {
        parent::__construct("users");
    }

    /**
     * Get user by username
     */
    public function getByUsername($username) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get user by email
     */
    public function getByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new user with hashed password
     */
    public function register($username, $email, $password, $bio = null, $avatar_url = null) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        return $this->add([
            'username' => $username,
            'email' => $email,
            'password_hash' => $password_hash,
            'bio' => $bio,
            'avatar_url' => $avatar_url
        ]);
    }

    /**
     * Check if login credentials are valid
     */
    public function login($email, $password) {
        $user = $this->getByEmail($email);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        
        return false;
    }

    /**
     * Update user profile
     */
    public function updateProfile($id, $bio, $avatar_url = null) {
        $params = ['bio' => $bio];
        if ($avatar_url) {
            $params['avatar_url'] = $avatar_url;
        }
        return $this->update($id, $params);
    }

    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM users");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 