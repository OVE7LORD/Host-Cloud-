<?php
declare(strict_types=1);

namespace HostCloud\Parts;

use PDO;
use Exception;

/**
 * @property PDO $db
 */

class Comment
{
    private PDO $db;
    private string $table = 'comments';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function add(int $postId, int $userId, string $content): bool
    {
        try {
            $query = "INSERT INTO {$this->table} (post_id, content, user_id, created_at) 
                     VALUES (:post_id, :content, :user_id, NOW())";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                'post_id' => $postId,
                'content' => $content,
                'user_id' => $userId
            ]);
        } catch (Exception $e) {
            error_log('Error adding comment: ' . $e->getMessage());
            return false;
        }
    }

    public function getAllByPostId(int $postId): array
    {
        try {
            $query = "SELECT c.*, u.username 
                     FROM {$this->table} c 
                     JOIN users u ON c.user_id = u.id 
                     WHERE c.post_id = :post_id 
                     ORDER BY c.created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['post_id' => $postId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function delete(int $commentId, int $userId): bool
    {
        try {
            $query = "DELETE FROM {$this->table} WHERE id = :id AND user_id = :user_id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                'id' => $commentId,
                'user_id' => $userId
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function update(int $commentId, string $content, int $userId): bool
    {
        try {
            $query = "UPDATE {$this->table} 
                     SET content = :content, updated_at = NOW() 
                     WHERE id = :id AND user_id = :user_id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                'id' => $commentId,
                'content' => $content,
                'user_id' => $userId
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
}