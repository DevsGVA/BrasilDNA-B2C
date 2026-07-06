<?php
/**
 * super-admin/upload_image.php
 * Endpoint de upload de imagens para o TinyMCE (corpo dos posts).
 * Salva em: /uploads/posts/<arquivo>
 * Retorna JSON: { "location": "<url_publica>" }
 */
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/includes/auth.php';

exigirLogin();

header('Content-Type: application/json; charset=utf-8');

// Apenas POST com arquivo
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['file']['tmp_name'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Nenhum arquivo recebido.']);
    exit;
}

$allowedExts  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

$ext   = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $_FILES['file']['tmp_name']);
finfo_close($finfo);

if (!in_array($ext, $allowedExts, true) || !in_array($mime, $allowedMimes, true)) {
    http_response_code(422);
    echo json_encode(['error' => 'Formato inválido. Use JPG, PNG, GIF ou WebP.']);
    exit;
}

if (($_FILES['file']['size'] ?? 0) > 5 * 1024 * 1024) {
    http_response_code(422);
    echo json_encode(['error' => 'Imagem muito grande. Máximo 5 MB.']);
    exit;
}

// Pasta de destino: <raiz>/uploads/posts/
$uploadDir = dirname(__DIR__) . '/uploads/posts';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
    http_response_code(500);
    echo json_encode(['error' => 'Não foi possível criar a pasta de uploads.']);
    exit;
}

$filename = uniqid('post_', true) . '.' . $ext;
$destino  = $uploadDir . '/' . $filename;

if (!move_uploaded_file($_FILES['file']['tmp_name'], $destino)) {
    http_response_code(500);
    echo json_encode(['error' => 'Falha ao salvar a imagem no servidor.']);
    exit;
}

// URL pública — dinâmica, funciona em qualquer domínio
$baseUrl  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
          . '://' . $_SERVER['HTTP_HOST'];
$location = $baseUrl . '/uploads/posts/' . $filename;

echo json_encode(['location' => $location]);