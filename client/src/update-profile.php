<?php
session_start();
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

// Debug
file_put_contents('update_log.txt', file_get_contents('php://input'), FILE_APPEND);

// Vérification des permissions
$allowedRoles = ['SVC-INFORMATIQUE', 'ADMIN-INTRA'];
if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], $allowedRoles)) {
    echo json_encode(['success' => false, 'message' => 'Action non autorisée']);
    exit;
}

// Récupération des données
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || empty($data['user_id']) || !is_numeric($data['user_id'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Données invalides']));
}

// Configuration des champs
$fieldConfigs = [
    'telephone' => [
        'column' => 'telephone',
        'validate' => fn($v) => preg_match('/^(\d{4}|[\+\d\s\-\(\)]{10,20})$/', $v)
    ],
    'email' => [
        'column' => 'email_professionnel',
        'validate' => fn($v) => filter_var($v, FILTER_VALIDATE_EMAIL)
    ],
    'nom_complet' => [
        'process' => fn($v) => explode(' ', $v, 2),
        'validate' => fn($v) => count(explode(' ', $v, 2)) === 2
    ],
    'service' => [
        'column' => 'service_id',
        'validate' => fn($v) => is_numeric($v)
    ]
];

// Validation du champ
if (empty($data['field']) || !array_key_exists($data['field'], $fieldConfigs)) {
    echo json_encode(['success' => false, 'message' => 'Champ non autorisé']);
    exit;
}

$config = $fieldConfigs[$data['field']];

// Validation de la valeur
if (isset($config['validate']) && !$config['validate']($data['value'])) {
    echo json_encode(['success' => false, 'message' => 'Valeur invalide']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Traitement spécial pour le nom complet
    if ($data['field'] === 'nom_complet') {
        [$prenom, $nom] = $config['process']($data['value']);
        $stmt = $pdo->prepare("UPDATE users SET prenom = ?, nom = ? WHERE id = ?");
        $stmt->execute([$prenom, $nom, $data['user_id']]);
    } 
    // Vérification email unique
   // Modifiez la partie email comme ceci :
elseif ($data['field'] === 'email') {
    // Vérification plus stricte de l'email existant
    $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email_professionnel = ?");
    $stmtCheck->execute([$data['value']]);
    $existingUser = $stmtCheck->fetch();
    
    if ($existingUser && $existingUser['id'] != $data['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé par un autre utilisateur']);
        exit;
    }

    // Mise à jour de l'email
    $stmt = $pdo->prepare("UPDATE users SET email_professionnel = ? WHERE id = ?");
    $stmt->execute([$data['value'], $data['user_id']]);
    
    // Mise à jour de la session si l'utilisateur modifie son propre email
    if ($_SESSION['user']['id'] == $data['user_id']) {
        $_SESSION['user']['email'] = $data['value'];
    }
}
    // Autres champs
    else {
        $stmt = $pdo->prepare("UPDATE users SET {$config['column']} = ? WHERE id = ?");
        $stmt->execute([$data['value'], $data['user_id']]);
    }

    // Récupération des données mises à jour
    $query = "SELECT u.*, s.nom as service_name FROM users u LEFT JOIN services s ON u.service_id = s.id WHERE u.id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$data['user_id']]);
    $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);


    
    // Détermination de la nouvelle valeur à afficher
    $newValue = match($data['field']) {
        'nom_complet' => $updatedUser['prenom'] . ' ' . $updatedUser['nom'],
        'service' => $updatedUser['service_name'] ?? 'Non spécifié',
        'email' => $updatedUser['email_professionnel'],
        'telephone' => $updatedUser['telephone'],
        default => $data['value']
    };

    $pdo->commit();
    echo json_encode(['success' => true, 'newValue' => $newValue]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Erreur : ' . $e->getMessage()
    ]);
}