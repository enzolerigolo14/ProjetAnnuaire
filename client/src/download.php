<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Vérification de sécurité
if (!isset($_SESSION['user'])) {
    header('HTTP/1.0 403 Forbidden');
    die('Accès interdit');
}

$service_id = intval($_GET['service_id']);
$filename = basename($_GET['file']);
$filepath = __DIR__ . '/uploads/service_' . $service_id . '/' . $filename;

// Vérification de l'existence du fichier
if (!file_exists($filepath)) {
    header('HTTP/1.0 404 Not Found');
    die('Fichier non disponible');
}

// Vérification des permissions
$stmt = $pdo->prepare("SELECT id FROM services WHERE id = ?");
$stmt->execute([$service_id]);
if (!$stmt->fetch()) {
    header('HTTP/1.0 403 Forbidden');
    die('Accès non autorisé');
}

// Envoi du fichier
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;