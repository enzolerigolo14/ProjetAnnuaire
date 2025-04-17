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

document.addEventListener('DOMContentLoaded', function () {
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
        const userId = parentP.getAttribute('data-userid');
        const valueSpan = parentP.querySelector('.editable-value');
        const currentValue = valueSpan.textContent;

        let input;

        if (field === "service_id") {
            input = document.createElement('select');
            const servicesData = JSON.parse(document.getElementById('services-data').getAttribute('data-services'));

            servicesData.forEach(service => {
                const option = document.createElement('option');
                option.value = service.id;
                option.textContent = service.nom;
                if (service.nom === currentValue) {
                    option.selected = true;
                }
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
            const newValue = input.value.trim(); // C'est l'ID du service si service_id
        
            // Si c'est le service_id, on récupère aussi le nom affiché
            let displayValue = newValue;
            if (field === "service_id") {
                const selectedOption = input.options[input.selectedIndex];
                displayValue = selectedOption.textContent;
            }
        
            try {
                const response = await fetch('update-profile.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId, field: field, value: newValue })
                });
        
                const result = await response.json();
        
                if (result.success) {
                    parentP.innerHTML = `
                        <strong>${parentP.querySelector('strong').textContent}</strong>
                        <span class="editable-value" ${field === 'service_id' ? `data-serviceid="${newValue}"` : ''}>${displayValue}</span>
                        <i class="fas fa-pencil-alt edit-icon"></i>
                    `;
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
        

        cancelBtn.addEventListener('click', function () {
            input.replaceWith(createValueSpan(currentValue));
            saveBtn.replaceWith(createEditIcon());
            cancelBtn.remove();
        });
    }

    document.querySelectorAll('.edit-icon').forEach(icon => {
        icon.addEventListener('click', handleEditClick);
    });
});
