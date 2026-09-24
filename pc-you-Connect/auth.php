<?php

function start_app_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function login_user(int $userId, string $role, string $firstName, string $lastName = ''): void
{
    start_app_session();
    session_regenerate_id(true);

    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = $role;
    $_SESSION['first_name'] = $firstName;
    $_SESSION['last_name'] = $lastName;
}

function current_user_id(): ?int
{
    start_app_session();
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function current_user_role(): ?string
{
    start_app_session();
    return $_SESSION['role'] ?? null;
}

function require_login(string $redirect = 'index.php'): void
{
    if (current_user_id() === null) {
        header("Location: {$redirect}");
        exit;
    }
}

function require_admin(string $redirect = 'index.php'): void
{
    require_login($redirect);

    if (current_user_role() !== 'admin') {
        header("Location: {$redirect}");
        exit;
    }
}

function logout_user(): void
{
    start_app_session();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
