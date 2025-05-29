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
    error_log("GET /api/blogs: >>> ANONYMOUS FUNCTION EXECUTING NOW <<<"); // Najraniji mogući log
    try {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');
        error_log("GET /api/blogs: Handler entered (inside try). Action: '" . (Flight::request()->query->action ?? 'N/A') . "', Page: '" . (Flight::request()->query->page ?? 'N/A') . "', Limit: '" . (Flight::request()->query->limit ?? 'N/A') . "'");

        $action = Flight::request()->query->action ?? '';
        $page   = (int)(Flight::request()->query->page  ?? 1);
        $limit  = (int)(Flight::request()->query->limit ?? 10);

        error_log("GET /api/blogs: In try block (main). Action: '{$action}'");
        $blogs = []; // Inicijalizacija
        if ($action === 'featured') {
            error_log("GET /api/blogs: Fetching featured blogs.");
            $blogs = Flight::blogService()->getFeaturedBlogs();
        } elseif ($action === 'latest') {
            error_log("GET /api/blogs: Fetching latest blogs.");
            $blogs = Flight::blogService()->getLatestBlogs();
        } else {
            error_log("GET /api/blogs: Fetching all blogs. Page: {$page}, Limit: {$limit}");
            $blogs = Flight::blogService()->getAllBlogs($page, $limit);
        }
        error_log("GET /api/blogs: Blog service call completed. Data: " . print_r($blogs, true));
        
        $jsonOutput = json_encode($blogs);
        $jsonError = json_last_error();

        if ($jsonError !== JSON_ERROR_NONE) {
            $jsonErrorMsg = json_last_error_msg();
            error_log("GET /api/blogs: JSON encoding error: " . $jsonErrorMsg . " (Code: " . $jsonError . ")");
            echo json_encode(['error' => 'Failed to encode blogs data to JSON', 'details' => $jsonErrorMsg]);
        } else {
            error_log("GET /api/blogs: JSON encoding successful. Outputting JSON.");
            echo $jsonOutput;
        }
        error_log("GET /api/blogs: JSON response supposedly sent. Terminating script execution.");
        exit;

    // Unutar ovog TRY bloka, PDOException i Exception će biti uhvaćeni ispod ako se dese
    // Ovo je više kao fallback za neočekivane greške na samom početku ili van specifičnih operacija.
    } catch (PDOException $e) {
        // Ovaj catch blok je sada unutar spoljašnjeg try, pa će biti uhvaćen od strane Throwable ako se desi greška PRIJE ovog catch-a.
        // Ako želimo specifično rukovanje za PDO unutar glavnog bloka, mora biti strukturirano drugačije.
        // Za sada, oslanjamo se na spoljašnji Throwable za greške pri inicijalizaciji.
        // Ako greška dođe iz blogService poziva, ovaj catch je relevantan.
        error_log("GET /api/blogs: PDOException caught (potentially from service): " . $e->getMessage() . "\nStack trace:\n" . $e->getTraceAsString());
        // Osiguraj da headeri nisu već poslati ako greška nastane rano
        if (!headers_sent()) {
            clearAllOutputBuffers(); // Ponovo očisti ako je nešto ispisano
            header('Content-Type: application/json; charset=UTF-8');
        }
        echo json_encode(['error' => 'Database error while fetching blogs', 'details' => $e->getMessage()]);
        exit;
    } catch (Exception $e) {
        error_log("GET /api/blogs: Exception caught (potentially from service): " . $e->getMessage() . "\nStack trace:\n" . $e->getTraceAsString());
        if (!headers_sent()) {
            clearAllOutputBuffers();
            header('Content-Type: application/json; charset=UTF-8');
        }
        echo json_encode(['error' => 'Server error while fetching blogs', 'details' => $e->getMessage()]);
        exit;
    } catch (Throwable $t) { // Ovaj će uhvatiti sve, uključujući fatalne greške na početku ako su uhvatljive
        error_log("GET /api/blogs: Throwable caught (critical): " . $t->getMessage() . "\nStack trace:\n" . $t->getTraceAsString());
        if (!headers_sent()) {
            clearAllOutputBuffers();
            header('Content-Type: application/json; charset=UTF-8');
        }
        echo json_encode(['error' => 'Critical server error while fetching blogs', 'details' => $t->getMessage()]);
        exit;
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
