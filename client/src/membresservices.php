<?php

require_once __DIR__ . '/config/database.php';
session_start();
$_SESSION['origin_page'] = [
    'url' => $_SERVER['REQUEST_URI'],
    'service_id' => $_GET['service_id'] ?? null 
];

if (!isset($_GET['id'])) {
    die("Aucun service sélectionné.");
}


$service_id = intval($_GET['id']); 


$stmt = $pdo->prepare("
    SELECT u.* 
    FROM users u 
    WHERE u.service_id = ?
");
$stmt->execute([$service_id]);
$membres = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();


if (empty($membres)) {
    echo "<p style='text-align:center;'>Aucun membre dans ce service.</p>";
} else {
    echo "<h2 class='service-nom'>Membres du service  " . htmlspecialchars($service['nom'] ?? 'Inconnu') . " : " . "</h2>";
    echo "<div class='membre-container'>";
foreach ($membres as $membre) {
    //echo "<a href='profilutilisateur.php?id=" . htmlspecialchars($membre['id']) . "' class='membre-link'>";
    //echo "<a href='profilutilisateur.php?id=" . $membre['id'] . "&from=services' class='membre-link'>";
    echo "<a href='profilutilisateur.php?id=".$membre['id']."&from=services&service_id=".$service_id."' class='membre-link'>"; 
    echo "<div class='membre-card'>";
    echo "<div class='membre-nom'>" . htmlspecialchars($membre['nom']) . " " . htmlspecialchars($membre['prenom']) . "</div>";
    echo "<div class='membre-role'>" . htmlspecialchars($membre['role']) . "</div>";
    echo "</div>";
    echo "</a>";
}
echo "</div>";

}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membres du service</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/membresservices.css">
    

    </head>
</html>