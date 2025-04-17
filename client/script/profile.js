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

// REMPLACEZ UNIQUEMENT LA PARTIE ÉDITION PAR CE CODE :
document.addEventListener('DOMContentLoaded', function() {
    // Fonctions utilitaires (inchangées)
    function createValueSpan(value) {
        const span = document.createElement('span');
        span.className = 'editable-value';
        span.textContent = value;
        return span;
    }

    function createEditIcon() {
        const icon = document.createElement('i');
        icon.className = 'fas fa-pencil-alt edit-icon';
        // Réattacher l'événement immédiatement
        icon.addEventListener('click', handleEditClick);
        return icon;
    }

    // Nouvelle fonction pour gérer l'édition
    function handleEditClick() {
        const parentP = this.parentElement;
        const field = parentP.getAttribute('data-field');
        const userId = parentP.getAttribute('data-userid');
        const valueSpan = parentP.querySelector('.editable-value');
        const currentValue = valueSpan.textContent;

        // Créer l'input
        const input = document.createElement('input');
        input.type = 'text';
        input.value = currentValue;
        input.className = 'edit-input';

        // Créer les boutons
        const saveBtn = document.createElement('button');
        saveBtn.innerHTML = '<i class="fas fa-check"></i>';
        saveBtn.className = 'save-btn';
        
        const cancelBtn = document.createElement('button');
        cancelBtn.innerHTML = '<i class="fas fa-times"></i>';
        cancelBtn.className = 'cancel-btn';

        // Remplacer les éléments
        valueSpan.replaceWith(input);
        this.replaceWith(saveBtn);
        parentP.appendChild(cancelBtn);

        input.focus();

        // Gestion sauvegarde
        saveBtn.addEventListener('click', async function() {
            const newValue = input.value.trim();
            try {
                const response = await fetch('update-profile.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({user_id: userId, field: field, value: newValue})
                });

                const result = await response.json();

                if (result.success) {
                    // RECRÉER TOUT L'ÉLÉMENT POUR RÉINITIALISER LES ÉVÉNEMENTS
                    parentP.innerHTML = `
                        <strong>${parentP.querySelector('strong').textContent}</strong>
                        <span class="editable-value">${newValue}</span>
                        <i class="fas fa-pencil-alt edit-icon"></i>
                    `;
                    // Réattacher l'événement
                    parentP.querySelector('.edit-icon').addEventListener('click', handleEditClick);
                } else {
                    alert('Erreur: ' + (result.message || 'Échec de la mise à jour'));
                    input.replaceWith(createValueSpan(currentValue));
                    saveBtn.replaceWith(createEditIcon());
                    cancelBtn.remove();
                }
            } catch (error) {
                alert('Erreur réseau: ' + error.message);
                input.replaceWith(createValueSpan(currentValue));
                saveBtn.replaceWith(createEditIcon());
                cancelBtn.remove();
            }
        });

        // Gestion annulation
        cancelBtn.addEventListener('click', function() {
            input.replaceWith(createValueSpan(currentValue));
            saveBtn.replaceWith(createEditIcon());
            cancelBtn.remove();
        });
    }

    // Attacher les événements initiaux
    document.querySelectorAll('.edit-icon').forEach(icon => {
        icon.addEventListener('click', handleEditClick);
    });
});