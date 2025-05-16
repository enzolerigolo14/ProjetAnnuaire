<?php
require_once __DIR__ . '/config/ldap_auth.php';
require_once __DIR__ . '/config/database.php';

session_start();

// Redirige si non connecté
if (!isset($_SESSION['user'])) {
    header('Location: /projetannuaire/client/src/pageaccueil.php');
    exit;
}

// Complète l'email en session si nécessaire
if (!isset($_SESSION['user']['email']) && isset($_SESSION['user']['id'])) {
    $stmt = $pdo->prepare("SELECT email_professionnel FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    if ($result = $stmt->fetch()) {
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

// Recherche dans AD ou BDD
if ($source === 'db') {
    $stmt = $pdo->prepare("
        SELECT users.*, services.nom as service_name 
        FROM users 
        LEFT JOIN services ON users.service_id = services.id 
        WHERE email_professionnel = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $user = recupererUtilisateurParEmail($email);
    
    // Vérifie si l'utilisateur existe en BDD
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email_professionnel = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header("Location: profilutilisateur.php?email=".urlencode($email)."&source=db&from=".($_GET['from'] ?? 'global'));
        exit;
    }
}

if (!$user) {
    die("Utilisateur non trouvé.");
}

// Services pour le select
$services = $pdo->query("SELECT id, nom FROM services")->fetchAll(PDO::FETCH_ASSOC);

// Détermine la page de retour
$return_url = match($_GET['from'] ?? 'global') {
    'services' => $_GET['service_id'] ? "membresservices.php?id=".$_GET['service_id'] : "pageaccueil.php",
    'global' => "membreglobal.php",
    default => "pageaccueil.php"
};

// Données de l'utilisateur
$nomComplet = $source === 'db' 
    ? htmlspecialchars($user['prenom'].' '.$user['nom'])
    : htmlspecialchars($user['givenname'][0].' '.$user['sn'][0]);

$emailAffiche = $source === 'db'
    ? htmlspecialchars($user['email_professionnel'] ?? 'Non renseigné')
    : htmlspecialchars($user['mail'][0] ?? 'Non renseigné');

$telephone = $source === 'db'
    ? htmlspecialchars($user['telephone'] ?? 'Non renseigné')
    : htmlspecialchars($user['telephonenumber'][0] ?? 'Non renseigné');

$service = $source === 'db'
    ? htmlspecialchars($user['service_name'] ?? 'Non spécifié')
    : htmlspecialchars($user['description'][0] ?? 'Non spécifié');

// Droits d'édition
$isEditable = in_array($_SESSION['user']['role'], ['SVC-INFORMATIQUE', 'ADMIN-INTRA']) && $source === 'db';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil de <?= $nomComplet ?></title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/profilutilisateur.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script>
        window.servicesData = <?= json_encode($services) ?>;
        window.currentEmail = "<?= $email ?>";
        window.servicesData = <?= json_encode($services) ?>;

    </script>
    <script src="/projetannuaire/client/script/profilutilisateur.js" defer></script>
</head>
<body data-user-id="<?= htmlspecialchars($user['id'] ?? '') ?>">
    <div class="profile-container">
        <div class="profile-header">
            <h1>Profil de <?= $nomComplet ?></h1>
            <a href="<?= htmlspecialchars($return_url) ?>" class="back-button">← Retour</a>
        </div>

        <div class="profile-content">
            <div class="profile-info">
                <div class="profile-avatar">
                    <img src="assets/images/search-icon.png" class="profile-avatar-img" 
                         data-user-id="<?= htmlspecialchars($user['id'] ?? '') ?>">
                </div>
                <div class="profile-details">
                    <h2>Informations personnelles</h2>
                    
                    <p data-field="nom_complet">
                        <strong>Nom complet:</strong>
                        <span class="editable-value"><?= $nomComplet ?></span>
                        <?= $isEditable ? '<i class="fas fa-pencil-alt edit-icon"></i>' : '' ?>
                    </p>

                    <p data-field="email">
                        <strong>Email:</strong>
                        <span class="editable-value"><?= $emailAffiche ?></span>
                        
                    </p>

                    <p data-field="telephone">
                        <strong>Téléphone:</strong>
                        <span class="editable-value"><?= $telephone ?></span>
                        <?= $isEditable ? '<i class="fas fa-pencil-alt edit-icon"></i>' : '' ?>
                    </p>

                    <p data-field="service">
                        <strong>Service:</strong>
                        <span class="editable-value"><?= $service ?></span>
                        <?= $isEditable ? '<i class="fas fa-pencil-alt edit-icon"></i>' : '' ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>