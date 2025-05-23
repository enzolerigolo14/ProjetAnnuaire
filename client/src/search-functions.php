<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/ldap_auth.php';

function performSearch($term, $context = 'global') {
    global $pdo;
    
    // 1. Recherche dans les actualités
    $stmt = $pdo->prepare("SELECT id FROM actualites WHERE titre LIKE ? LIMIT 1");
    $stmt->execute(["%$term%"]);
    if ($row = $stmt->fetch()) {
        return ['type' => 'actualite', 'id' => $row['id']];
    }

    // 2. Recherche dans les membres (DB)
    $stmt = $pdo->prepare("SELECT email_professionnel FROM users WHERE CONCAT(prenom, ' ', nom) LIKE ?");
    $stmt->execute(["%$term%"]);
    if ($context === 'membres' || $row = $stmt->fetch()) {
        if ($context === 'membres') {
            // Logique spécifique à membreglobal.php
            return ['type' => 'membre_list', 'term' => $term];
        }
        return ['type' => 'membre', 'email' => $row['email_professionnel']];
    }

    // 3. Recherche dans les services
    $stmt = $pdo->prepare("SELECT id FROM services WHERE nom LIKE ?");
    $stmt->execute(["%$term%"]);
    if ($row = $stmt->fetch()) {
        return ['type' => 'service', 'id' => $row['id']];
    }

    return ['type' => 'none'];
}