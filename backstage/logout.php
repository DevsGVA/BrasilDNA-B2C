<?php
require_once __DIR__ . '/includes/auth.php';

// Destroi sessão e redireciona para o login unificado
session_unset();
session_destroy();

header('Location: login.php');
exit;
