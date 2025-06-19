<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

header('Content-Type: application/json');

try {
    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not authenticated');
    }

    // Validate input
    $commentId = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);

    if (!$commentId) {
        throw new Exception('Invalid comment ID');
    }

    // Delete comment
    $comment = new Comment($db->getPdo());
    $success = $comment->delete($commentId, (int)$_SESSION['user_id']);

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Comment deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete comment');
    }
} catch (Exception $e) {
    error_log('Delete comment error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
