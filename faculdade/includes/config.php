<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'faculdade');

// Configurações do sistema
define('SITE_NAME', 'Sistema de Gerenciamento - Faculdade');
define('BASE_URL', 'http://localhost/faculdade');

// Configuração de fuso horário
date_default_timezone_set('America/Sao_Paulo');

// Configuração de exibição de erros (remover em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>