<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
	echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
	exit;
}

try {
	require_once __DIR__ . '/PrevisaoController.php';
	$ctrl = new PrevisaoController();
	$ret = $ctrl->gerarPrevisoes(); // retorna array de previsões geradas
	$count = is_array($ret) ? count($ret) : 0;
	echo json_encode(['success' => true, 'message' => "Previsões geradas: {$count}", 'count' => $count, 'data' => $ret]);
} catch (Exception $e) {
	echo json_encode(['success' => false, 'message' => 'Erro ao gerar previsões: ' . $e->getMessage()]);
}
