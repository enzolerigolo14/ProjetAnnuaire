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

echo "<div class='membre-container'>";
foreach ($membres as $membre) {
    //echo "<a href='profilutilisateur.php?id=" . htmlspecialchars($membre['id']) . "' class='membre-link'>";
    //echo "<a href='profilutilisateur.php?id=" . $membre['id'] . "&from=global' class='membre-link'>";
    echo "<a href='profilutilisateur.php?id=".$membre['id']."&from=global'  class='membre-link'>"; ;
    echo "<div class='membre-card'>";
    echo "<div class='membre-nom'>" . htmlspecialchars($membre['nom']) . " " . htmlspecialchars($membre['prenom']) . "</div>";
    echo "<div class='membre-role'>" . htmlspecialchars($membre['role']) . "</div>";
    echo "</div>";
    echo "</a>";
}
echo "</div>";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membres Global</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/membreglobal.css">
    
</head>

<body>
    <div class="top-button-container">
        
    <button class="top-button" onclick="window.location.href='pageaccueil.php'"> ← Retour</button>
    </div>
</body>
</html>