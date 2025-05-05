<?php
session_start();
require_once __DIR__ . '/config/database.php';
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
            // Récupération des données AD
            $prenom = $ldap_data['givenname'];
            $nom = $ldap_data['sn'];
            $email = $ldap_data['mail'];
            $telephone = $ldap_data['telephonenumber'];
            $service = $ldap_data['description'] ?? 'Service non défini';
            $description = $ldap_data['description'] ?? null;

            try {
                // Gestion du service
                $stmt_service = $pdo->prepare("SELECT id FROM services WHERE nom = ?");
                $stmt_service->execute([$service]);
                $service_id = $stmt_service->fetchColumn();

                if (!$service_id) {
                    $stmt_insert = $pdo->prepare("INSERT INTO services (nom) VALUES (?)");
                    $stmt_insert->execute([$service]);
                    $service_id = $pdo->lastInsertId();
                }

                // Vérification utilisateur
                $stmt_user = $pdo->prepare("SELECT id, description FROM users WHERE email_professionnel = ?");
                $stmt_user->execute([$email]);
                $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);
                
                $isNewUser = false;
                if (!$user_data) {
                    // Nouvel utilisateur
                    $insert_sql = "INSERT INTO users 
                                  (nom, prenom, telephone, email_professionnel, service_id, role, mot_de_passe, description) 
                                  VALUES (?, ?, ?, ?, ?, 'user', ?, ?)";
                    $stmt_insert = $pdo->prepare($insert_sql);
                    $stmt_insert->execute([
                        $nom,
                        $prenom,
                        $telephone,
                        $email,
                        $service_id,
                        password_hash($password, PASSWORD_DEFAULT),
                        $description
                    ]);
                    $user_id = $pdo->lastInsertId();
                    $isNewUser = true;
                } else {
                    // Utilisateur existant
                    $user_id = $user_data['id'];
                    
                    // Mise à jour de la description si elle est vide
                    if (empty($user_data['description']) && !empty($description)) {
                        $stmt_update = $pdo->prepare("UPDATE users SET description = ? WHERE id = ?");
                        $stmt_update->execute([$description, $user_id]);
                    }
                }

                // Création session
                $_SESSION['user'] = [
                    'id' => $user_id,
                    'prenom' => $prenom,
                    'nom' => $nom,
                    'email' => $email,
                    'telephone' => $telephone,
                    'service_id' => $service_id,
                    'role' => 'user',
                    'description' => $description
                ];

                if ($isNewUser) {
                    $_SESSION['new_user_registered'] = true;
                }

                header('Location: pageaccueil.php');
                exit;

            } catch (PDOException $e) {
                $error = "Erreur système : " . $e->getMessage();
            }
        } else {
            // Tentative de connexion avec la base de données locale
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email_professionnel = ?");
                $stmt->execute([$username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['mot_de_passe'])) {
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'prenom' => $user['prenom'],
                        'nom' => $user['nom'],
                        'email' => $user['email_professionnel'],
                        'telephone' => $user['telephone'],
                        'service_id' => $user['service_id'],
                        'role' => $user['role'],
                        'description' => $user['description']
                    ];
                    header('Location: pageaccueil.php');
                    exit;
                } else {
                    $error = "Identifiant ou mot de passe incorrect";
                }
            } catch (PDOException $e) {
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