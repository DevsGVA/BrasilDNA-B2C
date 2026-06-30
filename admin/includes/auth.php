<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function estaLogado(): bool
{
    if (!isset($_SESSION['admin_id'])) return false;
    $tipo = $_SESSION['admin_tipo'] ?? '';
    if ($tipo !== 'admin' && $tipo !== 'super_admin') return false;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
        session_unset();
        session_destroy();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

function ehSuperAdmin(): bool
{
    return ($_SESSION['admin_tipo'] ?? '') === 'super_admin';
}

function exigirLogin(): void
{
    if (!estaLogado()) {
        header('Location: login.php');
        exit;
    }
}

function exigirSuperAdmin(): void
{
    if (!estaLogado() || !ehSuperAdmin()) {
        header('Location: login.php');
        exit;
    }
}

function gerarCSRF(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validarCSRF(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
