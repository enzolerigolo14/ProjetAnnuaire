<?php

require_once __DIR__ . '/config/ldap_auth.php';
session_start();
// Connexion LDAP
$user = recupererTousLesUtilisateursAD();
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membres Global</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/membreglobal.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
</head>
<body>

    <div class="top-button-container"> 
        <button class="top-button" onclick="window.location.href='pageaccueil.php'"> ← Retour</button>
    </div>

    <!-- Titre -->
    <div class="membre-global-header">
        <h1>Membres Global</h1>
    </div>

    <!-- Cartes membres -->
    <div class="membre-container">
    <?php for ($i = 0; $i < $user["count"]; $i++): 
        $email = urlencode($user[$i]["mail"][0] ?? '');
    ?>
        <a href="profilutilisateur.php?email=<?= $email ?>" class="membre-link">
            <div class="membre-card">
                <div class="membre-nom">
                    <?= htmlspecialchars($user[$i]["givenname"][0] ?? '') ?>
                    <?= htmlspecialchars($user[$i]["sn"][0] ?? '') ?>
                </div>
                <div class="membre-role">
                    <?= htmlspecialchars($user[$i]["mail"][0] ?? 'Mail non disponible') ?><br>
                    <?= htmlspecialchars($user[$i]["description"][0] ?? 'Description non disponible') ?>
                </div>
            </div>
        </a>
    <?php endfor; ?>
</div>


    <footer>
        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </footer>
</body>
</html>
