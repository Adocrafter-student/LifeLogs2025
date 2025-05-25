<?php
require_once __DIR__ . '/../dao/UserDao.php';

class UserService {
    private $userDao;

    public function __construct() {
        try {
            $this->userDao = new UserDao();
            error_log("UserService: UserDao initialized successfully."); // DEBUG
        } catch (Exception $e) {
            error_log("UserService: Failed to initialize UserDao: " . $e->getMessage()); // DEBUG
            throw $e; // Ponovo baci izuzetak da se greška propagira
        }
    }

    public function createUser($username, $email, $password, $bio = null, $avatar_url = null) {
        error_log("UserService->createUser called with: username={$username}, email={$email}, bio (is_null): " . (is_null($bio) ? 'yes' : 'no') . ", avatar_url (is_null): " . (is_null($avatar_url) ? 'yes' : 'no')); // DEBUG
        // Validacija
        if (empty($username) || empty($email) || empty($password)) {
            throw new Exception("All required fields must be filled");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address");
        }

        // Check if user already exists
        if ($this->userDao->getByUsername($username)) {
            throw new Exception("Username already exists");
        }

        if ($this->userDao->getByEmail($email)) {
            throw new Exception("Email address already exists");
        }

        // create user through DAO
        return $this->userDao->register($username, $email, $password, $bio, $avatar_url);
    }

    public function getUserById($id) {
        return $this->userDao->getById($id);
    }

    public function getUserByUsername($username) {
        return $this->userDao->getByUsername($username);
    }

    public function getUserByEmail($email) {
        error_log("UserService: getUserByEmail called for email: " . $email);
        return $this->userDao->getByEmail($email);
    }

    public function updateUser($id, $username = null, $email = null, $password = null, $bio = null, $avatar_url = null) {
        $params = [];

        if ($username !== null) {
            $params['username'] = $username;
        }

        if ($email !== null) {
            $params['email'] = $email;
        }

        if ($password !== null) {
            $params['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($bio !== null) {
            $params['bio'] = $bio;
        }

        if ($avatar_url !== null) {
            $params['avatar_url'] = $avatar_url;
        }

        if (empty($params)) {
            throw new Exception("No data to update");
        }

        return $this->userDao->update($id, $params);
    }

    public function deleteUser($id) {
        return $this->userDao->delete($id);
    }

    public function verifyPassword($email, $password) {
        $user = $this->userDao->getByEmail($email);
        if (!$user) {
            return false;
        }
        
        return password_verify($password, $user['password_hash']);
    }

    public function login($email, $password) {
        return $this->userDao->login($email, $password);
    }

    public function updateProfile($id, $bio, $avatar_url = null) {
        return $this->userDao->updateProfile($id, $bio, $avatar_url);
    }

    public function getAllUsers() {
        return $this->userDao->getAll();
    }
}
?> 