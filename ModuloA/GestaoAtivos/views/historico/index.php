<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../../controllers/HistoricoController.php';
$controller = new HistoricoController();
$historico = [];
if (isset($_GET['ativo']) && $_GET['ativo'] != "") {
    $historico = $controller->listarHistorico($_GET['ativo']);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Histórico - Gestão de Ativos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard/index.php">Gestão de Ativos</a>
            <div class="navbar-nav">
                <a class="nav-link" href="../dashboard/index.php">Dashboard</a>
                <a class="nav-link" href="../manutencao/index.php">Manutenções</a>
                <a class="nav-link" href="../monitoramento/index.php">Monitoramento</a>
                <a class="nav-link active" href="index.php">Histórico</a>
                <a class="nav-link" href="../auth/logout.php">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Histórico de Manutenções</h2>
        <form method="get" action="index.php">
            <div class="mb-3">
                <label for="ativo" class="form-label">Selecione o Ativo</label>
                <select id="ativo" name="ativo" class="form-control" required>
                    <option value="">Selecione...</option>
                    <option value="1">Ativo 1</option>
                    <option value="2">Ativo 2</option>
                </select>
                <button type="submit" class="btn btn-primary mt-2">Consultar</button>
            </div>
        </form>
        <?php if (!empty($historico)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Tipo</th>
                        <th>Custo</th>
                        <th>Técnico</th>
                        <th>Descrição</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historico as $item): ?>
                    <tr>
                        <td><?= $item['data'] ?></td>
                        <td><?= $item['tipo'] ?></td>
                        <td><?= $item['custo'] ?></td>
                        <td><?= $item['tecnico'] ?></td>
                        <td><?= $item['descricao'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button class="btn btn-secondary" onclick="window.print()">Imprimir</button>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function filtrarHistorico() {
            // Implementar filtro
        }

        function exportarPDF() {
            // Implementar exportação PDF
        }

        function exportarExcel() {
            // Implementar exportação Excel
        }
    </script>
</body>
</html>
