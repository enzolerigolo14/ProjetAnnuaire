<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user'])) {
    header('Location: pageaccueil.php');
    exit();
}

// Vérifier si l'email est passé en paramètre
$email = $_GET['email'] ?? '';
$isAdAccount = false;

if (!empty($email)) {
    try {
        // Vérifier si c'est un compte AD
        $stmt = $pdo->prepare("SELECT ldap_user FROM users WHERE email_professionnel = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        $isAdAccount = ($user && $user['ldap_user'] == 1);
    } catch (PDOException $e) {
        // En cas d'erreur, on considère que c'est un compte local par défaut
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation mot de passe | Portail Mairie</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/connexion.css">
    <style>
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #17a2b8;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .ad-message {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .local-message {
            background-color: #e9f7ef;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
        }
        .contact-info {
            margin-top: 15px;
            padding: 10px;
            background-color: #e2e3e5;
            border-radius: 4px;
        }
        .btn-return {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Réinitialisation du mot de passe</h1>
        
        <div class="info-box">
            <?php if ($isAdAccount): ?>
                <div class="ad-message">
                    <h3>Compte AD détecté</h3>
                    <p>Votre mot de passe est le même que celui de votre session Windows.</p>
                    <p>Si vous ne parvenez pas à vous connecter :</p>
                    
                    <div class="contact-info">
                        <p><strong>Contactez le service informatique :</strong></p>
                        <p>✆ 02 31 48 30 00 (poste 1234)</p>
                        <p>✉ service.informatique@ville-lisieux.fr</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="local-message">
                    <h3>Compte local détecté</h3>
                    <p>Veuillez contacter les informaticiens du service DNSI pour réinitialiser votre mot de passe.</p>
                    
                    <div class="contact-info">
                        <p><strong>Service DNSI :</strong></p>
                        <p>✆ 02 31 48 30 00 (poste 5678)</p>
                        <p>✉ dnsisupport@ville-lisieux.fr</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <a href="/projetannuaire/client/src/connexion.php" class="btn-return">Retour à la connexion</a>
        </div>
    </div>
</body>
</html>