<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../services/TagService.php';

$tagService = new TagService();
$action = $_GET['action'] ?? '';

switch($action) {
    case 'all':
        $tags = $tagService->getAllTags();
        echo json_encode($tags);
        break;
    case 'byBlog':
        $blogId = $_GET['blog_id'] ?? null;
        if ($blogId) {
            $tags = $tagService->getTagsByBlogId($blogId);
            echo json_encode($tags);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Blog ID is required']);
        }
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
} 