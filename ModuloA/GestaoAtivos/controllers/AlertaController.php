<?php
require_once(__DIR__ . '/../config/Database.php');

class AlertaController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function verificarAlertas() {
        // Verificar manutenções programadas
        $this->verificarManutencoesProgramadas();
        // Verificar garantias
        $this->verificarGarantias();
        // Verificar devoluções
        $this->verificarDevolucoes();
    }

    public function listarAlertasPendentes() {
        $sql = "SELECT a.*, at.nome as nome_ativo 
                FROM alertas a 
                LEFT JOIN ativos at ON a.ativo_id = at.id 
                WHERE a.status = 'pendente' 
                ORDER BY a.data_alerta ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marcarComoResolvido($alerta_id, $usuario_id) {
        $sql = "UPDATE alertas SET status = 'resolvido' WHERE id = ?";
        $this->db->query($sql, [$alerta_id]);
        
        // Registrar log
        $sql = "INSERT INTO logs_notificacoes (alerta_id, acao, usuario_id) VALUES (?, 'resolvido', ?)";
        $this->db->query($sql, [$alerta_id, $usuario_id]);
    }

    private function verificarManutencoesProgramadas() {
        $sql = "SELECT id, ativo_id FROM manutencoes 
                WHERE data >= CURDATE() AND data <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        $manutencoes = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($manutencoes as $manutencao) {
            $this->criarAlerta('manutencao', 
                             'Manutenção programada para próximos 7 dias', 
                             $manutencao['ativo_id']);
        }
    }

    private function criarAlerta($tipo, $mensagem, $ativo_id) {
        $sql = "INSERT INTO alertas (tipo, mensagem, ativo_id, data_alerta) 
                VALUES (?, ?, ?, CURDATE())";
        $this->db->query($sql, [$tipo, $mensagem, $ativo_id]);
    }
}
