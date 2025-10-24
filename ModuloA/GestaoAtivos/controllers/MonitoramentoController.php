<?php
require_once(__DIR__ . '/../config/Database.php');

class MonitoramentoController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function listarAtivosComLocalizacao($categoria = null) {
        // Corrigido para garantir o relacionamento correto com localizacoes
        $sql = "SELECT a.*, u.nome as responsavel, l.descricao as localizacao 
                FROM ativos a 
                LEFT JOIN usuarios u ON a.responsavel_id = u.id 
                LEFT JOIN localizacoes l ON a.localizacao_id = l.id";
        if ($categoria) {
            $sql .= " WHERE a.categoria = ?";
            return $this->db->query($sql, [$categoria])->fetchAll(PDO::FETCH_ASSOC);
        }
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function atualizarLocalizacao($ativo_id, $localizacao_id, $responsavel_id) {
        $sql = "UPDATE ativos SET localizacao_id = ?, responsavel_id = ? WHERE id = ?";
        return $this->db->query($sql, [$localizacao_id, $responsavel_id, $ativo_id]);
    }
}
