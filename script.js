function showForm(formId) {
    const forms = document.querySelectorAll(".form-box");
    const authModal = document.querySelector(".auth-modal");
    const wrapper = document.getElementById("wrapper");
    const leftPanel = document.getElementById("login-message");

    forms.forEach(form => form.classList.remove("active"));
    document.getElementById(formId).classList.add("active");

    if (formId === "login-form") {
        authModal.classList.remove("register-mode");
        leftPanel.innerHTML = "<h1>Hello, Welcome!</h1>";
    } else {
        authModal.classList.add("register-mode");
        leftPanel.innerHTML = "<h1>Join Us!</h1>";
    }

    authModal.style.overflowY = "auto";
}

function checkPasswordStrength(password) {
    const strengthText = document.getElementById('password-strength-text');
    const feedback = document.getElementById('password-feedback');
    const lengthReq = document.getElementById('pw-length');
    const uppercaseReq = document.getElementById('pw-uppercase');
    const lowercaseReq = document.getElementById('pw-lowercase');
    const numberReq = document.getElementById('pw-number');
    const specialReq = document.getElementById('pw-special');

    if (!password) {
        strengthText.textContent = '';
        feedback.style.display = 'none';
        return;
    }

    feedback.style.display = 'block';

    const tests = {
        length: password.length >= 8,
        upper: /[A-Z]/.test(password),
        lower: /[a-z]/.test(password),
        number: /\d/.test(password),
        special: /[\W_]/.test(password),
    };

    lengthReq.className = tests.length ? 'valid' : 'invalid';
    uppercaseReq.className = tests.upper ? 'valid' : 'invalid';
    lowercaseReq.className = tests.lower ? 'valid' : 'invalid';
    numberReq.className = tests.number ? 'valid' : 'invalid';
    specialReq.className = tests.special ? 'valid' : 'invalid';

    const score = Object.values(tests).filter(Boolean).length;
    const labels = ['Too weak', 'Weak', 'Weak', 'Fair', 'Good', 'Strong'];
    const colors = ['gray', 'red', 'red', 'orange', 'orange', 'green'];

    strengthText.textContent = `Strength: ${labels[score]}`;
    strengthText.style.color = colors[score];
}


document.addEventListener("DOMContentLoaded", () => {
    // Open login modal
    document.querySelectorAll(".login-btn-modal").forEach(btn => {
        btn.addEventListener("click", () => {
            document.querySelector(".auth-modal").style.display = "flex";
            showForm("login-form");

            setTimeout(() => {
                const input = document.querySelector(".form-box.active input");
                if (input) input.focus();
            }, 300);
        });
    });

    // Close modal
    document.querySelector(".close-btn-modal").addEventListener("click", () => {
        document.querySelector(".auth-modal").style.display = "none";
    });

    document.querySelector(".auth-modal").addEventListener("click", e => {
        if (e.target.classList.contains("auth-modal")) {
            e.target.style.display = "none";
        }
    });

    // Toggle password visibility
    document.querySelectorAll(".toggle-password").forEach(icon => {
        icon.addEventListener("click", () => {
            const input = icon.parentElement.querySelector("input");
            if (!input) return;
            input.type = input.type === "password" ? "text" : "password";
            icon.classList.toggle("fa-eye");
            icon.classList.toggle("fa-eye-slash");
        });
    });

    // Password feedback visibility on input
    const passwordInput = document.querySelector("#register-form input[name='password']");
    const passwordFeedback = document.getElementById("password-feedback");

    if (passwordInput) {
        passwordInput.addEventListener("input", () => {
            checkPasswordStrength(passwordInput.value);
        });

        passwordInput.addEventListener("focus", () => {
            if (passwordInput.value.length > 0) {
                passwordFeedback.style.display = "block";
            }
        });

        passwordInput.addEventListener("blur", () => {
            if (passwordInput.value === '') {
                passwordFeedback.style.display = "none";
            }
        });
    }

    // Confirm password match
    const confirmInput = document.querySelector("#register-form input[name='repassword']");
    const confirmStatus = document.getElementById("confirm-password-status");

    if (confirmInput && passwordInput && confirmStatus) {
        confirmInput.addEventListener("input", () => {
            if (confirmInput.value === "") {
                confirmStatus.textContent = "";
            } else if (confirmInput.value === passwordInput.value) {
                confirmStatus.textContent = "✅ Passwords match";
                confirmStatus.style.color = "green";
            } else {
                confirmStatus.textContent = "❌ Passwords do not match";
                confirmStatus.style.color = "red";
            }
        });
    }

    // Remember Me
    const loginForm = document.querySelector("#login-form form");
    if (loginForm) {
        const emailInput = loginForm.querySelector("input[name='email']");
        const rememberCheckbox = document.getElementById("rememberMe");

        const rememberedEmail = localStorage.getItem("rememberedEmail");
        if (rememberedEmail && emailInput && rememberCheckbox) {
            emailInput.value = rememberedEmail;
            rememberCheckbox.checked = true;
        }

        loginForm.addEventListener("submit", () => {
            if (rememberCheckbox.checked && emailInput.value) {
                localStorage.setItem("rememberedEmail", emailInput.value);
            } else {
                localStorage.removeItem("rememberedEmail");
            }
        });
    }

    // reCAPTCHA injection on submit
    document.querySelectorAll("form").forEach(form => {
        form.addEventListener("submit", function (event) {
            event.preventDefault();

            grecaptcha.ready(() => {
                grecaptcha.execute('6LfYwXQrAAAAAKzzflI6J_KQ1hDy2xeg5fNP171r', { action: 'submit' })
                    .then(token => {
                        let recaptchaInput = form.querySelector("input[name='recaptcha_token']");
                        if (!recaptchaInput) {
                            recaptchaInput = document.createElement("input");
                            recaptchaInput.type = "hidden";
                            recaptchaInput.name = "recaptcha_token";
                            form.appendChild(recaptchaInput);
                        }
                        recaptchaInput.value = token;
                        form.submit();
                    })
                    .catch(error => {
                        alert("reCAPTCHA verification failed. Please try again.");
                        console.error("reCAPTCHA error:", error);
                    });
            });
        });
    });

    // ✅ Automatically show login modal if redirected with ?msg=verified
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get("msg") === "verified") {
        document.querySelector(".auth-modal").style.display = "flex";
        showForm("login-form");
    }

});
