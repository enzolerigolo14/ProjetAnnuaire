<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: /projetannuaire/client/src/connexion.php');
    exit();
}

$error = '';
$success = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Tous les champs sont obligatoires';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Les nouveaux mots de passe ne correspondent pas';
    } elseif (strlen($new_password) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères';
    } else {
        // Vérifier l'ancien mot de passe
        $stmt = $pdo->prepare("SELECT mot_de_passe FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user']['id']]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($old_password, $user['mot_de_passe'])) {
            $error = 'Ancien mot de passe incorrect';
        } else {
            // Mettre à jour le mot de passe
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET mot_de_passe = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $_SESSION['user']['id']]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['success_message'] = 'Mot de passe changé avec succès';
                header('Location: /projetannuaire/client/src/pageaccueil.php');
                exit();
            } else {
                $error = 'Erreur lors de la mise à jour du mot de passe';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer Mot de Passe | Trombinoscope Ville de Lisieux</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/changemdpprofil.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/header.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    <script>
        // Validation côté client
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('passwordForm').addEventListener('submit', function(e) {
                const oldPassword = document.getElementById('old_password').value;
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (!oldPassword || !newPassword || !confirmPassword) {
                    e.preventDefault();
                    alert('Tous les champs sont obligatoires');
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('Les nouveaux mots de passe ne correspondent pas');
                    return;
                }
                
                if (newPassword.length < 8) {
                    e.preventDefault();
                    alert('Le mot de passe doit contenir au moins 8 caractères');
                    return;
                }
                
                console.log("Formulaire validé, envoi au serveur...");
            });
        });
    </script>
</head>
<body>
    <div>
        <h1>Changer Mot de Passe</h1>
        <form id="passwordForm" method="POST" action="">
            <div class="form-group">
                <label for="old_password">Ancien Mot de Passe</label>
                <input type="password" id="old_password" name="old_password" required>
            </div>

            <div class="form-group">
                <label for="new_password">Nouveau Mot de Passe</label>
                <input type="password" id="new_password" name="new_password" required minlength="8">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le Nouveau Mot de Passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
            </div>

            <button type="submit" class="submit-btn">Changer le Mot de Passe</button>
        </form>

        <?php if ($error): ?>
            <div id="error-message" class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div id="success-message" class="success-message"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
    </div>
</body>
</html>