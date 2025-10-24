<?php
require_once '../../controllers/AuthController.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController();
    $result = $auth->create($_POST);
    if ($result['success']) {
        header('Location: login.php');
        exit;
    } else {
        $message = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Sistema de Gestão de Ativos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #1e1e2f;
            --secondary-color: #2a9df4;
            --text-color: #333;
            --input-bg: #f1f1f1;
            --card-bg: #fff;
        }

        body {
            background-color: var(--primary-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-container {
            width: 100%;
            max-width: 400px;
        }

        .register-card {
            background-color: var(--card-bg);
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 30px 25px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .register-header i {
            font-size: 2.5rem;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }

        .register-header h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .form-control {
            border-radius: 6px;
            border: 1px solid #ccc;
            background-color: var(--input-bg);
            padding: 12px 15px;
        }

        .btn-register {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .btn-register:hover {
            background-color: #2389d3;
        }

        .btn-back {
            border: 1px solid var(--secondary-color);
            color: var(--secondary-color);
            background: transparent;
            border-radius: 6px;
            padding: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background-color: var(--secondary-color);
            color: white;
        }

        .alert {
            border-radius: 6px;
            border: none;
            padding: 10px 12px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <i class="fas fa-user-plus"></i>
                <h3>Cadastro de Usuário</h3>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-danger d-flex align-items-center mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome completo" required>
                </div>

                <div class="mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="E-mail" required>
                </div>

                <div class="mb-3">
                    <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required>
                </div>

                <div class="mb-3">
                    <select class="form-control" id="nivel_acesso" name="nivel_acesso">
                        <option value="1">Usuário</option>
                        <option value="2">Administrador</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-register w-100 mb-3">Cadastrar</button>
            </form>

            <div class="text-center">
                <a href="login.php" class="btn btn-back w-100">Voltar para Login</a>
            </div>
        </div>
    </div>
</body>
</html>
