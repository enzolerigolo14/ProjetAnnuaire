<?php
session_start();
require __DIR__ . '/config/database.php';


$actualite_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;


$stmt = $pdo->prepare("SELECT a.*, s.nom as service_nom 
                      FROM actualites a
                      JOIN services s ON a.service_id = s.id
                      WHERE a.id = ?");
$stmt->execute([$actualite_id]);
$actualite = $stmt->fetch();

if (!$actualite) {
    header('Location: services-global.php');
    exit;
}

if (!$service_id && isset($actualite['service_id'])) {
    $service_id = $actualite['service_id'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'actualité - <?= htmlspecialchars($actualite['titre']) ?></title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/header.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/actualite-detail.css">

</head>
<body>

    <div class="featured-news">
        <div class="button-wrapper">
  <a href="pageaccueil.php?id=<?= $service_id ?>" class="top-button">← Retour à l'accueil</a>
</div>

    <span class="service-badge">Document du service : <?= htmlspecialchars($actualite['service_nom']) ?></span>
    
    <div class="actualite-item">
        <?php if (!empty($actualite['image'])): ?>
            <img src="<?= htmlspecialchars($actualite['image']) ?>" alt="<?= htmlspecialchars($actualite['titre']) ?>" class="featured-image">
        <?php endif; ?>
        <h2 class="featured-title"><?= htmlspecialchars($actualite['titre']) ?></h2>
        <p class="featured-text"><?= htmlspecialchars($actualite['description']) ?></p>

        <?php if (!empty($actualite['pdf_path'])): ?>
            <div class="pdf-container">
                <object data="download.php?type=actualite&id=<?= $actualite['id'] ?>&file=<?= basename($actualite['pdf_path']) ?>" 
                        type="application/pdf" 
                        class="pdf-viewer">
                </object>
            </div>
        <?php endif; ?>
        
    </div>
</div> <!-- Fermeture de featured-news -->
</body>
</html>