<?php
session_start();
require_once __DIR__ . '/config/ldap_auth.php';
require_once __DIR__ . '/config/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Récupération des membres AD
$membresAD = recupererUtilisateursParServiceAD($service['nom']);

// Récupération des membres BDD
$stmt = $pdo->prepare("SELECT id, nom, prenom, email_professionnel FROM users WHERE service_id = ?");
$stmt->execute([$service_id]);
$membresBDD = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fusion des membres AD et BDD
$tousLesMembres = array_merge($membresAD, $membresBDD);
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

    <?php if (empty($tousLesMembres)): ?>
        <div class="infos-techniques">
            <h3>⚠ Aucun membre trouvé pour ce service</h3>
            <p>Vérifiez que :</p>
            <ul>
                <li>Le groupe existe dans l'Active Directory avec ce nom exact</li>
                <li>Le compte LDAP a les droits de lecture</li>
                <li>Le nom du service correspond exactement au CN dans l'AD</li>
                <li>Des utilisateurs sont associés à ce service dans la base de données</li>
            </ul>
        </div>
    <?php else: ?>
        <div class="membre-container">
            <?php foreach ($tousLesMembres as $membre): ?>
                <?php 
                // Détermine si c'est un membre AD ou BDD
                $isAD = isset($membre['mail']);
                $email = $isAD ? ($membre['mail'][0] ?? '') : ($membre['email_professionnel'] ?? '');
                ?>
                <a href="profilutilisateur.php?email=<?= urlencode($email) ?>&source=<?= $isAD ? 'ad' : 'bdd' ?>&from=services&service_id=<?= $service_id ?>" class="membre-link">
                    <div class="membre-card">
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

    <footer>
        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </footer>

</body>
</html>