document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des éléments avec des noms plus spécifiques
    const profileAvatarInput = document.querySelector('.profile-avatar-input');
    const profileAvatarImg = document.querySelector('.profile-avatar-img');
    const profileFileName = document.querySelector('.profile-file-name');
    
    const userId = profileAvatarImg.dataset.userId;

    // Charger l'avatar sauvegardé au chargement de la page
    const loadSavedProfileAvatar = function() {
        const savedAvatar = localStorage.getItem('profileAvatar_' + userId );
        if (savedAvatar) {
            profileAvatarImg.src = savedAvatar;
        }
    };

    // Gestion du changement d'avatar
    const handleProfileAvatarChange = function(e) {
        const file = e.target.files[0];
        if (file) {
            if (!file.type.match('image.*')) {
                alert('Veuillez sélectionner une image valide (JPEG, PNG)');
                return;
            }

            profileFileName.textContent = file.name;
            
            const reader = new FileReader();
            
            reader.onload = function(event) {
                // Sauvegarder dans le localStorage avec un ID utilisateur spécifique
                localStorage.setItem('profileAvatar_' + userId , event.target.result);
                
                // Mettre à jour l'aperçu
                profileAvatarImg.src = event.target.result;
                profileAvatarImg.style.display = 'block';
            };
            
            reader.readAsDataURL(file);
        } else {
            profileFileName.textContent = 'Aucun fichier sélectionné';
        }
    };

    // Initialisation
    loadSavedProfileAvatar();

    if (profileAvatarInput && profileAvatarImg && profileFileName) {
        profileAvatarInput.addEventListener('change', handleProfileAvatarChange);
    }
});