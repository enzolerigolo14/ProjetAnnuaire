<?php

require_once 'config/database.php';
session_start();
function authentifierEtRecupererInfos($login, $password) {
    $ldap_host = "ldap://SVR-HDV-AD.ville-lisieux.fr";
    $ldap_port = 389;
    $ldap_dn = "DC=ville-lisieux,DC=fr";
    $user_upn = $login . "@ville-lisieux.fr";

    $ldap_conn = ldap_connect($ldap_host, $ldap_port);
    if (!$ldap_conn) {
        return false;
    }

    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

    if (@ldap_bind($ldap_conn, $user_upn, $password)) {
        // Ajout de l'attribut department
        $filter = "(&(objectClass=user)(sAMAccountName=$login))";
        $attributes = ["givenname", "sn", "mail", "telephonenumber", "description"];
        $search = ldap_search($ldap_conn, $ldap_dn, $filter, $attributes);
        $entries = ldap_get_entries($ldap_conn, $search);

        ldap_unbind($ldap_conn);

        if ($entries["count"] > 0) {
            return [
                'givenname' => $entries[0]['givenname'][0] ?? '',
                'sn' => $entries[0]['sn'][0] ?? '',
                'mail' => $entries[0]['mail'][0] ?? '',
                'telephonenumber' => $entries[0]['telephonenumber'][0] ?? '',
                'description' => $entries[0]['description'][0] ?? 'Service non défini' // Modification ici
            ];
        }
        return false;
    }
    ldap_unbind($ldap_conn);
    return false;
}



function recupererTousLesUtilisateursAD() {
    $ldap_host = "ldap://SVR-HDV-AD.ville-lisieux.fr";
    $ldap_port = 389;
    $ldap_dn = "DC=ville-lisieux,DC=fr";
    $admin_user = "svcintra@ville-lisieux.fr";
    $admin_pass = "Lisieux14100"; 

    $ldap_conn = ldap_connect($ldap_host, $ldap_port);
    if (!$ldap_conn) return [];

    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

    if (@ldap_bind($ldap_conn, $admin_user, $admin_pass)) {
        $filter = "(&(objectClass=user)(objectCategory=person)(!(userAccountControl:1.2.840.113556.1.4.803:=2))(mail=*))"; 
        $attributes = ["givenName", "sn", "mail", "telephoneNumber","description"]; 
        $search = ldap_search($ldap_conn, $ldap_dn, $filter, $attributes);

        if ($search) {
            $entries = ldap_get_entries($ldap_conn, $search);
            ldap_unbind($ldap_conn);
            return $entries;
        }
    }

    ldap_unbind($ldap_conn);
    return [];
}

function recupererUtilisateurParEmail($email) {
    if (empty($email)) {
        die("L'adresse email est manquante.");
    }

    // --- Recherche LDAP ---
    $ldap_host = "ldap://SVR-HDV-AD.ville-lisieux.fr";
    $ldap_port = 389;
    $ldap_dn = "DC=ville-lisieux,DC=fr";
    $admin_user = "svcintra@ville-lisieux.fr";
    $admin_pass = "Lisieux14100";

    $ldap_conn = ldap_connect($ldap_host, $ldap_port);
    if ($ldap_conn) {
        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

        if (@ldap_bind($ldap_conn, $admin_user, $admin_pass)) {
            $email_escaped = ldap_escape($email, "", LDAP_ESCAPE_FILTER);
            $filter = "(&(objectClass=user)(mail=$email_escaped))";
            $attributes = ["givenName", "sn", "mail", "telephoneNumber", "description", "memberOf"];

            $search = @ldap_search($ldap_conn, $ldap_dn, $filter, $attributes);
            if ($search) {
                $entries = ldap_get_entries($ldap_conn, $search);
                ldap_unbind($ldap_conn);

                if ($entries["count"] > 0) {
                    return $entries[0]; // Utilisateur trouvé dans LDAP
                }
            }
        }
    }

    // --- Sinon, on cherche dans la base de données ---
    require __DIR__ . '/config/database.php'; // S'assurer que $pdo est dispo ici

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email_professionnel = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $userDB = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userDB) {
        // On adapte pour que ce soit "compatibles" avec ce qu’attend la page profil
        return [
            'givenname' => [$userDB['prenom']],
            'sn' => [$userDB['nom']],
            'mail' => [$userDB['email_professionnel']],
            'telephonenumber' => [$userDB['telephone'] ?? 'Non renseigné'],
            'description' => [$userDB['role'] ?? 'Non spécifié'],
            'memberof' => [] // Vide par défaut
        ];
    }

    return false; // Rien trouvé
}






