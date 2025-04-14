<?php
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

$terme = $_GET['q'] ?? '';
$response = ['isService' => false];

if (!empty($terme)) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM services WHERE nom LIKE :terme");
    $stmt->execute(['terme' => '%' . $terme . '%']);
    $count = $stmt->fetchColumn();
    
    $response['isService'] = ($count > 0);
}

echo json_encode($response);