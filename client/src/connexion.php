<?php
session_start();
require_once __DIR__ . '/config/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_SESSION['user_id'])) {
    header('Location: /ProjetAnnuaire/client/src/pageaccueil.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    try {
        $pdo = Database::getInstance();

        $stmt = $pdo->prepare("
            SELECT id, nom, prenom, mot_de_passe as password, email_professionnel
            FROM users 
            WHERE CONCAT(LOWER(SUBSTRING(prenom, 1, 1)), LOWER(nom)) = LOWER(:username)
               OR email_professionnel = :username
            LIMIT 1
        ");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['email_professionnel'];
                $_SESSION['nom_complet'] = $user['prenom'] . ' ' . $user['nom'];
                $_SESSION['last_login'] = time();

                if (!empty($_SESSION['user_id'])) {
                    header('Location: /ProjetAnnuaire/client/src/pageaccueil.php');
                    exit;
                }
            } else {
                $error = "Mot de passe incorrect";
            }
        } else {
            $error = "Identifiant non reconnu";
        }
    } catch (PDOException $e) {
        $error = "Erreur système. Veuillez réessayer plus tard.";
        error_log("PDO Error: " . $e->getMessage());
    } catch (Exception $e) {
        $error = "Erreur inattendue. Contactez l'administrateur.";
        error_log("General Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Trombinoscope Ville de Lisieux</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base_url ?? '') ?>/ProjetAnnuaire/client/src/assets/styles/connexion.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="<?= htmlspecialchars($base_url ?? '') ?>/ProjetAnnuaire/client/script/connexion.js" defer></script>
</head>
<body>
    <div id="connexion-modal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <div class="login-header">
                <h2>Connexion</h2>
                <p>Accédez à votre espace personnel</p>
            </div>

            <form id="login-form" method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" novalidate>
                <div class="form-group">
                    <label for="username">Email professionnel</label>
                    <input type="email" id="username" name="username" required
                        placeholder="exemple@ville-lisieux.fr"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        pattern="[a-zA-Z]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$">
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required
                        minlength="8"
                        pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$">
                    <a href="motdepasse-oublie.php" class="forgot-password">Mot de passe oublié ?</a>
                </div>

                <?php if ($error): ?>
                    <div class="error-message">
                        <?= htmlspecialchars($error) ?>
                        <?php if (strpos($error, 'technique') !== false): ?>
                            <p>Veuillez contacter le support technique.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <button type="submit" class="submit-btn">Se connecter</button>

                <div class="signup-link">
                    Pas encore de compte ? <a href="inscription.php">Demander un accès</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        console.log("Session: ", <?= json_encode($_SESSION) ?>);
    </script>
</body>
</html>
