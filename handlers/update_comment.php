<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not authenticated');
    }

    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }


    // Validate required fields
    $commentId = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);
    $content = trim($_POST['content'] ?? '');

    if (!$commentId || empty($content)) {
        throw new Exception('Invalid input');
    }

    // Update comment
    $comment = new Comment($db->getPdo());
    $success = $comment->update($commentId, $content, (int)$_SESSION['user_id']);

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Comment updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update comment');
    }
} catch (Exception $e) {
    error_log('Update comment error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
