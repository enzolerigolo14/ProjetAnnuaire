<?php
session_start();
require_once __DIR__ . '/config/ldap_auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtolower(trim($_POST['username'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        $ldap_data = authentifierEtRecupererInfos($username, $password);

        if ($ldap_data) {
            $_SESSION['user'] = [
                'prenom' => $ldap_data['givenname'][0] ?? '',
                'nom' => $ldap_data['sn'][0] ?? '',
                'email' => $ldap_data['mail'][0] ?? '',
                'telephone' => $ldap_data['telephonenumber'][0] ?? '',
                'description' => $ldap_data['description'][0] ?? '',
                'groupe' => is_array($ldap_data['memberof']) ? implode(', ', $ldap_data['memberof']) : 
                ($ldap_data['memberof'] ?? ''),
                'identifiant' => $username
            ];

            header('Location: /projetannuaire/client/src/pageaccueil.php');
            exit;
        } else {
            $error = "Identifiant ou mot de passe incorrect";
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
