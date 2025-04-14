<?php
require_once __DIR__ . '/config/database.php';
session_start();
$default_return = '/projetannuaire/client/src/membreglobal.php';
$return_url = $_SESSION['origin_page']['url'] ?? $default_return;

// Nettoyage pour éviter les boucles
if (basename($return_url) === 'profilutilisateur.php') {
    $return_url = $default_return;
}



if (!isset($_GET['id'])) {
    header('Location: membreglobal.php');
    exit;
}

$userId = $_GET['id'];

try {

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        die("Utilisateur non trouvé");
    }
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$user['service_id']]);
    $service = $stmt->fetch();

} catch (PDOException $e) {
    die("Erreur de base de données: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/profilutilisateur.css">
    <script src="/projetannuaire/client/script/profilutilisateur.js" defer></script>
</head> 

<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1>Profil de <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h1>
            <a href="<?= htmlspecialchars($return_url) ?>" class="back-button">← Retour</a>
            
        </div>

        <div class="profile-content">
            <div class="profile-info">
                <div class="profile-avatar">   
                    <div class="avatar-preview">
                    <img src="/projetannuaire/client/src/assets/images/default-avatar.png" class="profile-avatar-img"
                    data-user-id="<?= htmlspecialchars($userId) ?>"></div>
            
                    <div class="avatar-upload">
                        <label for="profile-avatar-input" class="upload-label">
                            <span class="upload-text">Sélectionner une photo</span><br>
                            <input type="file" id="profile-avatar-input" class="profile-avatar-input" accept="image/*" style="display: none;">
                        </label>
                        <span class="profile-file-name">Aucun fichier sélectionné</span>
                    </div>
                </div>

                <div class="profile-details">
                    <h2>Informations personnelles</h2>
                    <p><strong>Nom complet:</strong> <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></p>
                    <p><strong>Email professionnel:</strong> <?= htmlspecialchars($user['email_professionnel'] ?? 'Non renseigné') ?></p>
                    <p><strong>Téléphone:</strong> <?= htmlspecialchars($user['telephone'] ?? 'Non renseigné') ?></p>
                    <p><strong>Service:</strong> <?= htmlspecialchars($service['nom'] ?? 'Non spécifié') ?></p>
                    <p><strong>Rôle:</strong> <?= htmlspecialchars($user['role'] ?? 'Non spécifié') ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>