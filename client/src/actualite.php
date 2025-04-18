<?php
session_start();

if (!isset($_SESSION['user'])) {
  header('Location: connexion.php');
  exit;
}

require_once __DIR__ . '/config/database.php';


$isAdmin = ($_SESSION['user']['role'] === 'super_admin' || $_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'admin2' || $_SESSION['user']['role'] === 'membre');


$service_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    header('Location: services-global.php');
    exit;
}


$stmt = $pdo->prepare("SELECT * FROM services");
$stmt->execute();
$services = $stmt->fetchAll();

// Gestion des actualités
if ($isAdmin && isset($_POST['titre']) && isset($_POST['description'])) {
    $titre = $_POST['titre'];
    $description = $_POST['description'];
    $image = $_POST['image'] ?? '';
    
    $stmt = $pdo->prepare("INSERT INTO actualites (titre, description, image, service_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$titre, $description, $image, $service_id]);
    header("Location: actualite.php?id=".$service_id);
    exit;
}

if ($isAdmin && isset($_GET['delete_actualite'])) {
    $id = $_GET['delete_actualite'];
    $stmt = $pdo->prepare("DELETE FROM actualites WHERE id = ? AND service_id = ?");
    $stmt->execute([$id, $service_id]);
    header("Location: actualite.php?id=".$service_id);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM actualites WHERE service_id = ? ORDER BY created_at DESC");
$stmt->execute([$service_id]);
$actualites = $stmt->fetchAll();


$derniere_actualite = array_shift($actualites);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualités - <?= htmlspecialchars($service['nom']) ?></title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/header.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/actualite.css">
    <script src="/projetannuaire/client/script/actualite.js"></script>
</head>

<body>

<div class="profile-header">
    <a href="services-global-actualite.php" class="back-button">← Retour aux services</a>
    <h2>Actualités du service: <?= htmlspecialchars($service['nom']) ?></h2>
</div>

<div class="actualite">
    <?php if ($isAdmin): ?>
    <button id="modifier-actualite" class="modifier-actualite">Ajouter une actualité</button>
    <?php endif; ?>
    
    <div class="main-container">
        <!-- Dernière actualité en gros plan -->
        <div class="featured-news">
            <?php if ($derniere_actualite): ?>
                <?php if (!empty($derniere_actualite['image'])): ?>
                <img src="<?= htmlspecialchars($derniere_actualite['image']) ?>" alt="<?= htmlspecialchars($derniere_actualite['titre']) ?>" class="featured-image">
                <?php endif; ?>
                <h2 class="featured-title"><?= htmlspecialchars($derniere_actualite['titre']) ?></h2>
                <p class="featured-text"><?= htmlspecialchars($derniere_actualite['description']) ?></p>
                <?php if ($isAdmin): ?>
                <div class="actualite-actions">
                    <a href="?id=<?= $service_id ?>&delete_actualite=<?= $derniere_actualite['id'] ?>" class="delete-actualite" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette actualité ?')">Supprimer</a>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <p>Aucune actualité disponible pour ce service</p>
            <?php endif; ?>
        </div>
        
        <!-- Liste des autres actualités -->
<div class="news-sidebar">
    <?php foreach ($actualites as $actualite): ?>
    <div class="news-item" onclick="window.location.href='actualite-detail.php?id=<?= $actualite['id'] ?>&service_id=<?= $service_id ?>'">
        <?php if (!empty($actualite['image'])): ?>
        <img src="<?= htmlspecialchars($actualite['image']) ?>" alt="<?= htmlspecialchars($actualite['titre']) ?>" class="sidebar-image">
        <?php endif; ?>
        <h3 class="sidebar-title"><?= htmlspecialchars($actualite['titre']) ?></h3>
        <p class="sidebar-text"><?= htmlspecialchars($actualite['description']) ?></p>
        <?php if ($isAdmin): ?>
        <div class="actualite-actions" onclick="event.stopPropagation();">
            <a href="?id=<?= $service_id ?>&delete_actualite=<?= $actualite['id'] ?>" class="delete-actualite" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette actualité ?')">Supprimer</a>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
    </div>
</div>

<?php if ($isAdmin): ?>
<div class="actualite-modification">
    <div id="overlay" class="overlay"></div>

    <div id="actualite-form" class="actualite-form hidden">
        <form id="form-actualite" action="" method="POST">
            <h2>Nouvelle Actualité pour <?= htmlspecialchars($service['nom']) ?></h2>

            <label for="titre">Titre:</label>
            <input type="text" id="titre" name="titre" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="image">Image URL:</label>
            <input type="text" id="image" name="image">

            <div class="form-buttons">
                <button type="submit">Ajouter</button>
                <button type="button" id="annuler-actualite">Annuler</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<footer>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</footer>

</body>
</html>