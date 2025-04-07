document.addEventListener("DOMContentLoaded", function () {
  const isLoggedIn = localStorage.getItem("isLoggedIn") === "true";
  const username = localStorage.getItem("username");
  const connexionButton = document.getElementById("connexion");
  const deconnexionButton = document.getElementById("deconnexion-button");
  const userProfile = document.getElementById("user-profile");
  const usernameDisplay = document.getElementById("username-display");

  if (isLoggedIn) {
    // Afficher le profil et masquer le bouton "Connexion"
    connexionButton.style.display = "none";
    deconnexionButton.style.display = "block";
    userProfile.style.display = "flex";
    usernameDisplay.textContent = username;
  }

  // Gestion de la déconnexion
  deconnexionButton.addEventListener("click", function () {
    localStorage.removeItem("isLoggedIn");
    localStorage.removeItem("username");
    window.location.reload();
  });
});

function validerRecherche(input) {
    let cleaned = input.value.replace(/[^a-zA-ZÀ-ÿ0-9\s\-']/g, '');
    const onlyDigits = cleaned.replace(/\D/g, ''); 
    if (/^\d+$/.test(cleaned.trim())) {
      cleaned = onlyDigits.slice(0, 10);
    }
    input.value = cleaned;
  }


  