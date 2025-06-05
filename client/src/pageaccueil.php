<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/ldaptest.php';



if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit;
}

// Vérifier si c'est une première connexion
$showWelcomeMessage = isset($_SESSION['new_user_registered']) && $_SESSION['new_user_registered'] === true;
if ($showWelcomeMessage) {
    unset($_SESSION['new_user_registered']);
}

$username = explode('@', $_SESSION['user']['email'])[0]; 



//return; // Commenter cette ligne pour activer la récupération des groupes depuis LDAP
// Récupérer les services pour le menu
$stmt = $pdo->prepare("SELECT * FROM services");
$stmt->execute();
$services = $stmt->fetchAll();

// Récupérer les 3 dernières actualités
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

  <?php if ($showWelcomeMessage): ?>
    <div class="welcome-banner">
        <h3>Bienvenue <?= htmlspecialchars($_SESSION['user']['prenom']) ?> dans l'annuaire !</h3>
        <p>Votre compte a été créé avec succès.</p>
        <p>Email: <?= htmlspecialchars($_SESSION['user']['email']) ?></p>

        <?php if (!empty($_SESSION['user']['groups'])): ?>
    <p>Groupes :</p>
    <ul>
        <?php foreach ($_SESSION['user']['groups'] as $group): ?>
            <li><?= htmlspecialchars($group) ?></li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Aucun groupe trouvé.</p>
<?php endif; ?>

    </div>
    <?php endif; ?>

  <nav class="navbar">
    <ul class="nav-list">
      <li><a href="membreglobal.php?from=accueil">Agents</a></li>
      <li><a href="services-global-membre.php">Services</a></li>
      <li><a href="services-global.php">Documents</a></li>
      <li><a href="services-global-actualite.php">Informations</a></li>
      <li><a href="membresservicesfaq.php">FAQ</a></li>
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
                class="pdf-preview"
                target="_blank">
        </object>
    </div>
<?php endif; ?>
            
<a href="actualite-detail.php?id=<?= $actualite['id'] ?>&service_id=<?= $actualite['service_id'] ?>" class="actualite-link" target="_blank"></a>        </div>
        <?php endforeach; ?>
    </div>
  </div>
</body>
</html>