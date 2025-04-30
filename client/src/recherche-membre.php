<?php
require_once __DIR__ . '/config/ldap_auth.php';
require_once __DIR__ . '/config/database.php';

function removeAccents($str) {
    if (!class_exists('Normalizer')) return $str; 
    return preg_replace('/[\x{0300}-\x{036f}]/u', '', normalizer_normalize($str, Normalizer::FORM_D));
}


$terme = trim($_GET['q'] ?? '');

if (!$terme) {
    die("Aucun terme de recherche fourni.");
}


// Recherche dans l'Active Directory
$usersAD = recupererTousLesUtilisateursAD();
$termeNormalise = strtolower(removeAccents($terme));
$parts = explode(' ', $termeNormalise);
$prenomRecherche = $parts[0] ?? '';
$nomRecherche = $parts[1] ?? '';

// Recherche dans AD
foreach ($usersAD as $user) {
    $prenom = strtolower(removeAccents($user["givenname"][0] ?? ''));
    $nom = strtolower(removeAccents($user["sn"][0] ?? ''));

    if (
        ($prenom === $prenomRecherche && $nom === $nomRecherche) || 
        strpos($prenom, $termeNormalise) !== false ||   
        strpos($nom, $termeNormalise) !== false
    ) {
        $email = urlencode($user["mail"][0] ?? '');
        header("Location: profilutilisateur.php?email=$email");
        exit;
    }
}



// Partie recherche BDD
$stmt = $pdo->query("SELECT id, prenom, nom FROM users");
$usersDB = $stmt->fetchAll();

foreach ($usersDB as $user) {
    $prenom = strtolower(removeAccents($user["prenom"]));
    $nom = strtolower(removeAccents($user["nom"]));

    if (strpos($prenom, $termeNormalise) !== false || strpos($nom, $termeNormalise) !== false) {
        header("Location: profilutilisateur.php?id=" . $user['id']);
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
</head>
<body>
    <h1>Aucun résultat trouvé pour : <?= htmlspecialchars($terme) ?></h1>
    <a href="membreglobal.php">← Retour</a>
</body>
</html>
