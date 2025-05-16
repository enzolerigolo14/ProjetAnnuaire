<?php
// 1. Initialisation
session_start();
require_once __DIR__ . '/config/database.php'; // Assure-toi que ce chemin est correct

// 2. Vérification de l'accès admin
if (!isset($_SESSION['user'])) {
    header('Location: /projetannuaire/client/src/connexion.php');
    exit();
}

// 3. Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomService = trim($_POST['nom_service']);

    // Validation basique
    if (empty($nomService)) {
        $_SESSION['error'] = "Le nom du service ne peut pas être vide";
    } else {
        try {
            // Insertion directe en base
            $stmt = $pdo->prepare("INSERT INTO services (nom) VALUES (?)");
            $stmt->execute([$nomService]);
            
            $_SESSION['success'] = "Service ajouté avec succès !";
            header('Location: parametre.php');
            exit();
            
        } catch(PDOException $e) {
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .error {
            background-color: #ffebee;
            color: #f44336;
            border: 1px solid #f44336;
        }
        .success {
            background-color: #e8f5e9;
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
    </style>
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