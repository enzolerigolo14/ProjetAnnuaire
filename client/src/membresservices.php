<?php
session_start();
require_once __DIR__ . '/config/ldap_auth.php';
require_once __DIR__ . '/config/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Validation de l'ID du service
$service_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($service_id === 0) {
    die("<div class='error'>ID de service invalide ou non spécifié</div>");
}

// Récupération du service
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();
if (!$service) {
    die("<div class='error'>Service avec l'ID $service_id non trouvé</div>");
}

// Récupération des membres déjà dans le service
$membresAD = recupererUtilisateursParServiceAD($service['nom']);
$stmt = $pdo->prepare("SELECT * FROM users WHERE service_id = ?");
$stmt->execute([$service_id]);
$membresBDD = $stmt->fetchAll(PDO::FETCH_ASSOC);
$tousLesMembres = array_merge($membresAD, $membresBDD);

// Récupération de tous les membres AD
$tousLesMembresAD = recupererUtilisateurADListe();

// Récupération de tous les membres BDD non liés à ce service
$stmt = $pdo->prepare("SELECT * FROM users WHERE service_id IS NULL OR service_id != ?");
$stmt->execute([$service_id]);
$tousLesMembresBDD = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Emails déjà dans le service
$stmt = $pdo->prepare("SELECT email_professionnel FROM users WHERE service_id = ? AND email_professionnel IS NOT NULL");
$stmt->execute([$service_id]);
$emailsDansService = array_column($stmt->fetchAll(), 'email_professionnel');

// Filtrage des membres AD déjà dans le service
$membresADDisponibles = array_filter($tousLesMembresAD, function ($membre) use ($emailsDansService) {
    $email = $membre['mail'][0] ?? null;
    return $email && !in_array($email, $emailsDansService);
});

$_SESSION['last_service_viewed'] = $service_id;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['membre'])) {
    $valeur = $_POST['membre'];

    if (str_starts_with($valeur, 'ad_')) {
        $idAD = substr($valeur, 3);
        foreach ($tousLesMembresAD as $membre) {
            if ($membre["id"] == $idAD) {
                $email = $membre["mail"][0] ?? null;
                $prenom = $membre["givenname"][0] ?? '';
                $nom = $membre["sn"][0] ?? '';

                if (!$email) {
                    $message = "Erreur : L'utilisateur AD n'a pas d'email défini";
                    break;
                }

                $stmt = $pdo->prepare("SELECT * FROM users WHERE email_professionnel = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user) {
                    $update = $pdo->prepare("UPDATE users SET service_id = ? WHERE id = ?");
                    if ($update->execute([$service_id, $user['id']])) {
                        
                    } else {
                        
                    }
                } else {
                    $insert = $pdo->prepare("INSERT INTO users (prenom, nom, email_professionnel, service_id) VALUES (?, ?, ?, ?)");
                    if ($insert->execute([$prenom, $nom, $email, $service_id])) {
                        
                    } else {
                        
                    }
                }
                break;
            }
        }
    } elseif (str_starts_with($valeur, 'bdd_')) {
        $idBDD = substr($valeur, 4);
        $update = $pdo->prepare("UPDATE users SET service_id = ? WHERE id = ?");
        if ($update->execute([$service_id, $idBDD])) {
            $message = "Utilisateur BDD associé au service.";
        } else {
            $message = "Erreur lors de l'association de l'utilisateur.";
        }
    }
}
$isAdmin = isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], ['SVC-INFORMATIQUE', 'ADMIN-INTRA', 'ADMIN-RH']);


$isAdminForm = isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], ['SVC-INFORMATIQUE', 'ADMIN-RH']);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Membres du <?= htmlspecialchars($service['nom']) ?></title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/membresservices.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
</head>
<body>

<div class="top-button-container">
    <button class="top-button" onclick="window.location.href='services-global-membre.php'">← Retour aux services</button>
</div>

<div class="membre-global-header">
    <h1>Membres du <?= htmlspecialchars($service['nom']) ?></h1>
</div>

