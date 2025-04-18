<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Vérification de sécurité
if (!isset($_SESSION['user'])) {
    header('HTTP/1.0 403 Forbidden');
    die('Accès interdit');
}

// Récupération des paramètres
$type = $_GET['type'] ?? '';
$filename = basename($_GET['file']);
$service_id = intval($_GET['service_id'] ?? 0);
$actualite_id = intval($_GET['id'] ?? 0);

// Détermination du chemin du fichier selon le type
if ($type === 'service') {
    // Ancienne logique pour les fichiers de service
    $filepath = __DIR__ . '/uploads/service_' . $service_id . '/' . $filename;
    
    // Vérification des permissions
    $stmt = $pdo->prepare("SELECT id FROM services WHERE id = ?");
    $stmt->execute([$service_id]);
    if (!$stmt->fetch()) {
        header('HTTP/1.0 403 Forbidden');
        die('Accès non autorisé');
    }
} elseif ($type === 'actualite') {
    // Nouvelle logique pour les PDF d'actualités
    $filepath = __DIR__ . '/uploads/actualites/' . $filename;
    
    // Vérification que l'actualité existe et que l'utilisateur a les droits
    $stmt = $pdo->prepare("SELECT a.id 
                          FROM actualites a
                          JOIN services s ON a.service_id = s.id
                          WHERE a.id = ?");
    $stmt->execute([$actualite_id]);
    if (!$stmt->fetch()) {
        header('HTTP/1.0 403 Forbidden');
        die('Accès non autorisé à cette actualité');
    }
} else {
    header('HTTP/1.0 400 Bad Request');
    die('Type de fichier non spécifié');
}

// Vérification de l'existence du fichier
if (!file_exists($filepath)) {
    header('HTTP/1.0 404 Not Found');
    die('Fichier non disponible');
}

// Envoi du fichier
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
?>