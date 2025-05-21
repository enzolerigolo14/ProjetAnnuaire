<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Vérifier qu'un fichier a été sélectionné
if (!isset($_FILES['documents']) || !isset($_FILES['documents']['name']) || 
    count($_FILES['documents']['name']) === 0 || empty($_FILES['documents']['name'][0])) {
    $_SESSION['upload_error'] = 'Aucun fichier sélectionné';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Récupérer et vérifier le service_id et le titre du document
$service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : null;
$document_title = isset($_POST['document_title']) ? trim($_POST['document_title']) : '';

if (!$service_id) {
    $_SESSION['upload_error'] = 'Service non précisé';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user']['id'])) {
    $_SESSION['upload_error'] = 'Utilisateur non identifié';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
$uploader_id = $_SESSION['user']['id'];

// Créer le dossier d'upload si nécessaire
$uploadDir = __DIR__ . '/uploads/service_' . $service_id . '/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        $_SESSION['upload_error'] = "Impossible de créer le dossier d'upload";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

$uploadedFiles = [];
$fileCount = count($_FILES['documents']['name']);

for ($i = 0; $i < $fileCount; $i++) {
    if ($_FILES['documents']['error'][$i] !== UPLOAD_ERR_OK) {
        continue;
    }
    
    $originalName = basename($_FILES['documents']['name'][$i]);
    $tmpName = $_FILES['documents']['tmp_name'][$i];
    
    // Générer un nom unique si nécessaire
    $targetPath = $uploadDir . $originalName;
    if (file_exists($targetPath)) {
        $fileInfo = pathinfo($originalName);
        $uniqueSuffix = '_' . time(); 
        $newName = $fileInfo['filename'] . $uniqueSuffix . '.' . $fileInfo['extension'];
        $targetPath = $uploadDir . $newName;
        $originalName = $newName;
    }
    
    // Déplacer le fichier
    if (is_uploaded_file($tmpName) && move_uploaded_file($tmpName, $targetPath)) {
        try {
            // Modifier cette requête pour inclure le document_title
            $stmt = $pdo->prepare("INSERT INTO service_files 
                                  (service_id, file_name, document_title, uploaded_by) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $service_id, 
                $originalName, 
                $document_title ?: pathinfo($originalName, PATHINFO_FILENAME), // Utilise le titre ou le nom du fichier
                $uploader_id
            ]);
            
            $uploadedFiles[] = $originalName;
        } catch (PDOException $e) {
            unlink($targetPath);
            error_log("Erreur DB: " . $e->getMessage());
        }
    }
}

if (!empty($uploadedFiles)) {
    $_SESSION['upload_success'] = count($uploadedFiles) . ' fichier(s) uploadé(s)';
} else {
    $_SESSION['upload_error'] = 'Aucun fichier uploadé';
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
?>