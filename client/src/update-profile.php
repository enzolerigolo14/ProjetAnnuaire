<?php
session_start();
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Liste des champs autorisés avec leur correspondance en base de données
$allowedFields = [
    'nom_complet' => ['prenom', 'nom'],
    'email_professionnel' => 'email_professionnel',
    'telephone' => 'telephone',
    'service_id' => 'service_id',  // Colonne probable dans votre table
    'role' => 'role'
];

if (!isset($data['user_id'], $data['field'], $data['value']) || !array_key_exists($data['field'], $allowedFields)) {
    echo json_encode(['success' => false, 'message' => 'Champ non autorisé']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Cas particulier pour le nom complet
    if ($data['field'] === 'nom_complet') {
        $names = explode(' ', $data['value'], 2);
        $prenom = $names[0];
        $nom = $names[1] ?? '';
        
        $stmt = $pdo->prepare("UPDATE users SET prenom = ?, nom = ? WHERE id = ?");
        $stmt->execute([$prenom, $nom, $data['user_id']]);
    }
    // Cas particulier pour le service (si c'est un select)
    elseif ($data['field'] === 'service_id') {
        $stmt = $pdo->prepare("UPDATE users SET service_id = ? WHERE id = ?");
        $stmt->execute([$data['value'], $data['user_id']]);
    }
    // Cas standard pour les autres champs
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