function recupererUtilisateursParServiceAD($nomGroupe) {
    $ldap_host = "ldap://SVR-HDV-AD.ville-lisieux.fr";
    $ldap_port = 389;
    $base_dn = "DC=ville-lisieux,DC=fr";
    $admin_user = "svcintra@ville-lisieux.fr";
    $admin_pass = "Lisieux14100";

    // Connexion
    $ldap = ldap_connect($ldap_host, $ldap_port);
    if (!$ldap) {
        error_log("Erreur connexion LDAP");
        return [];
    }

    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

    if (!@ldap_bind($ldap, $admin_user, $admin_pass)) {
        error_log("Erreur authentification LDAP");
        return [];
    }

    // 1. Recherche du groupe exact
    $group_filter = "(&(objectClass=group)(cn=$nomGroupe))";
    $group_search = ldap_search($ldap, $base_dn, $group_filter, ["cn", "distinguishedname"]);
    $group_info = ldap_get_entries($ldap, $group_search);

    if ($group_info["count"] == 0) {
        error_log("Groupe '$nomGroupe' introuvable dans l'AD");
        ldap_unbind($ldap);
        return [];
    }

    $group_dn = $group_info[0]["distinguishedname"][0];
    error_log("Groupe trouvé: $group_dn");

    // 2. Recherche des membres
    $filter = "(memberOf:1.2.840.113556.1.4.1941:=$group_dn)";
    $attrs = ["givenname", "sn", "mail", "description"];
    $search = ldap_search($ldap, $base_dn, $filter, $attrs);
    $members = ldap_get_entries($ldap, $search);

    ldap_unbind($ldap);

    // Formatage des résultats
    $result = [];
    for ($i = 0; $i < $members["count"]; $i++) {
        $result[] = [
            'givenname' => [$members[$i]['givenname'][0] ?? ''],
            'sn' => [$members[$i]['sn'][0] ?? ''],
            'mail' => [$members[$i]['mail'][0] ?? ''],
            'description' => [$members[$i]['description'][0] ?? ''],
            'telephonenumber' => [$members[$i]['telephonenumber'][0] ?? '']
        ];
    }

    return $result;
}


function afficherInfosUtilisateur() {
    if (!isset($_SESSION['user']['login'])) {
        return;
    }

    // Récupération des infos de base depuis la session
    $user = $_SESSION['user'];
    $nom = htmlspecialchars($user['nom'] ?? 'Non renseigné');
    $prenom = htmlspecialchars($user['prenom'] ?? 'Non renseigné');
    
    // Récupération de l'email depuis LDAP (version optimisée)
    $email = '';
    $login = $_SESSION['user']['login'];
    
    if (!empty($login)) {
        $ldap_conn = ldap_connect("ldap://SVR-HDV-AD.ville-lisieux.fr", 389);
        if ($ldap_conn) {
            ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
            
            if (@ldap_bind($ldap_conn, "svcintra@ville-lisieux.fr", "Lisieux14100")) {
                $filter = "(sAMAccountName=" . ldap_escape($login, "", LDAP_ESCAPE_FILTER) . ")";
                $search = ldap_search($ldap_conn, "DC=ville-lisieux,DC=fr", $filter, ["mail"]);
                $entries = ldap_get_entries($ldap_conn, $search);
                
                if ($entries["count"] > 0 && !empty($entries[0]["mail"][0])) {
                    $email = htmlspecialchars($entries[0]["mail"][0]);
                }
            }
            ldap_unbind($ldap_conn);
        }
    }

    // Affichage simple et propre
    echo '<div class="user-info-box" style="padding:15px; margin:20px 0; border:1px solid #ddd; background:#f8f9fa; border-radius:5px;">';
    echo '<h4 style="margin-top:0;">Informations utilisateur</h4>';
    echo '<p><strong>Nom :</strong> ' . $nom . '</p>';
    echo '<p><strong>Prénom :</strong> ' . $prenom . '</p>';
    echo '<p><strong>Email :</strong> ' . ($email ?: 'Non trouvé dans l\'AD') . '</p>';
    echo '</div>';
}

// Utilisation
afficherInfosUtilisateur();




?>

