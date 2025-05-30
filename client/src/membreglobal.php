<?php
require_once __DIR__ . '/config/ldap_auth.php';
require_once __DIR__ . '/config/database.php';

// Au début du fichier, récupérer la référence de la page précédente
$from = $_GET['from'] ?? 'accueil';
$backUrl = ($from === 'parametre') ? 'parametre.php' : 'pageaccueil.php';

$usersAD = recupererTousLesUtilisateursAD();

$stmt = $pdo->prepare("SELECT * FROM users");
$stmt->execute();
$usersDB = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tableau final indexé par email (clé unique)
$finalUsers = [];

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
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membres Global</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/membreglobal.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
</head>
<body>

<header>
<div class="membre-search-container">
    <input type="text" id="membre-search" placeholder="Rechercher un membre..." autocomplete="off" />
    <div id="membre-results" class="search-results"></div>
</div>
</header>

<div class="top-button-container"> 
    <button class="top-button" onclick="window.location.href='<?= $backUrl ?>'">← Retour</button>
</div>

<div class="membre-global-header">
    <h1>Membres Global</h1>
</div>

<div class="membre-container" id="membre-list">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('membre-search');
    const resultsContainer = document.getElementById('membre-results');
    const membreList = document.getElementById('membre-list');
    let originalMembers = membreList.cloneNode(true);

    let searchTimeout;

    input.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const term = input.value.trim().toLowerCase();
    const cards = document.querySelectorAll('.membre-card');
    const noResultsMsgId = "no-results-msg";

    searchTimeout = setTimeout(() => {
        let hasResults = false;

        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            if (text.includes(term)) {
                card.style.display = 'block';
                hasResults = true;
            } else {
                card.style.display = 'none';
            }
        });

        // Supprimer message précédent s'il existe
        const existingMsg = document.getElementById(noResultsMsgId);
        if (existingMsg) {
            existingMsg.remove();
        }

        // Ajouter message si aucun résultat
        if (!hasResults) {
            const msg = document.createElement('div');
            msg.id = noResultsMsgId;
            msg.className = 'no-results';
            msg.textContent = 'Aucun membre trouvé';
            membreList.appendChild(msg);
        }
    }, 300);
});



    function fetchSearchResults(term) {
        fetch(`/projetannuaire/client/src/autocomplete.php?q=${encodeURIComponent(term)}&context=membres`)
            .then(response => response.json())
            .then(results => {
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
                    //membreList.innerHTML = '<div class="no-results">Aucun résultat trouvé</div>';
                    membreList.appendChild(originalMembers.cloneNode(true));
                }
            })
            .catch(error => {
                console.error("Erreur recherche:", error);
                resultsContainer.style.display = 'none';
            });
    }
    
    // Cacher les résultats quand on clique ailleurs
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.style.display = 'none';
        }
    });
});
</script>
</body>
</html>