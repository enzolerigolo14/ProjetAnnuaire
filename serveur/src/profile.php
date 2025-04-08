<?php
require __DIR__ . '/vendor/autoload.php';

// Exemple d'utilisation (configuration AD)
$config = [
    'hosts'    => ['ville-lisieux.fr'],
    'base_dn'  => 'dc=domain,dc=com',
    'username' => 'admin@domain.com',
    'password' => 'votre_mdp',
];

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


// ... (voir la doc Adldap2 pour la suite)