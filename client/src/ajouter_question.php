<?php
session_start();
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$question = trim($_POST['question'] ?? '');
$service_id = !empty($_POST['service_id']) ? (int)$_POST['service_id'] : null;

if (empty($question)) {
    echo json_encode(['success' => false, 'message' => 'La question ne peut pas être vide']);
    exit;
}

$user_id = $_SESSION['user']['id'] ?? null;

try {
    $stmt = $pdo->prepare("INSERT INTO faq (question, user_id, service_id) VALUES (?, ?, ?)");
    $stmt->execute([$question, $user_id, $service_id]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
}