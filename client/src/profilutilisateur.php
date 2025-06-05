<?php
require_once __DIR__ . '/config/ldap_auth.php';
require_once __DIR__ . '/config/database.php';

session_start();

function refreshSessionUserData($pdo) {
    if (isset($_SESSION['user']['id'])) {
        $stmt = $pdo->prepare("SELECT email_professionnel, nom, prenom, role, service_id, description FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user']['id']]);
        if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $_SESSION['user'] = array_merge($_SESSION['user'], [
                'email' => $user['email_professionnel'],
                'nom' => $user['nom'],
                'prenom' => $user['prenom'],
                'role' => $user['role'],
                'service_id' => $user['service_id'],
                'service' => $user['description']
            ]);
        }
    }
}

$rolesAutorises = [
    'membre' => 'Membre standard',
    'SVC-INFORMATIQUE' => 'Service Informatique',
    'ADMIN-INTRA' => 'Administrateur Intranet',
    'ADMIN-RH' => 'Administrateur RH'
    // Ajoutez d'autres rôles si nécessaire
];

function safeHtmlSpecialChars($value, $default = 'Non renseigné') {
    if (is_array($value)) {
        $value = $value[0] ?? $default;
    }
    return htmlspecialchars($value ?? $default, ENT_QUOTES, 'UTF-8');
}

$stmt = $pdo->query("SELECT id, nom FROM services ORDER BY nom");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Redirige si non connecté
if (!isset($_SESSION['user'])) {
    header('Location: /projetannuaire/client/src/pageaccueil.php');
    exit;
}

if (!isset($_SESSION['user']['email']) && isset($_SESSION['user']['id'])) {
    $stmt = $pdo->prepare("SELECT email_professionnel FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    if ($result = $stmt->fetch()) {
        $_SESSION['user']['email'] = $result['email_professionnel'];
    }
}

if (!isset($_GET['email'])) {
    header('Location: /projetannuaire/client/src/pageaccueil.php');
    exit;
}


$email = urldecode($_GET['email']);
$source = $_GET['source'] ?? 'ad';


$stmt = $pdo->prepare("SELECT id FROM users WHERE email_professionnel = ?");
$stmt->execute([$email]);
$userExistsInDB = (bool)$stmt->fetch();


if ($userExistsInDB) {
    $source = 'db';

    if ($_GET['source'] !== 'db') {
        header("Location: profilutilisateur.php?email=".urlencode($email)."&source=db&from=".($_GET['from'] ?? 'global'));
        exit;
    }
}
// Vérifiez si c'est un compte local
$isLocalAccount = $source === 'db' && ($user['ldap_user'] ?? 0) == 0;

// Seuls les admins peuvent voir les mots de passe des comptes locaux
$canViewPassword = $isLocalAccount && in_array($_SESSION['user']['role'], ['SVC-INFORMATIQUE', 'ADMIN-INTRA', 'ADMIN-RH'])
                && $source === 'db' 
                && empty($user['ldap_user']);
$csvFile = __DIR__ . '/../../data/LISIEUX_MAIRIE.csv';
if (file_exists($csvFile)) {
    if (($handle = fopen($csvFile, 'r')) !== FALSE) {
        fgetcsv($handle, 1000, ";"); // Ignore l'en-tête
        
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $nom = trim($data[0], '="');
            $prenom = trim($data[1], '="');
            $numInterne = trim($data[4], '="');
            $numPublic = trim($data[6], '="');
            
            // Création d'une clé email standard (première lettre du prénom + nom)
            $emailStandard = strtolower($prenom[0] . preg_replace('/[^a-z]/', '', strtolower($nom))) . '@ville-lisieux.fr';
            
            $telephoneData[$emailStandard] = [
                'interne' => $numInterne,
                'public' => $numPublic
            ];
        }
        fclose($handle);
    }
}

