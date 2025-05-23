<?php
require_once __DIR__ . '/config/ldap_auth.php';
require_once __DIR__ . '/config/database.php';

function normalizeString($str) {
    $str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
    return strtolower(trim(preg_replace('/[^a-z0-9]/i', ' ', $str)));
}

$terme = trim($_GET['q'] ?? '');

if (empty($terme)) {
    header("Location: membreglobal.php");
    exit;
}

$termeNormalise = normalizeString($terme);

// 1. Recherche exacte dans la BDD en premier
$stmt = $pdo->prepare("SELECT id, prenom, nom, email_professionnel FROM users WHERE CONCAT(prenom, ' ', nom) = ?");
$stmt->execute([$terme]);
if ($user = $stmt->fetch()) {
    $email = urlencode($user["email_professionnel"]);
    header("Location: profilutilisateur.php?email=$email&source=db&from=membres");
    exit;
}

// 2. Recherche partielle dans la BDD
$stmt = $pdo->prepare("SELECT id, prenom, nom, email_professionnel FROM users WHERE CONCAT(prenom, ' ', nom) LIKE ?");
$stmt->execute(["%$terme%"]);
$resultsDB = $stmt->fetchAll();

// 3. Recherche dans l'AD seulement si pas de résultats en BDD
$resultsAD = [];
if (empty($resultsDB)) {
    $usersAD = recupererTousLesUtilisateursAD();
    foreach ($usersAD as $user) {
        $prenom = normalizeString($user["givenname"][0] ?? '');
        $nom = normalizeString($user["sn"][0] ?? '');
        $fullName = "$prenom $nom";
        
        if (str_contains($fullName, $termeNormalise)) {
            $resultsAD[] = [
                'prenom' => $user["givenname"][0] ?? '',
                'nom' => $user["sn"][0] ?? '',
                'email' => $user["mail"][0] ?? '',
                'source' => 'ad'
            ];
        }
    }
}

// Si un seul résultat, rediriger directement
if (count($resultsDB) === 1) {
    $email = urlencode($resultsDB[0]["email_professionnel"]);
    header("Location: profilutilisateur.php?email=$email&source=db&from=membres");
    exit;
} elseif (count($resultsAD) === 1 && empty($resultsDB)) {
    $email = urlencode($resultsAD[0]["email"]);
    header("Location: profilutilisateur.php?email=$email&source=ad&from=membres");
    exit;
}

// Sinon afficher la liste des résultats
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats pour <?= htmlspecialchars($terme) ?></title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/global.css">
</head>
<body>
    <div class="search-results-container">
        <h1>Résultats pour "<?= htmlspecialchars($terme) ?>"</h1>
        
        <?php if (!empty($resultsDB)): ?>
            <h2>Membres internes</h2>
            <ul class="results-list">
                <?php foreach ($resultsDB as $user): ?>
                    <li>
                        <a href="profilutilisateur.php?email=<?= urlencode($user['email_professionnel']) ?>&source=db&from=membres">
                            <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (!empty($resultsAD)): ?>
            <h2>Membres AD</h2>
            <ul class="results-list">
                <?php foreach ($resultsAD as $user): ?>
                    <li>
                        <a href="profilutilisateur.php?email=<?= urlencode($user['email']) ?>&source=ad&from=membres">
                            <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (empty($resultsDB) && empty($resultsAD)): ?>
            <p class="no-results">Aucun résultat trouvé.</p>
        <?php endif; ?>

        <a href="membreglobal.php" class="back-button">← Retour à la liste</a>
    </div>
</body>
</html>