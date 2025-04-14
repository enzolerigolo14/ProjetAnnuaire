document.addEventListener('DOMContentLoaded', function() {
    const profileAvatarInput = document.querySelector('.profile-avatar-input');
    const profileAvatarImg = document.querySelector('.profile-avatar-img');
    const profileFileName = document.querySelector('.profile-file-name');
    
    const userId = profileAvatarImg.dataset.userId;
    const loadSavedProfileAvatar = function() {
        const savedAvatar = localStorage.getItem('profileAvatar_' + userId);
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
                localStorage.setItem('profileAvatar_' + userId, event.target.result);
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
    const backButton = document.getElementById('backButton');
    
    if (backButton) {
        backButton.addEventListener('click', function(e) {
            e.preventDefault();
            handleBackNavigation(this);
        });
    }

    function handleBackNavigation(button) {
        const from = button.dataset.from;
        const serviceId = button.dataset.serviceId;
        const returnUrl = button.dataset.returnUrl || 'pageaccueil.php';


        if (from === 'search') {
            window.location.href = 'pageaccueil.php';
            return;
        }

        if (from === 'services' && serviceId) {
            window.location.href = `membresservices.php?id=${serviceId}`;
            return;
        }

        if (from === 'global') {
            window.location.href = 'membreglobal.php';
            return;
        }

        if (window.history.length > 1 && !document.referrer.includes('profilutilisateur.php')) {
            window.history.back();
            setTimeout(() => {
                window.location.href = returnUrl;
            }, 1000);
        } else {
            window.location.href = returnUrl;
        }
    }
});