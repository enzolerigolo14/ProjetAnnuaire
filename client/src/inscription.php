<?php 
session_start(); // Démarrer la session
require_once __DIR__ . '/config/database.php';

function genererMotDePasse($longueur = 10) {
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($caracteres), 0, $longueur);
}

// Récupération des services
$services = $pdo->query("SELECT id, nom FROM services ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
$servicesIds = array_column($services, 'id');

// Traitement du formulaire
$error = '';
$motDePasse = genererMotDePasse();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = htmlspecialchars($_POST['firstname'] ?? '');
    $lastname = htmlspecialchars($_POST['lastname'] ?? '');
    $fullname = htmlspecialchars($_POST['fullname'] ?? '');
    $loginname = strtolower(htmlspecialchars($_POST['loginname'] ?? ''));
    $service_id = isset($_POST['service']) ? (int)$_POST['service'] : 0;
    $motDePasse = $_POST['password'] ?? genererMotDePasse();

    // Validation
    if (empty($firstname) || empty($lastname) || empty($loginname)) {
        $error = "Tous les champs sont obligatoires";
    } elseif (!in_array($service_id, $servicesIds)) {
        $error = "Service invalide";
    } else {
       
        try {
            // Génération du mot de passe temporaire
            $motDePasse = genererMotDePasse();
            $motDePasseHash = password_hash($motDePasse, PASSWORD_BCRYPT);
    
            // Insertion dans la table inscription
            $stmt = $pdo->prepare("INSERT INTO inscription 
                                 (nom, prenom, email_professionnel, service_id, mot_de_passe) 
                                 VALUES (:nom, :prenom, :email, :service_id, :mot_de_passe)");
            
            $stmt->execute([
                ':nom' => $lastname,
                ':prenom' => $firstname,
                ':email' => $loginname,
                ':service_id' => $service_id,
                ':mot_de_passe' => $motDePasseHash
            ]);
    
            // Stockage des données en session
            $_SESSION['nouvelle_inscription'] = [
                'id' => $pdo->lastInsertId(),
                'password_temp' => $motDePasse
            ];
    
            // Redirection immédiate vers change_password.php
            header('Location: change_password.php');
            exit;
    
        } catch (PDOException $e) {
            $error = "Erreur base de données : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription | Trombinoscope Ville de Lisieux</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/inscription.css">
    <script src="/projetannuaire/client/script/inscription.js" defer></script>
</head>
<body>
    <div id="inscription-modal" class="modal">
        <div class="modal-content">
            <div class="login-header">
                <h2>Inscription</h2>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="firstname">Prénom</label>
                    <input type="text" id="firstname" name="firstname" required
                        placeholder="ex: Jean"
                        value="<?= htmlspecialchars($_POST['firstname'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="lastname">Nom</label>
                    <input type="text" id="lastname" name="lastname" required
                        placeholder="ex: Dupont"
                        value="<?= htmlspecialchars($_POST['lastname'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="text" id="password" name="password" required readonly
                        value="<?= htmlspecialchars($motDePasse) ?>">
                </div>

                <div class="form-group">
                    <label for="fullname">Nom complet</label>
                    <input type="text" id="fullname" name="fullname" required readonly
                        placeholder="ex: Jean Dupont"
                        value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="loginprefix">Nom d'ouverture de session</label>
                    <div class="login-container">
                        <input type="text" 
                               id="loginprefix" 
                               name="loginprefix"
                               required
                               placeholder="ex: jdupont"
                               value="<?= htmlspecialchars($_POST['loginprefix'] ?? '') ?>">
                        <input type="text" 
                               id="logindomain" 
                               value="@ville-lisieux.fr" 
                               disabled>
                    </div>
                </div>

                <div class="form-group">
                    <label for="loginname">Adresse complète</label>
                    <input type="text" 
                           id="loginname" 
                           name="loginname" 
                           required 
                           readonly
                           placeholder="ex: jdupont@ville-lisieux.fr"
                           value="<?= htmlspecialchars($_POST['loginname'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="service">Service</label>
                    <select id="service" name="service" required>
                        <option value="">-- Sélectionnez un service --</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?= $service['id'] ?>" 
                                <?= (isset($_POST['service']) && $_POST['service'] == $service['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($service['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="submit-btn">Inscrire</button>
            </form>
        </div>
    </div>
</body>
</html>