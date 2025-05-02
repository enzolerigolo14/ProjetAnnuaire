<?php
require_once __DIR__ . '/config/ldap_auth.php';
require_once __DIR__ . '/config/database.php';

function normalizeString($str) {
    $str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
    return strtolower(trim(preg_replace('/[^a-z0-9]/i', ' ', $str)));
}

$terme = trim($_GET['q'] ?? '');

if (empty($terme)) {
    die("Aucun terme de recherche fourni.");
}

$termeNormalise = normalizeString($terme);

// 1. Recherche dans l'Active Directory
$usersAD = recupererTousLesUtilisateursAD();
foreach ($usersAD as $user) {
    $prenom = normalizeString($user["givenname"][0] ?? '');
    $nom = normalizeString($user["sn"][0] ?? '');
    $fullName = "$prenom $nom";
    
    // Recherche dans toutes les parties du nom
    if (str_contains($fullName, $termeNormalise)) {
        $email = urlencode($user["mail"][0] ?? '');
        header("Location: profilutilisateur.php?email=$email&source=ad");
        exit;
    }
}

// 2. Recherche dans la BDD
$stmt = $pdo->prepare("SELECT id, prenom, nom, email_professionnel FROM users");
$stmt->execute();
$usersDB = $stmt->fetchAll();

foreach ($usersDB as $user) {
    $prenom = normalizeString($user["prenom"]);
    $nom = normalizeString($user["nom"]);
    $fullName = "$prenom $nom";
    
    if (str_contains($fullName, $termeNormalise)) {
        $email = urlencode($user["email_professionnel"]);
        header("Location: profilutilisateur.php?email=$email&source=db");
        exit;
    }
}

// 3. Aucun résultat
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Aucun résultat</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/global.css">
</head>
<body>
    <div class="error-container">
        <h1>Aucun résultat trouvé pour : <?= htmlspecialchars($terme) ?></h1>
        <a href="membreglobal.php" class="back-button">← Retour à la liste</a>
    </div>
</body>
</html>