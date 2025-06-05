<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Vérification de l'ID du service
$service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
if ($service_id === 0) {
    header("Location: membresservicesfaq.php");
    exit();
}

$user_service_id = null;
if (isset($_SESSION['user']['id'])) {
    $stmt = $pdo->prepare("SELECT service_id FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $user_service_id = $stmt->fetchColumn();
}

// Récupération des infos du service
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    die("Service non trouvé");
}

// Vérification des droits (uniquement ADMIN-INTRA et SVC-INFORMATIQUE peuvent répondre)
$canAnswer = false;
if (isset($_SESSION['user']['role'])) {
    // L'utilisateur peut répondre seulement si:
    // 1. Il a le bon rôle ET
    // 2. Soit il est dans le même service, soit c'est un SVC-INFORMATIQUE (qui peut répondre partout)
    $canAnswer = in_array($_SESSION['user']['role'], ['ADMIN-INTRA', 'SVC-INFORMATIQUE']) && 
                ($_SESSION['user']['role'] === 'SVC-INFORMATIQUE' || $user_service_id == $service_id);
}

// Récupération des questions pour ce service
$stmt = $pdo->prepare("
    SELECT f.*, u.prenom, u.nom 
    FROM faq f
    LEFT JOIN users u ON f.user_id = u.id
    WHERE f.service_id = ?
    ORDER BY f.date_creation DESC
");
$stmt->execute([$service_id]);
$questions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>FAQ - <?= htmlspecialchars($service['nom']) ?></title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/faq.css">
</head>
<body>

<div class="header">
  <h1>FAQ - <?= htmlspecialchars($service['nom']) ?></h1>
  <div class="back-button-container">
    <a href="membresservicesfaq.php" class="back-button">← Retour à la liste des services</a>
  </div>
</div>


<div class="faq-container">
    <!-- Formulaire pour poser une question -->
    <div class="question-form">
        <textarea id="nouvelle-question" placeholder="Posez votre question..."></textarea>
        <button id="envoyer-question">Envoyer</button>
    </div>

    <!-- Liste des questions -->
    <div class="questions-list">
        <?php foreach ($questions as $q): ?>
            <div class="question-item">
                <div class="question-header">
                    <div class="question-text"><?= htmlspecialchars($q['question']) ?></div>
                    <div class="question-meta">
                        <span class="author">
                            <?= $q['user_id'] ? htmlspecialchars($q['prenom'].' '.$q['nom']) : 'Anonyme' ?>
                        </span>
                        <span class="date">
                            <?= date('d/m/Y H:i', strtotime($q['date_creation'])) ?>
                        </span>
                    </div>
                </div>
                
                <?php if (!empty($q['reponse'])): ?>
                    <div class="reponse">
                        <strong>Réponse :</strong> <?= htmlspecialchars($q['reponse']) ?>
                        <div class="reponse-date">
                            Répondu le <?= date('d/m/Y H:i', strtotime($q['date_reponse'])) ?>
                        </div>
                    </div>
                <?php elseif ($canAnswer): ?>
                    <div class="reponse-form">
                        <textarea class="reponse-input" placeholder="Votre réponse..."></textarea>
                        <button class="btn-repondre" data-question-id="<?= $q['id'] ?>">Répondre</button>
                    </div>
                <?php else: ?>
                    <div class="reponse-pending">En attente de réponse...</div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Script pour gérer l'envoi des questions et réponses
document.addEventListener('DOMContentLoaded', function() {
    // Envoyer une nouvelle question
    document.getElementById('envoyer-question').addEventListener('click', function() {
        const question = document.getElementById('nouvelle-question').value.trim();
        if (question) {
            fetch('ajouter_question.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'question=' + encodeURIComponent(question) + '&service_id=<?= $service_id ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur: ' + data.message);
                }
            });
        }
    });

    // Répondre à une question
    document.querySelectorAll('.btn-repondre').forEach(btn => {
        btn.addEventListener('click', function() {
            const questionId = this.dataset.questionId;
            const reponse = this.parentElement.querySelector('.reponse-input').value.trim();
            
            if (reponse) {
                fetch('ajouter_reponse.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'question_id=' + questionId + '&reponse=' + encodeURIComponent(reponse)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                });
            }
        });
    });
});
</script>

</body>
</html>