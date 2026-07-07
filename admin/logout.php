<?php
require_once __DIR__ . '/includes/auth.php';

// Fonte única de logout — destroi sessão e volta para o login
session_unset();
session_destroy();

header('Location: ' . BASE_URL . 'admin/login.php');
exit;
