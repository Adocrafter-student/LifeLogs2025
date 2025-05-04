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

Flight::group('/api/likes', function() {
    /**
     * @OA\Get(
     *     path="/api/likes/blog/{id}",
     *     summary="Get blog likes",
     *     description="Get all likes for a specific blog post",
     *     tags={"likes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of likes"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Blog not found"
     *     )
     * )
     */
    Flight::route('GET /blog/@id', function($id) {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $likes = Flight::like_service()->getLikesByBlogId($id);
            Flight::json($likes);
        } catch (Exception $e) {
            Flight::halt(404, $e->getMessage());
        }
    });

    /**
     * @OA\Get(
     *     path="/api/likes/user/{id}",
     *     summary="Get user likes",
     *     description="Get all likes by a specific user",
     *     tags={"likes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of likes"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    Flight::route('GET /user/@id', function($id) {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $likes = Flight::like_service()->getLikesByUserId($id);
            Flight::json($likes);
        } catch (Exception $e) {
            Flight::halt(404, $e->getMessage());
        }
    });

    /**
     * @OA\Post(
     *     path="/api/likes",
     *     summary="Create like",
     *     description="Create a new like on a blog post",
     *     tags={"likes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"blog_id"},
     *             @OA\Property(property="blog_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Like created successfully"
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
        $blog_id = $data->blog_id;

        if (!$blog_id) {
            Flight::halt(400, 'Blog ID is required');
        }

        try {
            $token = str_replace('Bearer ', '', $token);
            $decoded = Flight::auth_service()->validateToken($token);
            $like = Flight::like_service()->createLike($blog_id, $decoded->user_id);
            Flight::json($like, 201);
        } catch (Exception $e) {
            Flight::halt(400, $e->getMessage());
        }
    });

    /**
     * @OA\Delete(
     *     path="/api/likes/{id}",
     *     summary="Delete like",
     *     description="Delete an existing like",
     *     tags={"likes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Like deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Like not found"
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
            Flight::like_service()->deleteLike($id, $decoded->user_id);
            Flight::json(['message' => 'Like deleted successfully']);
        } catch (Exception $e) {
            Flight::halt(400, $e->getMessage());
        }
    });
}); 