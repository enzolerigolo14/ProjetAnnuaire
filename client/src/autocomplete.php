<?php
require_once __DIR__ . '/config/ldap_auth.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

$term = strtolower($_GET['q'] ?? '');
$context = $_GET['context'] ?? 'header'; // 'header' ou 'membres'
$results = [];

if (strlen($term) >= 2) {
    // Recherche membres (toujours disponible)
    if ($context === 'membres' || $context === 'header') {
    $uniqueUsers = []; // Pour éviter les doublons
    
    // 1. Active Directory
    $usersAD = recupererTousLesUtilisateursAD();
    for ($i = 0; $i < $usersAD["count"]; $i++) {
        $prenom = $usersAD[$i]["givenname"][0] ?? '';
        $nom = $usersAD[$i]["sn"][0] ?? '';
        $mail = $usersAD[$i]["mail"][0] ?? '';
        $full = strtolower($prenom . ' ' . $nom);
        
        if (str_contains($full, $term)) {
            $uniqueUsers[$mail] = [ // Utilisez l'email comme clé unique
                'name' => $prenom . ' ' . $nom,
                'prenom' => $prenom,
                'nom' => $nom,
                'email' => $mail,
                'source' => 'ad'
            ];
        }
    }

    // 2. Base de données
    $stmt = $pdo->prepare("SELECT prenom, nom, email_professionnel FROM users WHERE CONCAT(prenom, ' ', nom) LIKE ?");
    $stmt->execute(["%$term%"]);
    foreach ($stmt->fetchAll() as $row) {
        $mail = $row['email_professionnel'];
        if (!isset($uniqueUsers[$mail])) { // Ne pas écraser si déjà présent depuis AD
            $uniqueUsers[$mail] = [
                'name' => $row['prenom'] . ' ' . $row['nom'],
                'prenom' => $row['prenom'],
                'nom' => $row['nom'],
                'email' => $mail,
                'source' => 'db'
            ];
        }
    }

    // Ajouter les utilisateurs uniques aux résultats
    foreach ($uniqueUsers as $user) {
        $results[] = [
            'name' => $user['name'],
            'prenom' => $user['prenom'],
            'nom' => $user['nom'],
            'url' => 'profilutilisateur.php?email=' . urlencode($user['email']) . '&source='.$user['source'].'&from='.$context,
            'type' => 'membre',
            'email' => $user['email'] // Ajout pour identification unique
        ];
    }
}
}

echo json_encode(array_values($results));