<?php
session_start();
require_once __DIR__ . '/config/database.php';


if (!isset($_SESSION['user'])) {
    header('Location: /projetannuaire/client/src/connexion.php');
    exit;
}
$userId = $_SESSION['user']['id'];
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$user['service_id']]);
    $services = $stmt->fetch();
    
    if (!$user) {
        die("Utilisateur non trouvé");
    }
} catch (PDOException $e) {
    die("Erreur de base de données: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil | Trombinoscope Ville de Lisieux</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/profile.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    <script src="/projetannuaire/client/script/profile.js" defer></script>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1>Profil de <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h1>
            <a href="/projetannuaire/client/src/pageaccueil.php" class="back-button">← Retour</a>
        </div>

        <div class="profile-content">
            <div class="profile-info">
                <div class="profile-avatar">   
                    <div class="avatar-preview">
                        <img src="/projetannuaire/client/src/assets/images/default-avatar.png" alt="Photo de profil" id="avatar-preview">
                    </div>
                    <div class="avatar-upload">
                        <label for="avatar-upload" class="upload-label">
                            <span class="upload-text">Sélectionner une photo</span><br>
                            <input type="file" id="avatar-upload" name="avatar-upload" accept="image/*" style="display: none;">
                        </label>
                        <span id="file-name">Aucun fichier sélectionné</span>
                    </div>
                </div>
                
                <div class="profile-details">
                    <h2>Informations personnelles</h2>
                    <p><strong>Nom complet:</strong> <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></p>
                    <p><strong>Email professionnel:</strong> <?= htmlspecialchars($user['email_professionnel']?? 'Non renseigné')  ?></p>
                    <p><strong>Téléphone:</strong> <?= htmlspecialchars($user['telephone'] ?? 'Non renseigné') ?></p>
                    <p><strong>Service:</strong> <?= htmlspecialchars($services['nom'] ?? 'Non spécifié') ?></p>
                    <p><strong>Role:</strong> <?= htmlspecialchars($user['role'] ?? 'Non spécifié') ?></p>
                </div>
            </div>

            <div class="profile-actions">
                <!--<h3>Actions</h3>-->
                <!--<a href="/projetannuaire/client/src/modifier-profil.php" class="action-button">Modifier le profil</a>-->
                <a href="/projetannuaire/client/src/changemdp.php" class="action-button">Changer le mot de passe</a>
                <a href="/projetannuaire/client/src/deconnexion.php" class="action-button logout">Déconnexion</a>
            </div>
        </div>
    </div>
    <footer>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</footer>
</body>
</html>