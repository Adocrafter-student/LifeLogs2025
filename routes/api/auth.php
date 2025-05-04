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
        $username = $data->username;
        $email = $data->email;
        $password = $data->password;

        if (!$username || !$email || !$password) {
            Flight::halt(400, 'Username, email and password are required');
        }

        try {
            $user = Flight::user_service()->createUser($username, $email, $password);
            Flight::json($user, 201);
        } catch (Exception $e) {
            Flight::halt(400, $e->getMessage());
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
        $email = $data->email;
        $password = $data->password;

        if (!$email || !$password) {
            Flight::halt(400, 'Email and password are required');
        }

        try {
            $user = Flight::user_service()->getUserByEmail($email);
            if (!password_verify($password, $user['password'])) {
                Flight::halt(401, 'Invalid credentials');
            }
            $token = Flight::auth_service()->generateToken($user['id']);
            Flight::json(['token' => $token]);
        } catch (Exception $e) {
            Flight::halt(401, 'Invalid credentials');
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

        $token = Flight::request()->getHeader('Authorization');
        if (!$token) {
            Flight::halt(401, 'No token provided');
        }

        try {
            $token = str_replace('Bearer ', '', $token);
            $decoded = Flight::auth_service()->validateToken($token);
            $user = Flight::user_service()->getUserById($decoded->user_id);
            unset($user['password']);
            Flight::json($user);
        } catch (Exception $e) {
            Flight::halt(401, 'Invalid token');
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