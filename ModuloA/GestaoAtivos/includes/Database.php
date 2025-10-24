<?php
if (!class_exists('Database')) {
    class Database {
        private static $instance = null;
        private $connection;

        private function __construct() {
            $this->connect();
        }

        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function connect() {
            $host = defined('DB_HOST') ? DB_HOST : (getenv('DB_HOST') ?: 'localhost');
            $user = defined('DB_USER') ? DB_USER : (getenv('DB_USER') ?: 'root');
            $pass = defined('DB_PASS') ? DB_PASS : (getenv('DB_PASS') ?: '');
            $name = defined('DB_NAME') ? DB_NAME : (getenv('DB_NAME') ?: '');

            try {
                $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                $this->connection = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Em dev mostrar erro; em produção logar e mostrar mensagem genérica
                die("Erro na conexão: " . $e->getMessage());
            }
        }

        public function query($sql, $params = []) {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        }

        public function lastInsertId() {
            return $this->connection->lastInsertId();
        }
    }
}
