<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Vérification de la session
if (!isset($_SESSION['nouvelle_inscription'])) {
    header('Location: inscription.php');
    exit;
}
$inscription_id = $_SESSION['nouvelle_inscription']['id'];
$password_temp = $_SESSION['nouvelle_inscription']['password_temp'];

// Fonction de validation du mot de passe
function validerMotDePasse($mdp) {
    // Au moins 12 caractères
    if (strlen($mdp) < 12) {
        return "Le mot de passe doit contenir au moins 12 caractères";
    }
    
    // Au moins une majuscule
    if (!preg_match('/[A-Z]/', $mdp)) {
        return "Le mot de passe doit contenir au moins une majuscule";
    }
    
    // Au moins une minuscule
    if (!preg_match('/[a-z]/', $mdp)) {
        return "Le mot de passe doit contenir au moins une minuscule";
    }
    
    // Au moins un caractère spécial
    if (!preg_match('/[\W_]/', $mdp)) {
        return "Le mot de passe doit contenir au moins un caractère spécial";
    }
    
    return true;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouveau_mdp = $_POST['new_password'];
    $confirmation = $_POST['confirm_password'];

    if ($nouveau_mdp !== $confirmation) {
        $error = "Les mots de passe ne correspondent pas";
    } else {
        // Validation du mot de passe
        $validation = validerMotDePasse($nouveau_mdp);
        if ($validation !== true) {
            $error = $validation;
        } else {
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("SELECT * FROM inscription WHERE id = ?");
                $stmt->execute([$inscription_id]);
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $pdo->prepare("INSERT INTO users 
                                     (nom, prenom, telephone, email_professionnel, role, ldap_groups, service_id, mot_de_passe) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $user_data['nom'],
                    $user_data['prenom'],
                    $user_data['telephone'],
                    $user_data['email_professionnel'],
                    $user_data['role'],
                    $user_data['ldap_groups'],
                    $user_data['service_id'],
                    password_hash($nouveau_mdp, PASSWORD_BCRYPT)
                ]);

                $stmt = $pdo->prepare("DELETE FROM inscription WHERE id = ?");
                $stmt->execute([$inscription_id]);

                $pdo->commit();
                unset($_SESSION['nouvelle_inscription']);
                header('Location: connexion.php?success=1');
                exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Erreur système : " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Finalisation de l'inscription</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/change_password.css">
    <style>
        .password-requirements {
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .requirement {
            margin: 8px 0;
            padding: 5px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        .requirement.valid {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .requirement.invalid {
            background-color: #ffebee;
            color: #c62828;
        }
        .error {
            color: #dc3545;
            margin: 10px 0;
            padding: 10px;
            background-color: #ffebee;
            border-radius: 4px;
        }
        .submit-btn {
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .submit-btn:hover {
            background-color: #0056b3;
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

        <div class="password-requirements">
            <h4>Exigences du mot de passe :</h4>
            <div class="requirement" id="length-req">
                • 12 caractères minimum
            </div>
            <div class="requirement" id="uppercase-req">
                • Au moins une majuscule (A-Z)
            </div>
            <div class="requirement" id="lowercase-req">
                • Au moins une minuscule (a-z)
            </div>
            <div class="requirement" id="special-req">
                • Au moins un caractère spécial (!@#$%^&*, etc.)
            </div>
            <div class="requirement" id="match-req">
                • Les mots de passe doivent correspondre
            </div>
        </div>

        <form method="POST" id="passwordForm">
            <div class="form-group">
                <label>Nouveau mot de passe :</label>
                <input type="password" name="new_password" id="new_password" required 
                       autocomplete="new-password" class="form-control">
            </div>

            <div class="form-group">
                <label>Confirmer le mot de passe :</label>
                <input type="password" name="confirm_password" id="confirm_password" required 
                       autocomplete="new-password" class="form-control">
            </div>

            <?php if (!empty($error)): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <button type="submit" class="submit-btn">Valider et activer le compte</button>
        </form>
    </div>

    <script>
        const passwordInput = document.getElementById('new_password');
        const confirmInput = document.getElementById('confirm_password');
        
        function validatePassword() {
            const password = passwordInput.value;
            const confirmation = confirmInput.value;
            
            // Longueur
            const lengthValid = password.length >= 12;
            document.getElementById('length-req').className = lengthValid ? 'requirement valid' : 'requirement invalid';
            
            // Majuscule
            const upperValid = /[A-Z]/.test(password);
            document.getElementById('uppercase-req').className = upperValid ? 'requirement valid' : 'requirement invalid';
            
            // Minuscule
            const lowerValid = /[a-z]/.test(password);
            document.getElementById('lowercase-req').className = lowerValid ? 'requirement valid' : 'requirement invalid';
            
            // Caractère spécial
            const specialValid = /[\W_]/.test(password);
            document.getElementById('special-req').className = specialValid ? 'requirement valid' : 'requirement invalid';
            
            // Correspondance
            const matchValid = password === confirmation && password !== '';
            document.getElementById('match-req').className = matchValid ? 'requirement valid' : 'requirement invalid';
            
            return lengthValid && upperValid && lowerValid && specialValid && matchValid;
        }
        
        passwordInput.addEventListener('input', validatePassword);
        confirmInput.addEventListener('input', validatePassword);
        
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            if (!validatePassword()) {
                e.preventDefault();
                alert("Veuillez respecter toutes les exigences du mot de passe");
            }
        });
        
        // Validation initiale
        validatePassword();
    </script>
</body>
</html>