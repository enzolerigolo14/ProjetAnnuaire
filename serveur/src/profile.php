<?php
require __DIR__ . '/vendor/autoload.php';

$ldap_server = "ldap://ville-lisieux.fr"; // Adresse de votre serveur LDAP
$ldap_port = 389; // Port par défaut pour LDAP
$ldap_dn = "DC=ville-lisieux,DC=fr"; // Base DN de votre AD
$ldap_user = "CN=Service GLP12,OU=Compte de services,OU=Ville de Lisieux,DC=ville-lisieux,DC=fr"; 


$app = new \Slim\App($config);
$app->get('/', function () use ($app) {
    $response = $app->response();
    $response->getBody()->write("Hello, world!");
    return $response;
});
$app->run();

// Connexion à l'AD
$ad = new \Adldap\Adldap($config);
$ad->authenticate();

// Rechercher un utilisateur dans l'AD
$search = $ad->search()->users()->find('jdoe');
var_dump($search);
?>