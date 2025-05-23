<?php
require_once __DIR__ . '/config/ldap_auth.php';
require_once __DIR__ . '/config/database.php';


// Récupération des utilisateurs LDAP
$usersAD = recupererTousLesUtilisateursAD();

// Récupération des utilisateurs BDD
$stmt = $pdo->prepare("SELECT * FROM users");
$stmt->execute();
$usersDB = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tableau final indexé par email (clé unique)
$finalUsers = [];
// Au début du fichier
$searchTerm = $_GET['search'] ?? '';
if ($searchTerm) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE CONCAT(prenom, ' ', nom) LIKE ? ORDER BY nom, prenom");
    $stmt->execute(["%$searchTerm%"]);
    $membres = $stmt->fetchAll();
    // Afficher ces résultats spécifiques
} else {
    // Afficher la liste normale des membres


// 1. D'abord les utilisateurs AD (prioritaires)
for ($i = 0; $i < $usersAD["count"]; $i++) {
    $email = $usersAD[$i]["mail"][0] ?? null;
    if ($email) {
        $finalUsers[strtolower($email)] = [
            "source" => "ad",
            "prenom" => $usersAD[$i]["givenname"][0] ?? '',
            "nom" => $usersAD[$i]["sn"][0] ?? '',
            "email" => $email,
            "role" => $usersAD[$i]["description"][0] ?? 'Description non disponible'
        ];
    }
}

// 2. Ensuite les utilisateurs BDD (seulement si non déjà présents)
foreach ($usersDB as $user) {
    $email = strtolower($user["email_professionnel"] ?? '');
    if ($email && !isset($finalUsers[$email])) {
        $finalUsers[$email] = [
            "source" => "db",
            "prenom" => $user["prenom"] ?? '',
            "nom" => $user["nom"] ?? '',
            "email" => $user["email_professionnel"] ?? '',
            "role" => $user["role"] ?? 'Rôle non disponible'
        ];
    }
}}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membres Global</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/membreglobal.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    <!--<script src="/projetannuaire/client/script/rechercher.js" defer></script>-->
</head>
<body>

<header>
    <!-- Ajoutez ceci dans membreglobal.php -->
<div class="membre-search-container">
    <input type="text" id="membre-search" placeholder="Rechercher un membre..." autocomplete="off" />
    <div id="membre-results" class="search-results"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('membre-search');
    const resultsContainer = document.getElementById('membre-results');
    
    input.addEventListener('input', async function() {
        const term = input.value.trim();
        if (term.length >= 2) {
            try {
                const response = await fetch(`/projetannuaire/client/src/autocomplete.php?q=${encodeURIComponent(term)}&context=membres`);
                const results = await response.json();
                
                resultsContainer.innerHTML = '';
                
                if (results.length > 0) {
                    results.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'search-result-item';
                        div.textContent = item.name;
                        div.addEventListener('click', () => {
                            window.location.href = `/projetannuaire/client/src/${item.url}`;
                        });
                        resultsContainer.appendChild(div);
                    });
                    resultsContainer.style.display = 'block';
                } else {
                    resultsContainer.style.display = 'none';
                }
            } catch (error) {
                console.error("Erreur recherche:", error);
                resultsContainer.style.display = 'none';
            }
        } else {
            resultsContainer.style.display = 'none';
        }
    });
});
</script>
</header>

<div class="top-button-container"> 
    <button class="top-button" onclick="window.location.href='pageaccueil.php'">← Retour</button>
</div>

<div class="membre-global-header">
    <h1>Membres Global</h1>
</div>

<div class="membre-container">
    <?php foreach ($finalUsers as $user): 
        $email = urlencode($user["email"]);
    ?>
        <div class="membre-card" onclick="window.location='profilutilisateur.php?email=<?= $email ?>&source=<?= $user['source'] ?>'">
            <div class="membre-nom">
                <?= htmlspecialchars($user["prenom"]) ?> <?= htmlspecialchars($user["nom"]) ?>
            </div>
            <div class="membre-role">
                <?= htmlspecialchars($user["email"]) ?><br>
                <?= htmlspecialchars($user["role"]) ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<footer>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</footer>
</body>
</html>
