document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('passwordForm');
    const errorMessage = document.getElementById('error-message');
    const successMessage = document.getElementById('success-message');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Masquer les messages précédents
        errorMessage.style.display = 'none';
        successMessage.style.display = 'none';
        
        // Validation côté client
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (newPassword !== confirmPassword) {
            showError('Les nouveaux mots de passe ne correspondent pas');
            return;
        }
        
        if (newPassword.length < 8) {
            showError('Le mot de passe doit contenir au moins 8 caractères');
            return;
        }
        
        // Soumettre le formulaire
        form.submit();
    });
    
    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
    }
});