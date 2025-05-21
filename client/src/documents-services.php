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

// Définition des chemins d'upload
$uploadDir = __DIR__ . '/uploads/service_' . intval($service_id) . '/';
$webPath = '/uploads/service_' . intval($service_id) . '/';

// Gestion de la suppression de fichier
if (isset($_GET['delete_file'])) {
    
        $fileToDelete = basename($_GET['delete_file']);
        $filePath = $uploadDir . $fileToDelete;
        
        if (file_exists($filePath)) {
            try {
                $stmt = $pdo->prepare("DELETE FROM service_files WHERE service_id = ? AND file_name = ?");
                $stmt->execute([$service_id, $fileToDelete]);
            } catch (PDOException $e) {
                error_log("Erreur suppression DB: " . $e->getMessage());
            }
            
            if (unlink($filePath)) {
                header("Location: documents-services.php?id=" . $service_id);
                exit();
            } else {
                echo "<script>alert('Erreur lors de la suppression du fichier');</script>";
            }
        } else {
            echo "<script>alert('Fichier introuvable');</script>";
        }
    
}

// Récupération des fichiers
$hasFiles = false;
$filesList = '';

try {
    $stmt = $pdo->prepare("SELECT sf.*, u.prenom, u.nom 
                      FROM service_files sf 
                      JOIN users u ON sf.uploaded_by = u.id 
                      WHERE sf.service_id = ?");
    $stmt->execute([$service_id]);
    $filesFromDb = $stmt->fetchAll();



// Dans la partie où vous générez la liste des fichiers, modifiez comme suit :
if (!empty($filesFromDb)) {
    $hasFiles = true;
    $filesList .= "<div class='uploaded-files'><h3>Fichiers uploadés :</h3><ul>";
    foreach ($filesFromDb as $file) {
        $filePath = $uploadDir . $file['file_name'];
        if (file_exists($filePath)) {
            $isPdf = pathinfo($file['file_name'], PATHINFO_EXTENSION) === 'pdf';
            $downloadParam = $isPdf ? '' : '&download=1';
            
            $documentTitle = !empty($file['document_title']) ? htmlspecialchars($file['document_title']) : htmlspecialchars($file['file_name']);
            $filesList .= "<li>
            <div class='file-title'>{$documentTitle}</div>
            <div class='file-details'>
                <a href='/projetannuaire/client/src/download.php?type=service&service_id=".$service_id."&file=".rawurlencode($file['file_name']).$downloadParam."'>".htmlspecialchars($file['file_name'])."</a>
                <span class='uploaded-by'> (uploadé par : ".htmlspecialchars($file['prenom'])." ".htmlspecialchars($file['nom']).")</span>";
            
            // Ajout de la condition pour le bouton de suppression
            if (isset($_SESSION['user']['role'])) {
                $role = strtoupper($_SESSION['user']['role']);
                if ($role === 'SVC-INFORMATIQUE' || $role === 'ADMIN-INTRA' || $role === 'ADMIN-RH') {
                    $filesList .= "<a href='/projetannuaire/client/src/documents-services.php?id=".$service_id."&delete_file=".rawurlencode($file['file_name'])."' class='delete-file' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer ce fichier ?\")' title='Supprimer'>
                        <svg class='trash-icon' viewBox='0 0 24 24' width='18' height='18'>
                            <path fill='currentColor' d='M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z' />
                        </svg>
                    </a>";
                }
            }
            
            $filesList .= "</li>";
        }
    }
    $filesList .= "</ul></div>";
}

} catch (PDOException $e) {
    // Fallback si la table n'existe pas
    if (is_dir($uploadDir)) {
        $files = scandir($uploadDir);
        $files = array_filter($files, function($file) use ($uploadDir) {
            return is_file($uploadDir . $file);
        });
        
        if (!empty($files)) {
            $hasFiles = true;
            $filesList .= "<div class='uploaded-files'><h3>Fichiers uploadés :</h3><ul>";
            foreach ($files as $file) {
                $isPdf = pathinfo($file, PATHINFO_EXTENSION) === 'pdf';
                $downloadParam = $isPdf ? '' : '&download=1';
                
                $filesList .= "<li>
                    <a href='/projetannuaire/client/src/download.php?type=service&service_id=".$service_id."&file=".rawurlencode($file).$downloadParam."'>".htmlspecialchars($file)."</a>
                    <a href='/projetannuaire/client/src/documents-services.php?id=".$service_id."&delete_file=".rawurlencode($file)."' class='delete-file' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer ce fichier ?\")' title='Supprimer'>
                        <svg class='trash-icon' viewBox='0 0 24 24' width='18' height='18'>
                            <path fill='currentColor' d='M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z' />
                        </svg>
                    </a>
                </li>";
            }
            $filesList .= "</ul></div>";
        }
    }
}

if (!$hasFiles) {
    $filesList = "<p class='no-files'>Aucun fichier uploadé pour le moment.</p>";
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
    <div class="top-button-container">
        <button class="top-button" onclick="window.location.href='services-global.php'"> ← Retour</button>
    </div>
    <h1 class="document-title">
        Document du service associé : <?= htmlspecialchars($service['nom'] ?? 'Inconnu') ?>
    </h1>

    <?php if (isset($_SESSION['user']['role'])): 
        $role = strtoupper($_SESSION['user']['role']);
        if ($role === 'SVC-INFORMATIQUE' || $role === 'ADMIN-INTRA' || $role === 'ADMIN-RH'): ?>
            <div class="admin-actions">
                <button class="upload-label" id="open-modal">Déposer un document</button>
            </div>
        <?php endif; 
    endif; ?>
    
    <?php echo $filesList; ?>

    <?php if (isset($_SESSION['user']['role'])): 
        $role = strtoupper($_SESSION['user']['role']);
        if ($role === 'SVC-INFORMATIQUE' || $role === 'ADMIN-INTRA' || $role === 'ADMIN-RH'): ?>
            <div class="modal" id="upload-modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2 class="modal-title">Importer des documents</h2>
                    <form id="upload-form" method="post" enctype="multipart/form-data" action="upload.php" class="upload-form">
                        <input type="hidden" name="service_id" value="<?= htmlspecialchars($service_id) ?>">
                        <input type="file" name="documents[]" id="documents" multiple class="file-input">

                        <!--Champs pour mettre un titre au document-->
                        <label for="document_title">Titre du document :</label>
                        <input type="text" name="document_title" id="document_title" class="document-title-input" placeholder="Titre du document" required>


                        <small class="file-instructions">Maintenez <strong>Ctrl</strong> pour sélectionner plusieurs fichiers.</small>
                        <button type="submit" class="btn-upload">Envoyer les fichiers</button>
                        <div id="file-list" class="file-list"></div>


                    </form>
                </div>
            </div>
        <?php endif; 
    endif; ?>
</div>
</body>
</html>