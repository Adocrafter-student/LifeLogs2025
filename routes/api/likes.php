<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../services/LikeDislikeService.php';

$likeService = new LikeDislikeService();
$action = $_GET['action'] ?? '';

switch($action) {
    case 'get':
        $blogId = $_GET['blog_id'] ?? null;
        if ($blogId) {
            $reactions = $likeService->getReactionsByBlogId($blogId);
            echo json_encode($reactions);
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