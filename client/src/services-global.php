<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Récupération de tous les services
$stmt = $pdo->prepare("SELECT id, nom FROM services");
$stmt->execute();
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trombinoscope ville de Lisieux</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/services-global.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/projetannuaire/client/script/services-global.js" defer></script>
</head>
<body>

<h1 class="title">Tous les services :</h1>

<div class="top-button-container">
    <button class="top-button" onclick="window.location.href='pageaccueil.php'">← Retour</button>
</div>

<!-- Barre de recherche avec autocomplétion -->
<div class="search-container">
    <input type="text" id="document-search" placeholder="Rechercher un document..." autocomplete="off">
    <div id="search-results" class="search-results"></div>
</div>

<div class="services-container">
    <?php foreach ($services as $service): ?>
        <div class="service-item">
            <h3><?= htmlspecialchars($service['nom']) ?></h3>
            <a href="documents-services.php?id=<?= $service['id'] ?>" class="service-button">Accéder au service</a>
        </div>
    <?php endforeach; ?>
</div>

<footer>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</footer>
</body>
</html>