<?php 
require_once '../../config/config.php'; 
require_once '../../controllers/AuthController.php';  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {     
    $auth = new AuthController();     
    $result = $auth->login($_POST['email'], $_POST['senha']);      

    if ($result['success']) {         
        header('Location: ' . $result['redirect']);         
        exit;     
    } else {         
        $error = $result['message'];     
    } 
} 
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Youtan</title>
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

        .login-container {
            width: 100%;
            max-width: 380px;
        }

        .login-card {
            background-color: var(--card-bg);
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 30px 25px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .login-header i {
            font-size: 2.5rem;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }

        .login-header h3 {
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

        .btn-login {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .btn-login:hover {
            background-color: #2389d3;
        }

        .btn-register {
            border: 1px solid var(--secondary-color);
            color: var(--secondary-color);
            background: transparent;
            border-radius: 6px;
            padding: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            background-color: var(--secondary-color);
            color: white;
        }

        .alert {
            border-radius: 6px;
            border: none;
            padding: 10px 12px;
        }

        .text-link {
            font-size: 0.9rem;
            color: var(--secondary-color);
            text-decoration: none;
        }

        .text-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-tools"></i>
                <h3>Youtan</h3>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger d-flex align-items-center mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <input type="email" class="form-control" name="email" placeholder="E-mail" required>
                </div>

                <div class="mb-3">
                    <input type="password" class="form-control" name="senha" placeholder="Senha" required>
                </div>

                <button type="submit" class="btn btn-login w-100 mb-3">
                    Entrar
                </button>
            </form>

            <div class="text-center mb-3">
                <a href="#" class="text-link">Esqueceu sua senha?</a>
            </div>

            <div class="text-center">
                <p class="mb-2">Não tem uma conta?</p>
                <a href="register.php" class="btn btn-register w-100">Criar nova conta</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
