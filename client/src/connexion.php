<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/ldap_auth.php';

$error = '';

function normaliserIdentifiant($input) {
    $input = strtolower(trim($input));
    return str_contains($input, '@') ? $input : $input . '@ville-lisieux.fr';
}

function detecterRoleDepuisGroupes($groupes) {
    $patterns = [
        '/SVC[-_]?INFORMATIQUE/i' => 'SVC-INFORMATIQUE',
        '/ADMIN[-_]?INTRA/i' => 'ADMIN-INTRA',
        '/RH[-_]?DIRECTION/i' => 'RH-DIRECTION'
    ];
    
    foreach ($groupes as $groupe) {
        foreach ($patterns as $pattern => $role) {
            if (preg_match($pattern, $groupe)) {
                return $role;
            }
        }
    }
    return 'user';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = normaliserIdentifiant($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        $login_sans_domaine = str_replace('@ville-lisieux.fr', '', $username);
        
        // 1. Tentative d'authentification AD
        $ldap_data = authentifierEtRecupererInfos($login_sans_domaine, $password);
        
        if ($ldap_data) {
            try {
                $pdo->beginTransaction();
                
                // Récupération des groupes AD
                $groupes = recupererGroupesUtilisateur($login_sans_domaine);
                $role = detecterRoleDepuisGroupes($groupes);
                
                // Gestion du service (double stockage)
                $description_service = $ldap_data['description'] ?? 'Service non défini';
                
              // Par ceci :
// 1. Vérifier d'abord si le service existe
$stmt = $pdo->prepare("SELECT id FROM services WHERE nom = ?");
$stmt->execute([$description_service]);
$service_id = $stmt->fetchColumn();

// 2. Si non existant, l'ajouter
if (!$service_id) {
    $stmt = $pdo->prepare("INSERT INTO services (nom) VALUES (?)");
    $stmt->execute([$description_service]);
    $service_id = $pdo->lastInsertId();
}
                
                // Vérification si l'utilisateur existe déjà
                $stmt = $pdo->prepare("SELECT id, role FROM users WHERE email_professionnel = ?");
                $stmt->execute([$ldap_data['mail']]);
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user_data) {
                    // Mise à jour utilisateur existant
                    $user_id = $user_data['id'];
                    $stmt = $pdo->prepare("UPDATE users SET 
                        nom = ?, 
                        prenom = ?, 
                        telephone = ?, 
                        description = ?,
                        service_id = ?,
                        ldap_groups = ?,
                        mot_de_passe = ?
                        WHERE id = ?");
                    $stmt->execute([
                        $ldap_data['sn'],
                        $ldap_data['givenname'],
                        $ldap_data['telephonenumber'],
                        $description_service,
                        $service_id,
                        json_encode($groupes),
                        password_hash($password, PASSWORD_DEFAULT),
                        $user_id
                    ]);
                } else {
                    // Création nouvel utilisateur
                    $stmt = $pdo->prepare("INSERT INTO users 
                        (email_professionnel, nom, prenom, telephone, description, service_id, role, ldap_groups, mot_de_passe) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $ldap_data['mail'],
                        $ldap_data['sn'],
                        $ldap_data['givenname'],
                        $ldap_data['telephonenumber'],
                        $description_service,
                        $service_id,
                        $role,
                        json_encode($groupes),
                        password_hash($password, PASSWORD_DEFAULT)
                    ]);
                    $user_id = $pdo->lastInsertId();
                }
                
                $pdo->commit();
                
                // Création de la session
                $_SESSION['user'] = [
                    'id' => $user_id,
                    'email' => $ldap_data['mail'],
                    'nom' => $ldap_data['sn'],
                    'prenom' => $ldap_data['givenname'],
                    'role' => $role,
                    'service_id' => $service_id,
                    'service' => $description_service,
                    'ldap_user' => true
                ];
                
                header('Location: pageaccueil.php');
                exit;
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = "Erreur lors de la synchronisation : " . $e->getMessage();
            }
        } else {
            // 2. Fallback : Authentification locale
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email_professionnel = ?");
                $stmt->execute([$username]);
                
                if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (password_verify($password, $user['mot_de_passe'])) {
                        $_SESSION['user'] = [
                            'id' => $user['id'],
                            'email' => $user['email_professionnel'],
                            'nom' => $user['nom'],
                            'prenom' => $user['prenom'],
                            'role' => $user['role'],
                            'service_id' => $user['service_id'],
                            'service' => $user['description'],
                            'ldap_user' => false
                        ];
                        header('Location: pageaccueil.php');
                        exit;
                    }
                }
                $error = "Identifiants incorrects";
            } catch (PDOException $e) {
                $error = "Erreur base de données : " . $e->getMessage();
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
    <title>Connexion | Portail Mairie</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/connexion.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="/projetannuaire/client/src/assets/images/logo_mairie.png" alt="Logo Mairie">
            <h1>Portail de connexion</h1>
        </div>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="username">Identifiant professionnel</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       required
                       placeholder="prenom.nom@ville-lisieux.fr"
                       autocapitalize="off">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required
                       placeholder="••••••••">
            </div>

            <button type="submit" class="btn-login">Se connecter</button>
            
            <div class="links">
                <a href="/mot-de-passe-oublie">Mot de passe oublié ?</a>
                <a href="/nouvel-utilisateur">Créer un compte</a>
            </div>
        </form>
    </div>
</body>
</html>