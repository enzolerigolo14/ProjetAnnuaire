<?php
session_start();
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Vérification de l'authentification
if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé - Utilisateur non connecté']);
    exit;
}

// Récupération du service de l'utilisateur
$user_service_id = null;
$stmt = $pdo->prepare("SELECT service_id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$user_service_id = $stmt->fetchColumn();

// Vérification des données POST
$question_id = isset($_POST['question_id']) ? (int)$_POST['question_id'] : 0;
$reponse = trim($_POST['reponse'] ?? '');

if ($question_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de question invalide']);
    exit;
}

if (empty($reponse)) {
    echo json_encode(['success' => false, 'message' => 'La réponse ne peut pas être vide']);
    exit;
}

// Vérification des droits
if (!isset($_SESSION['user']['role'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé - Rôle non défini']);
    exit;
}

// Récupération du service de la question
$stmt = $pdo->prepare("SELECT service_id FROM faq WHERE id = ?");
$stmt->execute([$question_id]);
$question_service_id = $stmt->fetchColumn();

// Vérification des permissions
$isAdminIntra = ($_SESSION['user']['role'] === 'ADMIN-INTRA');
$isSvcInfo = ($_SESSION['user']['role'] === 'SVC-INFORMATIQUE');

if (!($isAdminIntra || $isSvcInfo)) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé - Seuls ADMIN-INTRA et SVC-INFORMATIQUE peuvent répondre']);
    exit;
}

// Vérification si l'admin peut répondre à cette question (même service ou SVC-INFORMATIQUE)
if ($isAdminIntra && $user_service_id !== $question_service_id) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé - Vous ne pouvez répondre qu\'aux questions de votre service']);
    exit;
}

// Enregistrement de la réponse
try {
    $stmt = $pdo->prepare("UPDATE faq SET reponse = ?, date_reponse = NOW(), reponse_par = ? WHERE id = ?");
    $stmt->execute([
        $reponse,
        $_SESSION['user']['id'],
        $question_id
    ]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
}