document.addEventListener("DOMContentLoaded", () => {

    const STRONG_PWD = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{12,}$/;

    const forms = document.querySelectorAll("form.js-check-password");

    forms.forEach(form => {

        form.addEventListener("submit", (event) => {

            const pwd  = form.querySelector('input[name="password"]');
            const pwd2 = form.querySelector('input[name="password_confirm"]');

            if (!pwd || !pwd2) {
                return; 
            }

            const pass = pwd.value.trim();
            const confirm = pwd2.value.trim();

            if (!STRONG_PWD.test(pass)) {
                event.preventDefault(); 
                alert(
                    "Votre mot de passe doit contenir :\n" +
                    "• 12 caractères minimum\n" +
                    "• 1 MAJUSCULE\n" +
                    "• 1 minuscule\n" +
                    "• 1 chiffre\n" +
                    "• 1 caractère spécial\n"
                );
                pwd.focus();
                return;
            }

            if (pass !== confirm) {
                event.preventDefault();
                alert("⚠ Les mots de passe ne correspondent pas !");
                pwd2.focus();
                return;
            }

        });

    });

});
