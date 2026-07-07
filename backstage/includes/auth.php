<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Funções base ────────────────────────────────────────────────────────────

function estaLogado(): bool
{
    if (!isset($_SESSION['admin_id'])) return false;
    $tipo = $_SESSION['admin_tipo'] ?? '';
    if ($tipo !== 'admin' && $tipo !== 'super_admin') return false;
    // Session timeout: 2 horas de inatividade
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
        http_response_code(403);
        header('Location: painel.php?erro=acesso_negado');
        exit;
    }
}

// ─── Permissões granulares ────────────────────────────────────────────────────
//
// Ações disponíveis:
//   'criar_post'    | 'editar_post'    | 'excluir_post'
//   'criar_banner'  | 'editar_banner'  | 'excluir_banner'
//   'criar_parceiro'| 'editar_parceiro'| 'excluir_parceiro'
//   'gerenciar_admins'
//
// Regra geral:
//   admin       → pode criar e editar, mas NÃO pode excluir nem gerenciar admins
//   super_admin → acesso total

const PERMISSOES = [
    'admin' => [
        'criar_post',
        'editar_post',
        'criar_banner',
        'editar_banner',
        'criar_parceiro',
        'editar_parceiro',
    ],
    'super_admin' => [
        'criar_post',
        'editar_post',
        'excluir_post',
        'criar_banner',
        'editar_banner',
        'excluir_banner',
        'criar_parceiro',
        'editar_parceiro',
        'excluir_parceiro',
        'gerenciar_admins',
    ],
];

/**
 * Verifica se o usuário logado pode executar determinada ação.
 *
 * Exemplo de uso:
 *   if (canFazer('excluir_post')) { ... }
 */
function canFazer(string $acao): bool
{
    if (!estaLogado()) return false;
    $tipo = $_SESSION['admin_tipo'] ?? '';
    return in_array($acao, PERMISSOES[$tipo] ?? [], true);
}

/**
 * Bloqueia a execução se o usuário não tiver a permissão informada.
 * Retorna JSON de erro para requisições AJAX, redireciona para as demais.
 */
function exigirPermissao(string $acao): void
{
    if (!canFazer($acao)) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['erro' => 'Sem permissão para esta ação.']);
        } else {
            http_response_code(403);
            header('Location: painel.php?erro=acesso_negado');
        }
        exit;
    }
}

// ─── CSRF ─────────────────────────────────────────────────────────────────────

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
