<?php
session_start();
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'controllers/AuthController.php';

// Verifica se usuário está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: views/auth/login.php');
    exit;
}

// Roteamento básico
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

// Carrega o controlador apropriado
switch ($page) {
    case 'ativos':
        require_once 'controllers/AtivoController.php';
        $controller = new AtivoController();
        break;
    case 'manutencao':
        require_once 'controllers/ManutencaoController.php';
        $controller = new ManutencaoController();
        break;
    default:
        require_once 'controllers/DashboardController.php';
        $controller = new DashboardController();
}

// Executa a ação
if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    header('HTTP/1.1 404 Not Found');
    exit('Página não encontrada');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestão de Ativos Inteligente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Gestão de Ativos IA</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Ativos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Manutenções</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Análises IA</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Bem-vindo ao Sistema de Gestão de Ativos</h1>
        <p>Sistema inteligente para controle e monitoramento de ativos empresariais.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
