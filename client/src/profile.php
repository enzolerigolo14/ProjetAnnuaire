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

// Avant le HTML
$stmt = $pdo->prepare("SELECT id, nom FROM services");
$stmt->execute();
$allServices = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil | Trombinoscope Ville de Lisieux</title>
    
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/profile.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="/projetannuaire/client/script/profile.js" defer></script>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1>Profil de <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h1>
            <button class="top-button" onclick="window.location.href='pageaccueil.php'"> ← Retour</button>
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
                    <p data-field="nom_complet" data-userid="<?= $user['id'] ?>">
                        <strong>Nom complet:</strong>
                        <span class="editable-value"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></span>
                        <?php if ($_SESSION['user']['role'] === 'super_admin' || $_SESSION['user']['role'] === 'admin2'): ?>
                            <i class="fas fa-pencil-alt edit-icon"></i>
                        <?php endif; ?>
                    </p>

                    <p data-field="email_professionnel" data-userid="<?= $user['id'] ?>">
                        <strong>Email professionnel:</strong>
                        <span class="editable-value"><?= htmlspecialchars($user['email_professionnel'] ?? 'Non renseigné') ?></span>
                        <?php if ($_SESSION['user']['role'] === 'super_admin' || $_SESSION['user']['role'] === 'admin2'): ?>
                            <i class="fas fa-pencil-alt edit-icon"></i>
                        <?php endif; ?>
                    </p>

                    <p data-field="telephone" data-userid="<?= $user['id'] ?>">
                        <strong>Téléphone:</strong>
                        <span class="editable-value"><?= htmlspecialchars($user['telephone'] ?? 'Non renseigné') ?></span>
                        <?php if ($_SESSION['user']['role'] === 'super_admin' || $_SESSION['user']['role'] === 'admin2'): ?>
                            <i class="fas fa-pencil-alt edit-icon"></i>
                        <?php endif; ?>
                    </p>

                    <p data-field="service_id" data-userid="<?= $user['id'] ?>">
                        <strong>Service:</strong>
                        <span class="editable-value" data-serviceid="<?= $services['id'] ?? '' ?>">
                        <?= htmlspecialchars($services['nom'] ?? 'Non spécifié') ?></span>
                        <?php if ($_SESSION['user']['role'] === 'super_admin' || $_SESSION['user']['role'] === 'admin2'): ?>
                            <i class="fas fa-pencil-alt edit-icon"></i>
                        <?php endif; ?>
                    </p>

                    <p data-field="role" data-userid="<?= $user['id'] ?>">
                        <strong>Role:</strong>
                        <span class="editable-value"><?= htmlspecialchars($user['role'] ?? 'Non spécifié') ?></span>
                        <?php if ($_SESSION['user']['role'] === 'super_admin' || $_SESSION['user']['role'] === 'admin2'): ?>
                            <i class="fas fa-pencil-alt edit-icon"></i>
                        <?php endif; ?>
                    </p>
                </div>

                <div class="profile-actions">
                <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                    <a href="/projetannuaire/client/src/changemdp.php" class="action-button">Changer le mot de passe</a>
                        <?php endif; ?>
                    <a href="/projetannuaire/client/src/deconnexion.php" class="action-button logout">Déconnexion</a>
                </div>
            </div>
        </div>
    </div>
    <div id="services-data" data-services='<?= json_encode($allServices) ?>'></div>
    <footer>
        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </footer>
    
</body>
</html>
