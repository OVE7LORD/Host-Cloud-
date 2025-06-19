<?php

class QnA {
    private $pdo;
    
    public function __construct($pdo = null) {
        try {
            $this->pdo = $pdo ?: $this->getDatabaseConnection();
            // Set PDO to throw exceptions on error
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    private function getDatabaseConnection() {
        $host = 'localhost';
        $db = 'host_cloud_db';
        $user = 'root';
        $pass = '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new PDO($dsn, $user, $pass, $options);
    }
    
    public function addQuestion($question, $answer, $userId) {
        try {
            // Verify user exists
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            if (!$stmt->fetch()) {
                return ['error' => 'User not found'];
            }
            
            $query = "INSERT INTO questions (question, answer, user_id) VALUES (?, ?, ?)";
            $stmt = $this->pdo->prepare($query);
            
            if ($stmt->execute([$question, $answer, $userId])) {
                return [
                    'success' => true, 
                    'id' => $this->pdo->lastInsertId()
                ];
            }
        } catch (PDOException $e) {
            return ['error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getAllQuestions($currentUserId = null) {
        try {
            $query = "SELECT q.*, u.username 
                     FROM questions q 
                     LEFT JOIN users u ON q.user_id = u.id 
                     ORDER BY q.created_at DESC";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            
            $questions = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($currentUserId !== null) {
                    $row['is_owner'] = ($row['user_id'] == $currentUserId);
                }
                $questions[] = $row;
            }
            
            return $questions;
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function deleteQuestion($questionId, $userId) {
        try {
            // Check if the question belongs to the user
            $checkQuery = "SELECT id FROM questions WHERE id = ? AND user_id = ?";
            $checkStmt = $this->pdo->prepare($checkQuery);
            $checkStmt->execute([$questionId, $userId]);
            
            if ($checkStmt->rowCount() === 0) {
                return ['error' => 'Question not found or access denied'];
            }
            
            $query = "DELETE FROM questions WHERE id = ?";
            $stmt = $this->pdo->prepare($query);
            
            if ($stmt->execute([$questionId])) {
                return ['success' => true];
            } else {
                return ['error' => 'Error deleting question'];
            }
        } catch (PDOException $e) {
            return ['error' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function getQuestion($questionId) {
        try {
            $query = "SELECT q.*, u.username FROM questions q 
                     LEFT JOIN users u ON q.user_id = u.id 
                     WHERE q.id = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$questionId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function updateQuestion($questionId, $questionText, $answer, $userId) {
        try {
            // Verify the question exists and belongs to the user
            $checkQuery = "SELECT id FROM questions WHERE id = ? AND user_id = ?";
            $checkStmt = $this->pdo->prepare($checkQuery);
            $checkStmt->execute([$questionId, $userId]);
            
            if ($checkStmt->rowCount() === 0) {
                return ['error' => 'Question not found or access denied'];
            }
            
            // Update the question
            $query = "UPDATE questions SET question = ?, answer = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->pdo->prepare($query);
            
            if ($stmt->execute([$questionText, $answer, $questionId])) {
                return ['success' => true];
            } else {
                return ['error' => 'Error updating question'];
            }
        } catch (PDOException $e) {
            return ['error' => 'Database error: ' . $e->getMessage()];
        }
    }
}
?>
