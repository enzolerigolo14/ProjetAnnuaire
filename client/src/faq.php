<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Récupérer les questions existantes
$questions = $pdo->query("SELECT * FROM faq ORDER BY date_creation DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Ville de Lisieux</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/faq.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/header.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/projetannuaire/client/script/faq.js" defer></script>
</head>
<body>
    <header>
        <?php include __DIR__ . '/includes/header.php'; ?>
    </header>
    

    <div class="faq-container">
        <h1>Foire aux questions</h1>
        
        <!-- Formulaire d'ajout de question -->
        <div class="question-form">
            <textarea id="nouvelle-question" placeholder="Posez votre question..."></textarea>
            <button id="envoyer-question">Envoyer</button>
        </div>

        <!-- Liste des questions -->
        <div id="questions-list">
            <?php foreach ($questions as $q): ?>
                <div class="question-item" data-id="<?= $q['id'] ?>">
                    <div class="question-header">
                        <div class="question"><?= htmlspecialchars($q['question']) ?></div>
                        <div class="date"><?= date('d/m/Y H:i', strtotime($q['date_creation'])) ?></div>
                    </div>
                    
                    <div class="reponse-container">
                        <div class="reponse"><?= htmlspecialchars($q['reponse'] ?? 'En attente de réponse...') ?></div>
                        
                        <?php if (isset($_SESSION['user'])): ?>
                            <?php if (!empty($q['reponse'])): ?>
                                <!-- Bouton désactivé si réponse existe -->
                                <button class="btn-repondre btn-repondu" disabled>Répondu</button>
                            <?php else: ?>
                                <!-- Bouton actif si pas de réponse -->
                                <button class="btn-repondre">Répondre</button>
                                <div class="reponse-form" style="display:none;">
                                    <textarea class="reponse-input" placeholder="Votre réponse..."></textarea>
                                    <button class="btn-envoyer-reponse">Envoyer</button>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <footer>
        <?php include __DIR__ . '/includes/footer.php'; ?>
    </footer>
    
</body>
</html>