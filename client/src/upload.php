<?php
session_start();

// Pour du debugging (désactive sur prod)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    die('Accès refusé');
}

// Vérifier qu'un fichier a été sélectionné
if (
    !isset($_FILES['documents']) ||
    !isset($_FILES['documents']['name']) || 
    count($_FILES['documents']['name']) === 0 || 
    empty($_FILES['documents']['name'][0])
) {
    $_SESSION['upload_error'] = 'Aucun fichier sélectionné';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Récupérer et vérifier le service_id
$service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : null;
if (!$service_id) {
    $_SESSION['upload_error'] = 'Service non précisé';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Créer ou utiliser le dossier d'upload spécifique au service
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
    // Vérifier s'il y a eu une erreur pour ce fichier
    if ($_FILES['documents']['error'][$i] !== UPLOAD_ERR_OK) {
        continue;
    }
    
    $originalName = basename($_FILES['documents']['name'][$i]);
    $tmpName = $_FILES['documents']['tmp_name'][$i];
    
    // Générer un nom unique si un fichier du même nom existe déjà
    $targetPath = $uploadDir . $originalName;
    if (file_exists($targetPath)) {
        $fileInfo = pathinfo($originalName);
        $uniqueSuffix = '_' . time();  // ou uniqid()
        $newName = $fileInfo['filename'] . $uniqueSuffix . '.' . $fileInfo['extension'];
        $targetPath = $uploadDir . $newName;
    }
    
    // Déplacer le fichier
    if (is_uploaded_file($tmpName) && move_uploaded_file($tmpName, $targetPath)) {
        $uploadedFiles[] = basename($targetPath);
    }
}

if (!empty($uploadedFiles)) {
    $_SESSION['upload_success'] = count($uploadedFiles) . ' fichier(s) uploadé(s)';
} else {
    $_SESSION['upload_error'] = 'Aucun fichier n\'a pu être uploadé';
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
?>
