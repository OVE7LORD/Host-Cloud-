<?php
// Join DB
$host = 'localhost';
$db = 'phphost'; 
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    exit("Connection Error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
    $question = $_POST['question'] ?? '';
    $answer = $_POST['answer'] ?? '';
    
    if (!empty($question) && !empty($answer)) {
        $stmt = $pdo->prepare("SELECT * FROM questions WHERE question = ?");
        $stmt = $pdo->prepare("INSERT INTO questions (question, answer) VALUES (?, ?)");
        $stmt->execute([$question, $answer]);
        $success = true;
        $existingQuestion = $stmt->fetch();
    if ($existingQuestion) {
            $error = "This question already exist";
    } else {
        $error = "Please fulfil both";
    }
}

// get question
$questions = $pdo->query("SELECT * FROM questions ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вопросы и Ответы</title>
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/fontawesome.css">
    <link rel="stylesheet" href="assets/css/templatemo-host-cloud.css">
    <link rel="stylesheet" href="assets/css/owl.css">
    <style>
        form input[type="text"],
        form textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 12px;
            border-radius: 5px;
            border: 1px solid #ccc;
            resize: vertical;
        }

        form button {
            padding: 10px 20px;
            background: #2d89ef;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .qa-block {
            border-top: 1px solid #ddd;
            padding: 15px 0;
        }

        .qa-block h3 {
            margin-bottom: 5px;
        }

        .qa-block p {
            margin: 0;
        }
    </style>
</head>
<>
<div class="container">
    <h1>Add question and answer</h1>

    <?php if (!empty($success)): ?>
        <div class="message success">successefully added!</div>
    <?php elseif (!empty($error)): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Question:</label>
        <textarea name="question" rows="3" required></textarea>
        
        <label>Answer:</label>
        <textarea name="answer" rows="4" required></textarea>
        
        <button type="submit">ADD</button>
    </form>
    <a href="index.php">Main Page</a>
    <hr>

    <h2>Question list:</h2>
    <?php foreach ($questions as $q): ?>
        <div class="qa-block">
            <h3>Q: <?= htmlspecialchars($q['question']) ?></h3>
            <p><strong>A:</strong> <?= nl2br(htmlspecialchars($q['answer'])) ?></p>
        </div>
    <?php endforeach; ?>
</div>
<?php include("parts/footer.php"); ?>
</body>
</html>