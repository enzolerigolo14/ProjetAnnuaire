<?php
function authentifierEtRecupererInfos($login, $password) {
    $ldap_host = "ldap://SVR-HDV-AD.ville-lisieux.fr";
    $ldap_port = 389;
    $ldap_dn = "DC=ville-lisieux,DC=fr";
    $user_upn = $login . "@ville-lisieux.fr";

    $ldap_conn = ldap_connect($ldap_host, $ldap_port);
    if (!$ldap_conn) return false;

    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

    if (@ldap_bind($ldap_conn, $user_upn, $password)) {
        // Recherche de l'utilisateur
        $filter = "(&(objectClass=user)(sAMAccountName=$login))";
        $search = ldap_search($ldap_conn, $ldap_dn, $filter);
        $entries = ldap_get_entries($ldap_conn, $search);

        ldap_unbind($ldap_conn);

        if ($entries["count"] > 0) {
            return $entries[0]; // Données utilisateur AD
        }
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

    $ldap_host = "ldap://SVR-HDV-AD.ville-lisieux.fr";
    $ldap_port = 389;
    $ldap_dn = "DC=ville-lisieux,DC=fr";
    $admin_user = "svcintra@ville-lisieux.fr";
    $admin_pass = "Lisieux14100";

    $ldap_conn = ldap_connect($ldap_host, $ldap_port);
    if (!$ldap_conn) {
        die("Impossible de se connecter au serveur LDAP.");
    }

    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

    if (!@ldap_bind($ldap_conn, $admin_user, $admin_pass)) {
        die("Échec de la connexion LDAP avec l'utilisateur admin.");
    }

    $email_escaped = ldap_escape($email, "", LDAP_ESCAPE_FILTER);
    $filter = "(&(objectClass=user)(mail=$email_escaped))";
    $attributes = ["givenName", "sn", "mail", "telephoneNumber", "description", "memberOf"];

    $search = @ldap_search($ldap_conn, $ldap_dn, $filter, $attributes);
    if (!$search) {
        die("La recherche LDAP a échoué: " . ldap_error($ldap_conn));
    }

    $entries = ldap_get_entries($ldap_conn, $search);
    ldap_unbind($ldap_conn);

    if ($entries["count"] > 0) {
        return $entries[0];
    } else {
        return false;
    }
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

?>
