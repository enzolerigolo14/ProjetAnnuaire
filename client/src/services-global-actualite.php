<?php
session_start();
require_once __DIR__ . '/config/database.php';

$stmt = $pdo->prepare("SELECT * FROM services");
$stmt->execute();
$services = $stmt->fetchAll();

// Récupération des actualités avec leurs PDF
$stmt = $pdo->prepare("SELECT a.id, a.titre, a.pdf_path, s.id as service_id, s.nom as service_nom 
                      FROM actualites a
                      JOIN services s ON a.service_id = s.id
                      WHERE a.pdf_path IS NOT NULL AND a.pdf_path != ''");
$stmt->execute();
$actualites = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trombinoscope ville de Lisieux</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/services-global.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/services-global-actualite.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/header.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
     <script>
        const allActualites = <?php echo json_encode(array_map(function($a) {
            return [
                'id' => $a['id'],
                'titre' => $a['titre'],
                'pdf_url' => '/projetannuaire/client/src/download.php?type=actualite&id='.$a['id'].'&file='.rawurlencode(basename($a['pdf_path'])),
                'service_nom' => $a['service_nom']
            ];
        }, $actualites)); ?>;
    </script>
   
</head>
<body>

<h1 class="title">Tous les services :</h1>

<div class="top-button-container">
    <button class="top-button" onclick="window.location.href='pageaccueil.php'">← Retour</button>
</div>

<!-- Barre de recherche avec autocomplétion -->
<div class="search-container">
    <input type="text" id="actualite-search" placeholder="Rechercher une actualité..." autocomplete="off">
    <div id="search-results" class="search-results"></div>
</div>

<div class="services-container">
    <?php foreach ($services as $service): ?>
        <div class="service-item">
            <span class="service-name"><?= htmlspecialchars($service['nom']) ?></span>
            <a href="actualite.php?id=<?= $service['id'] ?>" class="service-button">Accéder aux informations</a>
        </div>
    <?php endforeach; ?>
</div>

<footer>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</footer>

<script src="/projetannuaire/client/script/services-global-actualite.js"></script>
</body>
</html>