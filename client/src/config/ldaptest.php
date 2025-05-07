<?php
function getUserGroupsFromLdap($username) {
    $ldap_host = "ldap://SVR-HDV-AD.ville-lisieux.fr";
    $ldap_port = 389;
    $ldap_dn = "DC=ville-lisieux,DC=fr";
    $ldap_user = "svcintra@ville-lisieux.fr";
    $ldap_pass = "Lisieux14100";

    $ldap_conn = ldap_connect($ldap_host, $ldap_port);
    $groupes = [];

    if (!$ldap_conn) return $groupes;

    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

    if (@ldap_bind($ldap_conn, $ldap_user, $ldap_pass)) {
        $filter = "(&(objectClass=user)(sAMAccountName=$username))";
        $result = ldap_search($ldap_conn, $ldap_dn, $filter, ['memberOf']);
        $entries = ldap_get_entries($ldap_conn, $result);

        if ($entries['count'] > 0 && isset($entries[0]['memberof'])) {
            for ($i = 0; $i < $entries[0]['memberof']['count']; $i++) {
                $dn = $entries[0]['memberof'][$i];
                if (preg_match('/^CN=([^,]+)/', $dn, $matches)) {
                    $groupes[] = $matches[1];
                }
            }
        }
        ldap_unbind($ldap_conn);
    }

    return array_unique($groupes);
}
?>