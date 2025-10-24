<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../../controllers/MonitoramentoController.php';
$controller = new MonitoramentoController();
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : null;
$ativos = $controller->listarAtivosComLocalizacao($categoria);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Monitoramento - Gestão de Ativos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard/index.php">Gestão de Ativos</a>
            <div class="navbar-nav">
                <a class="nav-link" href="../dashboard/index.php">Dashboard</a>
                <a class="nav-link" href="../manutencao/index.php">Manutenções</a>
                <a class="nav-link active" href="index.php">Monitoramento</a>
                <a class="nav-link" href="../historico/index.php">Histórico</a>
                <a class="nav-link" href="../auth/logout.php">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Monitoramento de Ativos</h2>
        <!-- RF06: Visualização em tempo real, busca/filtro, atualização manual -->
        <div class="row mb-4">
            <div class="col">
                <input type="text" id="busca" class="form-control" placeholder="Buscar ativo..." onkeyup="filtrarAtivos()">
            </div>
            <div class="col">
                <select id="categoria" class="form-control" onchange="filtrarAtivos()">
                    <option value="">Todas as Categorias</option>
                    <option value="equipamento">Equipamentos</option>
                    <option value="veiculo">Veículos</option>
                    <option value="ferramenta">Ferramentas</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Localização Atual</th>
                        <th>Responsável</th>
                        <th>Última Atualização</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="tabelaAtivos">
                    <?php foreach ($ativos as $ativo): ?>
                    <tr>
                        <td><?= $ativo['id'] ?></td>
                        <td><?= $ativo['nome'] ?></td>
                        <td><?= ucfirst($ativo['categoria']) ?></td>
                        <td><?= $ativo['localizacao'] ?></td>
                        <td><?= $ativo['responsavel'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($ativo['updated_at'])) ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="atualizarLocalizacao(<?= $ativo['id'] ?>)">
                                Atualizar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function filtrarAtivos() {
            const busca = document.getElementById('busca').value.toLowerCase();
            const categoria = document.getElementById('categoria').value;
            const tabela = document.getElementById('tabelaAtivos');
            const linhas = tabela.getElementsByTagName('tr');

            for (let i = 1; i < linhas.length; i++) {
                const colunaNome = linhas[i].getElementsByTagName('td')[1];
                const colunaCategoria = linhas[i].getElementsByTagName('td')[2];
                const textoNome = colunaNome.textContent || colunaNome.innerText;
                const textoCategoria = colunaCategoria.textContent || colunaCategoria.innerText;

                if (textoNome.toLowerCase().indexOf(busca) > -1 && 
                    (categoria === "" || textoCategoria.toLowerCase() === categoria)) {
                    linhas[i].style.display = "";
                } else {
                    linhas[i].style.display = "none";
                }
            }
        }

        function atualizarLocalizacao(id) {
            // Exemplo de atualização manual (RF06)
            const novaLocalizacao = prompt("Informe a nova localização do ativo:");
            if (novaLocalizacao) {
                // Aqui você faria uma requisição AJAX para atualizar no backend
                alert("Localização atualizada para: " + novaLocalizacao);
                // Atualize a página ou a linha conforme necessário
            }
        }
    </script>
</body>
</html>
