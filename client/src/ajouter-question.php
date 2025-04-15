<?php
require_once __DIR__ . '/config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Méthode non autorisée');
}

if (empty($_POST['question'])) {
    die('La question ne peut pas être vide');
}

try {
    $userId = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
    $question = trim($_POST['question']);
    
    $stmt = $pdo->prepare("INSERT INTO faq (question, user_id) VALUES (?, ?)");
    $stmt->execute([$question, $userId]);
    
    echo 'success|' . $pdo->lastInsertId();
} catch (PDOException $e) {
    die('Erreur de base de données: ' . $e->getMessage());
}