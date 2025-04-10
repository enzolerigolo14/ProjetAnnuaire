<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: /ProjetAnnuaire/client/src/connexion.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Mon Profil</title>
  <link rel="stylesheet" href="/client/src/assets/styles/profil.css">
  <script src="/client/script/profil.js"></script>
</head>
<body>
  <div class="profile-container">
    <h1>Mon Profil</h1>
    <div id="profile-info">
      <p>Nom d'utilisateur: <span id="profile-username"><?php echo htmlspecialchars($_SESSION['username']); ?></span></p>
    </div>
    <a href="logout.php" id="deconnexion-button">Déconnexion</a>
  </div>
</body>
</html>