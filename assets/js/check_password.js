// assets/js/check_password.js

document.addEventListener("DOMContentLoaded", () => {

    // Regex password : 12+ caractères, 1 maj, 1 min, 1 chiffre, 1 spécial
    const STRONG_PWD = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{12,}$/;

    // Sélectionne tous les formulaires qui doivent être vérifiés
    const forms = document.querySelectorAll("form.js-check-password");

    forms.forEach(form => {

        form.addEventListener("submit", (event) => {

            // Si changement MDP optionnel (edit_user.php)
            const changeFlag = form.querySelector('input[name="change_password"]');
            if (changeFlag && changeFlag.value === "0") {
                // → pas de modification du MDP, on autorise l’envoi
                return;
            }

            const pwd  = form.querySelector('input[name="password"]');
            const pwd2 = form.querySelector('input[name="password_confirm"]');

            if (!pwd || !pwd2) return;

            const pass = pwd.value.trim();
            const conf = pwd2.value.trim();

            // 🔥 Vérif force
            if (!STRONG_PWD.test(pass)) {
                event.preventDefault();
                alert(
                    "⚠ Mot de passe trop faible.\n\n" +
                    "Il doit contenir :\n" +
                    "• 12 caractères minimum\n" +
                    "• 1 MAJUSCULE\n" +
                    "• 1 minuscule\n" +
                    "• 1 chiffre\n" +
                    "• 1 caractère spécial\n"
                );
                pwd.focus();
                return;
            }

            // 🔥 Vérif correspondance
            if (pass !== conf) {
                event.preventDefault();
                alert("⚠ Les mots de passe ne correspondent pas !");
                pwd2.focus();
                return;
            }

        });
    });

});
