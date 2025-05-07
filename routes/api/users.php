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

Flight::group('/api/users', function() {
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get all users",
     *     description="Get all users with pagination",
     *     tags={"users"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of users"
     *     )
     * )
     */
    Flight::route('GET /', function() {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        $page = Flight::request()->query->page ?? 1;
        $limit = Flight::request()->query->limit ?? 10;

        try {
            $users = Flight::user_service()->getAllUsers($page, $limit);
            Flight::json($users);
        } catch (Exception $e) {
            Flight::halt(500, $e->getMessage());
        }
    });

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get user by ID",
     *     description="Get a specific user by their ID",
     *     tags={"users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    Flight::route('GET /@id', function($id) {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $user = Flight::user_service()->getUserById($id);
            Flight::json($user);
        } catch (Exception $e) {
            Flight::halt(404, $e->getMessage());
        }
    });

    /**
     * @OA\Get(
     *     path="/api/users/username/{username}",
     *     summary="Get user by username",
     *     description="Get a specific user by their username",
     *     tags={"users"},
     *     @OA\Parameter(
     *         name="username",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    Flight::route('GET /username/@username', function($username) {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $user = Flight::user_service()->getUserByUsername($username);
            Flight::json($user);
        } catch (Exception $e) {
            Flight::halt(404, $e->getMessage());
        }
    });

    /**
     * @OA\Get(
     *     path="/api/users/email/{email}",
     *     summary="Get user by email",
     *     description="Get a specific user by their email",
     *     tags={"users"},
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="email")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    Flight::route('GET /email/@email', function($email) {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $user = Flight::user_service()->getUserByEmail($email);
            Flight::json($user);
        } catch (Exception $e) {
            Flight::halt(404, $e->getMessage());
        }
    });

    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Create user",
     *     description="Create a new user",
     *     tags={"users"},
     *     security={{"bearerAuth": {}}},
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
     *         description="User created successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    Flight::route('POST /', function() {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        $token = Flight::request()->getHeader('Authorization');
        if (!$token) {
            Flight::halt(401, 'No token provided');
        }

        $data = Flight::request()->data;
        $username = $data->username;
        $email = $data->email;
        $password = $data->password;

        if (!$username || !$email || !$password) {
            Flight::halt(400, 'Username, email and password are required');
        }

        try {
            $token = str_replace('Bearer ', '', $token);
            $decoded = Flight::auth_service()->validateToken($token);
            $user = Flight::user_service()->createUser($username, $email, $password);
            Flight::json($user, 201);
        } catch (Exception $e) {
            Flight::halt(400, $e->getMessage());
        }
    });

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Update user",
     *     description="Update an existing user",
     *     tags={"users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
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
     *         response=200,
     *         description="User updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    Flight::route('PUT /@id', function($id) {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        $token = Flight::request()->getHeader('Authorization');
        if (!$token) {
            Flight::halt(401, 'No token provided');
        }

        $data = Flight::request()->data;
        $username = $data->username;
        $email = $data->email;
        $password = $data->password;

        if (!$username || !$email || !$password) {
            Flight::halt(400, 'Username, email and password are required');
        }

        try {
            $token = str_replace('Bearer ', '', $token);
            $decoded = Flight::auth_service()->validateToken($token);
            $user = Flight::user_service()->updateUser($id, $username, $email, $password);
            Flight::json($user);
        } catch (Exception $e) {
            Flight::halt(400, $e->getMessage());
        }
    });

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Delete user",
     *     description="Delete an existing user",
     *     tags={"users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    Flight::route('DELETE /@id', function($id) {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        $token = Flight::request()->getHeader('Authorization');
        if (!$token) {
            Flight::halt(401, 'No token provided');
        }

        try {
            $token = str_replace('Bearer ', '', $token);
            $decoded = Flight::auth_service()->validateToken($token);
            Flight::user_service()->deleteUser($id);
            Flight::json(['message' => 'User deleted successfully']);
        } catch (Exception $e) {
            Flight::halt(400, $e->getMessage());
        }
    });
}); 