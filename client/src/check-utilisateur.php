<?php
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

$terme = $_GET['q'] ?? '';
$response = ['isService' => false, 'isUser' => false];

if (!empty($terme)) {
    // Vérifier si c'est un service
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM services WHERE nom LIKE :terme");
    $stmt->execute(['terme' => '%' . $terme . '%']);
    $response['isService'] = ($stmt->fetchColumn() > 0);
    
    // Vérifier si c'est un utilisateur
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE nom LIKE :terme OR prenom LIKE :terme");
    $stmt->execute(['terme' => '%' . $terme . '%']);
    $response['isUser'] = ($stmt->fetchColumn() > 0);
}

echo json_encode($response);