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

Flight::group('/api/search', function() {
    /**
     * @OA\Get(
     *     path="/api/search",
     *     summary="Search content",
     *     description="Search for blogs, users, and tags",
     *     tags={"search"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"blogs", "users", "tags", "all"},
     *             default="all"
     *         )
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
     *         description="Search results"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */
    Flight::route('GET /', function() {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        $query = Flight::request()->query->q;
        $type = Flight::request()->query->type ?? 'all';
        $page = (int)(Flight::request()->query->page ?? 1);
        $limit = (int)(Flight::request()->query->limit ?? 10);

        if (!$query) {
            Flight::halt(400, 'Search query is required');
        }

        try {
            $results = Flight::search_service()->search($query, $type, $page, $limit);
            Flight::json($results);
        } catch (Exception $e) {
            Flight::halt(400, $e->getMessage());
        }
    });

    /**
     * @OA\Get(
     *     path="/api/search/blogs",
     *     summary="Search blogs",
     *     description="Search only in blogs",
     *     tags={"search"},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         required=true,
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
     *         description="Search results"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */
    Flight::route('GET /blogs', function() {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        $query = Flight::request()->query->q;
        $page = (int)(Flight::request()->query->page ?? 1);
        $limit = (int)(Flight::request()->query->limit ?? 10);

        if (!$query) {
            Flight::halt(400, 'Search query is required');
        }

        try {
            $results = Flight::search_service()->searchBlogs($query, $page, $limit);
            Flight::json($results);
        } catch (Exception $e) {
            Flight::halt(400, $e->getMessage());
        }
    });

    /**
     * @OA\Get(
     *     path="/api/search/users",
     *     summary="Search users",
     *     description="Search only in users",
     *     tags={"search"},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         required=true,
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
     *         description="Search results"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */
    Flight::route('GET /users', function() {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        $query = Flight::request()->query->q;
        $page = (int)(Flight::request()->query->page ?? 1);
        $limit = (int)(Flight::request()->query->limit ?? 10);

        if (!$query) {
            Flight::halt(400, 'Search query is required');
        }

        try {
            $results = Flight::search_service()->searchUsers($query, $page, $limit);
            Flight::json($results);
        } catch (Exception $e) {
            Flight::halt(400, $e->getMessage());
        }
    });

    /**
     * @OA\Get(
     *     path="/api/search/tags",
     *     summary="Search tags",
     *     description="Search only in tags",
     *     tags={"search"},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         required=true,
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
     *         description="Search results"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */
    Flight::route('GET /tags', function() {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        $query = Flight::request()->query->q;
        $page = (int)(Flight::request()->query->page ?? 1);
        $limit = (int)(Flight::request()->query->limit ?? 10);

        if (!$query) {
            Flight::halt(400, 'Search query is required');
        }

        try {
            $results = Flight::search_service()->searchTags($query, $page, $limit);
            Flight::json($results);
        } catch (Exception $e) {
            Flight::halt(400, $e->getMessage());
        }
    });
}); 