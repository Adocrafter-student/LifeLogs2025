<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../services/UserService.php';

$userService = new UserService();
$action = $_GET['action'] ?? '';

switch($action) {
    case 'get':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $user = $userService->getUserById($id);
            echo json_encode($user);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required']);
        }
        break;
    case 'all':
        $users = $userService->getAllUsers();
        echo json_encode($users);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
} 