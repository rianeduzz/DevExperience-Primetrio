<?php
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'gestao_ativos');

// carrega config geral se existir
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

// inclui a implementação única da classe Database (use require_once para evitar múltiplas inclusões)
if (file_exists(__DIR__ . '/../includes/Database.php')) {
    require_once __DIR__ . '/../includes/Database.php';
}

// OBS: A classe Database foi removida deste arquivo para evitar redeclaração.
class Database {
    private $conexao;

    public function __construct() {
        try {
            $this->conexao = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
            );
            $this->conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Erro na conexão: " . $e->getMessage());
        }
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            die("Erro na consulta: " . $e->getMessage());
        }
    }
}
?>
