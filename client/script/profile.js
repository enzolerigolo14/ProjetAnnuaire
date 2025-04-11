document.addEventListener("DOMContentLoaded", function () {
    const isLoggedIn = localStorage.getItem("isLoggedIn");
    const username = localStorage.getItem("username");
    const password = localStorage.getItem("password");

    // Vérifie si l'utilisateur est connecté
    if (isLoggedIn === "true" && username) {
        document.getElementById("profile-username").textContent = username; 
    } else {
        // Redirige si non connecté
        window.location.href = "/projetannuaire/client/src/pageaccueil.php";
    }

    if (isLoggedIn === "true" && username) {
        document.getElementById("profile-username").textContent = username; 
    } else {
        // Redirige si non connecté
        window.location.href = "/projetannuaire/client/src/pageaccueil.php";
    }


    // Gestion de la déconnexion
    document.getElementById("deconnexion-button").addEventListener("click", function () {
        localStorage.removeItem("isLoggedIn");
        localStorage.removeItem("username");
        window.location.href = "/projetannuaire/client/src/pageaccueil.php";
    });

    
});