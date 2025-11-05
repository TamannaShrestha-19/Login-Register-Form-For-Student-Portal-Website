<?php
$showSuccessMsg = false;
$redirectToLogin = false;

if (isset($_GET['msg']) && $_GET['msg'] === 'verified') {
    $showSuccessMsg = true;
    $redirectToLogin = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://www.google.com/recaptcha/api.js?render=6LfYwXQrAAAAAKzzflI6J_KQ1hDy2xeg5fNP171r"></script>
</head>

<body>
    <header>
        <a href="home.html" class="logo">
            <img src="images/logo.png" alt="Student Portal Logo">
            <span>Student Portal</span>
        </a>
        <div class="right-menu">
            <nav>
                <a href="home.html">Home</a>
                <a href="about.html">About</a>
                <a href="contact.html">Contact</a>
            </nav>
            <div class="user-auth">
                <button type="button" class="login-btn-modal">Login</button>
            </div>
        </div>
    </header>

    <section>
        <div class="welcome-text">
            <h1>Welcome to Your Student Portal!</h1>
            <p>Access your courses, track assignments, and stay connected.</p>
            <button class="login-btn-modal">Get Started</button>
        </div>
    </section>

    <div class="auth-modal">
        <button type="button" class="close-btn-modal">&times;</button>
        <div class="wrapper" id="wrapper">
            <div class="left-panel" id="login-message">
                <h1>Hello, Welcome!</h1>
            </div>

            <?php if ($showSuccessMsg): ?>
                <div class="modal-alert success-alert" id="emailVerifiedAlert" style="background-color:#d4edda; color:#155724; border:1.5px solid #c3e6cb; padding: 12px; border-radius:6px; margin-bottom:15px; font-weight:600; text-align:center;">
                    ✅ Your email has been verified. Please log in.
                    <span class="close-alert" onclick="document.getElementById('emailVerifiedAlert').style.display='none'" style="cursor:pointer; float:right; font-weight:bold;">&times;</span>
                </div>
            <?php endif; ?>

            <!-- Login form -->
            <div class="form-box active" id="login-form">
                <form action="process_login.php" method="POST">
                    <h2>Login</h2>
                    <div class="input-group">
                        <span class="input-icon"><i class="fa fa-envelope"></i></span>
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="input-group password-wrapper">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" placeholder="Password" required>
                        <i class="fa-regular fa-eye toggle-password"></i>
                    </div>
                    <div class="login-options">
                        <label class="remember-me">
                            <input type="checkbox" id="rememberMe" name="rememberMe">
                            Remember Me
                        </label>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>
                    <input type="hidden" name="recaptcha_token">
                    <button type="submit" name="login">Login</button>
                    <p>Don't have an account? <a href="#" onclick="event.preventDefault();showForm('register-form')">Register</a></p>
                    <div class="social-login">
                        <p>or sign in with</p>
                        <div class="social-icons">
                            <a href="google_login.php"><i class="fab fa-google"></i></a>
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Register form -->
            <div class="form-box" id="register-form">
                <form action="process_register.php" method="POST">
                    <h2>Register</h2>
                    <div class="input-group">
                        <span class="input-icon"><i class="fa fa-user"></i></span>
                        <input type="text" name="name" placeholder="Username" required>
                    </div>
                    <div class="input-group">
                        <span class="input-icon"><i class="fa fa-envelope"></i></span>
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="input-group password-wrapper">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" placeholder="Password" required oninput="checkPasswordStrength(this.value)">
                        <i class="fa-regular fa-eye toggle-password"></i>
                    </div>
                    <div id="password-suggestion">Use 8+ characters with letters, numbers & symbols.</div>
                    <div id="password-strength-text">Strength: </div>
                    <div id="password-feedback">
                        <ul>
                            <li id="pw-length" class="invalid">At least 8 characters</li>
                            <li id="pw-uppercase" class="invalid">Contains uppercase letter (A-Z)</li>
                            <li id="pw-lowercase" class="invalid">Contains lowercase letter (a-z)</li>
                            <li id="pw-number" class="invalid">Contains number (0-9)</li>
                            <li id="pw-special" class="invalid">Contains special character (!@#$%^&*)</li>
                        </ul>
                    </div>

                    <div class="input-group password-wrapper">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password" name="repassword" placeholder="Confirm Password" required>
                        <i class="fa-regular fa-eye toggle-password"></i>
                        <div id="confirm-password-status"></div>
                    </div>

                    <div class="terms-container">
                        <label>
                            <input type="checkbox" name="terms" required>
                            I agree to the <a href="#" class="terms-link">Terms and Conditions</a>
                        </label>
                    </div>

                    <input type="hidden" name="recaptcha_token">
                    <button type="submit" name="register">Register</button>
                    <p>Already have an account? <a href="#" onclick="event.preventDefault();showForm('login-form')">Log In</a></p>
                    <div class="social-login">
                        <p>or sign up with</p>
                        <div class="social-icons">
                            <a href="google_login.php"><i class="fab fa-google"></i></a>
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <p>© 2025 Website For Student Portal | <a href="#">Contact Us</a></p>
    </footer>

    <script src="script.js"></script>
</body>

</html>