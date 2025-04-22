<?php
session_start();

if (!isset($_SESSION['user'])) {
  header('Location: connexion.php');
  exit;
}

require_once __DIR__ . '/config/database.php';

// Récupérer les services pour le menu
$stmt = $pdo->prepare("SELECT * FROM services");
$stmt->execute();
$services = $stmt->fetchAll();

// Récupérer les 3 dernières actualités avec le nom du service
$stmt = $pdo->prepare("SELECT a.*, s.nom as service_nom 
                      FROM actualites a
                      JOIN services s ON a.service_id = s.id
                      ORDER BY a.created_at DESC 
                      LIMIT 3");
$stmt->execute();
$actualites = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trombinoscope ville de Lisieux</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/pageaccueil.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/header.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    
</head>

<body>
  <header>
    <?php require_once __DIR__ . '/includes/header.php'; ?>
  </header>

  <nav class="navbar">
    <ul class="nav-list">
      <li>
        <a href="membreglobal.php">Agents</a>
      </li>
      <li>
        <a href="">Services</a>
        <ul class="dropdown services-dropdown">
            <?php foreach ($services as $service): ?>
            <li><a href="membresservices.php?id=<?= $service['id'] ?>"><?= htmlspecialchars($service['nom']) ?></a></li>
            <?php endforeach; ?>
        </ul>
      </li>
      <li>
        <a href="services-global.php">Documents</a>
      </li>
      <li>
        <a href="services-global-actualite.php">Informations</a>
      </li>
      <li>
        <a href="faq.php">FAQ</a>
      </li>
    </ul>
  </nav>

  <div class="actualite">
    <h1>Actualités récentes</h1>
    <div class="actualite-container">
        <?php foreach ($actualites as $actualite): ?>
        <div class="actualite-item">
            <span class="service-badge"><?= htmlspecialchars($actualite['service_nom']) ?></span>
            <?php if (!empty($actualite['image'])): ?>
            <img src="<?= htmlspecialchars($actualite['image']) ?>" alt="<?= htmlspecialchars($actualite['titre']) ?>" class="actualite-image">
            <?php endif; ?>
            <h3 class="actualite-title"><?= htmlspecialchars($actualite['titre']) ?></h3>
            <p class="actualite-text"><?= htmlspecialchars($actualite['description']) ?></p>
            
            <?php if (!empty($actualite['pdf_path'])): ?>
                <div class="actualite-pdf-preview">
                    <object data="download.php?type=actualite&id=<?= $actualite['id'] ?>&file=<?= basename($actualite['pdf_path']) ?>" 
                            type="application/pdf" 
                            class="pdf-preview">
                    </object>
                    
                </div>
            <?php endif; ?>
            
            <a href="actualite-detail.php?id=<?= $actualite['id'] ?>&service_id=<?= $actualite['service_id'] ?>" class="actualite-link"></a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

  <footer>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
  </footer>
</body>
</html>