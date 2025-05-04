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

Flight::group('/api/comments', function() {
    /**
     * @OA\Get(
     *     path="/api/comments/blog/{id}",
     *     summary="Get blog comments",
     *     description="Get all comments for a specific blog post",
     *     tags={"comments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
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
     *         description="List of comments"
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

        $page = Flight::request()->query->page ?? 1;
        $limit = Flight::request()->query->limit ?? 10;

        try {
            $comments = Flight::comment_service()->getCommentsByBlogId($id, $page, $limit);
            Flight::json($comments);
        } catch (Exception $e) {
            Flight::halt(404, $e->getMessage());
        }
    });

    /**
     * @OA\Get(
     *     path="/api/comments/user/{id}",
     *     summary="Get user comments",
     *     description="Get all comments by a specific user",
     *     tags={"comments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
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
     *         description="List of comments"
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

        $page = Flight::request()->query->page ?? 1;
        $limit = Flight::request()->query->limit ?? 10;

        try {
            $comments = Flight::comment_service()->getCommentsByUserId($id, $page, $limit);
            Flight::json($comments);
        } catch (Exception $e) {
            Flight::halt(404, $e->getMessage());
        }
    });

    /**
     * @OA\Post(
     *     path="/api/comments",
     *     summary="Create comment",
     *     description="Create a new comment on a blog post",
     *     tags={"comments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"blog_id", "content"},
     *             @OA\Property(property="blog_id", type="integer"),
     *             @OA\Property(property="content", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Comment created successfully"
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
        $content = $data->content;

        if (!$blog_id || !$content) {
            Flight::halt(400, 'Blog ID and content are required');
        }

        try {
            $token = str_replace('Bearer ', '', $token);
            $decoded = Flight::auth_service()->validateToken($token);
            $comment = Flight::comment_service()->createComment($blog_id, $decoded->user_id, $content);
            Flight::json($comment, 201);
        } catch (Exception $e) {
            Flight::halt(400, $e->getMessage());
        }
    });

    /**
     * @OA\Put(
     *     path="/api/comments/{id}",
     *     summary="Update comment",
     *     description="Update an existing comment",
     *     tags={"comments"},
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
     *             required={"content"},
     *             @OA\Property(property="content", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment updated successfully"
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
     *         description="Comment not found"
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
        $content = $data->content;

        if (!$content) {
            Flight::halt(400, 'Content is required');
        }

        try {
            $token = str_replace('Bearer ', '', $token);
            $decoded = Flight::auth_service()->validateToken($token);
            $comment = Flight::comment_service()->updateComment($id, $decoded->user_id, $content);
            Flight::json($comment);
        } catch (Exception $e) {
            Flight::halt(400, $e->getMessage());
        }
    });

    /**
     * @OA\Delete(
     *     path="/api/comments/{id}",
     *     summary="Delete comment",
     *     description="Delete an existing comment",
     *     tags={"comments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found"
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
            Flight::comment_service()->deleteComment($id, $decoded->user_id);
            Flight::json(['message' => 'Comment deleted successfully']);
        } catch (Exception $e) {
            Flight::halt(400, $e->getMessage());
        }
    });

    /**
     * @OA\Get(
     *     path="/comments/replies/{comment_id}",
     *     summary="Get comment replies",
     *     description="Get replies to a specific comment",
     *     tags={"comments"},
     *     @OA\Parameter(
     *         name="comment_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of replies"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found"
     *     )
     * )
     */
    Flight::route('GET /replies/@id', function($id) {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        $page = (int)(Flight::request()->query->page ?? 1);
        $limit = (int)(Flight::request()->query->limit ?? 10);

        try {
            $replies = Flight::comment_service()->getReplies($id, $page, $limit);
            Flight::json($replies);
        } catch (Exception $e) {
            Flight::halt(404, $e->getMessage());
        }
    });
}); 