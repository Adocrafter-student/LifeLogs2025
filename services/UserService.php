<?php
require_once __DIR__ . '/../dao/UserDao.php';

class UserService {
    private $userDao;

    public function __construct() {
        $this->userDao = new UserDao();
    }

    public function createUser($username, $email, $password, $bio = null, $avatar_url = null) {
        // Validacija
        if (empty($username) || empty($email) || empty($password)) {
            throw new Exception("Sva obavezna polja moraju biti popunjena");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Nevažeća email adresa");
        }

        // Provjera da li korisnik već postoji
        if ($this->userDao->getByUsername($username)) {
            throw new Exception("Korisničko ime već postoji");
        }

        if ($this->userDao->getByEmail($email)) {
            throw new Exception("Email adresa već postoji");
        }

        // Kreiranje korisnika kroz DAO
        return $this->userDao->register($username, $email, $password, $bio, $avatar_url);
    }

    public function getUserById($id) {
        return $this->userDao->getById($id);
    }

    public function getUserByUsername($username) {
        return $this->userDao->getByUsername($username);
    }

    public function getUserByEmail($email) {
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
            throw new Exception("Nema podataka za ažuriranje");
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
}
?> 