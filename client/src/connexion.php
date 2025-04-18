<?php
session_start();
require_once __DIR__ . '/config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtolower(trim($_POST['username'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        try {
            // Recherche par format "jdupont" (première lettre prénom + nom)
            $sql = "SELECT * FROM users WHERE CONCAT(LOWER(LEFT(prenom, 1)), LOWER(nom)) = :identifiant";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':identifiant', $username);
            $stmt->execute();
            
            $user = $stmt->fetch();

            if ($user) {
                // Vérification SIMPLIFIÉE (à remplacer par password_verify plus tard)
                if ($password === $user['mot_de_passe']) {
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'prenom' => $user['prenom'],
                        'nom' => $user['nom'],
                        'email_professionel' => $user['email_professionel'],
                        'role' => $user['role']
                    ];


                    header('Location: /projetannuaire/client/src/pageaccueil.php');
                    exit;
                } else {
                    $error = "Mot de passe incorrect";
                }
            } else {
                $error = "Identifiant non trouvé";
            }
        } catch (PDOException $e) {
            $error = "Erreur système. Veuillez réessayer.";
        }
    }


}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Trombinoscope Ville de Lisieux</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/connexion.css">
</head>
<body>
    <div id="connexion-modal" class="modal">
        <div class="modal-content">
            <div class="login-header">
                <h2>Connexion</h2>
                <p>Accédez à votre espace personnel</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Identifiant (première lettre du prénom + nom)</label>
                    <input type="text" id="username" name="username" required
                           placeholder="ex: jdupont"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="submit-btn">Se connecter</button>
            </form>
        </div>
    </div>
</body>
</html>