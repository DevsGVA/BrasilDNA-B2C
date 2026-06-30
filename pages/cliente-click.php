<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

$id = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id <= 0) { http_response_code(400); exit; }

try {
    require_once __DIR__ . '/../includes/conexao.php';
    require_once __DIR__ . '/../includes/stats.php';
    registrarStat($pdo, 'cliente', $id, 'cliques');
    echo 'ok';
} catch (\Throwable $e) {
    http_response_code(500);
}
