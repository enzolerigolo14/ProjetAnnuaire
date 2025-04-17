<?php
require_once __DIR__ . '/config/database.php';
session_start();
$default_return = '/projetannuaire/client/src/pageaccueil.php';
if (isset($_GET['from'])) {
    switch ($_GET['from']) {
        case 'services':
            $service_id = $_GET['service_id'] ?? $user['service_id'] ?? null;
            $return_url = $service_id ? "membresservices.php?id=".$service_id : $default_return;
            break;
            
        case 'global':
            $return_url = "membreglobal.php";
            break;
        case 'search':
            $return_url = "pageaccueil.php";
            break;
            
        default:
            $return_url = $default_return;
    }
} 
elseif (isset($_SESSION['origin_page']['url'])) {
    $return_url = $_SESSION['origin_page']['url'];
    if (strpos($return_url, 'membresservices.php') !== false && !strpos($return_url, 'id=')) {
        $service_id = $_SESSION['origin_page']['service_id'] ?? $user['service_id'] ?? null;
        if ($service_id) {
            $return_url = "membresservices.php?id=".$service_id;
        }
    }
} 

else {
    $return_url = $default_return;
}
if (basename($return_url) === 'profilutilisateur.php') {
    $return_url = $default_return;
}
if (!isset($_GET['id'])) {
    header('Location: ' . $default_return);
    exit;
}
$userId = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        die("Utilisateur non trouvé");
    }
        if (!isset($service) && isset($user['service_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([$user['service_id']]);
        $service = $stmt->fetch();
    }
} catch (PDOException $e) {
    die("Erreur de base de données: " . $e->getMessage());
}

if (!isset($_GET['from'])) {
    $_SESSION['origin_page'] = [
        'url' => $return_url,
        'service_id' => $user['service_id'] ?? null
    ];
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
    <title>Profil de <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/profilutilisateur.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
            </div>
        </div>
    </div>
    <div id="services-data" data-services='<?= json_encode($allServices) ?>'></div>
    <footer>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</footer>
</body>
</html>