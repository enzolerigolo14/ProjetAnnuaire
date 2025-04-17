<?php

session_start();

require  __DIR__ . '/config/database.php';


//je veux afficher l'actulité en fonction de l'id passé dans l'url
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM actualites WHERE id = ?");
    $stmt->execute([$id]);
    $actualite = $stmt->fetch();
}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'actualité</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/header.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/actualite-detail.css">

    </head>
<body>

    <div class="actualite-detail-container">
        <h1><?= htmlspecialchars($actualite['titre']) ?></h1>
        <img src="<?= htmlspecialchars($actualite['image']) ?>" alt="Image de l'actualité">
        <p><?= htmlspecialchars($actualite['description']) ?></p>
        <a href="actualite.php" class="back-button">Retour aux actualités</a>
    </div>

    <footer>
        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </footer>
</html>