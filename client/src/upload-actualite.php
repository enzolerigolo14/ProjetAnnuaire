<?php
session_start();

// Debugging (à désactiver en production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Vérifier les permissions
if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], ['super_admin', 'admin', 'admin2', 'membre'])) {
    die('Accès refusé');
}

require_once __DIR__ . '/config/database.php';

// Vérifier les données du formulaire
if (!isset($_POST['titre']) || !isset($_POST['description']) || empty($_POST['titre'])) {
    $_SESSION['upload_error'] = 'Titre et description requis';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Récupérer le service_id
$service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
if ($service_id <= 0) {
    $_SESSION['upload_error'] = 'Service invalide';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Dossier d'upload pour les PDF d'actualités
$uploadDir = __DIR__ . '/uploads/actualites/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        $_SESSION['upload_error'] = "Impossible de créer le dossier d'upload";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

// Traitement du PDF s'il est uploadé
$pdfPath = null;
if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
    $originalName = basename($_FILES['pdf_file']['name']);
    $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    
    // Vérifier que c'est bien un PDF
    if ($fileExt !== 'pdf') {
        $_SESSION['upload_error'] = 'Seuls les fichiers PDF sont autorisés';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Générer un nom unique
    $newName = 'actu_' . uniqid() . '.pdf';
    $targetPath = $uploadDir . $newName;
    
    if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $targetPath)) {
        $pdfPath = '/uploads/actualites/' . $newName;
    } else {
        $_SESSION['upload_error'] = 'Erreur lors de l\'upload du PDF';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

// Insertion en base de données
try {
    $stmt = $pdo->prepare("INSERT INTO actualites (titre, description, image, pdf_path, service_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['titre'],
        $_POST['description'],
        $_POST['image'] ?? null,
        $pdfPath,
        $service_id
    ]);
    
    $_SESSION['upload_success'] = 'Actualité créée avec succès';
    header('Location: actualite.php?id=' . $service_id);
    exit;
    
} catch (PDOException $e) {
    // Supprimer le PDF uploadé si l'insertion a échoué
    if ($pdfPath && file_exists(__DIR__ . $pdfPath)) {
        unlink(__DIR__ . $pdfPath);
    }
    
    $_SESSION['upload_error'] = 'Erreur base de données: ' . $e->getMessage();
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}