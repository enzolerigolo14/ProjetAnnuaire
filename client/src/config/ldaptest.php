<?php
$ldap_host = "ldap://SVR-HDV-AD.ville-lisieux.fr"; // Adresse du serveur LDAP
$ldap_port = 389; // Port standard LDAP
$ldap_dn = "DC=ville-lisieux,DC=fr"; // Base DN
$ldap_user = "svcintra@ville-lisieux.fr"; // Utilisateur (UPN)
$ldap_pass = "Lisieux14100"; // Mot de passe

// Connexion au serveur LDAP
$ldap_conn = ldap_connect($ldap_host, $ldap_port);

if (!$ldap_conn) {
    die("Connexion au serveur LDAP échouée.");
}

// Paramètres LDAP
ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

// Authentification
if (@ldap_bind($ldap_conn, $ldap_user, $ldap_pass)) {
    echo "<p>✅ Connexion et authentification réussies à l'Active Directory</p>";

    // Recherche de tous les utilisateurs actifs
    $filter = "(&(objectClass=user)(objectCategory=person)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))";
    $result = ldap_search($ldap_conn, $ldap_dn, $filter, ['memberOf']);
    $entries = ldap_get_entries($ldap_conn, $result);

    $allGroups = [];

    foreach ($entries as $entry) {
        if (isset($entry['memberof'])) {
            for ($i = 0; $i < $entry['memberof']['count']; $i++) {
                $dn = $entry['memberof'][$i];
                if (preg_match('/^CN=([^,]+)/', $dn, $matches)) {
                    $allGroups[] = $matches[1];
                }
            }
        }
    }

    // Supprimer les doublons et trier
    $uniqueGroups = array_unique($allGroups);
    sort($uniqueGroups);

    // Affichage des groupes
    echo "<h2>📋 Liste des rôles/groupes trouvés dans l'AD :</h2>";
    echo "<ul>";
    foreach ($uniqueGroups as $group) {
        echo "<li>" . htmlspecialchars($group) . "</li>";
    }
    echo "</ul>";

} else {
    echo "<p>❌ Échec de l'authentification à l'Active Directory.</p>";
}

// Fermer la connexion
ldap_unbind($ldap_conn);
?>
