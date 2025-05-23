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
        // Active Directory
        $usersAD = recupererTousLesUtilisateursAD();
        for ($i = 0; $i < $usersAD["count"]; $i++) {
            $prenom = $usersAD[$i]["givenname"][0] ?? '';
            $nom = $usersAD[$i]["sn"][0] ?? '';
            $mail = $usersAD[$i]["mail"][0] ?? '';
            $full = strtolower($prenom . ' ' . $nom);
            if (str_contains($full, $term)) {
                $results[] = [
                    'name' => $prenom . ' ' . $nom,
                    'url' => 'profilutilisateur.php?email=' . urlencode($mail) . '&source=ad&from='.$context,
                    'type' => 'membre'
                ];
            }
        }

        // Base de données
        $stmt = $pdo->prepare("SELECT prenom, nom, email_professionnel FROM users WHERE CONCAT(prenom, ' ', nom) LIKE ?");
        $stmt->execute(["%$term%"]);
        foreach ($stmt->fetchAll() as $row) {
            $results[] = [
                'name' => $row['prenom'] . ' ' . $row['nom'],
                'url' => 'profilutilisateur.php?email=' . urlencode($row['email_professionnel']) . '&source=db&from='.$context,
                'type' => 'membre'
            ];
        }
    }

    // Recherche services et actualités (uniquement pour le header)
    if ($context === 'header') {
        // Services
        $stmt = $pdo->prepare("SELECT id, nom FROM services WHERE nom LIKE ?");
        $stmt->execute(["%$term%"]);
        foreach ($stmt->fetchAll() as $row) {
            $results[] = [
                'name' => $row['nom'],
                'url' => 'membresservices.php?id=' . $row['id'].'&from=search',
                'type' => 'service'
            ];
        }

    }
}

echo json_encode(array_values($results));