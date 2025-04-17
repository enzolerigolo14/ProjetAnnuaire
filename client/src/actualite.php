<?php
session_start();

if (!isset($_SESSION['user'])) {
  header('Location: connexion.php');
  exit;
}

require_once __DIR__ . '/config/database.php';

// Vérifier si l'utilisateur est admin
$isAdmin = ($_SESSION['user']['role'] === 'super_admin' || $_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'admin2' || $_SESSION['user']['role'] === 'membre');

// Récupérer les services pour le menu
$stmt = $pdo->prepare("SELECT * FROM services");
$stmt->execute();
$services = $stmt->fetchAll();

// Gestion des actualités
if ($isAdmin && isset($_POST['titre']) && isset($_POST['description'])) {
    $titre = $_POST['titre'];
    $description = $_POST['description'];
    $image = $_POST['image'] ?? '';
    
    $stmt = $pdo->prepare("INSERT INTO actualites (titre, description, image) VALUES (?, ?, ?)");
    $stmt->execute([$titre, $description, $image]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
    
}

if ($isAdmin && isset($_GET['delete_actualite'])) {
    $id = $_GET['delete_actualite'];
    $stmt = $pdo->prepare("DELETE FROM actualites WHERE id = ?");
    $stmt->execute([$id]);
}

$stmt = $pdo->prepare("SELECT * FROM actualites ORDER BY created_at DESC");
$stmt->execute();
$actualites = $stmt->fetchAll();

// Séparer la dernière actualité des autres
$derniere_actualite = array_shift($actualites);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trombinoscope ville de Lisieux</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/header.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/actualite.css">

    <script src="/projetannuaire/client/script/actualite.js"></script>
</head>

<body>


<div class="profile-header">
    <a href="pageaccueil.php" class="back-button">← Retour</a>
</div>

<div class="actualite">
    <h1>Actualités</h1>
    <button id="modifier-actualite" class="modifier-actualite">Ajouter une actualité</button>
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
                    <a href="?delete_actualite=<?= $derniere_actualite['id'] ?>" class="delete-actualite" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette actualité ?')">Supprimer</a>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <p>Aucune actualité disponible</p>
            <?php endif; ?>
        </div>
        
        <!-- Liste des autres actualités -->
        <div class="news-sidebar">
            <?php foreach ($actualites as $actualite): ?>
            <div class="news-item" onclick="window.location.href='actualite-detail.php?id=<?= $actualite['id'] ?>'">
                <?php if (!empty($actualite['image'])): ?>
                <img src="<?= htmlspecialchars($actualite['image']) ?>" alt="<?= htmlspecialchars($actualite['titre']) ?>" class="sidebar-image">
                <?php endif; ?>
                <h3 class="sidebar-title"><?= htmlspecialchars($actualite['titre']) ?></h3>
                <p class="sidebar-text"><?= htmlspecialchars($actualite['description']) ?></p>
                <?php if ($isAdmin): ?>
                <div class="actualite-actions">
                    <a href="?delete_actualite=<?= $actualite['id'] ?>" class="delete-actualite" onclick="event.stopPropagation(); return confirm('Êtes-vous sûr de vouloir supprimer cette actualité ?')">Supprimer</a>
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
            <h2>Nouvelle Actualité</h2>

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