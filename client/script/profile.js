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


document.addEventListener('DOMContentLoaded', function() {
    const avatarUpload = document.getElementById('avatar-upload');
    const avatarPreview = document.getElementById('avatar-preview');
    const fileName = document.getElementById('file-name');

    // Charger l'avatar sauvegardé au chargement de la page
    const savedAvatar = localStorage.getItem('userAvatar');
    if (savedAvatar) {
        avatarPreview.src = savedAvatar;
    }

    if (avatarUpload && avatarPreview && fileName) {
        avatarUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (!file.type.match('image.*')) {
                    alert('Veuillez sélectionner une image (JPEG, PNG)');
                    return;
                }

                fileName.textContent = file.name;
                
                const reader = new FileReader();
                
                reader.onload = function(event) {
                    // Sauvegarder dans le localStorage
                    localStorage.setItem('userAvatar', event.target.result);
                    
                    // Mettre à jour l'aperçu
                    avatarPreview.src = event.target.result;
                    avatarPreview.style.display = 'block';
                };
                
                reader.readAsDataURL(file);
            } else {
                fileName.textContent = 'Aucun fichier sélectionné';
            }
        });
    }
});