<?php
session_start();
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');
header("Cache-Control: no-cache, must-revalidate");

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

// Configuration des champs avec validation améliorée
$fieldConfigs = [
    'telephone' => [
        'column' => 'telephone',
        'validate' => fn($v) => preg_match('/^[\d\s\-\+\(\)]{10,20}$/', $v),
        'sanitize' => fn($v) => preg_replace('/[^\d\+]/', '', $v)
    ],
    'email' => [
        'column' => 'email_professionnel',
        'validate' => fn($v) => filter_var($v, FILTER_VALIDATE_EMAIL),
        'unique' => true
    ],
    'nom_complet' => [
        'process' => fn($v) => array_map('trim', explode(' ', $v, 2)),
        'validate' => fn($v) => count(explode(' ', $v, 2)) === 2
    ],
    'service' => [
        'column' => 'service_id',
        'validate' => function($v) use ($pdo) {
            if (!is_numeric($v)) return false;
            $stmt = $pdo->prepare("SELECT 1 FROM services WHERE id = ?");
            $stmt->execute([$v]);
            return (bool)$stmt->fetch();
        }
    ]
];

// Validation du champ
if (empty($data['field']) || !array_key_exists($data['field'], $fieldConfigs)) {
    echo json_encode(['success' => false, 'message' => 'Champ non autorisé']);
    exit;
}

$config = $fieldConfigs[$data['field']];
$value = trim($data['value']);

// Validation de la valeur
if (isset($config['validate']) && !$config['validate']($value)) {
    echo json_encode(['success' => false, 'message' => 'Valeur invalide']);
    exit;
}

// Nettoyage de la valeur si nécessaire
if (isset($config['sanitize'])) {
    $value = $config['sanitize']($value);
}

try {
    $pdo->beginTransaction();

    // Vérification d'unicité pour l'email
    if (!empty($config['unique'])) {
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE {$config['column']} = ? AND id != ?");
        $stmtCheck->execute([$value, $data['user_id']]);
        if ($stmtCheck->fetch()) {
            throw new PDOException("Cette valeur est déjà utilisée par un autre utilisateur");
        }
    }

    // Traitement spécial pour chaque champ
    switch ($data['field']) {
        case 'nom_complet':
            [$prenom, $nom] = $config['process']($value);
            $stmt = $pdo->prepare("UPDATE users SET prenom = ?, nom = ? WHERE id = ?");
            $stmt->execute([$prenom, $nom, $data['user_id']]);
            break;
            
        case 'service':
            // Validation supplémentaire pour le service
            $stmt = $pdo->prepare("SELECT nom FROM services WHERE id = ?");
            $stmt->execute([$value]);
            $service = $stmt->fetch();
            
            if (!$service) {
                throw new PDOException("Service invalide");
            }
            
            $stmt = $pdo->prepare("UPDATE users SET service_id = ? WHERE id = ?");
            $stmt->execute([$value, $data['user_id']]);
            break;
            
        default:
            $stmt = $pdo->prepare("UPDATE users SET {$config['column']} = ? WHERE id = ?");
            $stmt->execute([$value, $data['user_id']]);
    }

    // Mise à jour de la session si nécessaire
    if ($data['field'] === 'email' && $_SESSION['user']['id'] == $data['user_id']) {
        $_SESSION['user']['email'] = $value;
    }

    // Récupération des données mises à jour avec jointure sur le service
    $query = "SELECT u.*, s.nom as service_name FROM users u LEFT JOIN services s ON u.service_id = s.id WHERE u.id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$data['user_id']]);
    $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$updatedUser) {
        throw new PDOException("Utilisateur non trouvé après mise à jour");
    }

    $pdo->commit();

    // Préparation de la réponse
    $response = [
        'success' => true,
        'newValue' => match($data['field']) {
            'nom_complet' => $updatedUser['prenom'] . ' ' . $updatedUser['nom'],
            'service' => $updatedUser['service_name'] ?? 'Non spécifié',
            'email' => $updatedUser['email_professionnel'],
            'telephone' => $updatedUser['telephone'],
            default => $value
        },
        'field' => $data['field']
    ];

    // Ajout des infos service si nécessaire
    if ($data['field'] === 'service') {
        $response['serviceId'] = $updatedUser['service_id'];
        $response['serviceName'] = $updatedUser['service_name'];
    }

    echo json_encode($response);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Erreur : ' . $e->getMessage(),
        'field' => $data['field'] ?? null
    ]);
}