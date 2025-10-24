<?php
require_once '../../controllers/ManutencaoController.php';

$controller = new ManutencaoController();
$ativos = $controller->listarAtivos();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->registrar($_POST);
    if ($result['success']) {
        header('Location: lista.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrar Manutenção</title>
</head>
<body>
    <h2>Registrar Manutenção</h2>
    <form method="POST">
        <select name="ativo_id" required>
            <option value="">Selecione o Ativo</option>
            <?php foreach ($ativos as $ativo): ?>
                <option value="<?= $ativo['id'] ?>"><?= $ativo['nome'] ?></option>
            <?php endforeach; ?>
        </select>

        <select name="tipo" required>
            <option value="preventiva">Preventiva</option>
            <option value="corretiva">Corretiva</option>
        </select>

        <input type="date" name="data" required>
        <input type="text" name="responsavel_tecnico" placeholder="Responsável Técnico" required>
        <input type="number" step="0.01" name="custo" placeholder="Custo" required>
        <textarea name="descricao" placeholder="Descrição do serviço" required></textarea>

        <button type="submit">Registrar Manutenção</button>
    </form>
</body>
</html>
