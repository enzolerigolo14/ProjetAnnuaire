<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/ldap_auth.php';



$error = '';
$error_type = ''; 

function normaliserIdentifiant($input) {
    $input = strtolower(trim($input));
    return str_contains($input, '@') ? $input : $input . '@ville-lisieux.fr';
}

function detecterRoleDepuisGroupes($groupes) {
    $patterns = [
        '/SVC[-_]?INFORMATIQUE/i' => 'SVC-INFORMATIQUE',
        '/ADMIN[-_]?INTRA/i' => 'ADMIN-INTRA',
        '/ADMIN[-_]?RH/i' => 'ADMIN-RH',
    ];
    
    foreach ($groupes as $groupe) {
        foreach ($patterns as $pattern => $role) {
            if (preg_match($pattern, $groupe)) {
                return $role;
            }
        }
    }
    return 'membre';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = normaliserIdentifiant($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Veuillez remplir tous les champs";
        $error_type = 'generic';
    } else {
        $login_sans_domaine = str_replace('@ville-lisieux.fr', '', $username);
        
        // 1. Tentative d'authentification AD
        $ldap_data = authentifierEtRecupererInfos($login_sans_domaine, $password);
        
        if ($ldap_data) {
            try {
                $pdo->beginTransaction();
                
                $groupes = recupererGroupesUtilisateur($login_sans_domaine);
                $role = detecterRoleDepuisGroupes($groupes);
                // Par cette version simplifiée :
$description_service = $ldap_data['description'] ?? 'Service non défini';
$service_id = null; // On ne lie à aucun service spécifique

// Seulement si le service est explicitement défini dans l'AD, on le cherche/crée
if (!empty($ldap_data['description'])) {
    $stmt = $pdo->prepare("SELECT id FROM services WHERE nom = ?");
    $stmt->execute([$ldap_data['description']]);
    $service_id = $stmt->fetchColumn();

    if (!$service_id) {
        $stmt = $pdo->prepare("INSERT INTO services (nom) VALUES (?)");
        $stmt->execute([$ldap_data['description']]);
        $service_id = $pdo->lastInsertId();
    }
}
                
                // Vérification utilisateur existant
                $stmt = $pdo->prepare("SELECT id, role FROM users WHERE email_professionnel = ?");
                $stmt->execute([$ldap_data['mail']]);
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user_data) {
    $user_id = $user_data['id'];
    // TOUJOURS utiliser le rôle de la base de données plutôt que celui détecté depuis l'AD
    $role = $user_data['role']; 
    
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
    
    // Création de la session avec le rôle de la base
    $_SESSION['user'] = [
        'id' => $user_id,
        'email' => $ldap_data['mail'],
        'nom' => $ldap_data['sn'],
        'prenom' => $ldap_data['givenname'],
        'role' => $role, // <-- Ici on utilise le rôle de la base
        'service_id' => $service_id,
        'service' => $description_service,
        'ldap_user' => true
    ];
} else {
    // Pour les nouveaux utilisateurs AD - CORRECTION: une seule insertion
    $stmt = $pdo->prepare("INSERT INTO users 
        (email_professionnel, nom, prenom, telephone, description, service_id, role, ldap_groups, mot_de_passe, ldap_user) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
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
                $error_type = 'generic';
            }
        } else {
            // 2. Authentification locale
            try {
                $exists_in_ad = verifierExistenceAD($username);
                
                if ($exists_in_ad) {
                    $error = "Votre compte AD existe mais le mot de passe est incorrect. Contactez le service informatique.";
                    $error_type = 'ad_user';
                } else {
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE email_professionnel = ?");
                    $stmt->execute([$username]);
                    
                    if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Vérification si c'est un compte local (non AD)
    if (empty($user['ldap_user'])) {
        // Compte local - vérification directe sans hash
        if ($password === $user['mot_de_passe']) {  // Comparaison en clair
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email_professionnel'],
                'nom' => $user['nom'],
                'prenom' => $user['prenom'],
                'role' => $user['role'],
                'service_id' => $user['service_id'],
                'service' => $user['description'],
                'ldap_user' => false,
                'password_plain' => $user['mot_de_passe'] // Stockage temporaire
            ];
            
            header('Location: pageaccueil.php');
            exit;
        } else {
            $error = "Mot de passe incorrect pour cet utilisateur local.";
            $error_type = 'local_user';
        }
    } else {
        // Compte AD - vérification avec password_verify
        if (password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email_professionnel'],
                'nom' => $user['nom'],
                'prenom' => $user['prenom'],
                'role' => $user['role'],
                'service_id' => $user['service_id'],
                'service' => $user['description'],
                'ldap_user' => true
            ];
            
            header('Location: pageaccueil.php');
            exit;
        } else {
            $error = "Mot de passe AD incorrect.";
            $error_type = 'ad_user';
        }
    }
} else {
                        $error = "Identifiants incorrects - Compte introuvable";
                        $error_type = 'generic';
                    }
                }
            } catch (Exception $e) {
                $error = "Erreur lors de la vérification : " . $e->getMessage();
                $error_type = 'generic';
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
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .close-modal {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close-modal:hover {
            color: black;
        }
        
        .modal-title {
            margin-top: 0;
            color: #d9534f;
        }
        
        .modal-actions {
            margin-top: 20px;
            text-align: right;
        }
        
        .modal-actions button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .modal-actions .btn-confirm {
            background-color: #d9534f;
            color: white;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
            
            <div id="errorModal" class="modal" style="display: block;">
                <div class="modal-content">
                    <span class="close-modal" onclick="document.getElementById('errorModal').style.display='none'">&times;</span>
                    <h3 class="modal-title">Erreur de connexion</h3>
                    <p>
                        <?php 
                        if ($error_type === 'ad_user') {
                            echo "Vous êtes authentifié via l'Active Directory. Veuillez :<br><br>
                            - Vérifier votre mot de passe<br>
                            - Contacter le service informatique si besoin";
                        } elseif ($error_type === 'local_user') {
                            echo "Vous avez un compte local. Veuillez :<br><br>
                            - Vérifier votre mot de passe<br>
                            - Utiliser 'Mot de passe oublié' si besoin";
                        } else {
                            echo htmlspecialchars($error);
                        }
                        ?>
                    </p>
                    <div class="modal-actions">
                        <button class="btn-confirm" onclick="document.getElementById('errorModal').style.display='none'">OK</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="username">Identifiant professionnel</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       required
                       placeholder="identifiant windows"
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
        </form>
    </div>

    <script>
        window.onclick = function(event) {
            var modal = document.getElementById('errorModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>