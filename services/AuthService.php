<?php
require_once __DIR__ . '/../dao/UserDao.php';
// Nema potrebe za require Config.php ako je već uključen u index.php prije AuthService-a
// Ali ako AuthService može biti instanciran nezavisno, onda je dobro imati ga.
// Za sada, pretpostavljamo da je Config.php dostupan zbog index.php.

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService {
    private $userDao;
    // private $jwtSecret; // Više nećemo čuvati jwtSecret kao polje instance
    private $jwtExpiry;

    public function __construct() {
        $this->userDao = new UserDao();
        // $this->jwtSecret = getenv('JWT_SECRET') ?: 'your-secret-key'; // UKLANJAMO OVO
        $this->jwtExpiry = 3600; // 1 sat - može i ovo da dođe iz Config klase ako želite
    }

    public function generateToken($user) {
        $issuedAt = time();
        $expire = $issuedAt + $this->jwtExpiry;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'] ?? 'user' // Osiguraj da role postoji ili ima default
            ]
        ];
        
        // DIREKTNO KORISTIMO Config::JWT_SECRET() ZA GENERISANJE
        return JWT::encode($payload, Config::JWT_SECRET(), 'HS256');
    }

    public function verifyToken($token) {
        error_log("AuthService: verifyToken called. Token (first 20 chars): " . substr($token, 0, 20));
        // DIREKTNO KORISTIMO Config::JWT_SECRET() ZA VALIDACIJU
        $currentJwtSecret = Config::JWT_SECRET(); 
        error_log("AuthService: verifyToken - Using JWT Secret (first 5 chars): " . substr($currentJwtSecret, 0, 5) . "... Actual length: " . strlen($currentJwtSecret));
        try {
            $decoded = JWT::decode($token, new Key($currentJwtSecret, 'HS256'));
            error_log("AuthService: verifyToken - Token decoded successfully. User from token: " . json_encode($decoded->user ?? ($decoded['user'] ?? null) ) );
            return (array) $decoded;
        } catch (\Firebase\JWT\ExpiredException $e) {
            error_log("AuthService: verifyToken - ExpiredException: " . $e->getMessage());
            throw new Exception('Token has expired: ' . $e->getMessage());
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            error_log("AuthService: verifyToken - SignatureInvalidException: " . $e->getMessage());
            throw new Exception('Invalid token signature: ' . $e->getMessage());
        } catch (\Firebase\JWT\BeforeValidException $e) { // Token još nije validan (nbf)
            error_log("AuthService: verifyToken - BeforeValidException: " . $e->getMessage());
            throw new Exception('Token not yet valid: ' . $e->getMessage());
        } catch (\Exception $e) { // Ostali JWT izuzeci (npr. \Firebase\JWT\InvalidArgumentException) ili generalni izuzeci
            error_log("AuthService: verifyToken - General Exception: " . $e->getMessage() . " | Exception Type: " . get_class($e));
            throw new Exception('Invalid token processing error: ' . $e->getMessage());
        }
    }

    public function login($email, $password) {
        error_log("AuthService: login method called for email: " . $email . " and password supplied: " . !empty($password));
        $user = $this->userDao->getByEmail($email);
        
        if (!$user) {
            error_log("AuthService: User not found by email '{$email}' in userDao->getByEmail.");
            throw new Exception('Invalid credentials. User not found.');
        }

        if (!password_verify($password, $user['password_hash'])) {
            error_log("AuthService: Password verification failed for user '{$email}'. Hash from DB: '{$user['password_hash']}'");
            throw new Exception('Invalid credentials. Password mismatch.');
        }

        error_log("AuthService: Credentials valid for '{$email}'. Generating token.");
        $token = $this->generateToken($user);

        return [
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'] ?? 'user'
            ]
        ];
    }

    public function register($username, $email, $password) {
        // Provjera da li korisnik već postoji
        if ($this->userDao->getByEmail($email)) {
            throw new Exception('Email already exists');
        }
        if ($this->userDao->getByUsername($username)) {
            throw new Exception('Username already exists');
        }

        // Kreiranje korisnika
        $user = $this->userDao->register($username, $email, $password);
        
        // Generisanje tokena
        return [
            'token' => $this->generateToken($user),
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'] ?? 'user'
            ]
        ];
    }
} 