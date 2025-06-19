<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

header('Content-Type: application/json');

try {
    // Validate required fields
    $postId = filter_input(INPUT_GET, 'post_id', FILTER_VALIDATE_INT);
    if (!$postId) {
        throw new Exception('Invalid or missing post_id parameter');
    }

    // Get comments
    $comment = new Comment($db->getPdo());
    $comments = $comment->getAllByPostId($postId);

    // Render comments to HTML
    $html = '';
    if (empty($comments)) {
        $html = '<p>No comments yet. Be the first to comment!</p>';
    } else {
        foreach ($comments as $comment) {
            $isOwner = isset($_SESSION['user_id']) && ($_SESSION['user_id'] == ($comment['user_id'] ?? null));
            
            $html .= sprintf(
                '<div class="comment mb-3 p-3 border rounded">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="font-weight-bold">%s</span>
                        <small class="text-muted">%s</small>
                    </div>
                    <div class="comment-content">%s</div>
                    %s
                </div>',
                htmlspecialchars($comment['username'] ?? 'Unknown'),
                date('M j, Y g:i A', strtotime($comment['created_at'] ?? 'now')),
                nl2br(htmlspecialchars($comment['content'] ?? '')),
                $isOwner ? 
                    sprintf('<div class="comment-actions mt-2">
                        <button class="btn btn-sm btn-outline-primary edit-comment mr-2" data-comment-id="%d">Edit</button>
                        <button class="btn btn-sm btn-outline-danger delete-comment" data-comment-id="%d">Delete</button>
                    </div>', $comment['id'], $comment['id']) : ''
            );
        }
    }

    echo json_encode([
        'success' => true,
        'html' => $html,
        'comments' => $comments
    ]);

} catch (Exception $e) {
    error_log('Get comments error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Error loading comments: ' . $e->getMessage()
    ]);
}
