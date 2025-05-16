document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('passwordForm');
    const errorMessage = document.getElementById('error-message');
    const successMessage = document.getElementById('success-message');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const old_password = document.getElementById('old_password').value.trim();
        const new_password = document.getElementById('new_password').value.trim();
        const confirm_password = document.getElementById('confirm_password').value.trim();

        errorMessage.style.display = 'none';
        successMessage.style.display = 'none';

        if (new_password !== confirm_password) {
            showError("Les nouveaux mots de passe ne correspondent pas.");
            return;
        }

        try {
            const response = await fetch(form.action || '/projetannuaire/server/api/changemdp.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    old_password: old_password,
                    new_password: new_password
                })
            });

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                throw new Error("Réponse inattendue du serveur :\n" + text);
            }

            const result = await response.json();

            if (!response.ok || result.error) {
                throw new Error(result.error || 'Erreur inconnue.');
            }

            showSuccess(result.message || 'Mot de passe changé avec succès');
            form.reset();

            setTimeout(() => {
                window.location.href = '/projetannuaire/client/src/profile.php';
            }, 2000);
        } catch (err) {
            showError(err.message);
        }
    });

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
