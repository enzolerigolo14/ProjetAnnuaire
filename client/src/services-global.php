<?php
session_start();
require_once __DIR__ . '/config/database.php';



$stmt = $pdo->prepare("SELECT * FROM services");
$stmt->execute();
$services = $stmt->fetchAll();
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trombinoscope ville de Lisieux</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/services-global.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/header.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    

    <script src="/projetannuaire/client/script/services-global.js" defer></script>

</head>
<body>

  <!-- BOUTON RETOUR placé AVANT le header -->
  

  <header>
    <?php require_once __DIR__ . '/includes/header.php'; ?>
  </header>
<button class="top-button" onclick="window.location.href='pageaccueil.php'">← Retour</button>
  <div class="services-container">
    <?php foreach ($services as $service): ?>
        <div class="service-item">
            <span class="service-name"><?= htmlspecialchars($service['nom']) ?></span>
            <a href="documents-services.php?id=<?= $service['id'] ?>" class="service-button">Accéder au service</a>
        </div>
    <?php endforeach; ?>
  </div>

</body>
<footer>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</footer>


    
</html>