<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Récupération du service
$service_id = $_GET['id'] ?? null;
if ($service_id) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$service_id]);
    $service = $stmt->fetch();
}

// Vérification de l'existence du répertoire d'upload
// Modifiez cette partie dans votre code
$uploadDir = __DIR__ . '/uploads/service_' . intval($service_id) . '/';
$webPath = '/uploads/service_' . intval($service_id) . '/'; // Chemin web relatif
$hasFiles = false;
$filesList = '';

if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    $files = array_filter($files, function($file) use ($uploadDir) {
        return is_file($uploadDir . $file);
    });
    
    if (!empty($files)) {
        $hasFiles = true;
        $filesList .= "<div class='uploaded-files'><h3>Fichiers uploadés :</h3><ul>";
        // Dans la partie où vous générez les liens :
        foreach ($files as $file) {
            $filesList .= "<li><a href='/projetannuaire/client/src/download.php?service_id=".$service_id."&file=".rawurlencode($file)."'>".htmlspecialchars($file)."</a></li>";
        }
        $filesList .= "</ul></div>";
    }
}

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trombinoscope ville de Lisieux</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/documents-services.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/header.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    <script src="/projetannuaire/client/script/documents-services.js" defer></script>
</head>

<body>
<div class="document-container">
    <h1 class="document-title">
        Document du service associé : <?= htmlspecialchars($service['nom'] ?? 'Inconnu') ?>
    </h1>

    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
    <div class="admin-actions">
        <button class="upload-label" id="open-modal">Déposer un document</button>
    </div>
    <?php endif; ?>

    <?php
    // Affichage des fichiers existants
    if ($hasFiles) {
        echo $filesList;
    } else {
        echo "<p class='no-files'>Aucun fichier uploadé pour le moment.</p>";
    }
    ?>

    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
    <div class="modal" id="upload-modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 class="modal-title">Importer des documents</h2>
            <form id="upload-form" method="post" enctype="multipart/form-data" action="upload.php" class="upload-form">
                <input type="hidden" name="service_id" value="<?= htmlspecialchars($service_id) ?>">
                <input type="file" name="documents[]" id="documents" multiple class="file-input">
                <small class="file-instructions">Maintenez <strong>Ctrl</strong> pour sélectionner plusieurs fichiers.</small>
                <button type="submit" class="btn-upload">Envoyer les fichiers</button>
                <div id="file-list" class="file-list"></div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>
</body>
</html>