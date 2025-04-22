<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit;
}

require_once __DIR__ . '/config/database.php';

// Vérifie si l'utilisateur est admin ou membre autorisé
$isAdmin = in_array($_SESSION['user']['role'], ['super_admin', 'admin', 'admin2', 'membre']);

// Récupération de l'ID du service
$service_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    header('Location: services-global.php');
    exit;
}

// Récupère la liste de tous les services
$stmt = $pdo->prepare("SELECT * FROM services");
$stmt->execute();
$services = $stmt->fetchAll();

// Ajout d'une actualité
if ($isAdmin && isset($_POST['titre'], $_POST['description'])) {
    $titre = $_POST['titre'];
    $description = $_POST['description'];
    $image = $_POST['image'] ?? '';
    $pdf_path = '';

    // Gestion de l'upload de fichier PDF
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/actualites/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_info = pathinfo($_FILES['pdf_file']['name']);
        if (strtolower($file_info['extension']) !== 'pdf') {
            $_SESSION['error'] = "Seuls les fichiers PDF sont acceptés";
            header("Location: actualite.php?id=" . $service_id);
            exit;
        }

        $file_name = uniqid() . '.pdf';
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $target_path)) {
            $pdf_path = '/uploads/actualites/' . $file_name;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO actualites (titre, description, image, pdf_path, service_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$titre, $description, $image, $pdf_path, $service_id]);

    $_SESSION['success'] = "Actualité ajoutée avec succès";
    header("Location: actualite.php?id=" . $service_id);
    exit;
}

// Suppression d'une actualité
if ($isAdmin && isset($_GET['delete_actualite'])) {
    $id = $_GET['delete_actualite'];
    $stmt = $pdo->prepare("DELETE FROM actualites WHERE id = ? AND service_id = ?");
    $stmt->execute([$id, $service_id]);
    header("Location: actualite.php?id=" . $service_id);
    exit;
}

// Récupération des actualités
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
    <script src="/projetannuaire/client/script/actualite.js" defer></script>
</head>
<body>

    <div class="profile-header">
        <a href="services-global-actualite.php" class="back-button">← Retour aux services</a>
        <h2>Actualités du service : <?= htmlspecialchars($service['nom']) ?></h2>
    </div>

    <div class="actualite">
        <?php if ($isAdmin): ?>
            <button id="modifier-actualite" class="modifier-actualite">Ajouter une actualité</button>
        <?php endif; ?>

        <div class="main-container">
    <!-- Actualité principale -->
    <div class="featured-news">
        <?php if ($derniere_actualite): ?>
            <?php if (!empty($derniere_actualite['image'])): ?>
                <img src="<?= htmlspecialchars($derniere_actualite['image']) ?>" alt="<?= htmlspecialchars($derniere_actualite['titre']) ?>" class="featured-image">
            <?php endif; ?>
            <h2 class="featured-title"><?= htmlspecialchars($derniere_actualite['titre']) ?></h2>
            <p class="featured-text"><?= htmlspecialchars($derniere_actualite['description']) ?></p>

            <?php if (!empty($derniere_actualite['pdf_path'])): ?>
                <div class="pdf-container">
                    <object data="download.php?type=actualite&id=<?= $derniere_actualite['id'] ?>&file=<?= basename($derniere_actualite['pdf_path']) ?>" 
                            type="application/pdf" 
                            class="pdf-viewer">
                    </object>
                </div>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
                <div class="actualite-actions">
                    <a href="?id=<?= $service_id ?>&delete_actualite=<?= $derniere_actualite['id'] ?>" class="delete-actualite" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette actualité ?')">Supprimer</a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p>Aucune actualité disponible pour ce service.</p>
        <?php endif; ?>
    </div>

    <!-- Liste des autres actualités -->
    <div class="news-sidebar">
        <?php foreach ($actualites as $actualite): ?>
            <div class="news-item">
                <div class="news-content">
                    <?php if (!empty($actualite['image'])): ?>
                        <img src="<?= htmlspecialchars($actualite['image']) ?>" alt="<?= htmlspecialchars($actualite['titre']) ?>" class="sidebar-image">
                    <?php endif; ?>
                    <h3 class="sidebar-title"><?= htmlspecialchars($actualite['titre']) ?></h3>
                    <p class="sidebar-text"><?= htmlspecialchars($actualite['description']) ?></p>
                </div>

                <?php if (!empty($actualite['pdf_path'])): ?>
                    <div class="pdf-container">
                        <object data="download.php?type=actualite&id=<?= $actualite['id'] ?>&file=<?= basename($actualite['pdf_path']) ?>" 
                                type="application/pdf" 
                                class="pdf-viewer">
                        </object>
                    </div>
                <?php endif; ?>

                <?php if ($isAdmin): ?>
                    <div class="actualite-actions">
                        <a href="?id=<?= $service_id ?>&delete_actualite=<?= $actualite['id'] ?>" 
                           class="delete-actualite" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette actualité ?')">
                           Supprimer
                        </a>
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
            <form id="form-actualite" action="actualite.php?id=<?= $service_id ?>" method="POST" enctype="multipart/form-data">
                <h2>Nouvelle Actualité pour <?= htmlspecialchars($service['nom']) ?></h2>

                <label for="titre">Titre:</label>
                <input type="text" id="titre" name="titre" required>

                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>

                <label for="image">Image (URL):</label>
                <input type="text" id="image" name="image">

                <label for="pdf_file">Document PDF :</label>
                <input type="file" id="pdf_file" name="pdf_file" accept=".pdf">

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
