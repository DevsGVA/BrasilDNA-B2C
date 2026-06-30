<?php
session_start();
require_once __DIR__ . '/../includes/conexao.php';

if (empty($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$allowedExts  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

if (empty($_FILES['file']['tmp_name'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Nenhum arquivo enviado']);
    exit;
}

$ext  = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
$mime = mime_content_type($_FILES['file']['tmp_name']);

if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMimes)) {
    http_response_code(415);
    echo json_encode(['error' => 'Formato inválido. Use JPG, PNG, GIF ou WebP.']);
    exit;
}

if ($_FILES['file']['size'] > 5 * 1024 * 1024) {
    http_response_code(413);
    echo json_encode(['error' => 'Arquivo muito grande. Máximo 5 MB.']);
    exit;
}

$filename  = uniqid('tinymce_') . '.' . $ext;
$uploadDir = __DIR__ . '/../uploads/editor/';

if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $filename)) {
    http_response_code(500);
    echo json_encode(['error' => 'Falha ao salvar imagem.']);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['location' => '/BrasilDNA-Website/uploads/editor/' . $filename]);
