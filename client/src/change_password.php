<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Vérification de la session
if (!isset($_SESSION['nouvelle_inscription'])) {
    header('Location: inscription.php');
    exit;
}

// Récupération des données
$inscription_id = $_SESSION['nouvelle_inscription']['id'];
$password_temp = $_SESSION['nouvelle_inscription']['password_temp'];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouveau_mdp = $_POST['new_password'];
    $confirmation = $_POST['confirm_password'];

    if ($nouveau_mdp !== $confirmation) {
        $error = "Les mots de passe ne correspondent pas";
    } else {
        try {
            $pdo->beginTransaction();

            // Migration vers la table users
            $stmt = $pdo->prepare("SELECT * FROM inscription WHERE id = ?");
            $stmt->execute([$inscription_id]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("INSERT INTO users 
                                 (nom, prenom, email_professionnel, service_id, mot_de_passe) 
                                 VALUES (?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $user_data['nom'],
                $user_data['prenom'],
                $user_data['email_professionnel'],
                $user_data['service_id'],
                password_hash($nouveau_mdp, PASSWORD_BCRYPT)
            ]);

            // Nettoyage de l'inscription
            $stmt = $pdo->prepare("DELETE FROM inscription WHERE id = ?");
            $stmt->execute([$inscription_id]);

            $pdo->commit();

            // Nettoyage de session et redirection
            unset($_SESSION['nouvelle_inscription']);
            header('Location: connexion.php?success=1');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erreur système : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Finalisation de l'inscription</title>
    <style>
        .password-box {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .temp-password {
            background: #f3f4f6;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="password-box">
        <h2>Finalisation de l'inscription</h2>
        
        <div class="temp-password">
            <p>Mot de passe temporaire généré :</p>
            <strong><?= htmlspecialchars($password_temp) ?></strong>
        </div>

        <form method="POST">
            <div class="form-group">
                <label>Nouveau mot de passe :</label>
                <input type="password" name="new_password" required>
            </div>

            <div class="form-group">
                <label>Confirmer le mot de passe :</label>
                <input type="password" name="confirm_password" required>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <button type="submit" class="submit-btn">Valider et activer le compte</button>
        </form>
    </div>
</body>
</html>