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
    

<div class="parametres-options">
    <h2>Options d'administration</h2>
    <a href="/projetannuaire/client/src/inscription.php" class="option-link">Gérer les inscriptions</a>
    <a href="/projetannuaire/client/src/membreglobal.php?source=db" class="option-link">Gérer les utilisateurs</a>
    <a href="/projetannuaire/client/src/ajouterservice.php" class="option-link">Ajouter un service</a>
</div>
</body>
</html>