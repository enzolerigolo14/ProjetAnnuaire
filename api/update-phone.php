<?php
require_once __DIR__ . '/../client/src/config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Méthode non autorisée']));
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['userId'], $data['field'], $data['value'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Données manquantes']));
}

// Validation des données
$fieldMap = [
    'phone_public' => 'telephone',
    'phone_internal' => 'telephone_interne' 
];

if (!array_key_exists($data['field'], $fieldMap)) {
    http_response_code(400);
    die(json_encode(['error' => 'Champ invalide']));
}

try {
    $stmt = $pdo->prepare("UPDATE users SET {$fieldMap[$data['field']]} = ? WHERE id = ?");
    $stmt->execute([$data['value'], $data['userId']]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
}