<?php
// 1. Initialisation
session_start();
require_once __DIR__ . '/config/database.php'; 

// 2. Vérification de l'accès admin
if (!isset($_SESSION['user'])) {
    header('Location: /projetannuaire/client/src/connexion.php');
    exit();
}

// 3. Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomService = trim($_POST['nom_service']);
    if (empty($nomService)) {
        $_SESSION['error'] = "Le nom du service ne peut pas être vide";
    } else {
        try {
           $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO services (nom) VALUES (?)");
    $stmt->execute([$nomService]);
    $pdo->commit();
            
            $_SESSION['success'] = "Service ajouté avec succès !";
            header('Location: gerer-services.php');
            exit();
            
        } catch(PDOException $e) {
            $pdo->rollBack();
    $_SESSION['error'] = "Erreur technique : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un service</title>
     <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/ajouter-services.css">
</head>
<body>
    <div class="container">
        <h1>Ajouter un nouveau service</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="nom_service">Nom du service :</label>
                <input type="text" id="nom_service" name="nom_service" required>
            </div>
            
            <button type="submit">Ajouter le service</button>
        </form>
    </div>
</body>
</html>