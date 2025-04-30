<?php
require_once __DIR__ . '/config/ldap_auth.php';
require_once __DIR__ . '/config/database.php';
session_start();

// Récupération des utilisateurs
$usersAD = recupererTousLesUtilisateursAD();

$stmt = $pdo->prepare("SELECT * FROM users");
$stmt->execute();
$usersDB = $stmt->fetchAll();

// Tableau pour stocker les utilisateurs finaux sans doublons (par prénom)
$finalUsers = [];

// On commence par insérer ceux de l'AD
for ($i = 0; $i < $usersAD["count"]; $i++) {
    $prenom = strtolower($usersAD[$i]["givenname"][0] ?? '');
    if ($prenom) {
        $finalUsers[$prenom] = [
            "source" => "ad",
            "prenom" => $usersAD[$i]["givenname"][0] ?? '',
            "nom" => $usersAD[$i]["sn"][0] ?? '',
            "email" => $usersAD[$i]["mail"][0] ?? '',
            "role" => $usersAD[$i]["description"][0] ?? 'Description non disponible'
        ];
    }
}

// Puis ceux de la BDD, uniquement si le prénom n'est pas déjà pris
foreach ($usersDB as $user) {
    $prenom = strtolower($user["prenom"] ?? '');
    if ($prenom && !isset($finalUsers[$prenom])) {
        $finalUsers[$prenom] = [
            "source" => "db",
            "prenom" => $user["prenom"] ?? '',
            "nom" => $user["nom"] ?? '',
            "email" => $user["email"] ?? '',
            "role" => $user["role"] ?? 'Rôle non disponible'
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membres Global</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/membreglobal.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    <script src="/projetannuaire/client/script/rechercher.js" defer></script>
</head>
<body>
<header>
<div class="header-container">
  <div class="search-container">
    <img src="/projetannuaire/client/src/assets/images/search-icon.png" alt="Search Icon" class="search-icon">

    <!-- Ajoute list="suggestions" ici -->
    <input type="text" id="site-search" list="suggestions" placeholder="Nom, prénom, téléphone ou service" maxlength="32" />

    <datalist id="suggestions"></datalist> <!-- Ajoute cette balise -->

    <button class="bouton-search" type="button" onclick="rechercher()">Rechercher</button>
  </div>
</div>

</header>

<div class="top-button-container"> 
    <button class="top-button" onclick="window.location.href='pageaccueil.php'"> ← Retour</button>
</div>

<div class="membre-global-header">
    <h1>Membres Global</h1>
</div>

<div class="membre-container">
    <?php foreach ($finalUsers as $user): 
        $email = urlencode($user["email"]);
    ?>
        <a href="profilutilisateur.php?email=<?= $email ?>" class="membre-link">
            <div class="membre-card">
                <div class="membre-nom">
                    <?= htmlspecialchars($user["prenom"]) ?>
                    <?= htmlspecialchars($user["nom"]) ?>
                </div>
                <div class="membre-role">
                    <?= htmlspecialchars($user["email"]) ?><br>
                    <?= htmlspecialchars($user["role"]) ?>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<footer>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</footer>
</body>
</html>
