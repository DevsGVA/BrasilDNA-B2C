<?php
require_once __DIR__ . '/includes/auth.php';

// Apaga todos os dados da sessão (desloga o admin).
session_unset();
session_destroy();

header('Location: login.php');
exit;
