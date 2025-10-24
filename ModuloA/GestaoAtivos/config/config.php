<?php
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'gestao_ativos');
}

// Configurações gerais da aplicação
define('BASE_URL', 'http://localhost/DevExperience-Primetrio/ModuloA/GestaoAtivos');

// Configurações da Aplicação
define('APP_NAME', 'Sistema de Gestão de Ativos');
define('APP_VERSION', '1.0.0');

// Configurações de Email
define('MAIL_HOST', 'smtp.exemplo.com');
define('MAIL_USER', 'seu@email.com');
define('MAIL_PASS', 'sua_senha');
define('MAIL_PORT', 587);

// Configurações de Segurança
define('JWT_SECRET', 'sua_chave_secreta_aqui');
define('SESSION_TIMEOUT', 3600); // 1 hora

// Configurações da IA
define('IA_API_KEY', 'sua_chave_api_ia');
define('IA_ENDPOINT', 'https://api.ia.exemplo.com');
