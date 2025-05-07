<?php
/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="LifeLogs API",
 *   description="API documentation for LifeLogs",
 *   @OA\Contact(name="API Support", email="admin@lifelogs.com")
 * )
 * @OA\Server(
 *   url="http://localhost/LifeLogs2025/api",
 *   description="Local development server"
 * )
 */

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

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

foreach (glob(__DIR__ . '/services/*.php') as $file) {
    require $file;
    $class = pathinfo($file, PATHINFO_FILENAME);
    Flight::register(lcfirst($class), $class);
}

/**
 * @OA\Get(
 *     path="/",
 *     summary="Get frontend application",
 *     description="Returns the frontend application HTML",
 *     tags={"frontend"},
 *     @OA\Response(
 *         response=200,
 *         description="Frontend application HTML"
 *     )
 * )
 */
Flight::route('/*', function() {
    clearAllOutputBuffers();
    $url = Flight::request()->url;  
    if (preg_match('#^/(api/|auth/(login|register))#', $url)) {
        return true;
    }
    header('Content-Type: text/html; charset=UTF-8');
    readfile(__DIR__ . '/index.html');
    return false;
});

foreach (glob(__DIR__ . '/routes/api/*.php') as $routeFile) {
    require $routeFile;
}

/**
 * @OA\Options(
 *     path="/*",
 *     summary="Handle CORS preflight requests",
 *     description="Handles CORS preflight requests for all routes",
 *     tags={"cors"},
 *     @OA\Response(
 *         response=200,
 *         description="CORS headers set successfully"
 *     )
 * )
 */
Flight::route('OPTIONS *', function(){
    clearAllOutputBuffers();
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type,Authorization');
    Flight::halt(200);
});

Flight::after('start', function(){
    header('Access-Control-Allow-Origin: *');
});

/**
 * @OA\Get(
 *     path="/*",
 *     summary="Handle 404 errors",
 *     description="Returns a 404 error response for undefined routes",
 *     tags={"errors"},
 *     @OA\Response(
 *         response=404,
 *         description="Route not found"
 *     )
 * )
 */
Flight::map('notFound', function(){
    clearAllOutputBuffers();
    header('Content-Type: application/json; charset=UTF-8');
    Flight::json(['error'=>'Not found'], 404);
});

Flight::start();