<?php if (!empty($message)) echo "<p>$message</p>"; ?>
<?php if ($isAdminForm): ?>
<div class="form-ajout-container">
    <h2>Ajouter un utilisateur au service</h2>
    <form method="post">
        <select name="membre" class="select-membre">
            <option value="">-- Sélectionnez un membre --</option>
            
            <!-- Utilisateurs BDD non attribués -->
            <optgroup label="Utilisateurs locaux">
                <?php 
                // Trier les membres BDD par nom puis prénom
                usort($tousLesMembresBDD, function($a, $b) {
                    $nomA = $a['nom'] ?? '';
                    $nomB = $b['nom'] ?? '';
                    if ($nomA === $nomB) {
                        return strcmp($a['prenom'] ?? '', $b['prenom'] ?? '');
                    }
                    return strcmp($nomA, $nomB);
                });
                
                foreach ($tousLesMembresBDD as $membre): 
                    // Ne montrer que les utilisateurs locaux (non AD)
                    if (empty($membre['ldap_user']) || $membre['ldap_user'] == 0):
                ?>
                    <option value="bdd_<?= $membre['id'] ?>">
                        <?= htmlspecialchars($membre['prenom'] . ' ' . $membre['nom']) ?> (Local)
                    </option>
                <?php 
                    endif;
                endforeach; 
                ?>
            </optgroup>
            
            <!-- Utilisateurs AD non encore dans la BDD -->
            <optgroup label="Active Directory (non importés)">
                <?php 
                // Trier les membres AD par nom puis prénom
                usort($tousLesMembresAD, function($a, $b) {
                    $nomA = $a['sn'][0] ?? '';
                    $nomB = $b['sn'][0] ?? '';
                    if ($nomA === $nomB) {
                        return strcmp($a['givenname'][0] ?? '', $b['givenname'][0] ?? '');
                    }
                    return strcmp($nomA, $nomB);
                });
                
                foreach ($tousLesMembresAD as $membre): 
                    $email = $membre['mail'][0] ?? '';
                    // Vérifier si l'utilisateur AD n'est pas déjà dans la BDD
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email_professionnel = ?");
                    $stmt->execute([$email]);
                    $existsInDB = $stmt->fetch();
                    
                    if (!empty($email) && !$existsInDB && !in_array($email, $emailsDansService)):
                ?>
                    <option value="ad_<?= htmlspecialchars($membre['id']) ?>">
                        <?= htmlspecialchars(($membre['givenname'][0] ?? '') . ' ' . ($membre['sn'][0] ?? '')) ?> (AD)
                        <?= !empty($email) ? ' - ' . htmlspecialchars($email) : '' ?>
                    </option>
                <?php endif; ?>
                <?php endforeach; ?>
            </optgroup>
        </select>
        <br><br>
        <button type="submit" class="btn-ajouter">Ajouter au service</button>
    </form>
</div>
<?php endif; ?>

<?php if (empty($tousLesMembres)): ?>
    <div class="infos-techniques">
        <h3>⚠ Aucun membre trouvé pour ce service</h3>
        <ul>
            <li>Vérifiez l'existence du groupe dans l'AD</li>
            <li>Vérifiez les droits LDAP</li>
            <li>Vérifiez la correspondance du nom de service</li>
            <li>Ajoutez des utilisateurs à ce service</li>
        </ul>
    </div>
<?php else: ?>
    <div class="membre-container">
        <?php foreach ($tousLesMembres as $membre): ?>
            <?php 
                $isAD = isset($membre['mail']);
                $email = $isAD ? ($membre['mail'][0] ?? '') : ($membre['email_professionnel'] ?? '');
            ?>
<a href="profilutilisateur.php?email=<?= urlencode($email) ?>&source=<?= $isAD ? 'ad' : 'bdd' ?>&from=services&service_id=<?= $service_id ?>" class="membre-link">                <div class="membre-card">
                    <div class="membre-nom">
                        <?= htmlspecialchars($isAD ? ($membre['givenname'][0] ?? '') : $membre['prenom']) ?>
                        <?= htmlspecialchars($isAD ? ($membre['sn'][0] ?? '') : $membre['nom']) ?>
                    </div>
                    <div class="membre-role">
                        <?= htmlspecialchars($isAD ? ($membre['description'][0] ?? '') : '') ?><br>
                        <?= htmlspecialchars($email) ?>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="bottom-button-container">
    <button class="bottom-button" onclick="window.location.href='actualite.php?id=<?= $service_id ?>&debug=1'">
        Actualités du service
    </button>
</div>

</body>
</html>
