<?php
session_start();
require_once __DIR__ . '/config/ldap_auth.php';

if (!isset($_SESSION['user'])) {
    header('Location: /projetannuaire/client/src/connexion.php');
    exit;
}

$user = $_SESSION['user'];

function estDansGroupe($user, $nomGroupe) {
    if (!isset($user['memberof'])) return false;
    foreach ($user['memberof'] as $groupe) {
        // Vérifie uniquement le CN du groupe, sans prendre en compte l'OU et le DC
        if (stripos($groupe, "CN=$nomGroupe") !== false) {
            return true;
        }
    }
    return false;
}

if (estDansGroupe($user, 'SVC-INFORMATIQUE')) {
    echo "Dans le groupe SVC-INFORMATIQUE<br>";
} else {
    echo "Pas dans le groupe SVC-INFORMATIQUE<br>";
}

if (estDansGroupe($user, 'Utilisa. du domaine')) {
    echo "Dans le groupe Utilisa. du domaine<br>";
} else {
    echo "Pas dans le groupe Utilisa. du domaine<br>";
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
                    <p>
                        <strong>Nom complet:</strong>
                        <span><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></span>
                    </p>

                    <p>
                        <strong>Email professionnel:</strong>
                        <span><?= htmlspecialchars($user['email'] ?? 'Non renseigné') ?></span>
                    </p>

                    <p>
                        <strong>Téléphone:</strong>
                        <span><?= htmlspecialchars($user['telephone'] ?? 'Non renseigné') ?></span>
                    </p>

                    <p>
                        <strong>Service:</strong>
                        <span><?= htmlspecialchars($user['description'] ?? 'Non spécifié') ?></span>
                    </p>

                </div>

                <div class="profile-actions">
            
<!-- Manque la gerance du bouton pour qu'il s'affiche seulement avec les droits admins SVC-INFORMATIQUE(super admin ) ADMIN-INTRA(admin service) -->
                        <a href="/projetannuaire/client/src/changemdp.php" class="action-button">Changer le mot de passe</a>
    
                    <a href="/projetannuaire/client/src/deconnexion.php" class="action-button logout">Déconnexion</a>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </footer>
</body>
</html>
