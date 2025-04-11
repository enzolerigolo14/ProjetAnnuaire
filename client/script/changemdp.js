document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('passwordForm');
    const errorMessage = document.getElementById('error-message');
    const successMessage = document.getElementById('success-message');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Récupération des valeurs
        const formData = {
            old_password: document.getElementById('old_password').value.trim(),
            new_password: document.getElementById('new_password').value.trim(),
            confirm_password: document.getElementById('confirm_password').value.trim()
        };

        // Réinitialisation des messages
        errorMessage.style.display = 'none';
        successMessage.style.display = 'none';

        // Validation côté client
        let isValid = true;
        
        if (formData.new_password !== formData.confirm_password) {
            showError('Les nouveaux mots de passe ne correspondent pas');
            isValid = false;
        }

        if (formData.new_password.length < 8) {
            showError('Le mot de passe doit contenir au moins 8 caractères');
            isValid = false;
        }

        if (!isValid) return;

        try {
            // Envoi des données au serveur
            const response = await fetch('/projetannuaire/server/api/change_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest' // Pour identification AJAX
                },
                credentials: 'include', // Pour les cookies de session
                body: JSON.stringify({
                    old_password: formData.old_password,
                    new_password: formData.new_password
                })
            });

            // Gestion de la réponse
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Erreur serveur');
            }

            const data = await response.json();
            
            // Succès
            showSuccess(data.message || 'Mot de passe changé avec succès');
            form.reset();
            
            // Redirection après délai
            setTimeout(() => {
                window.location.href = '/projetannuaire/client/src/profile.php';
            }, 2000);

        } catch (error) {
            console.error('Erreur:', error);
            showError(error.message || 'Une erreur est survenue lors de la mise à jour');
            
            // Réactivation du formulaire en cas d'erreur
            form.querySelector('button[type="submit"]').disabled = false;
        }
    });

    // Fonctions d'affichage des messages
    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
        errorMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function showSuccess(message) {
        successMessage.textContent = message;
        successMessage.style.display = 'block';
        successMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});