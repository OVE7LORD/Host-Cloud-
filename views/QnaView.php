<?php
declare(strict_types=1);

require_once __DIR__ . '/parts/QnA_part.php';

class QnAView
{
    private QnA $qna;

    public function __construct(PDO $db)
    {
        $this->qna = new QnA($db);
    }

    public function renderForm(): string
    {
        return "
            <div class='qna-form'>
                <form method='POST' action='add-qna.php'>
                    <div class='form-group'>
                        <label for='question'>Question:</label>
                        <input type='text' id='question' name='question' required>
                    </div>
                    <div class='form-group'>
                        <label for='answer'>Answer:</label>
                        <textarea id='answer' name='answer' required></textarea>
                    </div>
                    <button type='submit'>Add Question</button>
                </form>
            </div>
        ";
    }

    public function renderQuestions(): string
    {
        $questions = $this->qna->getAll();
        $html = '<div class="questions-list">';
        foreach ($questions as $q) {
            $html .= $this->renderSingleQuestion($q);
        }
        $html .= '</div>';
        return $html;
    }

    private function renderSingleQuestion(array $question): string
    {
        return "
            <div class='question'>
                <div class='question-header'>
                    <span class='question-author'>{$question['username']}</span>
                    <span class='question-date'>{$question['created_at']}</span>
                </div>
                <div class='question-content'>
                    <h3>Q: {$question['question']}</h3>
                    <p>A: {$question['answer']}</p>
                </div>
            </div>
        ";
    }
}