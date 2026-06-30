<?php
if (!defined('BASE_URL')) {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
    $base = preg_replace('#/(pages|admin|clientes)(/.*)?$#', '', $scriptDir);
    define('BASE_URL', rtrim($base, '/') . '/');
}
