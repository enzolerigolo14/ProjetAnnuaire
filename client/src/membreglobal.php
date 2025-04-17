<?php
require_once __DIR__ . '/config/database.php';
session_start();

$_SESSION['origin_page'] = [
    'url' => $_SERVER['REQUEST_URI'],
    'service_id' => $_GET['service_id'] ?? null 
];

$stmt = $pdo->prepare("SELECT * FROM users");
$stmt->execute();
$membres = $stmt->fetchAll();
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
        <?php foreach ($membres as $membre): ?>
            <a href="profilutilisateur.php?id=<?= $membre['id'] ?>&from=global" class="membre-link">
                <div class="membre-card">
                    <div class="membre-nom"><?= htmlspecialchars($membre['nom']) ?> <?= htmlspecialchars($membre['prenom']) ?></div>
                    <div class="membre-role"><?= htmlspecialchars($membre['role']) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <footer>
        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </footer>
</body>
</html>
