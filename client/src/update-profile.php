<?php
session_start();
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');


if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);


$allowedFields = [
    'nom_complet' => ['prenom', 'nom'],  
    'email_professionnel' => 'email_professionnel',
    'telephone' => 'telephone',
    'service_id' => 'service_id',  
    'role' => 'role'
];


if (!isset($data['user_id'], $data['field'], $data['value']) || !array_key_exists($data['field'], $allowedFields)) {
    echo json_encode(['success' => false, 'message' => 'Champ non autorisé']);
    exit;
}

try {
    $pdo->beginTransaction();

    if ($data['field'] === 'nom_complet') {
        $names = explode(' ', $data['value'], 2);
        $stmt = $pdo->prepare("UPDATE users SET prenom = ?, nom = ? WHERE id = ?");
        $stmt->execute([$names[0], $names[1] ?? '', $data['user_id']]);
    }
  
    else {
        $dbField = $allowedFields[$data['field']];
        $stmt = $pdo->prepare("UPDATE users SET $dbField = ? WHERE id = ?");
        $stmt->execute([$data['value'], $data['user_id']]);
    }
    
    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
}