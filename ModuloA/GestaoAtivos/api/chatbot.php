<?php
header('Content-Type: application/json');
require_once '../config/Database.php';

$input = json_decode(file_get_contents('php://input'), true);
$pergunta = strtolower($input['pergunta']);

$db = new Database();

// Busca resposta na base de conhecimento
$sql = "SELECT resposta FROM chatbot_respostas WHERE ? LIKE CONCAT('%', palavra_chave, '%') LIMIT 1";
$result = $db->query($sql, [$pergunta])->fetch(PDO::FETCH_ASSOC);

if ($result) {
    echo json_encode(['resposta' => $result['resposta']]);
} else {
    echo json_encode([
        'resposta' => "Desculpe, não entendi sua pergunta. Você pode perguntar sobre:\n" .
                     "- Como cadastrar manutenções\n" .
                     "- Como verificar localização de ativos\n" .
                     "- Como gerar relatórios"
    ]);
}
