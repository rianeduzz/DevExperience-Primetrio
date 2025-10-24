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
    <title>Login - Sistema de Gestão de Ativos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #1abc9c;
            --light-gray: #f8f9fa;
            --border-color: #e0e0e0;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .login-container {
            max-width: 400px;
            width: 100%;
            margin: 0 auto;
        }
        
        .login-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 30px;
            text-align: center;
        }
        
        .login-body {
            padding: 30px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-register {
            border: 2px solid var(--secondary-color);
            border-radius: 8px;
            padding: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            padding: 12px 15px;
        }
        
        .brand-logo {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .input-group-icon {
            position: relative;
        }
        
        .input-group-icon .form-control {
            padding-left: 45px;
        }
        
        .input-group-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 5;
        }
        
        .footer-links {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }
        
        .footer-links a {
            color: var(--secondary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <div class="brand-logo">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h3 class="mb-0">Gestão de Ativos</h3>
                    <p class="mb-0 mt-2 opacity-75">Faça login em sua conta</p>
                </div>
                
                <div class="login-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3 input-group-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Seu e-mail" required>
                        </div>
                        
                        <div class="mb-4 input-group-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" class="form-control" id="senha" name="senha" placeholder="Sua senha" required>
                        </div>
                        
                        <button type="submit" class="btn btn-login w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Entrar no Sistema
                        </button>
                    </form>
                    
                    <div class="text-center mb-3">
                        <a href="#" class="text-decoration-none small">
                            <i class="fas fa-key me-1"></i>Esqueceu sua senha?
                        </a>
                    </div>
                    
                    <div class="footer-links">
                        <p class="mb-2">Não tem uma conta?</p>
                        <a href="register.php" class="btn btn-register w-100">
                            <i class="fas fa-user-plus me-2"></i>Criar Nova Conta
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-white mb-0">
                    <small>
                        <i class="fas fa-shield-alt me-1"></i>Sistema seguro | 
                        <i class="fas fa-copyright me-1"></i>2024 Gestão de Ativos
                    </small>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Efeito de foco nos campos
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    if (this.value === '') {
                        this.parentElement.classList.remove('focused');
                    }
                });
            });
            
            // Foco automático no primeiro campo
            document.getElementById('email').focus();
        });
    </script>
</body>
</html>