<?php
require_once __DIR__ . '/config/database.php';

$terme = $_GET['q'] ?? '';

if (!empty($terme)) {
    $stmt = $pdo->prepare("SELECT id FROM services WHERE nom LIKE ?");
    $stmt->execute(["%$terme%"]);
    $service = $stmt->fetch();

    if ($service) {
        header("Location: membresservices.php?id=" . $service['id']);
        exit;
    }

    // Vérifier si c'est un utilisateur
    $stmt = $pdo->prepare("SELECT id FROM users WHERE nom LIKE ? OR prenom LIKE ?");
    $stmt->execute(["%$terme%", "%$terme%"]);
    $user = $stmt->fetch();
    
    if ($user) {
        header("Location: profilutilisateur.php?id=".$user['id']."&from=search");
        exit;
    }
}

// Rediriger vers une page par défaut si rien n'est trouvé
header("Location: pageaccueil.php");
exit;