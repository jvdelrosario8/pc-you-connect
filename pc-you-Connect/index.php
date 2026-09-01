<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PC-YOU! Connect</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

    <div id="errorPopup" class="error-popup">
        <div class="error-popup-box">

            <button class="error-close" onclick="closeErrorPopup()">
                ×
            </button>

            <div class="error-icon">
                !
            </div>

            <h3 id="errorTitle">Login Failed</h3>

            <p id="errorMessage"></p>

            <button class="error-ok" onclick="closeErrorPopup()">
                OK
            </button>

        </div>
    </div>

    <header class="navbar">
        <div class="brand">
            <img src="assets/icon.jpg" alt="PC-YOU Connect logo" class="brand-logo">
            <span>PC-YOU! Connect</span>
        </div>
        <button class="nav-login" id="openLogin">Log in</button>
    </header>

    <main class="hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Connect to Your Campus World</h1>
            <p class="subtitle">Your integrated portal for campus facilities, lost-and-found, announcements,<br
                    class="desktop-break"> and service notifications - all in one place.</p>

            <div class="features">
                <div class="feature">
                    <span class="feature-icon">
                        <i class="fa-solid fa-calendar-check"></i>
                    </span>
                    <span>Book facilities instantly</span>
                </div>

                <div class="feature">
                    <span class="feature-icon">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <span>Track lost &amp; found items in real-time</span>
                </div>

                <div class="feature">
                    <span class="feature-icon">
                        <i class="fa-solid fa-bullhorn"></i>
                    </span>
                    <span>Stay updated with announcements</span>
                </div>
            </div>
        </div>
    </main>

    <!-- Login selection modal -->
    <div class="modal-backdrop" id="loginModal" aria-hidden="true">
        <section class="modal small-modal" role="dialog" aria-modal="true" aria-labelledby="loginTitle">
            <button class="close-btn" data-close aria-label="Close">×</button>
            <h2 id="loginTitle">Log in</h2>
            <div class="role-buttons">
                <button class="role-btn secondary" id="studentChoice">Student log in</button>
                <button class="role-btn primary" id="adminChoice">Admin log in</button>
            </div>
        </section>
    </div>

    <!-- Student login modal -->
    <div class="modal-backdrop" id="studentModal" aria-hidden="true">
        <section class="modal" role="dialog" aria-modal="true" aria-labelledby="studentTitle">
            <button class="close-btn" data-close aria-label="Close">×</button>
            <h2 id="studentTitle">Student Log in</h2>
            <form action="login.php" method="POST" autocomplete="on">
                <input type="hidden" name="login_type" value="student">
                <label for="studentID">Student Number</label>
                <div class="input-wrap">
                    <span class="input-icon"></span>
                    <input id="studentID" name="studentID" type="text" placeholder="Enter your Student Number" required>
                </div>

                <label for="studentPassword">Password</label>
                <div class="input-wrap">
                    <span class="input-icon"></span>

                    <input id="studentPassword" name="password" type="password" placeholder="Enter your password"
                        required>

                    <button type="button" class="eye-btn" data-toggle="studentPassword" aria-label="Show password">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>

                <label class="remember"><input type="checkbox" name="remember"> <span>Remember me</span></label>
                <button class="submit-btn" type="submit">Log in</button>
                <button class="back-btn" type="button" data-back="student">Back</button>
            </form>
        </section>
    </div>

    <!-- Admin login modal -->
    <div class="modal-backdrop" id="adminModal" aria-hidden="true">
        <section class="modal" role="dialog" aria-modal="true" aria-labelledby="adminTitle">
            <button class="close-btn" data-close aria-label="Close">×</button>
            <h2 id="adminTitle">Admin Log in</h2>
            <form action="login.php" method="POST" autocomplete="on">
                <input type="hidden" name="login_type" value="admin">
                <label for="userID">User ID</label>
                <div class="input-wrap">
                    <span class="input-icon"></span>
                    <input id="userID" name="userID" type="text" placeholder="Enter your User ID" required>
                </div>

                <label for="adminPassword">Password</label>
                <div class="input-wrap">
                    <span class="input-icon"></span>

                    <input id="adminPassword" name="password" type="password" placeholder="Enter your password"
                        required>

                    <button type="button" class="eye-btn" data-toggle="adminPassword" aria-label="Show password">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>

                <label class="remember"><input type="checkbox" name="remember"> <span>Remember me</span></label>
                <button class="submit-btn" type="submit">Log in</button>
                <button class="back-btn" type="button" data-back="admin">Back</button>
            </form>
        </section>
    </div>

    <script src="script.js"></script>

    <script>
        const params = new URLSearchParams(window.location.search);
        const error = params.get('error');

        if (error) {

            const popup = document.getElementById('errorPopup');
            const title = document.getElementById('errorTitle');
            const message = document.getElementById('errorMessage');

            if (error === 'student_password') {
                title.textContent = 'Incorrect Password';
                message.textContent = 'The password you entered is incorrect.';
            }

            else if (error === 'student_not_found') {
                title.textContent = 'Student Not Found';
                message.textContent = 'No student account was found with that Student Number.';
            }

            else if (error === 'admin_password') {
                title.textContent = 'Incorrect Password';
                message.textContent = 'The password you entered is incorrect.';
            }

            else if (error === 'admin_not_found') {
                title.textContent = 'Admin Not Found';
                message.textContent = 'No admin account was found with that User ID.';
            }

            else {
                title.textContent = 'Login Error';
                message.textContent = 'Something went wrong. Please try again.';
            }

            popup.classList.add('show');

            // Remove error from URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        function closeErrorPopup() {
            document.getElementById('errorPopup').classList.remove('show');
        }
    </script>
</body>

</html>