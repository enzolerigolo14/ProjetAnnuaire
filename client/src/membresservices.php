<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/ldap_auth.php';

// Mapping exact des noms de groupes AD
$services = [
    1 => 'Service Accueil',
    2 => 'Service Administration Générale',
    3 => 'Service Bâtiment',
    4 => 'Service Bureau d\'études',
    5 => 'Service Cabinet',
    6 => 'Service Communication',
    7 => 'Service Élections',
    8 => 'Service État Civil',
    9 => 'Service Événementiel',
    10 => 'Service Fêtes & Cérémonies',
    11 => 'Service Finances',
    12 => 'Service Juridique',
    13 => 'Service Marché Public',
    14 => 'Service Pompes Funèbres',
    15 => 'Service Ressources Humaines',
    16 => 'Service Secrétariat Général',
    17 => 'Service Stationnement Payant',
    18 => 'Tous les services de la Ville',
    19 => 'Tous les services de l\'Hôtel de Ville',
];

$service_id = (int)($_GET['id'] ?? 1);
$nomService = $services[$service_id] ?? null;

if (!$nomService) {
    die("<div class='error'>Service ID $service_id non valide</div>");
}

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
        <button class="top-button" onclick="window.history.back()">← Retour</button>
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
                <div class="membre-card">
                    <div class="membre-nom">
                        <?= htmlspecialchars($membre['givenname'][0] ?? '') ?>
                        <?= htmlspecialchars($membre['sn'][0] ?? '') ?>
                    </div>
                    <div class="membre-role">
                        <?= htmlspecialchars($membre['description'][0] ?? '') ?>
                        <?php if (!empty($membre['telephonenumber'][0])): ?>
                            <br>📞 <?= htmlspecialchars($membre['telephonenumber'][0]) ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="bottom-button-container">
        <button class="bottom-button" onclick="window.location.href='services-global-actualite.php?id=<?php echo $service_id ?? 0; ?>'">
            Actualités du service
        </button>
    </div>

    <footer>
        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </footer>

</body>
</html>
