README.txt – Secure Student Portal (Login & Registration System)

Student ID: 240672386  
Name: Tamanna Shrestha  
Course: CET324 – Advanced Cybersecurity  
Assignment 2 – 2024/25  

PROJECT SUMMARY
This is a secure student portal developed using PHP and MySQL in a local XAMPP environment. It includes strong authentication features and follows OWASP best practices.

Key Features:
- Secure registration with Google reCAPTCHA v3.
- Real-time password strength meter.
- Password Policies:
   • Minimum 8 characters  
   • Includes uppercase, lowercase, digits, and special characters  
   • Mandatory password change every 30 days  
   • Prevents reuse of the last 3 passwords  
- Email verification via PHPMailer (real Gmail SMTP).  
- MFA (OTP via email) for login.
- Resend OTP functionality.
- Account lockout after 3 failed login attempts.
- Role-Based Access Control (RBAC): user(student) and admin.  
- Session timeout after 25 minutes of inactivity. 
- Google Sign-In via OAuth 2.0.
- Logging login attempts in 'logins.log'.
- SQL Injection prevention using prepared statements.  
- XSS prevention using 'htmlspecialchars()'.


HOW TO RUN THE SYSTEM
1. Open XAMPP and start Apache & MySQL.
2. Place the entire project folder inside 'htdocs'.
3. Import the SQL database using phpMyAdmin.
4. Update credentials:
   - 'db.php' – your MySQL database config  
   - 'send_otp.php' – use Gmail SMTP (enable App Passwords if 2FA is on)
5. For Google Sign-In:
   - Set up OAuth 2.0 client credentials in Google Developer Console.
   - Paste Client ID and Secret into 'google_login.php' and 'google_callback.php.
6. Visit:  
   'http://localhost:8080/ACS_Code_StudentPortal/login_register.php'

MAIN FILES & FEATURES
• login_register.php  
  → UI for login/register modals, real-time password strength, and reCAPTCHA.  
  → Also includes OTP resend links and Google login button.

• process_register.php  
  → Handles secure registration, reCAPTCHA check, password hashing, and email verification.

• verify_email.php  
  → Verifies the email token and activates the account.  

• process_login.php  
  → Checks login credentials, enforces lockout after 3 failures, triggers OTP, and logs events.  

• send_otp.php  
  → Sends 6-digit OTP via Gmail using PHPMailer.

• verify_mfa.php  
  → Verifies OTP and redirects to the appropriate dashboard. 

• change_password.php  
  → Forces password change after 30 days, blocks the last 3 password reuses.  

• logout.php  
  → Destroys the session and redirects to the login.  

• user_dashboard.php  
  → Student dashboard protected by session + auto logout after 25 minutes.  

• admin_dashboard.php  
  → Admin panel for enabling/disabling users.

• toggle_user.php  
  → Enables/disables users (admin only).  

• google_login.php / google_callback.php  
  → Google OAuth 2.0 login logic  

• db.php  
  → Secure database connection using PDO + prepared statements.  

• logins.log  
  → Logs login attempts, OTP sent, lockouts, and suspicious activity.  

• style.css / script.js  
  → Frontend styling and JavaScript for UI & password strength feedback.  


TEST CREDENTIALS
• User: tmnnshrsth@gmail.com  
  Password: Tamanna20#  

• Admin: arpanaxtha2020@gmail.com  
  Password: #Admin01  

→ You can also test with Google login or register a new user


SYSTEM FUNCTIONALITY WALKTHROUGH
1. Go to: 'http://localhost:8080/ACS_Code_StudentPortal/login_register.php'.
2. Click “Login” or “Get Started” → Opens login/register form.  
3. On registration:  
   - Fill details → Password strength shown live  
   - Submit → reCAPTCHA & email sent for verification  
   - Enter code from email → Account activated  
4. On login:  
   - Enter valid credentials → OTP sent to email for extra security.  
   - Enter OTP → Logged into student or admin dashboard  
5. Session auto logs out after 25 minutes of inactivity  
6. Password must be changed monthly (if expired)  
7. Admin can lock/unlock users.


NOTES
- Uses Gmail SMTP with PHPMailer (App Password recommended)  
- OTP expires in a short period for security  
- OTP can be resent if lost  
- MFA (OTP via email) required on every login  
- Self-signed SSL can be enabled locally (optional)  
- Login activity logged in `logins.log`  
- reCAPTCHA v3 runs invisibly and uses a score threshold ≥ 0.5  


Contact
Submitted by: Tamanna Shrestha  
Student ID: 240672386  
Submission Date: 4th July,2025
