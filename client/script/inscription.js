document.addEventListener('DOMContentLoaded', function() {
            const firstname = document.getElementById('firstname');
            const lastname = document.getElementById('lastname');
            const fullname = document.getElementById('fullname');
            const loginprefix = document.getElementById('loginprefix');
            const loginname = document.getElementById('loginname');
            const regenerateBtn = document.getElementById('regenerate-password');
            const passwordField = document.getElementById('password');

            function updateFullname() {
                fullname.value = `${firstname.value} ${lastname.value}`.trim();
            }

            function generateLogin() {
                if (firstname.value && lastname.value) {
                    const login = `${firstname.value.charAt(0)}${lastname.value}`.toLowerCase();
                    loginprefix.value = login;
                    loginname.value = `${login}@ville-lisieux.fr`;
                }
            }

            firstname.addEventListener('input', function() {
                updateFullname();
                generateLogin();
            });

            lastname.addEventListener('input', function() {
                updateFullname();
                generateLogin();
            });

            regenerateBtn.addEventListener('click', function() {
                fetch('generate_password.php')
                    .then(response => response.text())
                    .then(password => {
                        passwordField.value = password;
                    });
            });
            document.getElementById('telephone').addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length > 4) {
                    this.value = this.value.slice(0, 4);
                }
            });
        });
 