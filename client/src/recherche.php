<?php
require_once __DIR__ . '/config/database.php';

$terme = $_GET['q'] ?? '';

if (!empty($terme)) {
    // 1. Recherche dans les actualités (prioritaire)
    $stmt = $pdo->prepare("SELECT id FROM actualites WHERE titre LIKE ?");
    $stmt->execute(["%$terme%"]);
    $actualite = $stmt->fetch();

    if ($actualite) {
        header("Location: actualite.php?id=" . $actualite['id']);
        exit;
    }

    // 2. Recherche dans les services
    $stmt = $pdo->prepare("SELECT id FROM services WHERE nom LIKE ?");
    $stmt->execute(["%$terme%"]);
    $service = $stmt->fetch();

    if ($service) {
        header("Location: membresservices.php?id=" . $service['id']);
        exit;
    }

    // 3. Recherche dans les utilisateurs
    $stmt = $pdo->prepare("SELECT id FROM users WHERE nom LIKE ? OR prenom LIKE ?");
    $stmt->execute(["%$terme%", "%$terme%"]);
    $user = $stmt->fetch();

    if ($user) {
        header("Location: profilutilisateur.php?id=" . $user['id'] . "&from=search");
        exit;
    }

    
}

// Si rien n'est trouvé
header("Location: pageaccueil.php?error=notfound");
exit;