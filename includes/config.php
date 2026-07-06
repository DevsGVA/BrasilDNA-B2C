<?php
if (!defined('BASE_URL')) {
    // Domínio fixo após migração para brasildna.com
    // Detecta automaticamente http vs https e o host atual
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'brasildna.com';
    define('BASE_URL', $scheme . '://' . $host . '/');
}
