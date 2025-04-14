document.addEventListener('DOMContentLoaded', function() {
    const profileAvatarInput = document.querySelector('.profile-avatar-input');
    const profileAvatarImg = document.querySelector('.profile-avatar-img');
    const profileFileName = document.querySelector('.profile-file-name');
    
    const userId = profileAvatarImg.dataset.userId;
    const loadSavedProfileAvatar = function() {
        const savedAvatar = localStorage.getItem('profileAvatar_' + userId );
        if (savedAvatar) {
            profileAvatarImg.src = savedAvatar;
        }
    };

 
    const handleProfileAvatarChange = function(e) {
        const file = e.target.files[0];
        if (file) {
            if (!file.type.match('image.*')) {
                alert('Veuillez sélectionner une image valide (JPEG, PNG)');
                return;
            }
            profileFileName.textContent = file.name;
            const reader = new FileReader()
            reader.onload = function(event) {
                localStorage.setItem('profileAvatar_' + userId , event.target.result);
                profileAvatarImg.src = event.target.result;
                profileAvatarImg.style.display = 'block';
            };
            
            reader.readAsDataURL(file);
        } else {
            profileFileName.textContent = 'Aucun fichier sélectionné';
        }
    };

    loadSavedProfileAvatar();

    if (profileAvatarInput && profileAvatarImg && profileFileName) {
        profileAvatarInput.addEventListener('change', handleProfileAvatarChange);
    }
});