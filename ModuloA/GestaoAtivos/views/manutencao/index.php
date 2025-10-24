<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../../controllers/ManutencaoController.php';
$controller = new ManutencaoController();
$manutencoes = $controller->listarManutencoes();
$ativos = $controller->listarAtivos();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Manutenções - Gestão de Ativos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard/index.php">Gestão de Ativos</a>
            <div class="navbar-nav">
                <a class="nav-link" href="../dashboard/index.php">Dashboard</a>
                <a class="nav-link active" href="index.php">Manutenções</a>
                <a class="nav-link" href="../monitoramento/index.php">Monitoramento</a>
                <a class="nav-link" href="../historico/index.php">Histórico</a>
                <a class="nav-link" href="../auth/logout.php">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between mb-4">
            <h2>Manutenções</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#novaManutencao">
                Nova Manutenção
            </button>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ativo</th>
                    <th>Tipo</th>
                    <th>Data</th>
                    <th>Responsável</th>
                    <th>Custo</th>
                    <th>Descrição</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($manutencoes as $manutencao): ?>
                <tr>
                    <td><?= $manutencao['id'] ?></td>
                    <td><?= $manutencao['ativo'] ?></td>
                    <td><?= $manutencao['tipo'] ?></td>
                    <td><?= $manutencao['data'] ?></td>
                    <td><?= $manutencao['responsavel'] ?></td>
                    <td><?= $manutencao['custo'] ?></td>
                    <td><?= $manutencao['descricao'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Nova Manutenção -->
    <div class="modal fade" id="novaManutencao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Manutenção</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="salvar.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Ativo</label>
                            <select name="ativo_id" class="form-control" required>
                                <?php foreach ($ativos as $ativo): ?>
                                    <option value="<?= $ativo['id'] ?>"><?= $ativo['nome'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Tipo</label>
                            <select name="tipo" class="form-control" required>
                                <option value="preventiva">Preventiva</option>
                                <option value="corretiva">Corretiva</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Data</label>
                            <input type="date" name="data" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Responsável Técnico</label>
                            <input type="text" name="responsavel_tecnico" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Custo</label>
                            <input type="number" step="0.01" name="custo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Descrição</label>
                            <textarea name="descricao" class="form-control" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
