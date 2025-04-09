<?php
require __DIR__ . '/vendor/autoload.php';

$ldap_dn = "CN=Administrateur,OU=Ville de Lisieux,DC=ville-lisieux,DC=fr";
$ldap_password = ""; 
$ldap_host = "ville-lisieux.fr"; 

echo "<h1>Debug LDAP </h1>";
echo "<h2>Configuration:</h2>";
echo "<p>DN: $ldap_dn</p>";
echo "<p>Host: $ldap_host</p>";

echo "<h2>Étape 1: Connexion LDAP</h2>";
$ldap_connexion = ldap_connect($ldap_host);
if (!$ldap_connexion) {
    die("<p style='color:red'>Échec connexion LDAP</p>");
}
echo "<p style='color:green'>Connexion OK</p>";

echo "<h2>Étape 2: Options LDAP</h2>";
ldap_set_option($ldap_connexion, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldap_connexion, LDAP_OPT_REFERRALS, 0);
echo "<p>Version 3 et referrals désactivés</p>";

echo "<h2>Étape 3: Authentification</h2>";
$bind = @ldap_bind($ldap_connexion, $ldap_dn, $ldap_password);
if (!$bind) {
    die("<p style='color:red'>Échec bind: " . ldap_error($ldap_connexion) . "</p>");
}
echo "<p style='color:green'>Bind réussi</p>";

echo "<h2>Étape 4: Récupération du Base DN (RootDSE)</h2>";
$rootDse = @ldap_read($ldap_connexion, "", "(objectClass=*)", ["defaultNamingContext"]);
$entries = ldap_get_entries($ldap_connexion, $rootDse);

if ($entries["count"] > 0 && isset($entries[0]["defaultnamingcontext"][0])) {
    $base_dn = $entries[0]["defaultnamingcontext"][0];
    echo "<p style='color:green'>Base DN détecté automatiquement: $base_dn</p>";
} else {
    // Utilisation du Base DN explicite si détection échoue
    $base_dn = "DC=ville-lisieux,DC=fr"; 
    echo "<p style='color:red'>Impossible de récupérer le base DN via RootDSE, utilisation du DN par défaut</p>";
}

echo "<h2>Étape 5: Recherche des utilisateurs</h2>";
$filter = "(objectClass=inetOrgPerson)";
$attributes = ["cn"];

$search = @ldap_search($ldap_connexion, $base_dn, $filter, $attributes);
if (!$search) {
    echo "<p style='color:red'>Échec recherche: " . ldap_error($ldap_connexion) . "</p>";
    echo "<p>Code erreur: " . ldap_errno($ldap_connexion) . "</p>";
} else {
    $entries = ldap_get_entries($ldap_connexion, $search);
    if ($entries['count'] == 0) {
        echo "<p style='color:orange'>Aucun utilisateur trouvé</p>";
    } else {
        echo "<h4>Utilisateurs trouvés (" . $entries['count'] . "):</h4>";
        echo "<ul>";
        for ($i = 0; $i < $entries['count']; $i++) {
            $entry = $entries[$i];
            if (isset($entry['cn'][0])) {
                echo "<li>" . htmlspecialchars($entry['cn'][0]) . "</li>";
            } else {
                echo "<li><i>Nom non disponible</i></li>";
            }
        }
        echo "</ul>";
    }
}

echo "<h2>Étape 6: Fermeture</h2>";
ldap_close($ldap_connexion);
echo "<p>Connexion fermée</p>";

echo "<h2>Debug complet:</h2>";
if (isset($entries)) {
    echo "<pre>" . print_r($entries, true) . "</pre>";
} else {
    echo "<p>Aucune donnée à afficher</p>";
}
?>
