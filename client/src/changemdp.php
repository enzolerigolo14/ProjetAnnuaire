
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer Mot de Passe | Trombinoscope Ville de Lisieux</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/changemdp.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/header.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    <script src="/projetannuaire/client/script/changemdp.js" defer></script>
</head>
<body>
    <div>
        <h1>Changer Mot de Passe</h1>
        <form id="passwordForm" method="POST">
            <div class="form-group">
                <label for="old_password">Ancien Mot de Passe</label>
                <input type="password" id="old_password" name="old_password" required>
            </div>

            <div class="form-group">
                <label for="new_password">Nouveau Mot de Passe</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le Nouveau Mot de Passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="submit-btn">Changer le Mot de Passe</button>
        </form>

        <!-- Messages dynamiques gérés par JavaScript -->
        <div id="error-message" class="error-message" style="display: none;"></div>
        <div id="success-message" class="success-message" style="display: none;"></div>
    </div>
    <footer>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</footer>
</body>
</html>