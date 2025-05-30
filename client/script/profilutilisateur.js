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
    let currentValue = valueSpan.textContent.trim();

    if (currentValue === 'Non renseigné' || currentValue === 'Non spécifié' || currentValue === 'Non disponible (AD)') {
        currentValue = '';
    }

    let input;
    if (field === "service_id") {  
    input = document.createElement('select');
    input.className = 'edit-select';
    
    // Option par défaut
    input.innerHTML = `
        <option value="">-- Sélectionnez un service --</option>
        ${window.servicesHTML}
    `;
    
    // Sélectionne la valeur actuelle
    if (currentValue) {
        const options = input.options;
        for (let i = 0; i < options.length; i++) {
            if (options[i].text === currentValue) {
                options[i].selected = true;
                break;
            }
        }
    }
} else if (field === "role") {
        input = document.createElement('select');
        input.className = 'edit-select';
        
        const roles = ['membre', 'SVC-INFORMATIQUE', 'ADMIN-INTRA', 'ADMIN-RH'];
        
        roles.forEach(role => {
            const option = document.createElement('option');
            option.value = role;
            option.textContent = role;
            if (role === currentValue) option.selected = true;
            input.appendChild(option);
        });
    } else {
        input = document.createElement('input');
        input.type = 'text';
        input.value = currentValue;
        input.className = 'edit-input';
        
        // Validation spécifique pour les numéros de téléphone
        if (field.includes('telephone')) {
            input.pattern = field.includes('interne') ? '\\d{4}' : '\\d{10}';
            input.maxLength = field.includes('interne') ? 4 : 10;
        }
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
    const newValue = input.tagName === 'SELECT' ? input.options[input.selectedIndex].value : input.value.trim();
    
    try {
        // Correction: Utilisez le bon nom de champ (field au lieu de 'service')
        const response = await fetch('update-profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                user_id: document.body.dataset.userId,
                field: field, // Utilisez la variable field au lieu de 'service'
                value: newValue,
                email: window.currentEmail
            })
        });

        const result = await response.json();
        if (!response.ok) throw new Error(result.message || 'Échec de la mise à jour');
        
        // Mise à jour de l'affichage
        let displayValue = newValue;
        
        if (field === 'role') {
            // Pour le rôle, affichez la version lisible
            displayValue = getRoleDisplayName(newValue);
        }
        
        const newSpan = document.createElement('span');
        newSpan.className = 'editable-value';
        newSpan.textContent = displayValue;
        
        input.replaceWith(newSpan);
        saveBtn.replaceWith(createEditIcon());
        cancelBtn.remove();
        
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la mise à jour: ' + error.message);
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

function getRoleDisplayName(roleKey) {
    const roles = {
        'membre': 'Membre',
        'SVC-INFORMATIQUE': 'Service Informatique',
        'ADMIN-INTRA': 'Admin Intranet', 
        'ADMIN-RH': 'Admin RH'
    };
    return roles[roleKey] || roleKey;
}

document.getElementById('avatar-upload')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
    const maxSize = 2 * 1024 * 1024; 
    
    if (!validTypes.includes(file.type)) {
        updateStatus('Seuls les JPG, PNG et GIF sont autorisés', 'red');
        return;
    }
    
    if (file.size > maxSize) {
        updateStatus('Fichier trop volumineux (max 2MB)', 'red');
        return;
    }

    const formData = new FormData();
    formData.append('avatar', file);
    formData.append('user_id', document.body.dataset.userId);

    updateStatus('Téléchargement en cours...', '#666');

    fetch('/projetannuaire/client/src/upload_avatar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Erreur réseau');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            updateStatus('Photo mise à jour!', 'green');
            const imgElement = document.querySelector('.profile-avatar-img');
            imgElement.src = data.filePath + '?t=' + Date.now();
            imgElement.onerror = () => {
                imgElement.src = '/projetannuaire/client/src/assets/images/search-icon.png';
            };
        } else {
            throw new Error(data.message || 'Erreur inconnue');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        updateStatus(`Erreur: ${error.message}`, 'red');
    });

    function updateStatus(message, color) {
        const statusElement = document.getElementById('upload-status');
        if (statusElement) {
            statusElement.textContent = message;
            statusElement.style.color = color;
            if (color === 'green') {
                setTimeout(() => statusElement.textContent = '', 5000);
            }
        }
    }
});

window.addEventListener('DOMContentLoaded', () => {
    const img = document.querySelector('.profile-avatar-img');
    if (img) img.onerror = () => {
        img.src = '/projetannuaire/client/src/assets/images/search-icon.png';
    };
});

function setupPhoneEdit() {
    document.querySelectorAll('.phone-section .edit-icon').forEach(icon => {
        icon.addEventListener('click', function() {
            const fieldType = this.getAttribute('data-field');
            const isInternal = fieldType === 'phone_internal';
            const container = this.closest('.phone-number');
            const valueSpan = container.querySelector('.editable-value');
            const currentValue = valueSpan.textContent.replace(/[^0-9]/g, '');
            
            const maxLength = isInternal ? 4 : 10;
            const inputType = isInternal ? 'number' : 'tel';
            
            const inputHtml = `
                <div class="edit-phone-input">
                    <input type="${inputType}" 
                           value="${currentValue}" 
                           maxlength="${maxLength}"
                           pattern="${isInternal ? '\\d{4}' : '\\d{10}'}"
                           class="${isInternal ? 'short' : ''}"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <button class="save-btn">✓</button>
                    <button class="cancel-btn">✗</button>
                </div>
            `;
            
            valueSpan.innerHTML = inputHtml;
            valueSpan.querySelector('input').focus();
            
            const saveBtn = valueSpan.querySelector('.save-btn');
            const cancelBtn = valueSpan.querySelector('.cancel-btn');
            const input = valueSpan.querySelector('input');
            
            saveBtn.addEventListener('click', async function() {
                const newValue = input.value.trim();
                
                // Validation
                if (isInternal && !/^\d{4}$/.test(newValue)) {
                    alert('Le poste interne doit contenir exactement 4 chiffres');
                    return;
                }
                
                if (!isInternal && !/^\d{10}$/.test(newValue)) {
                    alert('Le numéro public doit contenir exactement 10 chiffres');
                    return;
                }
                
                // Formatage pour l'affichage
                const displayValue = isInternal ? newValue : formatPhoneDisplay(newValue);
                valueSpan.textContent = displayValue;
                
                // Enregistrement en base de données
                try {
                    const response = await fetch('/api/update-phone.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            userId: document.body.getAttribute('data-user-id'),
                            field: fieldType,
                            value: newValue
                        })
                    });
                    
                    if (!response.ok) throw new Error('Erreur de sauvegarde');
                } catch (error) {
                    console.error(error);
                    alert('Erreur lors de la sauvegarde');
                    valueSpan.textContent = currentValue;
                }
            });
            
            cancelBtn.addEventListener('click', function() {
                valueSpan.textContent = isInternal ? currentValue : formatPhoneDisplay(currentValue);
            });
            
            input.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') saveBtn.click();
                if (e.key === 'Escape') cancelBtn.click();
            });
        });
    });
}

function formatPhoneDisplay(number) {
    return number.replace(/(\d{2})(?=\d)/g, '$1 ');
}

document.addEventListener('DOMContentLoaded', function() {
    setupPhoneEdit();
});


function togglePasswordVisibility(button) {
    const container = button.closest('.password-display');
    const input = container.querySelector('.password-field');
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

