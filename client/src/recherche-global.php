<?php
require_once __DIR__ . '/config/ldap_auth.php';
require_once __DIR__ . '/config/database.php';

$terme = trim($_GET['q'] ?? '');

if (empty($terme)) {
    header("Location: pageaccueil.php");
    exit;
}

// Recherche d'actualité
$stmt = $pdo->prepare("
    SELECT service_id 
    FROM actualites 
    WHERE titre LIKE ? 
    LIMIT 1
");
$stmt->execute(["%$terme%"]);
if ($row = $stmt->fetch()) {
    header("Location: services-global-actualite.php?service_id=" . $row['service_id']);
    exit;
}

// 2. Recherche dans les services
$stmt = $pdo->prepare("SELECT id FROM services WHERE nom LIKE ? LIMIT 1");
$stmt->execute(["%$terme%"]);
if ($row = $stmt->fetch()) {
    header("Location: membresservices.php?id=" . $row['id'] . "&from=search");
    exit;
}

// 3. Recherche dans les membres (DB)
$stmt = $pdo->prepare("SELECT email_professionnel FROM users WHERE CONCAT(prenom, ' ', nom) LIKE ? LIMIT 1");
$stmt->execute(["%$terme%"]);
if ($row = $stmt->fetch()) {
    header("Location: profilutilisateur.php?email=" . urlencode($row['email_professionnel']) . "&source=db&from=search");
    exit;
}

// 4. Recherche dans l'AD
$usersAD = recupererTousLesUtilisateursAD();
foreach ($usersAD as $user) {
    $prenom = strtolower($user["givenname"][0] ?? '');
    $nom = strtolower($user["sn"][0] ?? '');
    $fullName = "$prenom $nom";
    $searchTerm = strtolower($terme);
    
    if (str_contains($fullName, $searchTerm)) {
        $email = urlencode($user["mail"][0] ?? '');
        header("Location: profilutilisateur.php?email=$email&source=ad&from=search");
        exit;
    }
}

// Si aucun résultat trouvé
header("Location: pageaccueil.php?search=" . urlencode($terme) . "&error=notfound");