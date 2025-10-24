<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/Database.php');
require_once(__DIR__ . '/../models/User.php');

class AuthController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function authenticate($email, $senha) {
        $user = new User($email, $senha);
        if ($user->getId() && password_verify($senha, $user->getPassword())) {
            return $user;
        }
        return false;
    }

    public function create($dados) {
        try {
            $senha_hash = password_hash($dados['senha'], PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES (?, ?, ?, ?)";
            $nivel_acesso = isset($dados['nivel_acesso']) ? $dados['nivel_acesso'] : 1;
            
            $stmt = $this->db->query($sql, [
                $dados['nome'],
                $dados['email'],
                $senha_hash,
                $nivel_acesso
            ]);

            return ['success' => true, 'message' => 'Usuário cadastrado com sucesso!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erro ao cadastrar: ' . $e->getMessage()];
        }
    }

    public function login($email, $senha) {
        if (empty($email) || empty($senha)) {
            return ['success' => false, 'message' => 'Preencha todos os campos'];
        }

        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $this->db->query($sql, [$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            session_start();
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_name'] = $usuario['nome'];
            $_SESSION['user_nivel'] = $usuario['nivel_acesso'];
            
            return ['success' => true, 'redirect' => BASE_URL . '/views/dashboard/index.php'];
        }

        return ['success' => false, 'message' => 'Email ou senha inválidos'];
    }
}
