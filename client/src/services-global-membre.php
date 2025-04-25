<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

$services = [
    1 => 'Service Accueil',
    2 => 'Service Administration Générale',
    3 => 'Service Bâtiment',
    4 => 'Service Bureau d\'études',
    5 => 'Service Cabinet',
    6 => 'Service Communication',
    7 => 'Service Élections',
    8 => 'Service État Civil',
    9 => 'Service Événementiel',
    10 => 'Service Fêtes & Cérémonies',
    11 => 'Service Finances',
    12 => 'Service Juridique',
    13 => 'Service Marché Public',
    14 => 'Service Pompes Funèbres',
    15 => 'Service Ressources Humaines',
    16 => 'Service Secrétariat Général',
    17 => 'Service Stationnement Payant',
    18 => 'Tous les services de la Ville',
    19 => 'Tous les services de l\'Hôtel de Ville',
];

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
    <script src="/projetannuaire/client/script/services-global.js" defer></script>
</head>
<body>

<h1 class="title">Tous les services :</h1>

<div class="top-button-container">
    <button class="top-button" onclick="window.location.href='pageaccueil.php'">← Retour</button>
</div>

<div class="services-container">
    <?php foreach ($services as $id => $nom): ?>
        <div class="service-item">
            <span class="service-name"><?= htmlspecialchars($nom) ?></span>
            <!-- Changement ici : on passe l'ID au lieu du nom -->
            <a href="membresservices.php?id=<?= $id ?>" class="service-button">Accéder au service</a>
        </div>
    <?php endforeach; ?>
</div>

<footer>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</footer>

</body>
</html>
