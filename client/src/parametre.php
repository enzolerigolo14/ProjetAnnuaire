<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/parametre.css">
</head>
<body>
    <div class="top-button-container">
        <button class="top-button" onclick="window.location.href='pageaccueil.php'"> ← Retour</button>
    </div>

<div class="parametres-options">
    <h2>Options d'administration</h2>
    <a href="/projetannuaire/client/src/inscription.php" class="option-link">Gérer les inscriptions</a>
    <a href="/projetannuaire/client/src/membreglobal.php?from=parametre" class="option-link">Gérer les utilisateurs</a>
    <a href="/projetannuaire/client/src/gerer-services.php" class="option-link">Gérer les services</a>
    <a href="/projetannuaire/client/src/copyright.php" class="option-link">À propos</a>
</div>
</body>
</html>