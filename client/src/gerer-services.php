<?php
require_once __DIR__ . '/config/database.php';

// Traitement activation/désactivation
if (isset($_GET['toggle_id'])) {
    $id = $_GET['toggle_id'];

    $stmt = $pdo->prepare("SELECT actif FROM services WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $service = $stmt->fetch();

    if ($service) {
        $nouvelEtat = $service['actif'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE services SET actif = :actif WHERE id = :id");
        $stmt->execute(['actif' => $nouvelEtat, 'id' => $id]);
    }

    header("Location: gerer-services.php");
    exit;
}

// Récupération des services
$stmt = $pdo->query("SELECT * FROM services ORDER BY nom ASC");
$services = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer les services</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/gerer-services.css">
    
</head>
<body>
    <div class="top-button-container right-align">
    <button class="top-button" onclick="window.location.href='parametre.php'">← Retour</button>
</div>
<div class="container">
    


    <h2>Gérer les services</h2>

    <a href="ajouter-services.php" class="ajouter-btn">+ Ajouter un service</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom du service</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($services as $service): ?>
                <tr class="<?= $service['actif'] ? '' : 'inactif' ?>">
                    <td><?= $service['id'] ?></td>
                    <td><?= htmlspecialchars($service['nom']) ?></td>
                    <td><?= $service['actif'] ? 'Actif' : 'Inactif' ?></td>
                    <td>
                        
                        <a href="gerer-services.php?toggle_id=<?= $service['id'] ?>" class="actions toggle">
                            <?= $service['actif'] ? 'Désactiver' : 'Activer' ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