if ($source === 'db') {
$stmt = $pdo->prepare("
    SELECT users.*, services.nom AS service_name 
    FROM users 
    LEFT JOIN services ON users.service_id = services.id 
    WHERE email_professionnel = ?
");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Priorité à l'avatar de profile.php s'il existe
    if (!empty($user['profile_avatar'])) {
        $user['avatar_path'] = $user['profile_avatar'];
    }

    // Construire la clé "prenom nom" selon la source
$nomCompletForSearch = $source === 'db'
    ? strtolower($user['prenom'] . ' ' . $user['nom'])
    : strtolower($user['givenname'][0] . ' ' . $user['sn'][0]);

$userPhoneData = $telephoneData[$nomCompletForSearch] ?? [
    'interne' => '',
    'public' => ''
];

} else {
    $user = recupererUtilisateurParEmail($email);
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email_professionnel = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header("Location: profilutilisateur.php?email=".urlencode($email)."&source=db&from=".($_GET['from'] ?? 'global'));
        exit;
    }
    
    // Stockez les données AD pour utilisation ultérieure
    $_SESSION['ad_user_data'] = $user;
}
if (!$user) {
    die("Utilisateur non trouvé.");
}

$return_url = match($_GET['from'] ?? 'global') {
    'services' => "membresservices.php?id=".($_GET['service_id'] ?? $_SESSION['last_service_viewed'] ?? ''),
    'global' => "membreglobal.php",
    default => "pageaccueil.php"
};

$nomComplet = $source === 'db' 
    ? htmlspecialchars($user['prenom'].' '.$user['nom'])
    : htmlspecialchars($user['givenname'][0].' '.$user['sn'][0]);

$emailAffiche = $source === 'db'
    ? htmlspecialchars($user['email_professionnel'] ?? 'Non renseigné')
    : htmlspecialchars($user['mail'][0] ?? 'Non renseigné');

$nomCompletForSearch = '';
if ($source === 'db') {
    $nomCompletForSearch = strtolower($user['prenom'] . ' ' . $user['nom']);
} elseif ($source === 'ad') {
    $nomCompletForSearch = strtolower($user['givenname'][0] . ' ' . $user['sn'][0]);
}

$emailKey = strtolower($email);
$userPhoneData = $telephoneData[$emailKey] ?? ['interne' => '', 'public' => ''];

// Attribution des numéros
$telephonePro = [
    'public' => htmlspecialchars($userPhoneData['public'] ?: ''),
    'interne' => htmlspecialchars($userPhoneData['interne'] ?: '')
];



$telephonePerso = $source === 'db'
    ? htmlspecialchars($user['telephone_perso'] ?? '')
    : '';

$service = htmlspecialchars($user['service_name'] ?? 'Non attribué');


$nomService = $source === 'db' 
    ? htmlspecialchars($user['service_name'] ?? '') 
    : htmlspecialchars($user['description'][0] ?? '');

$role = $source === 'db' 
    ? htmlspecialchars($user['role'] ?? 'Non spécifié') 
    : htmlspecialchars($user['description'][0] ?? 'Non spécifié');
    
$isEditable = in_array($_SESSION['user']['role'], ['SVC-INFORMATIQUE', 'ADMIN-INTRA','ADMIN-RH']) 
              && $source === 'db' 
              && $userExistsInDB;

