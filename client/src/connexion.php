<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/ldap_auth.php';
require_once __DIR__ . '/config/ldaptest.php';

$error = '';

// Associer certains groupes à des rôles spécifiques
function detecterRoleLDAP($groupes) {
    $groupesRoles = [
        '/SVC[-_]?INFORMATIQUE/i' => 'SVC-INFORMATIQUE',
        '/ADMIN[-_]?INTRA/i' => 'ADMIN-INTRA',
        //'/DOMAIN\s*ADMINS/i' => 'Admins du domaine',
        //'/ADMINS?\s*DU\s*DOMAINE/i' => 'Admins du domaine'
    ];

    foreach ($groupes as $group) {
        foreach ($groupesRoles as $pattern => $role) {
            if (preg_match($pattern, $group)) {
                return $role;
            }
        }
    }
    return 'membre';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameInput = strtolower(trim($_POST['username'] ?? ''));
$password = trim($_POST['password'] ?? '');
$domain = '@ville-lisieux.fr';

// Ajouter le domaine si absent
if (!str_ends_with($usernameInput, $domain)) {
    $username = $usernameInput . $domain;
} else {
    $username = $usernameInput;
}

    if (empty($username) || empty($password)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        $ldap_data = authentifierEtRecupererInfos($username, $password);

        if ($ldap_data) {
            $prenom = $ldap_data['givenname'];
            $nom = $ldap_data['sn'];
            $email = $ldap_data['mail'];
            $telephone = $ldap_data['telephonenumber'];
            $service = $ldap_data['description'] ?? 'Service non défini';
            $description = $ldap_data['description'] ?? null;

            try {
                // Récupération des groupes via ldaptest.php
                $groupes = getUserGroupsFromLdap($username);
$groupesStr = json_encode($groupes);
$role = detecterRoleLDAP($groupes);


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
                $stmt_user = $pdo->prepare("SELECT id, role, ldap_groups, description FROM users WHERE email_professionnel = ?");
                $stmt_user->execute([$email]);
                $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

                $isNewUser = false;
                if (!$user_data) {
                    // Insertion utilisateur
                    $stmt_insert = $pdo->prepare("INSERT INTO users 
                        (nom, prenom, telephone, email_professionnel, service_id, role, ldap_groups, mot_de_passe, description) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt_insert->execute([
                        $nom, $prenom, $telephone, $email,
                        $service_id, $role, $groupesStr, password_hash($password, PASSWORD_DEFAULT), $description
                    ]);
                    $user_id = $pdo->lastInsertId();
                    $isNewUser = true;
                } else {
                    $user_id = $user_data['id'];

                    // Mise à jour si nécessaire
                    if ($user_data['role'] !== $role) {
                        $stmt_update = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                        $stmt_update->execute([$role, $user_id]);
                    }

                    if (empty($user_data['description']) && !empty($description)) {
                        $stmt_update = $pdo->prepare("UPDATE users SET description = ? WHERE id = ?");
                        $stmt_update->execute([$description, $user_id]);
                    }

                    if ($user_data['ldap_groups'] !== $groupesStr) {
                        $stmt_update = $pdo->prepare("UPDATE users SET ldap_groups = ? WHERE id = ?");
                        $stmt_update->execute([$groupesStr, $user_id]);
                    }
                }

                $_SESSION['user'] = [
                    'id' => $user_id,
                    'prenom' => $prenom,
                    'nom' => $nom,
                    'email' => $email,
                    'telephone' => $telephone,
                    'service_id' => $service_id,
                    'role' => $role,
                    'description' => $description,
                    'groups' => $groupes
                ];

                if ($isNewUser) {
                    $_SESSION['new_user_registered'] = true;
                }

                header('Location: pageaccueil.php');
                exit;

            } catch (Exception $e) {
                $error = "Erreur système : " . $e->getMessage();
            }
        } else {
            // Connexion locale
            try {
                // 1. Vérification dans la table inscription
                $stmt_inscription = $pdo->prepare("SELECT * FROM inscription WHERE email_professionnel = ?");
                $stmt_inscription->execute([$username]);
                $inscription_user = $stmt_inscription->fetch(PDO::FETCH_ASSOC);
            
                if ($inscription_user) {
                    // Utilisateur trouvé dans inscription
                    if (password_verify($password, $inscription_user['mot_de_passe'])) {
                        $_SESSION['temp_user_id'] = $inscription_user['id'];
                        $_SESSION['nom'] = $inscription_user['nom'];
                        $_SESSION['prenom'] = $inscription_user['prenom'];
                        $_SESSION['email'] = $inscription_user['email_professionnel'];
                        $_SESSION['service_id'] = $inscription_user['service_id'];
                    
                        header('Location: change_password.php');
                        exit;
                    } else {
                        $error = "Mot de passe incorrect.";
                    }
                    
                } else {
                    // 2. Vérification normale dans users
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE email_professionnel = ?");
                    $stmt->execute([$username]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
                    if ($user && password_verify($password, $user['mot_de_passe'])) {
                        // Connexion standard
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
                        $error = "Identifiants invalides";
                    }
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