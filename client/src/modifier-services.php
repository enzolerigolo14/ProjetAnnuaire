<?php
require_once __DIR__ . '/config/database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id']) && isset($_POST['nom'])) {
    $id = $_POST['id'];
    $nom = $_POST['nom'];

    $stmt = $pdo->prepare("UPDATE services SET nom = :nom WHERE id = :id");
    $stmt->execute(['nom' => $nom, 'id' => $id]);

    header("Location: gerer-services.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "ID du service manquant.";
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = :id");
$stmt->execute(['id' => $id]);
$service = $stmt->fetch();

if (!$service) {
    echo "Service introuvable.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Service</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/modifier-services.css">

</head>
<body>
    <h2>Modifier le service</h2>
    <form method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($service['id']) ?>">
        <label>Nom du service :</label>
        <input type="text" name="nom" value="<?= htmlspecialchars($service['nom']) ?>" required>
        <button type="submit">Enregistrer</button>
    </form>
</body>
</html>
