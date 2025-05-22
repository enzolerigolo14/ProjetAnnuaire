<?php
require_once __DIR__ . '/config/database.php';
session_start();

header('Content-Type: application/json');

// Debug - Enregistrer les entrées
error_log("Received request: " . print_r($_REQUEST, true));
error_log("Files: " . print_r($_FILES, true));

// Vérification des permissions
$allowedRoles = ['SVC-INFORMATIQUE', 'ADMIN-INTRA', 'ADMIN-RH'];
if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], $allowedRoles)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Action non autorisée']);
    exit;
}

if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID utilisateur invalide']);
    exit;
}

$userId = (int)$_POST['user_id'];
$uploadDir = __DIR__ . '/../../client/src/assets/avatars/';

// Création du dossier
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Impossible de créer le dossier de destination']);
        exit;
    }
}

// Vérification du fichier
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Aucun fichier reçu ou erreur de transfert: ' . $_FILES['avatar']['error']]);
    exit;
}

// Vérification du type MIME
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $_FILES['avatar']['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Type de fichier non supporté']);
    exit;
}

// Génération du nouveau nom de fichier
$extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
$newFilename = 'user_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
$destination = $uploadDir . $newFilename;

// Déplacement du fichier
if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement du fichier']);
    exit;
}

// Mise à jour en base de données
try {
    $relativePath = '/projetannuaire/client/src/assets/avatars/' . $newFilename;
    $stmt = $pdo->prepare("UPDATE users SET avatar_path = ? WHERE id = ?");
    $stmt->execute([$relativePath, $userId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Avatar mis à jour avec succès',
        'filePath' => $relativePath
    ]);
} catch (PDOException $e) {
    unlink($destination);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
}