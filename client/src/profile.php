<?php
session_start();
require_once __DIR__ . '/config/database.php';

$basePath = '/projetannuaire/client'; // TOUT EN MINUSCULES
$uploadDirAbsolute = $_SERVER['DOCUMENT_ROOT'] . $basePath . '/src/uploads/avatars/';
$uploadDirRelative = $basePath . '/src/uploads/avatars/';
$defaultAvatar = $basePath . '/src/assets/images/profile-icon.png';


// Vérification de session
if (!isset($_SESSION['user'])) {
    header('Location: /projetannuaire/client/src/connexion.php');
    exit;
}

// Traitement de l'upload de photo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    if (array_key_exists($_FILES['avatar']['type'], $allowedTypes) && 
        $_FILES['avatar']['size'] <= $maxSize) {
        
        // Chemin absolu corrigé
        $uploadDir = $uploadDirAbsolute;
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Suppression ancien avatar
        if (!empty($_SESSION['user']['avatar_path'])) {
            $oldPath = '/projetannuaire/client' . $_SESSION['user']['avatar_path'];
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }
        
        // Nouveau fichier
        $extension = $allowedTypes[$_FILES['avatar']['type']];
        $filename = 'avatar_' . $_SESSION['user']['id'] . '_' . time() . '.' . $extension;
        $destination = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
            $relativePath = $uploadDirRelative . $filename;

            
            // Mise à jour BDD
            $stmt = $pdo->prepare("UPDATE users SET avatar_path = ? WHERE id = ?");
            $stmt->execute([$relativePath, $_SESSION['user']['id']]);
            
            // Mise à jour session
            $_SESSION['user']['avatar_path'] = $relativePath;
            
            // Rechargement pour afficher la nouvelle image
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = "Erreur lors de l'enregistrement du fichier";
        }
    } else {
        $error = "Format de fichier invalide ou taille trop importante (max 2MB)";
    }
}

// Récupération des données utilisateur
$user = $_SESSION['user'];
try {
    $stmt = $pdo->prepare("SELECT u.*, s.nom as service_nom 
                          FROM users u 
                          LEFT JOIN services s ON u.service_id = s.id 
                          WHERE u.id = ?");
    $stmt->execute([$user['id']]);
    $dbUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($dbUser) {
        $user = array_merge($dbUser, $user);
        $user['service'] = $dbUser['service_nom'] ?? $user['service'] ?? '';
    }
} catch (PDOException $e) {
    error_log("Erreur DB: " . $e->getMessage());
}

$avatarPath = !empty($user['avatar_path']) ? 
    $user['avatar_path'] : 
    $defaultAvatar;




?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil | Trombinoscope</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1>Profil de <?= htmlspecialchars($user['prenom'].' '.$user['nom']) ?></h1>
            <button onclick="window.location.href='pageaccueil.php'" class="top-button">← Retour</button>
        </div>

        <div class="profile-content">
            <form method="POST" enctype="multipart/form-data" class="profile-info">
                <!-- Section Avatar simplifiée -->
                <div class="profile-avatar">
    <label for="avatar-upload" class="avatar-wrapper">
        <img src="<?= $avatarPath ?>" alt="" id="avatar-preview">
        <div class="avatar-overlay">
            <i class="fas fa-camera"></i>
        </div>
    </label>
    <input type="file" id="avatar-upload" name="avatar" accept="image/*" style="display: none;">
</div>


                <!-- Détails du profil -->
                <div class="profile-details">
                    <div class="info-item">
                        <span class="info-label">Nom complet:</span>
                        <span class="info-value"><?= htmlspecialchars($user['prenom'].' '.$user['nom']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Email professionnel:</span>
                        <span class="info-value"><?= !empty($user['email_professionnel']) ? htmlspecialchars($user['email_professionnel']) : 'Non renseigné' ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Téléphone:</span>
                        <span class="info-value"><?= !empty($user['telephone']) ? htmlspecialchars($user['telephone']) : 'Non renseigné' ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Service:</span>
                        <span class="info-value"><?= !empty($user['service']) ? htmlspecialchars($user['service']) : (!empty($user['description']) ? htmlspecialchars($user['description']) : 'Non spécifié') ?></span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="profile-actions">
                    <a href="/projetannuaire/client/src/changemdpprofil.php" class="action-button">
                        <i class="fas fa-key"></i> Changer mot de passe
                    </a>
                    <a href="/projetannuaire/client/src/deconnexion.php" class="action-button logout">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Gestion automatique de l'upload au changement de fichier
    document.getElementById('avatar-upload').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Vérifications
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type) || file.size > 2 * 1024 * 1024) {
                alert('Veuillez choisir une image valide (JPEG/PNG/GIF, max 2MB)');
                return;
            }
            
            // Soumission automatique du formulaire
            e.target.closest('form').submit();
        }
    });
    </script>
</body>
</html>