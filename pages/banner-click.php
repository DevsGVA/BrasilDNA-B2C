<?php
require_once __DIR__ . '/../includes/conexao.php';

$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id === 0) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT link_url FROM banners WHERE id = :id AND ativo = 1');
$stmt->execute([':id' => $id]);
$banner = $stmt->fetch();

if (!$banner || empty($banner['link_url'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$pdo->prepare('UPDATE banners SET cliques = cliques + 1 WHERE id = :id')
    ->execute([':id' => $id]);

require_once __DIR__ . '/../includes/stats.php';
registrarStat($pdo, 'banner', $id, 'cliques');

$url = trim($banner['link_url']);
if (!preg_match('#^https?://#i', $url)) {
    $url = 'https://' . $url;
}
if (!filter_var($url, FILTER_VALIDATE_URL) || !in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

header('Location: ' . $url);
exit;

