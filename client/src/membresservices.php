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

// Récupération du nom du service depuis la base de données
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    die("<div class='error'>Service avec l'ID $service_id non trouvé dans la base de données</div>");
}

$nomService = $service['nom']; // Assurez-vous que ce champ correspond au nom dans l'AD

// Récupération des membres via LDAP
$membresAD = recupererUtilisateursParServiceAD($nomService);
error_log("Service: $nomService - Membres trouvés: " . count($membresAD));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Membres du <?= htmlspecialchars($nomService) ?></title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/membresservices.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
</head>
<body>

    <div class="top-button-container">
        <button class="top-button" onclick="window.location.href='services-global-membre.php'">← Retour aux services</button>
    </div>

    <div class="membre-global-header">
        <h1>Membres du <?= htmlspecialchars($nomService) ?></h1>
    </div>

    <?php if (empty($membresAD)): ?>
        <div class="infos-techniques">
            <h3>⚠ Aucun membre trouvé pour ce service</h3>
            <p>Vérifiez que :</p>
            <ul>
                <li>Le groupe existe dans l'Active Directory avec ce nom exact</li>
                <li>Le compte LDAP a les droits de lecture</li>
                <li>Le nom du service correspond exactement au CN dans l'AD</li>
            </ul>
        </div>
    <?php else: ?>
        <div class="membre-container">
            <?php foreach ($membresAD as $membre): ?>
                <a href="profilutilisateur.php?email=<?= urlencode($membre['mail'][0] ?? '') ?>" class="membre-link">
                    <div class="membre-card">
                        <div class="membre-nom">
                            <?= htmlspecialchars($membre['givenname'][0] ?? '') ?>
                            <?= htmlspecialchars($membre['sn'][0] ?? '') ?>
                        </div>
                        <div class="membre-role">
                            <?= htmlspecialchars($membre['description'][0] ?? '') ?><br>
                            <?= htmlspecialchars($membre['mail'][0] ?? '') ?>
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