function formatPhoneDisplay($number) {
    if (empty($number)) return 'Non renseigné';
    
    if (strlen($number) === 4) {
        return $number; // Poste interne - pas de formatage
    }
    
    if (strlen($number) === 10) {
        return preg_replace('/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', '$1 $2 $3 $4 $5', $number);
    }
    
    return $number;
}

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
         window.currentEmail = "<?= htmlspecialchars($email, ENT_QUOTES) ?>";
        window.currentEmail = "<?= $email ?>";
        window.servicesHTML = <?= json_encode(
        array_reduce($services, function($carry, $service) {
            return $carry . sprintf('<option value="%d">%s</option>', $service['id'], htmlspecialchars($service['nom']));
        }, '')
    ) ?>;
        
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
                    <img src="<?= 
                        !empty($user['avatar_path']) ? 
                        htmlspecialchars($user['avatar_path']) : 
                        '/projetannuaire/client/src/assets/images/search-icon.png' 
                    ?>" 
                    class="profile-avatar-img" 
                    data-user-id="<?= htmlspecialchars($user['id'] ?? '') ?>"
                    onerror="this.src='/projetannuaire/client/src/assets/images/search-icon.png'">
                    
                    <?php if (isset($_SESSION['user']['role'])) {
                        $role = strtoupper($_SESSION['user']['role']);
                        if ($role === 'SVC-INFORMATIQUE' || $role === 'ADMIN-INTRA' || $role === 'ADMIN-RH') { ?>
                    <form id="avatar-upload-form" enctype="multipart/form-data">
                        <input type="file" id="avatar-upload" name="avatar" accept="image/*" style="display: none;">
                        <button type="button" class="upload-button" onclick="document.getElementById('avatar-upload').click()">
                            Changer la photo
                        </button>
                        <div class="upload-status" id="upload-status"></div>
                    </form>
                    <?php }} ?>
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

                  <div class="phone-section">
    <strong>Téléphone professionnel:</strong>
    <div class="phone-numbers">
        <div class="phone-number" data-field="phone_public">
            <span>Numéro public:</span>
            <span class="editable-value"><?= formatPhoneDisplay($telephonePro['public']) ?></span>
           
        </div>
        <div class="phone-number" data-field="phone_internal">
            <span>Poste interne:</span>
            <span class="editable-value"><?= $telephonePro['interne'] ? htmlspecialchars($telephonePro['interne']) : 'Non renseigné' ?></span>
            
        </div>
    </div>
</div>

                    <?php if ($canViewPassword && $isLocalAccount): ?>
                        <div class="password-section">
                            <strong>Mot de passe:</strong>
                                <div class="password-display">
                                    <input type="password" 
                                    value="<?= htmlspecialchars($user['mot_de_passe'] ?? '') ?>" 
                                    class="password-field" readonly>
                                    <button class="toggle-password" onclick="togglePasswordVisibility(this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            <small class="password-warning">Visible uniquement pour les comptes locaux</small>
                        </div>
                    <?php endif; ?>

                    <p data-field="telephone_perso">
    <strong>Téléphone mobile:</strong>
    <span class="editable-value"><?= htmlspecialchars($user['telephone_perso'] ?? '') ?></span>
    <?= $isEditable ? '<i class="fas fa-pencil-alt edit-icon"></i>' : '' ?>
</p>

                    <p data-field="service_id">
                        <strong>Service:</strong>
                        <span class="editable-value"><?= $service ?></span>
                        <?= $isEditable ? '<i class="fas fa-pencil-alt edit-icon"></i>' : '' ?>
                    </p>

                    <!-- Description (input texte) -->
                    <p data-field="description">
                        <strong>Description de poste:</strong>
                        <span class="editable-value"><?= safeHtmlSpecialChars($user['description'] ?? null) ?></span>
                        <?= $isEditable ? '<i class="fas fa-pencil-alt edit-icon"></i>' : '' ?>
                    </p>

                    <!-- Rôle (select) -->
                    <p data-field="role" data-field-type="select">
    <strong>Rôle:</strong>
    <?php if ($isEditable): ?>
        <select class="editable-select" style="display: none;">
            <?php foreach ($rolesAutorises as $value => $label): ?>
                <option value="<?= htmlspecialchars($value) ?>" <?= ($user['role'] ?? 'membre') === $value ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>
    <span class="editable-value"><?= htmlspecialchars($rolesAutorises[$user['role'] ?? 'membre'] ?? $user['role'] ?? 'membre') ?></span>
    <?= $isEditable ? '<i class="fas fa-pencil-alt edit-icon"></i>' : '' ?>
</p>
                </div>
                <?php if (!$isEditable): ?>
                    <div class="edit-notice">
                        <p>* Pour modifier les informations, veuillez demander à l'utilisateur de se connecter.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>