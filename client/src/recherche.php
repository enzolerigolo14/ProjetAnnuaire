<?php
require_once __DIR__ . '/config/database.php';

$terme = $_GET['q'] ?? '';

// Vérifier si c'est un service
$stmt = $pdo->prepare("SELECT id FROM services WHERE nom LIKE ?");
$stmt->execute(["%$terme%"]);
$service = $stmt->fetch();

if ($service) {
    header("Location: membresservices.php?id=" . $service['id']);
    exit;
}
