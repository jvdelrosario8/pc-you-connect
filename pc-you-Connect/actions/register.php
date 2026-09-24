<?php
session_start();

// ── Only accept POST ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../register.php");
    exit;
}

// ── Collect & sanitize inputs ────────────────────────────────
$name     = trim($_POST['name']     ?? '');
$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');
$confirm  = trim($_POST['confirm']  ?? '');
$role     = trim($_POST['role']     ?? '');

// ── Validation ───────────────────────────────────────────────

// 1. Required fields
if (empty($name) || empty($email) || empty($password) || empty($confirm) || empty($role)) {
    header("Location: ../register.php?error=required");
    exit;
}

// 2. Name length (2–100 characters)
if (strlen($name) < 2 || strlen($name) > 100) {
    header("Location: ../register.php?error=invalid_name");
    exit;
}

// 3. Valid email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../register.php?error=invalid_email");
    exit;
}

// 4. Role must be one of the allowed values (no one can self-register as admin)
if (!in_array($role, ['student', 'faculty', 'staff'])) {
    header("Location: ../register.php?error=invalid_role");
    exit;
}

// 5. Password minimum 8 characters
if (strlen($password) < 8) {
    header("Location: ../register.php?error=password_short");
    exit;
}

// 6. Passwords must match
if ($password !== $confirm) {
    header("Location: ../register.php?error=password_mismatch");
    exit;
}

// 7. Hash the password — never store plain text
$hashed = password_hash($password, PASSWORD_DEFAULT);

// ── TODO: Uncomment when database is ready ───────────────────
/*
require '../config/db.php';

// Check if email is already taken
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    header("Location: ../register.php?error=email_taken");
    exit;
}

// Insert the new user
$stmt = $pdo->prepare("
    INSERT INTO users (name, email, password, role)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$name, $email, $hashed, $role]);
*/

// ── Temporary success (remove when DB is ready) ──────────────
header("Location: ../index.php?success=registered");
exit;
?>
