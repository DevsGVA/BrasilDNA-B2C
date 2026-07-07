<?php
/**
 * backstage/includes/auth.php
 *
 * Sistema de autenticação unificado para o painel backstage.
 * - Usa a mesma sessão do admin/ (login/logout compartilhados).
 * - super_admin → acesso total.
 * - admin       → acesso parcial (sem Admins, sem exclusões críticas).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Garante que BASE_URL está disponível mesmo se config.php ainda não foi carregado
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../../includes/config.php';
}

// ── Verifica se há sessão ativa (admin ou super_admin) ──────────────────
function estaLogado(): bool
{
    if (!isset($_SESSION['admin_id'])) return false;

    $tipo = $_SESSION['admin_tipo'] ?? '';
    if ($tipo !== 'admin' && $tipo !== 'super_admin') return false;

    // Timeout: 2 horas de inatividade
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
        session_unset();
        session_destroy();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

// ── Helpers de tipo ──────────────────────────────────────────────────
function ehSuperAdmin(): bool
{
    return ($_SESSION['admin_tipo'] ?? '') === 'super_admin';
}

// ── Mapa de permissões por tipo ─────────────────────────────────────────
function canFazer(string $permissao): bool
{
    if (!estaLogado()) return false;
    if (ehSuperAdmin()) return true;

    $permitidas = [
        'criar_post',
        'editar_post',
        'criar_banner',
        'editar_banner',
        'criar_parceiro',
        'editar_parceiro',
        'aprovar_parceiro',
        'rejeitar_parceiro',
    ];

    return in_array($permissao, $permitidas, true);
}

// ── Guards ──────────────────────────────────────────────────────────────

/** Redireciona para o login do admin/ se não estiver autenticado. */
function exigirLogin(): void
{
    if (!estaLogado()) {
        header('Location: ' . BASE_URL . 'admin/login.php');
        exit;
    }
}

/** Redireciona para o login se não for super_admin. */
function exigirSuperAdmin(): void
{
    if (!estaLogado() || !ehSuperAdmin()) {
        header('Location: ' . BASE_URL . 'admin/login.php');
        exit;
    }
}

/**
 * Exige uma permissão específica.
 * Se o usuário não a tiver, redireciona para o painel com aviso.
 */
function exigirPermissao(string $permissao): void
{
    if (!canFazer($permissao)) {
        header('Location: ' . BASE_URL . 'backstage/painel.php?erro=acesso_negado');
        exit;
    }
}

// ── CSRF ──────────────────────────────────────────────────────────────────
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
