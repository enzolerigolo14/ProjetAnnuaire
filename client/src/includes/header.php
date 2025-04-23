<div class="header-container">
      <div class="header-logo">
        <a href="https://www.ville-lisieux.fr/fr/" target="_blank">
          <img src="/projetannuaire/client/src/assets/images/logo-lisieux.png" alt="Logo de la ville de Lisieux" class="logo">
        </a>
      </div>
      
      <div class="search-container">
    <img src="/projetannuaire/client/src/assets/images/search-icon.png" alt="Search Icon" class="search-icon">
    <input type="text" id="site-search" placeholder="Nom, prénom, téléphone ou service" maxlength="32" />
    <button class="bouton-search" type="button" onclick="rechercher()">Rechercher</button>
</div>
      
      <div class="header-profile">
        <div id="user-profile">
          <span id="username-display"></span>
          <a href="/projetannuaire/client/src/profile.php" id="profil-link" class="header-button">
            <img src="/projetannuaire/client/src/assets/images/profile-icon.png" alt="Profil" class="profile-icon">
          </a>
          <!--<a href="logout.php" id="deconnexion-button" class="header-button">Déconnexion</a>-->
        </div>
      </div>
    </div>

    <script src="/projetannuaire/client/script/rechercher.js" defer></script> 