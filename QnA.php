<?php
// бутстрап для ошб и аутолог
require_once __DIR__ . '/config/bootstrap.php';

use HostCloud\Validation\Validator;

$error = '';
$success = '';
$formData = [
    'id' => '',
    'question' => '',
    'answer' => ''
];

// генер токена есл не екз
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'parts/QnA_part.php';

$error = '';
$success = ''; 

if (isset($_SESSION['flash'])) {
    if (isset($_SESSION['flash']['success'])) {
        $success = $_SESSION['flash']['success'];
    }
    if (isset($_SESSION['flash']['error'])) {
        $error = $_SESSION['flash']['error'];
    }
    unset($_SESSION['flash']);
}

$formData = [
    'question' => '',
    'answer' => ''
];

// конект ДБ
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=host_cloud_db;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    $qna = new QnA($pdo);
    
} catch (Exception $e) {
    error_log('Database error: ' . $e->getMessage());
    $error = 'A database error occurred. Please try again later.';
    
    // в разроб фул ошибк
    if (ini_get('display_errors')) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// разреш
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }
        
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('You must be logged in to perform this action');
        }
        
        // по действ санитайз
        $action = $_POST['action'] ?? '';
        $formData['question'] = Validator::sanitizeInput($_POST['question'] ?? '');
        $formData['answer'] = Validator::sanitizeInput($_POST['answer'] ?? '');

        $validationErrors = Validator::validateQuestion([
            'question' => $formData['question'],
            'answer' => $formData['answer']
        ]);
        
        if (!empty($validationErrors)) {
            throw new Exception(implode(' ', $validationErrors));
        }
        
        // оброб разн действ
        switch ($action) {
            case 'edit_question':
                $questionId = filter_input(INPUT_POST, 'question_id', FILTER_VALIDATE_INT);
                if (!$questionId) {
                    throw new Exception('Invalid question ID');
                }
                
                $result = $qna->updateQuestion(
                    $questionId,
                    $formData['question'],
                    $formData['answer'],
                    $_SESSION['user_id']
                );
                
                if (isset($result['success'])) {
                    $_SESSION['flash'] = ['success' => 'Question updated successfully'];
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    throw new Exception($result['error'] ?? 'Failed to update question');
                }
                
            case 'add_question':
                $result = $qna->addQuestion(
                    $formData['question'],
                    $formData['answer'],
                    $_SESSION['user_id']
                );
                
                if (isset($result['success'])) {
                    $_SESSION['flash'] = ['success' => 'Question added successfully'];
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    throw new Exception($result['error'] ?? 'Failed to add question');
                }
                
            default:
                throw new Exception('Invalid action');
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        // Keep form data for repopulation
        $formData['id'] = $_POST['question_id'] ?? '';
    }
}

