
        document.addEventListener('DOMContentLoaded', function() {
            // Concaténation Nom/Prénom
            const firstname = document.getElementById('firstname');
            const lastname = document.getElementById('lastname');
            const fullname = document.getElementById('fullname');
            
            function updateFullName() {
                fullname.value = `${firstname.value.trim()} ${lastname.value.trim()}`.trim();
            }
            
            firstname.addEventListener('input', updateFullName);
            lastname.addEventListener('input', updateFullName);
            
            // Génération du login
            const loginPrefix = document.getElementById('loginprefix');
            const loginDomain = document.getElementById('logindomain');
            const loginName = document.getElementById('loginname');
            
            function updateLogin() {
                const prefix = loginPrefix.value.trim().toLowerCase();
                loginName.value = prefix + loginDomain.value;
            }
            
            loginPrefix.addEventListener('input', updateLogin);
            
            // Génération automatique du préfixe login si vide
            firstname.addEventListener('blur', function() {
                if (!loginPrefix.value && firstname.value && lastname.value) {
                    loginPrefix.value = 
                        firstname.value.charAt(0).toLowerCase() + 
                        lastname.value.toLowerCase().replace(/\s+/g, '');
                    updateLogin();
                }
            });
            
            lastname.addEventListener('blur', function() {
                if (!loginPrefix.value && firstname.value && lastname.value) {
                    loginPrefix.value = 
                        firstname.value.charAt(0).toLowerCase() + 
                        lastname.value.toLowerCase().replace(/\s+/g, '');
                    updateLogin();
                }
            });

            document.getElementById('loginprefix').addEventListener('input', function() {
                const prefix = this.value;
                const domain = '@ville-lisieux.fr';
                document.getElementById('loginname').value = prefix.toLowerCase() + domain;
            });
        });
