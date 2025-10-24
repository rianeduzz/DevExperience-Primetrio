<?php
require_once(__DIR__ . '/../config/Database.php');

class ChatbotController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function processarPergunta($pergunta) {
        $palavras = explode(' ', strtolower($pergunta));
        
        $sql = "SELECT * FROM chatbot_conhecimento WHERE ";
        $condicoes = [];
        $params = [];
        
        foreach ($palavras as $palavra) {
            if (strlen($palavra) > 3) {
                $condicoes[] = "palavras_chave LIKE ?";
                $params[] = "%$palavra%";
            }
        }
        
        if (empty($condicoes)) {
            return $this->getRespostaPadrao();
        }
        
        $sql .= implode(' OR ', $condicoes);
        $resultado = $this->db->query($sql, $params)->fetch(PDO::FETCH_ASSOC);
        
        return $resultado ? $resultado['resposta'] : $this->getRespostaPadrao();
    }

    private function getRespostaPadrao() {
        return "Desculpe, não entendi sua pergunta. Tente perguntar sobre:\n- Como cadastrar manutenções\n- Como verificar localizações\n- Como gerar relatórios";
    }
}
