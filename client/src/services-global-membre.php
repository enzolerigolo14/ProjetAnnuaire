<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Récupération uniquement des services actifs
$stmt = $pdo->prepare("SELECT id, nom FROM services WHERE actif = 1 ORDER BY nom ASC");
$stmt->execute();
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trombinoscope Ville de Lisieux - Services</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/services-global.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/header.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
</head>
<body>

<h1 class="title">Tous les services :</h1>

<div class="top-button-container">
    <button class="top-button" onclick="window.location.href='pageaccueil.php'">← Retour</button>
</div>

<div class="services-container">
    <?php foreach ($services as $service): ?>
        <div class="service-item">
            <h3><?= htmlspecialchars($service['nom']) ?></h3>
            <a href="membresservices.php?id=<?= $service['id'] ?>" class="service-button">Accéder au service</a>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>