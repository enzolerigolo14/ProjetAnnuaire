document.addEventListener('DOMContentLoaded', function () {
    // --- Gestion avatar utilisateur ---
    const profileAvatarInput = document.querySelector('.profile-avatar-input');
    const profileAvatarImg = document.querySelector('.profile-avatar-img');
    const profileFileName = document.querySelector('.profile-file-name');

    if (profileAvatarImg) {
        const userId = profileAvatarImg.dataset.userId;
        const savedAvatar = localStorage.getItem('profileAvatar_' + userId);
        if (savedAvatar) {
            profileAvatarImg.src = savedAvatar;
        }
    }

    if (profileAvatarInput && profileAvatarImg && profileFileName) {
        profileAvatarInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file && file.type.match('image.*')) {
                profileFileName.textContent = file.name;
                const reader = new FileReader();
                reader.onload = function (event) {
                    const dataUrl = event.target.result;
                    localStorage.setItem('profileAvatar_' + profileAvatarImg.dataset.userId, dataUrl);
                    profileAvatarImg.src = dataUrl;
                    profileAvatarImg.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                profileFileName.textContent = 'Aucun fichier sélectionné';
                alert('Veuillez sélectionner une image valide (JPEG, PNG)');
            }
        });
    }

    // --- Bouton retour ---
    const backButton = document.getElementById('backButton');
    if (backButton) {
        backButton.addEventListener('click', function (e) {
            e.preventDefault();
            const from = this.dataset.from;
            const serviceId = this.dataset.serviceId;
            const returnUrl = this.dataset.returnUrl || 'pageaccueil.php';

            if (from === 'search') {
                location.href = 'pageaccueil.php';
            } else if (from === 'services' && serviceId) {
                location.href = `membresservices.php?id=${serviceId}`;
            } else if (from === 'global') {
                location.href = 'membreglobal.php';
            } else if (history.length > 1 && !document.referrer.includes('profilutilisateur.php')) {
                history.back();
                setTimeout(() => (location.href = returnUrl), 1000);
            } else {
                location.href = returnUrl;
            }
        });
    }

    // --- Modification en ligne des champs ---
    function createValueSpan(value) {
        const span = document.createElement('span');
        span.className = 'editable-value';
        span.textContent = value;
        return span;
    }

    function createEditIcon() {
        const icon = document.createElement('i');
        icon.className = 'fas fa-pencil-alt edit-icon';
        icon.addEventListener('click', handleEditClick);
        return icon;
    }

    async function handleEditClick() {
        const parentP = this.parentElement;
        const field = parentP.getAttribute('data-field');
        const userId = document.body.getAttribute('data-user-id');
        const valueSpan = parentP.querySelector('.editable-value');
        const currentValue = valueSpan.textContent;

        let input;
        if (field === "service") {
            input = document.createElement('select');
            window.servicesData.forEach(service => {
                const option = document.createElement('option');
                option.value = service.id;
                option.textContent = service.nom;
                if (service.nom === currentValue) option.selected = true;
                input.appendChild(option);
            });
        } else {
            input = document.createElement('input');
            input.type = 'text';
            input.value = currentValue;
            input.className = 'edit-input';
        }

        const saveBtn = document.createElement('button');
        saveBtn.innerHTML = '<i class="fas fa-check"></i>';
        saveBtn.className = 'save-btn';

        const cancelBtn = document.createElement('button');
        cancelBtn.innerHTML = '<i class="fas fa-times"></i>';
        cancelBtn.className = 'cancel-btn';

        valueSpan.replaceWith(input);
        this.replaceWith(saveBtn);
        parentP.appendChild(cancelBtn);
        input.focus();


saveBtn.addEventListener('click', async function () {
    const newValue = input.value.trim();
    const sendValue = field === "service" ? input.options[input.selectedIndex].value : newValue;

    try {
        const response = await fetch('update-profile.php', {  // Correction du nom de fichier
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                user_id: userId, 
                field: field,
                value: sendValue
            })
        });

        const result = await response.json();
        
        if (result.success) {
            // Mise à jour de l'affichage selon le champ modifié
            if (field === 'service') {
                valueSpan.textContent = result.serviceName;
                // Si vous avez besoin de stocker l'ID quelque part
                if (result.serviceId) {
                    input.options[input.selectedIndex].value = result.serviceId;
                }
            } else {
                valueSpan.textContent = result.newValue;
            }
            
            // Nettoyage des éléments d'édition
            saveBtn.remove();
            cancelBtn.remove();
            parentP.appendChild(createEditIcon());
        } else {
            alert('Erreur: ' + (result.message || 'Échec de la mise à jour'));
            input.replaceWith(createValueSpan(currentValue));
            saveBtn.replaceWith(createEditIcon());
            cancelBtn.remove();
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la mise à jour');
        input.replaceWith(createValueSpan(currentValue));
        saveBtn.replaceWith(createEditIcon());
        cancelBtn.remove();
    }
});

        cancelBtn.addEventListener('click', function () {
            input.replaceWith(createValueSpan(currentValue));
            saveBtn.replaceWith(createEditIcon());
            cancelBtn.remove();
        });
    }

    // Initialisation des icônes d'édition
    document.querySelectorAll('.edit-icon').forEach(icon => {
        icon.addEventListener('click', handleEditClick);
    });
});