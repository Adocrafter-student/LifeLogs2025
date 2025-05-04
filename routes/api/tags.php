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

Flight::group('/api/tags', function() {
    /**
     * @OA\Get(
     *     path="/api/tags",
     *     summary="Get all tags",
     *     description="Get all tags with pagination",
     *     tags={"tags"},
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
     *         description="List of tags"
     *     )
     * )
     */
    Flight::route('GET /', function() {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        $page = Flight::request()->query->page ?? 1;
        $limit = Flight::request()->query->limit ?? 10;

        try {
            $tags = Flight::tag_service()->getAllTags($page, $limit);
            Flight::json($tags);
        } catch (Exception $e) {
            Flight::halt(500, $e->getMessage());
        }
    });

    /**
     * @OA\Get(
     *     path="/api/tags/{id}",
     *     summary="Get tag by ID",
     *     description="Get a specific tag by its ID",
     *     tags={"tags"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found"
     *     )
     * )
     */
    Flight::route('GET /@id', function($id) {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $tag = Flight::tag_service()->getTagById($id);
            Flight::json($tag);
        } catch (Exception $e) {
            Flight::halt(404, $e->getMessage());
        }
    });

    /**
     * @OA\Get(
     *     path="/api/tags/name/{name}",
     *     summary="Get tag by name",
     *     description="Get a specific tag by its name",
     *     tags={"tags"},
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found"
     *     )
     * )
     */
    Flight::route('GET /name/@name', function($name) {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $tag = Flight::tag_service()->getTagByName($name);
            Flight::json($tag);
        } catch (Exception $e) {
            Flight::halt(404, $e->getMessage());
        }
    });

    /**
     * @OA\Get(
     *     path="/api/tags/blog/{id}",
     *     summary="Get blog tags",
     *     description="Get all tags for a specific blog post",
     *     tags={"tags"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of tags"
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
            $tags = Flight::tag_service()->getTagsByBlogId($id);
            Flight::json($tags);
        } catch (Exception $e) {
            Flight::halt(404, $e->getMessage());
        }
    });

    /**
     * @OA\Get(
     *     path="/api/tags/popular",
     *     summary="Get popular tags",
     *     description="Get the most popular tags",
     *     tags={"tags"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of popular tags"
     *     )
     * )
     */
    Flight::route('GET /popular', function() {
        clearAllOutputBuffers();
        header('Content-Type: application/json; charset=UTF-8');

        $limit = Flight::request()->query->limit ?? 10;

        try {
            $tags = Flight::tag_service()->getPopularTags($limit);
            Flight::json($tags);
        } catch (Exception $e) {
            Flight::halt(500, $e->getMessage());
        }
    });

    /**
     * @OA\Post(
     *     path="/api/tags",
     *     summary="Create tag",
     *     description="Create a new tag",
     *     tags={"tags"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tag created successfully"
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
        $name = $data->name;

        if (!$name) {
            Flight::halt(400, 'Tag name is required');
        }

        try {
            $token = str_replace('Bearer ', '', $token);
            $decoded = Flight::auth_service()->validateToken($token);
            $tag = Flight::tag_service()->createTag($name);
            Flight::json($tag, 201);
        } catch (Exception $e) {
            Flight::halt(400, $e->getMessage());
        }
    });

    /**
     * @OA\Put(
     *     path="/api/tags/{id}",
     *     summary="Update tag",
     *     description="Update an existing tag",
     *     tags={"tags"},
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
     *             required={"name"},
     *             @OA\Property(property="name", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag updated successfully"
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
     *         description="Tag not found"
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
        $name = $data->name;

        if (!$name) {
            Flight::halt(400, 'Tag name is required');
        }

        try {
            $token = str_replace('Bearer ', '', $token);
            $decoded = Flight::auth_service()->validateToken($token);
            $tag = Flight::tag_service()->updateTag($id, $name);
            Flight::json($tag);
        } catch (Exception $e) {
            Flight::halt(400, $e->getMessage());
        }
    });

    /**
     * @OA\Delete(
     *     path="/api/tags/{id}",
     *     summary="Delete tag",
     *     description="Delete an existing tag",
     *     tags={"tags"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found"
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
            Flight::tag_service()->deleteTag($id);
            Flight::json(['message' => 'Tag deleted successfully']);
        } catch (Exception $e) {
            Flight::halt(400, $e->getMessage());
        }
    });
}); 