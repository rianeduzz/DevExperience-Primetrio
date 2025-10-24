<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

try {
    require_once '../controllers/ManutencaoController.php';

    $dados = json_decode(file_get_contents('php://input'), true);

    if (!$dados) {
        throw new Exception('Dados inválidos');
    }

    $controller = new ManutencaoController();
    $resultado = $controller->salvarManutencao($dados);

    echo json_encode(['success' => true, 'message' => 'Manutenção cadastrada com sucesso!']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar: ' . $e->getMessage()]);
}
