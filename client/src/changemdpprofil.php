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
    } elseif ($new_password === $old_password) {
        $error = 'Le nouveau mot de passe doit être différent de l\'ancien';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Les nouveaux mots de passe ne correspondent pas';
    } elseif (strlen($new_password) < 12) {
        $error = 'Le mot de passe doit contenir au moins 12 caractères';
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $error = 'Le mot de passe doit contenir au moins une majuscule';
    } elseif (!preg_match('/[a-z]/', $new_password)) {
        $error = 'Le mot de passe doit contenir au moins une minuscule';
    } elseif (!preg_match('/[0-9]/', $new_password)) {
        $error = 'Le mot de passe doit contenir au moins un chiffre';
    } elseif (!preg_match('/[\W_]/', $new_password)) {
        $error = 'Le mot de passe doit contenir au moins un caractère spécial';
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
    <style>
        .password-rules {
            background-color: #f8f9fa;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 0.9em;
        }
        .password-rules ul {
            margin: 5px 0;
            padding-left: 20px;
        }
        .password-rules li.valid {
            color: #28a745;
        }
        .password-rules li.invalid {
            color: #dc3545;
        }
    </style>
    <script>
        function checkPasswordStrength() {
            const oldPassword = document.getElementById('old_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Règles de validation
            const isDifferent = newPassword !== oldPassword && oldPassword !== '';
            const hasMinLength = newPassword.length >= 12;
            const hasUpperCase = /[A-Z]/.test(newPassword);
            const hasLowerCase = /[a-z]/.test(newPassword);
            const hasNumber = /[0-9]/.test(newPassword);
            const hasSpecialChar = /[\W_]/.test(newPassword);
            const passwordsMatch = newPassword === confirmPassword && newPassword !== '';
            
            // Mise à jour de l'affichage des règles
            document.getElementById('diff-rule').className = isDifferent ? 'valid' : 'invalid';
            document.getElementById('length-rule').className = hasMinLength ? 'valid' : 'invalid';
            document.getElementById('upper-rule').className = hasUpperCase ? 'valid' : 'invalid';
            document.getElementById('lower-rule').className = hasLowerCase ? 'valid' : 'invalid';
            document.getElementById('number-rule').className = hasNumber ? 'valid' : 'invalid';
            document.getElementById('special-rule').className = hasSpecialChar ? 'valid' : 'invalid';
            document.getElementById('match-rule').className = passwordsMatch ? 'valid' : 'invalid';
        }
    </script>
</head>
<body>
    <div>
        <h1>Changer Mot de Passe</h1>
        
        <div class="password-rules">
            <strong>Votre nouveau mot de passe doit :</strong>
            <ul>
                <li id="diff-rule" class="invalid">Être différent de l'ancien mot de passe</li>
                <li id="length-rule" class="invalid">Contenir au moins 12 caractères</li>
                <li id="upper-rule" class="invalid">Contenir au moins une majuscule (A-Z)</li>
                <li id="lower-rule" class="invalid">Contenir au moins une minuscule (a-z)</li>
                <li id="number-rule" class="invalid">Contenir au moins un chiffre (0-9)</li>
                <li id="special-rule" class="invalid">Contenir au moins un caractère spécial (!@#$%^&*, etc.)</li>
                <li id="match-rule" class="invalid">Correspondre à la confirmation</li>
            </ul>
        </div>

        <form id="passwordForm" method="POST" action="" oninput="checkPasswordStrength()">
            <div class="form-group">
                <label for="old_password">Ancien Mot de Passe</label>
                <input type="password" 
                       id="old_password" 
                       name="old_password" 
                       required
                       onkeyup="checkPasswordStrength()">
            </div>

            <div class="form-group">
                <label for="new_password">Nouveau Mot de Passe</label>
                <input type="password" 
                       id="new_password" 
                       name="new_password" 
                       required
                       minlength="12"
                       onkeyup="checkPasswordStrength()">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le Nouveau Mot de Passe</label>
                <input type="password" 
                       id="confirm_password" 
                       name="confirm_password" 
                       required
                       minlength="12"
                       onkeyup="checkPasswordStrength()">
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