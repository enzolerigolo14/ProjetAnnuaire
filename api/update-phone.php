<?php


ob_start();
require_once __DIR__ . '/../client/src/config/database.php';

session_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
header('Content-Type: application/json');

// Fonction pour valider et nettoyer un numéro de téléphone
function validatePhoneNumber($number, $isInternal = false) {
    $number = preg_replace('/[^0-9]/', '', $number);
    
    if ($isInternal) {
        return (strlen($number) === 4) ? $number : false;
    } else {
        return (strlen($number) === 10 && strpos($number, '0') === 0) ? $number : false;
    }
}

try {
    // Vérification de la méthode HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée', 405);
    }

    // Vérification des droits d'accès
    if (!isset($_SESSION['user']['role']) {
        throw new Exception('Authentification requise', 401);
    }

    // Seuls ces rôles peuvent modifier les numéros
    $allowedRoles = ['SVC-INFORMATIQUE', 'ADMIN-INTRA', 'ADMIN-RH'];
    if (!in_array($_SESSION['user']['role'], $allowedRoles)) {
        throw new Exception('Permissions insuffisantes', 403);
    }

    // Récupération et validation des données
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if ($data === null || json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Données JSON invalides', 400);
    }

    // Vérification des champs obligatoires
    $requiredFields = ['userId', 'field', 'value'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Champ manquant: $field", 400);
        }
    }

    // Validation des champs autorisés
    $allowedFields = ['telephone', 'telephone_internal', 'telephone_perso'];
    if (!in_array($data['field'], $allowedFields)) {
        throw new Exception('Champ non autorisé', 400);
    }

    // Validation des valeurs
    $isInternal = ($data['field'] === 'telephone_internal');
    $cleanedValue = validatePhoneNumber($data['value'], $isInternal);
    
    if ($cleanedValue === false) {
        $errorMsg = $isInternal 
            ? 'Le poste interne doit contenir exactement 4 chiffres' 
            : 'Le numéro doit contenir 10 chiffres et commencer par 0';
        throw new Exception($errorMsg, 400);
    }

    $pdo = getPDO();

    // Vérification que l'utilisateur cible existe
    $stmt = $pdo->prepare("SELECT id, ldap_user FROM users WHERE id = ?");
    $stmt->execute([$data['userId']]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception('Utilisateur non trouvé', 404);
    }

    // Empêcher la modification des utilisateurs LDAP
    if ($user['ldap_user'] == 1) {
        throw new Exception('Impossible de modifier un utilisateur LDAP', 403);
    }

    // Préparation de la requête avec des paramètres nommés pour plus de sécurité
    $stmt = $pdo->prepare("UPDATE users SET {$data['field']} = :value WHERE id = :userId");
    $success = $stmt->execute([
        ':value' => $cleanedValue,
        ':userId' => $data['userId']
    ]);

    if (!$success) {
        throw new Exception('Échec de la mise à jour en base de données', 500);
    }

    // Journalisation de la modification (optionnel)
    error_log("User {$_SESSION['user']['id']} updated phone {$data['field']} for user {$data['userId']}");

    // Réponse en cas de succès
    echo json_encode([
        'success' => true,
        'message' => 'Mise à jour réussie',
        'field' => $data['field'],
        'value' => $cleanedValue
    ]);

} catch (Exception $e) {
    // Gestion des erreurs avec code HTTP approprié
    $code = $e->getCode() ?: 400;
    http_response_code($code);
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => $code
    ]);
}
ob_end_clean();