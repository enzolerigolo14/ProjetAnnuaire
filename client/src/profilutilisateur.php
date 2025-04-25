<?php
require_once __DIR__ . '/config/ldap_auth.php';
session_start();

if (!isset($_GET['email'])) {
    header('Location: /projetannuaire/client/src/pageaccueil.php');
    exit;
}

$email = urldecode($_GET['email']);
$user = recupererUtilisateurParEmail($email);

if (!$user) {
    echo "Utilisateur non trouvé.";
    exit;
}

// Gestion de l'URL de retour
$from = $_GET['from'] ?? 'global'; // Par défaut, on revient à membreglobal
switch ($from) {
    case 'services':
        $service_id = $_GET['service_id'] ?? null;
        $return_url = $service_id ? "membresservices.php?id=" . $service_id : "pageaccueil.php";
        break;
    case 'global':
        $return_url = "membreglobal.php";
        break;
    default:
        $return_url = "pageaccueil.php";
}

function estDansGroupe($user, $nomGroupe) {
    if (!isset($user['memberof'])) return false;

    foreach ($user['memberof'] as $groupe) {
        // Normalise la chaîne pour comparaison
        if (stripos($groupe, $nomGroupe) !== false) {
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
    <title>Profil de <?= htmlspecialchars($user['givenname'][0] . ' ' . $user['sn'][0]) ?></title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/profilutilisateur.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    <script src="/projetannuaire/client/script/profilutilisateur.js" defer></script>
</head>

<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1>Profil de <?= htmlspecialchars($user['givenname'][0] . ' ' . $user['sn'][0]) ?></h1>
            <a href="<?= htmlspecialchars($return_url) ?>" class="back-button">← Retour</a>
        </div>

        <div class="profile-content">
            <div class="profile-info">
                <div class="profile-avatar">   
                    <div class="avatar-preview">
                        <img src="/projetannuaire/client/src/assets/images/default-avatar.png" class="profile-avatar-img">
                    </div>

                </div>

                <div class="profile-details">
                    <h2>Informations personnelles</h2>
                    <p>
    <strong>Nom complet:</strong>
    <span class="editable-value">
        <?= htmlspecialchars($user['givenname'][0] . ' ' . $user['sn'][0]) ?>
    </span>
    <?php if (estDansGroupe($user, 'Utilisa. du domaine')): ?>
        <span class="edit-icon-wrapper">
            <i class="fas fa-pencil-alt edit-icon"></i>
        </span>
    <?php endif; ?>
</p>


                    <p>
                        <strong>Email professionnel:</strong>
                        <span class="editable-value"><?= htmlspecialchars($user['mail'][0] ?? 'Non renseigné') ?></span>
                    </p>

                    <p>
                        <strong>Téléphone:</strong>
                        <span class="editable-value"><?= htmlspecialchars($user['telephonenumber'][0] ?? 'Non renseigné') ?></span>
                    </p>

                    <p>
                        <strong>Service:</strong>
                        <span class="editable-value"><?= htmlspecialchars($user['description'][0] ?? 'Non spécifié') ?></span>
                    </p>

                    
                </div>
            </div>
        </div>
    </div>

    <footer>
        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </footer>
</body>
</html>
