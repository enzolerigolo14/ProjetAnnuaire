<?php
require_once __DIR__ . '/config/database.php';

if (!isset($_GET['id'])) {
    echo "ID du service manquant.";
    exit;
}

$id = $_GET['id'];

// Optionnel : sécuriser la suppression par confirmation ou rôle admin

$stmt = $pdo->prepare("DELETE FROM services WHERE id = :id");
$stmt->execute(['id' => $id]);

header("Location: gerer-services.php");
exit;
?>
