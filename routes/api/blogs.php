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

/**
 * @OA\Get(
 *     path="/api/blogs",
 *     summary="List all blogs",
 *     description="Get a list of all blogs with optional filtering by featured or latest",
 *     tags={"blogs"},
 *     @OA\Parameter(
 *         name="action",
 *         in="query",
 *         required=false,
 *         description="Filter blogs by action (featured|latest)",
 *         @OA\Schema(type="string")
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
 *         description="List of blogs retrieved successfully"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server error"
 *     )
 * )
 */
Flight::route('GET /api/blogs', function() {
    clearAllOutputBuffers();
    header('Content-Type: application/json; charset=UTF-8');

    $action = Flight::request()->query->action ?? '';
    $page   = (int)(Flight::request()->query->page  ?? 1);
    $limit  = (int)(Flight::request()->query->limit ?? 10);

    try {
        if ($action === 'featured') {
            $blogs = Flight::blogService()->getFeaturedBlogs();
        } elseif ($action === 'latest') {
            $blogs = Flight::blogService()->getLatestBlogs();
        } else {
            $blogs = Flight::blogService()->getAllBlogs($page, $limit);
        }
        Flight::json($blogs);
    } catch (Exception $e) {
        Flight::halt(500, $e->getMessage());
    }
});

/**
 * @OA\Get(
 *     path="/api/blogs/{id}",
 *     summary="Get blog by ID",
 *     description="Get a single blog post by its ID",
 *     tags={"blogs"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Blog retrieved successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Blog not found"
 *     )
 * )
 */
Flight::route('GET /api/blogs/@id', function($id) {
    clearAllOutputBuffers();
    header('Content-Type: application/json; charset=UTF-8');

    try {
        $blog = Flight::blogService()->getBlogById($id);
        Flight::json($blog);
    } catch (Exception $e) {
        Flight::halt(404, $e->getMessage());
    }
});

/**
 * @OA\Get(
 *     path="/api/blogs/user/{id}",
 *     summary="List blogs by user",
 *     description="Get all blogs created by a specific user",
 *     tags={"blogs"},
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
 *         description="List of user's blogs retrieved successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found"
 *     )
 * )
 */
Flight::route('GET /api/blogs/user/@id', function($id) {
    clearAllOutputBuffers();
    header('Content-Type: application/json; charset=UTF-8');

    $page  = (int)(Flight::request()->query->page  ?? 1);
    $limit = (int)(Flight::request()->query->limit ?? 10);

    try {
        $blogs = Flight::blogService()->getBlogsByUserId($id, $page, $limit);
        Flight::json($blogs);
    } catch (Exception $e) {
        Flight::halt(404, $e->getMessage());
    }
});

/**
 * @OA\Get(
 *     path="/api/blogs/tag/{id}",
 *     summary="List blogs by tag",
 *     description="Get all blogs associated with a specific tag",
 *     tags={"blogs"},
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
 *         description="List of tagged blogs retrieved successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Tag not found"
 *     )
 * )
 */
Flight::route('GET /api/blogs/tag/@id', function($id) {
    clearAllOutputBuffers();
    header('Content-Type: application/json; charset=UTF-8');

    $page  = (int)(Flight::request()->query->page  ?? 1);
    $limit = (int)(Flight::request()->query->limit ?? 10);

    try {
        $blogs = Flight::blogService()->getBlogsByTagId($id, $page, $limit);
        Flight::json($blogs);
    } catch (Exception $e) {
        Flight::halt(404, $e->getMessage());
    }
});

/**
 * @OA\Post(
 *     path="/api/blogs",
 *     summary="Create blog",
 *     description="Create a new blog post",
 *     tags={"blogs"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "title", "content"},
 *             @OA\Property(property="user_id", type="integer"),
 *             @OA\Property(property="title", type="string"),
 *             @OA\Property(property="content", type="string"),
 *             @OA\Property(property="tags", type="array", @OA\Items(type="integer"))
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Blog created successfully"
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
Flight::route('POST /api/blogs', function() {
    clearAllOutputBuffers();
    header('Content-Type: application/json; charset=UTF-8');

    $data = Flight::request()->data->getData();
    try {
        $blog = Flight::blogService()->createBlog(
            $data['user_id'],
            $data['title'],
            $data['content'],
            $data['tags'] ?? []
        );
        Flight::json($blog, 201);
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Put(
 *     path="/api/blogs/{id}",
 *     summary="Update blog",
 *     description="Update an existing blog post",
 *     tags={"blogs"},
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
 *             @OA\Property(property="title", type="string"),
 *             @OA\Property(property="content", type="string"),
 *             @OA\Property(property="tags", type="array", @OA\Items(type="integer"))
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Blog updated successfully"
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
 *         description="Blog not found"
 *     )
 * )
 */
Flight::route('PUT /api/blogs/@id', function($id) {
    clearAllOutputBuffers();
    header('Content-Type: application/json; charset=UTF-8');

    $data = Flight::request()->data->getData();
    try {
        $blog = Flight::blogService()->updateBlog(
            $id,
            $data['title']   ?? null,
            $data['content'] ?? null,
            $data['tags']    ?? null
        );
        Flight::json($blog);
    } catch (Exception $e) {
        Flight::halt(404, $e->getMessage());
    }
});

/**
 * @OA\Delete(
 *     path="/api/blogs/{id}",
 *     summary="Delete blog",
 *     description="Delete an existing blog post",
 *     tags={"blogs"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=204,
 *         description="Blog deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Blog not found"
 *     )
 * )
 */
Flight::route('DELETE /api/blogs/@id', function($id) {
    clearAllOutputBuffers();
    header('Content-Type: application/json; charset=UTF-8');

    try {
        Flight::blogService()->deleteBlog($id);
        Flight::halt(204);
    } catch (Exception $e) {
        Flight::halt(404, $e->getMessage());
    }
});

// No PHP closing tag; keep file clean for JSON responses
