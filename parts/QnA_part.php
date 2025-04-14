<?php
require_once 'Database.php';

class QnA extends Database {
    public function addQuestion($question, $answer) {
        $stmt = $this->pdo->prepare("SELECT * FROM questions WHERE question = ?");
        $stmt->execute([$question]);
        if ($stmt->fetch()) {
            return ['error' => 'This question already exists'];
        }

        $stmt = $this->pdo->prepare("INSERT INTO questions (question, answer) VALUES (?, ?)");
        $stmt->execute([$question, $answer]);
        return ['success' => true];
    }

    public function getAllQuestions() {
        return $this->pdo->query("SELECT * FROM questions ORDER BY id DESC")->fetchAll();
    }
}
?>
