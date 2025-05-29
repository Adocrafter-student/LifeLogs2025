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
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get user by email
     */
    public function getByEmail($email) {
        error_log("UserDao: getByEmail preparing statement for email: " . $email);
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(); // Koristi default FETCH_ASSOC
        error_log("UserDao: User fetched for email '" . $email . "': " . ($user ? 'User data found' : 'NULL (User not found)'));
        if ($user) { error_log("UserDao: Fetched user data: " . json_encode($user)); }
        return $user;
    }

    /**
     * Create new user with hashed password
     */
    public function register($username, $email, $password, $bio = null, $avatar_url = null, $role = 'user') {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        return $this->add([
            'username' => $username,
            'email' => $email,
            'password_hash' => $password_hash,
            'bio' => $bio,
            'avatar_url' => $avatar_url,
            'role' => $role
        ]);
    }

    /**
     * Check if login credentials are valid
     */
    public function login($email, $password) {
        error_log("UserDao: (Deprecated login method within UserDao) called for email: " . $email);
        $user = $this->getByEmail($email);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            error_log("UserDao: (Deprecated login) Password verified for " . $email);
            return $user;
        }
        error_log("UserDao: (Deprecated login) Password verification FAILED for " . $email . " or user not found.");
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
        $stmt = $this->connection->prepare("SELECT * FROM users");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 