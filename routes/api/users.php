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
     *     summary="Get all users (Admin only)",
     *     description="Get all users with pagination. Requires admin privileges.",
     *     tags={"users"},
     *     security={{"bearerAuth": {}}},
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
     *     @OA\Response(response=200, description="List of users", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/User"))),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    Flight::route('GET /', function() {
        clearAllOutputBuffers(); 
        header('Content-Type: application/json; charset=UTF-8');
        error_log("GET /api/users - Attempting to list all users.");

        if (!Flight::auth_middleware()->authorizeRole('admin')) {
            error_log("GET /api/users - Access DENIED. User is not admin.");
            return; // authorizeRole already sends 403
        }
        error_log("GET /api/users - Access GRANTED. User is admin.");

        $page = Flight::request()->query->page ?? 1; 
        $limit = Flight::request()->query->limit ?? 10; 

        try {
            $users = Flight::userService()->getAllUsers(); // Za sada bez paginacije, kao što je bilo
            echo json_encode($users);
        } catch (Exception $e) {
            error_log("GET /api/users - Error: " . $e->getMessage());
            Flight::halt(500, json_encode(['message' => $e->getMessage()]));
        }
        exit;
    });

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get user by ID",
     *     description="Get a specific user by their ID. Admins can get any user. Regular users can only get their own profile.",
     *     tags={"users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="User details", @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    Flight::route('GET /@id', function($id) {
        clearAllOutputBuffers(); 
        header('Content-Type: application/json; charset=UTF-8');
        $target_user_id = intval($id);
        $requesting_user = Flight::get('user');
        error_log("GET /api/users/{$target_user_id} - Requested by user ID: " . ($requesting_user->id ?? 'N/A') . " with role: " . ($requesting_user->role ?? 'N/A'));

        if (!$requesting_user) {
            Flight::halt(401, json_encode(['message' => 'Authentication required.']));
            exit;
        }

        if ($requesting_user->id != $target_user_id && $requesting_user->role !== 'admin') {
            error_log("GET /api/users/{$target_user_id} - Access DENIED. User {$requesting_user->id} cannot access other user's profile.");
            Flight::halt(403, json_encode(['message' => 'Forbidden: You can only view your own profile or an admin can view any.']));
            exit;
        }

        try {
            $user = Flight::userService()->getUserById($target_user_id);
            if ($user) {
                echo json_encode($user);
            } else {
                Flight::halt(404, json_encode(['message' => 'User not found.']));
            }
        } catch (Exception $e) {
            error_log("GET /api/users/{$target_user_id} - Error: " . $e->getMessage());
            Flight::halt(500, json_encode(['message' => $e->getMessage()]));
        }
        exit;
    });

    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Create user (Admin only)",
     *     description="Create a new user. Requires admin privileges.",
     *     tags={"users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "email", "password"},
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="role", type="string", example="user", description="Role for the new user (e.g. user, admin)"),
     *             @OA\Property(property="bio", type="string", example="User bio")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User created successfully", @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    Flight::route('POST /', function() {
        clearAllOutputBuffers(); 
        header('Content-Type: application/json; charset=UTF-8');
        error_log("POST /api/users - Attempting to create user.");

        if (!Flight::auth_middleware()->authorizeRole('admin')) {
            error_log("POST /api/users - Access DENIED. User is not admin.");
            return; // authorizeRole already sends 403
        }
        error_log("POST /api/users - Access GRANTED. User is admin.");

        $data = Flight::request()->data->getData();

        $username = $data['username'] ?? null;
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $role = $data['role'] ?? 'user'; // Default to 'user' if not provided
        $bio = $data['bio'] ?? null;

        if (!$username || !$email || !$password) {
            Flight::halt(400, json_encode(['message' => 'Username, email and password are required.']));
            exit;
        }

        try {
            $user = Flight::userService()->createUser($username, $email, $password, $bio, null, $role);
            http_response_code(201); // Set HTTP status code to 201 Created
            echo json_encode($user);
        } catch (Exception $e) {
            error_log("POST /api/users - Error: " . $e->getMessage());
            $statusCode = (strpos(strtolower($e->getMessage()), 'already exists') !== false) ? 409 : 400;
            Flight::halt($statusCode, json_encode(['message' => $e->getMessage()]));
        }
        exit;
    });

    /**
     * @OA\Put(
     *      path="/api/users/{id}",
     *      tags={"users"},
     *      summary="Update user by ID",
     *      description="Allows a user to update their own profile, or an admin to update any profile.",
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *          description="User data to update",
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              properties={
     *                  @OA\Property(property="username", type="string", example="new_username"),
     *                  @OA\Property(property="email", type="string", format="email", example="new_email@example.com"),
     *                  @OA\Property(property="password", type="string", format="password", description="Provide only if changing password", example="newSecurePassword123"),
     *                  @OA\Property(property="bio", type="string", example="New bio description"),
     *                  @OA\Property(property="role", type="string", example="user", description="Admin only: can change user role (e.g., 'user', 'admin')")
     *              }
     *          )
     *      ),
     *      @OA\Response(response=200, description="User updated", @OA\JsonContent(ref="#/components/schemas/User")),
     *      @OA\Response(response=400, description="Invalid input"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="User not found")
     * )
     */
    Flight::route('PUT /@id', function($id){
        clearAllOutputBuffers(); 
        header('Content-Type: application/json; charset=UTF-8');
        $requesting_user = Flight::get('user'); 
        $target_user_id_to_update = intval($id);
        error_log("PUT /api/users/{$target_user_id_to_update} - Requested by user ID: " . ($requesting_user->id ?? 'N/A') . " with role: " . ($requesting_user->role ?? 'N/A'));
        
        if (!$requesting_user || !isset($requesting_user->id) || !isset($requesting_user->role) ) {
            Flight::halt(401, json_encode(['message' => 'Authentication error: User data not found or incomplete in token.']));
            exit;
        }

        $is_admin = ($requesting_user->role === 'admin');
        if ($requesting_user->id != $target_user_id_to_update && !$is_admin) {
            error_log("PUT /api/users/{$target_user_id_to_update} - Access DENIED. User {$requesting_user->id} cannot update other user's profile.");
            Flight::halt(403, json_encode(['message' => 'Forbidden: You can only update your own profile, or an admin is required.']));
            exit;
        }
        error_log("PUT /api/users/{$target_user_id_to_update} - Access GRANTED.");

        $data_payload = Flight::request()->data->getData();
        
        if (empty($data_payload)) {
             Flight::halt(400, json_encode(['message' => 'No data provided for update.']));
             exit;
        }
        error_log("PUT /api/users/{$target_user_id_to_update} - Received data for update: " . json_encode($data_payload));

        try {
            $updatedUser = Flight::userService()->updateUser($target_user_id_to_update, $data_payload, $is_admin);
            
            if ($updatedUser) {
                echo json_encode($updatedUser);
            } else {
                error_log("PUT /api/users/{$target_user_id_to_update} - UserService->updateUser returned null/false.");
                Flight::halt(404, json_encode(['message' => 'User not found or update failed internally.'])); 
            }
        } catch (Exception $e) {
            error_log("PUT /api/users/{$target_user_id_to_update} - Error: " . $e->getMessage());
            $statusCode = 400;
            if (strpos(strtolower($e->getMessage()), 'not found') !== false) $statusCode = 404;
            if (strpos(strtolower($e->getMessage()), 'already exists') !== false) $statusCode = 409;
            Flight::halt($statusCode, json_encode(['message' => $e->getMessage()]));
        }
        exit;
    });

    /**
     * @OA\Delete(
     *      path="/api/users/{id}",
     *      tags={"users"},
     *      summary="Delete a user by ID (Admin only)",
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="User ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response=200, description="User deleted successfully", @OA\JsonContent(type="object", properties={"message":@OA\Property(type="string")})),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="User not found")
     * )
     */
    Flight::route('DELETE /@id', function($id){
        clearAllOutputBuffers(); 
        header('Content-Type: application/json; charset=UTF-8');
        $target_user_id = intval($id);
        error_log("DELETE /api/users/{$target_user_id} - Attempting to delete user.");

        if (!Flight::auth_middleware()->authorizeRole('admin')) {
            error_log("DELETE /api/users/{$target_user_id} - Access DENIED. User is not admin.");
            return; // authorizeRole already sends 403
        }
        error_log("DELETE /api/users/{$target_user_id} - Access GRANTED. User is admin.");

        try {
            $requesting_user = Flight::get('user');
            if ($requesting_user && $requesting_user->id == $target_user_id) {
                error_log("DELETE /api/users/{$target_user_id} - Admin attempted to delete self. Denied.");
                Flight::halt(403, json_encode(['message' => 'Admins cannot delete themselves through this endpoint.']));
                exit;
            }

            $result = Flight::userService()->deleteUser($target_user_id);
            if ($result) { 
                echo json_encode(['message' => 'User deleted successfully.']);
            } else {
                Flight::halt(404, json_encode(['message' => 'User not found or delete operation failed.']));
            }
        } catch (Exception $e) {
            error_log("DELETE /api/users/{$target_user_id} - Error: " . $e->getMessage());
            $statusCode = (strpos(strtolower($e->getMessage()), 'not found') !== false) ? 404 : 500;
            Flight::halt($statusCode, json_encode(['message' => $e->getMessage()]));
        }
        exit;
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
}); 