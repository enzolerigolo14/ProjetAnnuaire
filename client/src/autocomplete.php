<?php
require_once __DIR__ . '/config/ldap_auth.php';
require_once __DIR__ . '/config/database.php';

$term = strtolower($_GET['q'] ?? '');
$suggestions = [];

if ($term) {
    // Active Directory
    $usersAD = recupererTousLesUtilisateursAD();
    for ($i = 0; $i < $usersAD["count"]; $i++) {
        $prenom = $usersAD[$i]["givenname"][0] ?? '';
        $nom = $usersAD[$i]["sn"][0] ?? '';
        $full = strtolower($prenom . ' ' . $nom);
        if (str_contains($full, $term)) {
            $suggestions[] = $prenom . ' ' . $nom;
        }
    }

    // Base de données
    $stmt = $pdo->prepare("SELECT prenom, nom FROM users WHERE CONCAT(prenom, ' ', nom) LIKE ?");
    $stmt->execute(["%$term%"]);
    foreach ($stmt->fetchAll() as $row) {
        $suggestions[] = $row['prenom'] . ' ' . $row['nom'];
    }
}

// Supprimer les doublons
$suggestions = array_unique($suggestions);

// Retour JSON
header('Content-Type: application/json');
echo json_encode(array_values($suggestions));
