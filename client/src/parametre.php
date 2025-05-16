<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['origin'] = 'parametre.php'; // Marquer la provenance
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/css/parametre.css">
</head>
<body>
    

<div class="parametres-options">
    <h2>Options d'administration</h2>
    
    <!-- Option pour aller vers l'inscription -->
    <a href="/projetannuaire/client/src/inscription.php" class="option-link">
        <img src="/chemin/vers/icone-inscription.png" alt="Inscription">
        Gérer les inscriptions
    </a>

    <!-- Autres options de paramètres -->
   <a href="/projetannuaire/client/src/membreglobal.php?source=db" class="option-link">
    <img src="/chemin/vers/icone-utilisateurs.png" alt="Utilisateurs">
    Gérer les utilisateurs
</a>
</div>
</body>
</html>