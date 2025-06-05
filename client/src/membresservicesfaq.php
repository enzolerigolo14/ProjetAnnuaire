<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Récupérer tous les services
$services = $pdo->query("SELECT * FROM services ORDER BY nom")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>FAQ - Liste des services</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/membresservicesfaq.css">
</head>
<body>



<!-- Titre principal -->
<h1 class="title">FAQ - Sélectionnez un service</h1>

<!-- Bouton retour juste en dessous du titre -->
<div class="return-button-container">
    <a href="pageaccueil.php" class="top-button">← Retour à l'accueil</a>
</div>

<!-- Conteneur des services -->
<div class="services-container">
    <?php foreach ($services as $service): ?>
        <div class="service-item">
            <div class="service-name"><?= htmlspecialchars($service['nom']) ?></div>
            <a class="service-button" href="faq.php?service_id=<?= $service['id'] ?>">Voir la FAQ</a>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
