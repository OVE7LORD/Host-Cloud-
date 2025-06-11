<?php
require_once dirname(__DIR__) . '/Database.php';

class QnA {
    private $db;
    
    public function __construct() {
        try {
            $this->db = (new Database())->connect();
        } catch (PDOException $e) {
            die("Connection error: " . $e->getMessage());
        }
    }
    
    public function addQuestion($question, $answer) {
        try {
            $query = "INSERT INTO questions (question, answer) VALUES (?, ?)";
            $stmt = $this->db->prepare($query);
            
            if ($stmt->execute([$question, $answer])) {
                return ['success' => true];
            } else {
                return ['error' => 'Error adding question'];
            }
        } catch (PDOException $e) {
            return ['error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getAllQuestions() {
        try {
            $query = "SELECT * FROM questions ORDER BY id DESC";
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>
