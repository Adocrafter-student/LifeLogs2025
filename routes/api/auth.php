<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Start output buffering to catch any stray output
ob_start();

// Helper to clear all output buffers
if (!function_exists('clearAllOutputBuffers')) {
    function clearAllOutputBuffers() {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();
    }
}

Flight::group('/api/auth', function() {
    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Register user",
     *     description="Register a new user",
     *     tags={"auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "email", "password"},
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */
    Flight::route('POST /register', function() {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        $data = Flight::request()->data;
        $username = isset($data->username) ? $data->username : null;
        $email = isset($data->email) ? $data->email : null;
        $password = isset($data->password) ? $data->password : null;

        if (empty($username) || empty($email) || empty($password)) {
            Flight::halt(400, json_encode(['message' => 'Username, email, and password are required.']));
            return;
        }

        // Dodatna osnovna validacija (primjer)
        if (strlen($password) < 6) {
            Flight::halt(400, json_encode(['message' => 'Password must be at least 6 characters long.']));
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flight::halt(400, json_encode(['message' => 'Invalid email format.']));
            return;
        }

        try {
            // Uklonjeno default_avatar_url jer se ne koristi u createUser
            $user = Flight::userService()->createUser($username, $email, $password);
            
            // Uspješna registracija - prema frontend logici, ne šaljemo token odmah
            // Frontend preusmjerava na login stranicu
            // Šaljemo kreiranog korisnika (bez osjetljivih informacija poput lozinke)
            unset($user['password']); // Osiguraj da se lozinka ne vraća
            Flight::json($user, 201); // 201 Created

        } catch (PDOException $e) {
            // Greška vezana za bazu, npr. dupli email/username ako postoji unique constraint
            // Logovati $e->getMessage() na serveru za interne potrebe
            error_log("PDOException in /api/auth/register: " . $e->getMessage()); 
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                Flight::halt(400, json_encode(['message' => 'Email or username already exists.']));
            } else {
                Flight::halt(500, json_encode(['message' => 'Database error during registration.']));
            }
        } catch (Exception $e) {
            error_log("Exception in /api/auth/register: " . $e->getMessage());
            Flight::halt(400, json_encode(['message' => $e->getMessage()]));
        }
    });

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Login user",
     *     description="Login an existing user",
     *     tags={"auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     )
     * )
     */
    Flight::route('POST /login', function() {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        $data = Flight::request()->data;
        $email = isset($data->email) ? trim($data->email) : null;
        $password = isset($data->password) ? $data->password : null;

        error_log("API Login Route: Received email: '{$email}', password supplied: " . !empty($password));

        if (empty($email) || empty($password)) {
            Flight::halt(400, json_encode(['message' => 'Email and password are required.']));
            return;
        }

        try {
            // Korisnik se dohvaća preko UserService
            $user = Flight::userService()->getUserByEmail($email);

            if (!$user) {
                error_log("API Login Route: User not found for email: " . $email);
                Flight::halt(401, json_encode(['message' => 'Invalid credentials. User not found.']));
                return;
            }
            
            error_log("API Login Route: User found for email: " . $email . ". Comparing provided password with hash: " . $user['password_hash']);
            if (!password_verify($password, $user['password_hash'])) { 
                error_log("API Login Route: Password verification failed for email: " . $email);
                Flight::halt(401, json_encode(['message' => 'Invalid credentials. Password mismatch.']));
                return;
            }
            
            // Ako su kredencijali ispravni, AuthService generiše token i vraća podatke
            error_log("API Login Route: Credentials verified for email: " . $email . ". Calling AuthService->login.");
            $authService = Flight::auth_service();
            // AuthService->login metoda sama radi dohvaćanje korisnika i provjeru lozinke, 
            // ali pošto smo to već uradili, idealno bi bilo da AuthService ima metodu koja samo generiše token za već autentifikovanog korisnika.
            // Za sada, ostavljamo kako je u AuthService, on će ponoviti provjeru.
            $tokenData = $authService->login($email, $password); // Ovo će ponovo provjeriti lozinku

            error_log("API Login Route: AuthService->login returned. Attempting to send JSON response.");
            error_log("API Login Route: Token data for email '{$email}': " . print_r($tokenData, true));

            // Flight::json($tokenData); // Komentarisano radi testa
            $jsonOutput = json_encode($tokenData);
            $jsonError = json_last_error();
            $jsonErrorMsg = json_last_error_msg();

            if ($jsonError !== JSON_ERROR_NONE) {
                error_log("API Login Route: JSON encoding error for email '{$email}': " . $jsonErrorMsg . " (Code: " . $jsonError . ")");
                // Možda poslati generičku grešku servera umjesto da se oslanjamo na prazan odgovor
                // Ali za sada, samo logujemo
            } else {
                error_log("API Login Route: JSON encoding successful for email '{$email}'. Outputting JSON.");
                echo $jsonOutput;
            }

            error_log("API Login Route: JSON response supposedly sent (manually) for email '{$email}'. Terminating script execution.");
            exit; // Eksplicitni prekid izvršavanja

        } catch (Exception $e) {
            // AuthService->login baca Exception ako kredencijali nisu dobri.
            error_log("API Login Route Exception for email '{$email}': " . $e->getMessage());
            Flight::halt(401, json_encode(['message' => $e->getMessage()])); // Vrati poruku iz AuthService izuzetka
        }
    });

    /**
     * @OA\Get(
     *     path="/api/auth/me",
     *     summary="Get current user",
     *     description="Get the currently authenticated user",
     *     tags={"auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User details"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    Flight::route('GET /me', function() {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        error_log("GET /me ROUTE: Handler entered.");

        try {
            error_log("GET /me ROUTE: Attempting to retrieve user information from Flight state (set by middleware).");
            
            // AuthMiddleware log: "[AuthMiddleware] User i jwt_token USPJEŠNO postavljeni u Flight. User ID: 11"
            // Ovo sugeriše da je 'user_id' ili 'user' (kao objekat/niz sa 'id' poljem) postavljen.
            // Pokušaćemo prvo sa 'user_id', zatim sa 'user->id' ili 'user['id']'.
            
            $user_id = Flight::get('user_id');

            if (!$user_id) {
                error_log("GET /me ROUTE: Flight::get('user_id') returned null. Trying Flight::get('user').");
                $user_payload_from_token = Flight::get('user');
                
                if ($user_payload_from_token && is_object($user_payload_from_token) && isset($user_payload_from_token->id)) {
                    $user_id = $user_payload_from_token->id;
                    error_log("GET /me ROUTE: Successfully retrieved user ID '{$user_id}' from Flight::get('user')->id.");
                } elseif ($user_payload_from_token && is_array($user_payload_from_token) && isset($user_payload_from_token['id'])) {
                    $user_id = $user_payload_from_token['id'];
                    error_log("GET /me ROUTE: Successfully retrieved user ID '{$user_id}' from Flight::get('user')['id'].");
                } else {
                    $actual_user_payload_log = is_null($user_payload_from_token) ? "null" : (is_scalar($user_payload_from_token) ? strval($user_payload_from_token) : gettype($user_payload_from_token));
                    error_log("GET /me ROUTE: Flight::get('user') is '{$actual_user_payload_log}' or does not contain a usable 'id'. Halting.");
                    Flight::halt(401, json_encode(['message' => 'User not authenticated or user ID not found in token payload. Middleware might not be setting user details correctly.']));
                    return; // Flight::halt bi trebao prekinuti izvršavanje
                }
            } else {
                error_log("GET /me ROUTE: Successfully retrieved user ID '{$user_id}' directly from Flight::get('user_id').");
            }

            if (empty($user_id)) { // Dodatna provjera za svaki slučaj
                 error_log("GET /me ROUTE: User ID is empty after attempting to retrieve it. Halting.");
                 Flight::halt(401, json_encode(['message' => 'User ID could not be determined. Authentication failed.']));
                 return;
            }

            error_log("GET /me ROUTE: Attempting to fetch user by ID: " . $user_id . " using Flight::userService()->getUserById()");
            $user = Flight::userService()->getUserById($user_id);

            if (!$user) {
                error_log("GET /me ROUTE: User not found in database for ID: " . $user_id);
                Flight::halt(404, json_encode(['message' => 'User not found in database.']));
                return;
            }
            error_log("GET /me ROUTE: User successfully fetched from database for ID: " . $user_id . ". Data: " . print_r($user, true));

            // Ukloni osjetljive podatke prije slanja klijentu
            unset($user['password_hash']); // Pretpostavljamo da je kolona 'password_hash'
            unset($user['password']);    // Za svaki slučaj

            error_log("GET /me ROUTE: Preparing to send user data: " . print_r($user, true));

            $jsonOutput = json_encode($user);
            $jsonError = json_last_error();
            
            if ($jsonError !== JSON_ERROR_NONE) {
                $jsonErrorMsg = json_last_error_msg();
                error_log("GET /me ROUTE: JSON encoding error for user ID '{$user_id}': " . $jsonErrorMsg . " (Code: " . $jsonError . ")");
                Flight::halt(500, json_encode(['message' => 'Error encoding user data to JSON: ' . $jsonErrorMsg]));
                return;
            }

            error_log("GET /me ROUTE: JSON encoding successful for user ID '{$user_id}'. Outputting JSON.");
            echo $jsonOutput;
            error_log("GET /me ROUTE: JSON response supposedly sent (manually) for user ID '{$user_id}'. Terminating script execution.");
            exit;

        } catch (PDOException $e) { // Specifično za greške baze
            error_log("GET /me ROUTE: PDOException caught: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . " Trace: " . $e->getTraceAsString());
            Flight::halt(500, json_encode(['message' => 'Database error while fetching user details: ' . $e->getMessage()]));
        } catch (Exception $e) { // Opšte greške koje su tipa Exception
            error_log("GET /me ROUTE: Exception caught: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . " Trace: " . $e->getTraceAsString());
            Flight::halt(500, json_encode(['message' => 'Server error while fetching user details: ' . $e->getMessage()]));
        } catch (Throwable $t) { // Hvata sve ostale greške (Error, TypeError, itd.)
            error_log("GET /me ROUTE: Throwable caught: " . $t->getMessage() . " at " . $t->getFile() . ":" . $t->getLine() . " Trace: " . $t->getTraceAsString());
            Flight::halt(500, json_encode(['message' => 'Critical server error: ' . $t->getMessage()]));
        }
    });

    /**
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     summary="Refresh token",
     *     description="Refresh the authentication token",
     *     tags={"auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    Flight::route('POST /refresh', function() {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        $token = Flight::request()->getHeader('Authorization');
        if (!$token) {
            Flight::halt(401, 'No token provided');
        }

        try {
            $token = str_replace('Bearer ', '', $token);
            $decoded = Flight::auth_service()->validateToken($token);
            $newToken = Flight::auth_service()->generateToken($decoded->user_id);
            Flight::json(['token' => $newToken]);
        } catch (Exception $e) {
            Flight::halt(401, 'Invalid token');
        }
    });
}); 