// обооб удалн
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        if (!validate_csrf_token($_GET['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }
        
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('You must be logged in to delete questions');
        }
        
        $questionId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$questionId) {
            throw new Exception('Invalid question ID');
        }
        
        $result = $qna->deleteQuestion($questionId, $_SESSION['user_id']);
        if (isset($result['success'])) {
            $_SESSION['flash'] = ['success' => 'Question deleted successfully'];
        } else {
            throw new Exception($result['error'] ?? 'Failed to delete question');
        }
        
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// оброб редакт
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    try {
        $questionId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($questionId) {
            $question = $qna->getQuestion($questionId);
            if ($question && $question['user_id'] == $_SESSION['user_id']) {
                $formData = [
                    'id' => $question['id'],
                    'question' => $question['question'],
                    'answer' => $question['answer']
                ];
            } else {
                throw new Exception('Question not found or access denied');
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// все вопр
try {
    $questions = $qna->getAllQuestions($_SESSION['user_id'] ?? null);
} catch (Exception $e) {
    $error = 'Failed to load questions: ' . $e->getMessage();
    $questions = [];
}

// флеш мэсдж
if (isset($_SESSION['flash'])) {
    if (isset($_SESSION['flash']['success'])) {
        $success = $_SESSION['flash']['success'];
    }
    if (isset($_SESSION['flash']['error'])) {
        $error = $_SESSION['flash']['error'];
    }
    unset($_SESSION['flash']);
}

if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    
    if ($flash['type'] === 'success') {
        $success = $flash['message'];
    } else {
        $error = $flash['message'];
    }
}

// оброб удал
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        if (!validate_csrf_token($_GET['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }
        
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('You must be logged in to delete questions');
        }
        
        $result = $qna->deleteQuestion($_GET['id'], $_SESSION['user_id']);
        
        if (!isset($result['success'])) {
            throw new Exception($result['error'] ?? 'Failed to delete question');
        }
        
        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Question deleted successfully!'
        ];
        
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// все вопр
$questions = [];
if (isset($qna)) {
    $questions = $qna->getAllQuestions($_SESSION['user_id'] ?? null);
}

// Handle edit parameter
if (isset($_GET['edit']) && isset($_SESSION['user_id'])) {
    $questionId = (int)$_GET['edit'];
    $question = $qna->getQuestion($questionId);
    
    if ($question && $question['user_id'] == ($_SESSION['user_id'] ?? null)) {
        $formData = [
            'id' => $question['id'],
            'question' => $question['question'],
            'answer' => $question['answer']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Q&A - Host Cloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/qna.css">
</head>
<body>
<div class="container">
    <h1><i class="fas fa-question-circle"></i> Questions & Answers</h1>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php if (isset($formData['id']) && !empty($formData['id'])): ?>
            <!-- Edit Question Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-0">
                        <i class="fas fa-edit me-2"></i>Edit Question
                    </h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="edit_question">
                        <input type="hidden" name="question_id" value="<?= htmlspecialchars($formData['id']) ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        
                        <div class="mb-3">
                            <label for="question" class="form-label">Question</label>
                            <input type="text" class="form-control <?= !empty($error) && empty($formData['question']) ? 'is-invalid' : '' ?>" 
                                   id="question" name="question" 
                                   value="<?= htmlspecialchars($formData['question']) ?>" 
                                   required>
                            <div class="invalid-feedback">
                                Please provide a question.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="answer" class="form-label">Answer</label>
                            <textarea class="form-control <?= !empty($error) && empty($formData['answer']) ? 'is-invalid' : '' ?>" 
                                      id="answer" name="answer" 
                                      rows="4" required><?= htmlspecialchars($formData['answer']) ?></textarea>
                            <div class="invalid-feedback">
                                Please provide an answer.
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="?" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Question
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- нов вопр-->
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Add New Question
                    </h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="action" value="add_question">
                        
                        <div class="mb-3">
                            <label for="question" class="form-label">Question</label>
                            <input type="text" class="form-control <?= !empty($error) && empty($formData['question']) ? 'is-invalid' : '' ?>" 
                                   id="question" name="question" 
                                   value="<?= htmlspecialchars($formData['question'] ?? '') ?>" 
                                   required>
                            <div class="invalid-feedback">
                                Please provide a question.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="answer" class="form-label">Answer</label>
                            <textarea class="form-control <?= !empty($error) && empty($formData['answer']) ? 'is-invalid' : '' ?>" 
                                      id="answer" name="answer" 
                                      rows="4" required><?= htmlspecialchars($formData['answer'] ?? '') ?></textarea>
                            <div class="invalid-feedback">
                                Please provide an answer.
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Add Question
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="login-notice">
            <i class="fas fa-info-circle"></i> Please <a href="login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>">log in</a> to ask questions.
        </div>
    <?php endif; ?>

    <?php if (isset($questions)): ?>
        <?php if (!empty($questions)): ?>
            <div class="questions-list mt-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 mb-0">
                        <i class="fas fa-list-ul me-2"></i>Questions & Answers
                    </h2>
                    <span class="badge bg-primary rounded-pill">
                        <?= count($questions) ?> <?= count($questions) === 1 ? 'Question' : 'Questions' ?>
                    </span>
                </div>
                <div class="accordion" id="qnaAccordion">
                <?php foreach ($questions as $index => $q): 
                    $isOwner = isset($q['is_owner']) && $q['is_owner'];
                    $createdDate = new DateTime($q['created_at']);
                    $formattedDate = $createdDate->format('F j, Y \a\t g:i a');
                ?>
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h3 class="accordion-header" id="heading<?= $index ?>">
                            <button class="accordion-button collapsed" type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapse<?= $index ?>" 
                                    aria-expanded="false" 
                                    aria-controls="collapse<?= $index ?>">
                                <i class="fas fa-question-circle text-primary me-2"></i>
                                <?= htmlspecialchars($q['question']) ?>
                            </button>
                        </h3>
                        <div id="collapse<?= $index ?>" 
                             class="accordion-collapse collapse" 
                             aria-labelledby="heading<?= $index ?>" 
                             data-bs-parent="#qnaAccordion">
                            <div class="accordion-body">
                                <div class="answer bg-light p-3 rounded mb-3">
                                    <?= nl2br(htmlspecialchars($q['answer'])) ?>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center text-muted small">
                                    <div>
                                        <i class="fas fa-user me-1"></i>
                                        <?= htmlspecialchars($q['username'] ?? 'Anonymous') ?>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton<?= $index ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton<?= $index ?>">
                                                <li>
                                                    <a class="dropdown-item" href="?edit=<?= $q['id'] ?>">
                                                        <i class="fas fa-edit me-2"></i> Edit
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" 
                                                       href="?action=delete&id=<?= $q['id'] ?>&csrf_token=<?= urlencode($_SESSION['csrf_token'] ?? '') ?>" 
                                                       onclick="return confirm('Are you sure you want to delete this question? This action cannot be undone.')">
                                                        <i class="fas fa-trash-alt me-2"></i> Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="mx-2">•</span>
                                        <i class="far fa-clock me-1"></i>
                                        <?= $formattedDate ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No questions found. Be the first to ask a question!</div>
        <?php endif; ?>
        <div class="mt-4 text-end">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Home
            </a>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// иниц тултип
document.addEventListener('DOMContentLoaded', function() {
    // иниц паден
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
    
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this question? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
});
</script>
</body>
</html>