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

    public function createUser($username, $email, $password, $bio = null, $avatar_url = null, $role = 'user') {
        error_log("UserService->createUser called with: username={$username}, email={$email}, bio (is_null): " . (is_null($bio) ? 'yes' : 'no') . ", avatar_url (is_null): " . (is_null($avatar_url) ? 'yes' : 'no') . ", role: {$role}");
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
        return $this->userDao->register($username, $email, $password, $bio, $avatar_url, $role);
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

    public function updateUser($id, array $data, $isAdmin = false) {
        error_log("UserService: updateUser called for ID: {$id}, isAdmin: " . ($isAdmin ? 'true' : 'false'));
        error_log("UserService: Data for update: " . json_encode($data));

        $allowed_fields_user = ['username', 'email', 'password', 'bio', 'avatar_url'];
        // Admin može mijenjati i 'role' ili druga polja ako ih dodate
        $allowed_fields_admin = array_merge($allowed_fields_user, ['role']); 

        $params = [];
        $current_user_data = $this->userDao->getById($id); // Dohvati trenutne podatke

        if (!$current_user_data) {
            throw new Exception("User not found for update.");
        }

        $allowed_fields = $isAdmin ? $allowed_fields_admin : $allowed_fields_user;

        foreach ($allowed_fields as $field) {
            if (array_key_exists($field, $data)) {
                if ($field === 'password') {
                    if (!empty($data[$field])) { // Samo ako je lozinka poslata i nije prazna
                        $params['password_hash'] = password_hash($data[$field], PASSWORD_DEFAULT);
                        error_log("UserService: Password will be updated for user ID: {$id}.");
                    }
                } elseif ($field === 'email') {
                    // Provjera da li novi email već postoji za drugog korisnika
                    if (isset($data['email']) && $data['email'] !== $current_user_data['email']) {
                        if ($this->userDao->getByEmail($data['email'])) {
                            throw new Exception("Email already exists for another user.");
                        }
                        $params['email'] = $data['email'];
                    }
                } elseif ($field === 'username') {
                     // Provjera da li novi username već postoji za drugog korisnika
                    if (isset($data['username']) && $data['username'] !== $current_user_data['username']) {
                        if ($this->userDao->getByUsername($data['username'])) {
                            throw new Exception("Username already exists for another user.");
                        }
                        $params['username'] = $data['username'];
                    }
                }

                else if (isset($data[$field])) { // Ako je ključ prisutan u $data
                    // Ako je vrijednost različita od trenutne ILI ako admin mijenja (admin može forsirati istu vrijednost ako želi)
                    // ili ako je polje 'role' i admin mijenja (dozvoli postavljanje iste role ako treba)
                    if($data[$field] !== $current_user_data[$field] || $isAdmin || ($field === 'role' && $isAdmin)){
                         $params[$field] = $data[$field];
                    }
                }
            }
        }

        if (empty($params)) {
            error_log("UserService: No valid fields to update for user ID: {$id} or data is not changed.");
            return $current_user_data; // Vraćamo neizmijenjene podatke kao uspjeh (ništa nije ažurirano)
        }

        error_log("UserService: Params for DAO update for user ID {$id}: " . json_encode($params));
        $success = $this->userDao->update($id, $params);

        if ($success) {
            return $this->userDao->getById($id); // Vrati ažurirane podatke korisnika
        }
        return null; // Ili baci izuzetak ako ažuriranje nije uspjelo u DAO
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