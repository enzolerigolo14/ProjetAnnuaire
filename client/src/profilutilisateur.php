<?php
require_once __DIR__ . '/config/ldap_auth.php';
require_once __DIR__ . '/config/database.php';

session_start();

// Redirige vers l'accueil si personne n'est connecté
if (!isset($_SESSION['user'])) {
    header('Location: /projetannuaire/client/src/pageaccueil.php');
    exit;
}

// Complète l'email s'il n'existe pas en session
if (!isset($_SESSION['user']['email']) && isset($_SESSION['user']['id'])) {
    $userId = $_SESSION['user']['id'];
    $stmt = $pdo->prepare("SELECT email_professionnel FROM users WHERE id = :id");
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result && !empty($result['email_professionnel'])) {
        $_SESSION['user']['email'] = $result['email_professionnel'];
    }
}

// Vérifie que l'email est passé
if (!isset($_GET['email'])) {
    header('Location: /projetannuaire/client/src/pageaccueil.php');
    exit;
}

$email = urldecode($_GET['email']);
$source = $_GET['source'] ?? 'ad';
$user = null;

// Recherche dans AD ou dans la BDD selon la source
if ($source === 'db') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email_professionnel = :email_pro");
    $stmt->bindParam(':email_pro', $email); 
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $user = recupererUtilisateurParEmail($email);
}

// Redirige si l'utilisateur n'existe pas
if (!$user) {
    echo "Utilisateur non trouvé.";
    exit;
}

// Détermine la page de retour
$from = $_GET['from'] ?? 'global';
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

// Fonction pour vérifier l'appartenance à un groupe AD
function estDansGroupe($user, $nomGroupe) {
    if (!isset($user['memberof'])) return false;
    foreach ($user['memberof'] as $groupe) {
        if (stripos($groupe, $nomGroupe) !== false) return true;
    }
    return false;
}

// Variables communes
$nomComplet = $source === 'db'
    ? htmlspecialchars($user['prenom'] . ' ' . $user['nom'])
    : htmlspecialchars($user['givenname'][0] . ' ' . $user['sn'][0]);

$emailAffiche = $source === 'db'
    ? htmlspecialchars($user['email_professionnel'] ?? 'Non renseigné')
    : htmlspecialchars($user['mail'][0] ?? 'Non renseigné');

$telephone = $source === 'db'
    ? htmlspecialchars($user['telephone'] ?? 'Non renseigné')
    : htmlspecialchars($user['telephonenumber'][0] ?? 'Non renseigné');

$service = $source === 'db'
    ? htmlspecialchars($user['role'] ?? 'Non spécifié')
    : htmlspecialchars($user['description'][0] ?? 'Non spécifié');

$isEditable = $source === 'ad' && estDansGroupe($user, 'Utilisa. du domaine');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil de <?= $nomComplet ?></title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/profilutilisateur.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    <script src="/projetannuaire/client/script/profilutilisateur.js" defer></script>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1>Profil de <?= $nomComplet ?></h1>
            <a href="membresservices.php" class="back-button">← Retour</a>
        </div>

        <div class="profile-content">
            <div class="profile-info">
                <div class="profile-avatar">
                    <div class="avatar-preview">
<!--faire en sorte que la rh puisse mettre des photos-->
                        <img src="assets/images/search-icon.png" class="profile-avatar-img">
                    </div>
                </div>
                <div class="profile-details">
                    <h2>Informations personnelles</h2>
                    <p>
                        <strong>Nom complet:</strong>
                        <span class="editable-value"><?= $nomComplet ?></span>
                        <?php if ($isEditable): ?>
                            <span class="edit-icon-wrapper">
                                <i class="fas fa-pencil-alt edit-icon"></i>
                            </span>
                        <?php endif; ?>
                    </p>
                    <p><strong>Email professionnel:</strong> <span class="editable-value"><?= $emailAffiche ?></span></p>
                    <p><strong>Téléphone:</strong> <span class="editable-value"><?= $telephone ?></span></p>
                    <p><strong>Service:</strong> <span class="editable-value"><?= $service ?></span></p>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </footer>
</body>
</html>
