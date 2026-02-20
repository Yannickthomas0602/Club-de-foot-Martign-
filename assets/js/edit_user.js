document.addEventListener("DOMContentLoaded", () => {

  const STRONG_PWD = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{12,}$/;

  const form       = document.querySelector("form.js-edit-user-form");
  const toggleBtn  = document.getElementById("togglePwd");
  const pwdBlock   = document.getElementById("pwd_block");
  const changeFlag = document.getElementById("change_password");

  const pwdInput   = document.getElementById("password");
  const confInput  = document.getElementById("password_confirm");

  if (toggleBtn && pwdBlock && changeFlag) {
    toggleBtn.addEventListener("click", () => {
      const isHidden = pwdBlock.classList.contains("hidden");
      if (isHidden) {
        pwdBlock.classList.remove("hidden");
        changeFlag.value = "1";
        pwdInput && pwdInput.focus();
      } else {
        pwdBlock.classList.add("hidden");
        changeFlag.value = "0";
        if (pwdInput)  pwdInput.value  = "";
        if (confInput) confInput.value = "";
      }
    });
  }

  if (form) {
    form.addEventListener("submit", (e) => {
      if (changeFlag && changeFlag.value === "1") {
        const pass = (pwdInput?.value || "").trim();
        const conf = (confInput?.value || "").trim();

        if (!STRONG_PWD.test(pass)) {
          e.preventDefault();
          alert(
            "Mot de passe trop faible.\n\n" +
            "Exigences : 12 caractères minimum, 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial."
          );
          pwdInput && pwdInput.focus();
          return;
        }
        if (pass !== conf) {
          e.preventDefault();
          alert("Les mots de passe ne correspondent pas.");
          confInput && confInput.focus();
          return;
        }
      }
    });
  }
});
