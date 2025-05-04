<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../services/CommentService.php';

$commentService = new CommentService();
$action = $_GET['action'] ?? '';

switch($action) {
    case 'get':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $comment = $commentService->getCommentById($id);
            echo json_encode($comment);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required']);
        }
        break;
    case 'byBlog':
        $blogId = $_GET['blog_id'] ?? null;
        if ($blogId) {
            $comments = $commentService->getCommentsByBlogId($blogId);
            echo json_encode($comments);
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