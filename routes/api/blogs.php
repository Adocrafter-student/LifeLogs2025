<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../services/BlogService.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$database = new Database();
$db = $database->getConnection();
$blogService = new BlogService($db);

$action = $_GET['action'] ?? '';

switch($action) {
    case 'featured':
        $blogs = $blogService->getFeaturedBlogs();
        foreach($blogs as &$blog) {
            error_log("Original image_url: " . $blog['image_url']);
            error_log("Original author_avatar: " . $blog['author_avatar']);
        }
        echo json_encode($blogs);
        break;
        
    case 'latest':
        $blogs = $blogService->getLatestBlogs();
        foreach($blogs as &$blog) {
            error_log("Original image_url: " . $blog['image_url']);
            error_log("Original author_avatar: " . $blog['author_avatar']);
        }
        echo json_encode($blogs);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?> 