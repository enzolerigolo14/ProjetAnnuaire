<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Vérifier les permissions
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

$actualite_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;

// Récupérer la nouvelle actualité principale
$stmt = $pdo->prepare("SELECT * FROM actualites WHERE id = ? AND service_id = ?");
$stmt->execute([$actualite_id, $service_id]);
$new_featured = $stmt->fetch();

if (!$new_featured) {
    echo json_encode(['success' => false, 'error' => 'Actualité non trouvée']);
    exit;
}

// Récupérer toutes les actualités (y compris l'ancienne principale)
$stmt = $pdo->prepare("SELECT * FROM actualites WHERE service_id = ? AND id != ? ORDER BY created_at DESC");
$stmt->execute([$service_id, $actualite_id]);
$other_actualites = $stmt->fetchAll();

// Préparer la réponse
$response = [
    'success' => true,
    'actualite' => $new_featured,
    'other_actualites' => $other_actualites
];

header('Content-Type: application/json');
echo json_encode($response);