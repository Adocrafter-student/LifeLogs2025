<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../dao/BlogDao.php';

$blogDao = new BlogDao();

$action = $_GET['action'] ?? '';

switch($action) {
    case 'featured':
        $blogs = $blogDao->getFeaturedBlogs();
        echo json_encode($blogs);
        break;
        
    case 'latest':
        $blogs = $blogDao->getLatestBlogs();
        echo json_encode($blogs);
        break;
        
    case 'get':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $blog = $blogDao->getBlogWithUser($id);
            echo json_encode($blog);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required']);
        }
        break;
        
    case 'category':
        $category = $_GET['category'] ?? null;
        if ($category) {
            $blogs = $blogDao->getBlogsByCategory($category);
            echo json_encode($blogs);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Category is required']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?> 