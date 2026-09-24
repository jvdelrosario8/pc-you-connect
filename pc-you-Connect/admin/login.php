<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — PC-YOU! Connect</title>
</head>
<body>

    <h1>PC-YOU! Connect</h1>
    <p>Admin Login</p>

    <!-- Error message -->
    <?php if (isset($_GET['error'])): ?>
        <p>
            <?php
                $errors = [
                    'invalid'  => 'Incorrect email or password.',
                    'noadmin'  => 'Your account does not have admin access.',
                    'required' => 'Please fill in all fields.',
                ];
                echo htmlspecialchars($errors[$_GET['error']] ?? 'An error occurred.');
            ?>
        </p>
    <?php endif; ?>

    <!-- Success message (shown after logout) -->
    <?php if (isset($_GET['logout'])): ?>
        <p>You have been logged out.</p>
    <?php endif; ?>

    <!-- Admin login form -->
    <form action="../login.php" method="POST">

        <!-- Hidden field so login.php knows this came from the admin login page -->
        <input type="hidden" name="login_type" value="admin">

        <label for="userID">User ID</label>
        <input
            type="text"
            id="userID"
            name="userID"
            placeholder="Enter your User ID"
            required
            autofocus
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            placeholder="Enter your password"
            required
        >

        <button type="submit">Sign in as Admin</button>

    </form>

    <!-- Back to student login -->
    <a href="../index.php">Back to Student Login</a>

</body>
</html>
