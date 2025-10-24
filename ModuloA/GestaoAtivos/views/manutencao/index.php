<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Nova Manutenção</title>
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
        <h2>Nova Manutenção</h2>
        <div id="mensagem"></div>
        
        <form id="formManutencao" method="post">
            <div class="mb-3">
                <label for="ativo_id">ID do Ativo</label>
                <input type="number" class="form-control" id="ativo_id" name="ativo_id" required>
            </div>

            <div class="mb-3">
                <label for="tipo">Tipo</label>
                <select class="form-control" id="tipo" name="tipo" required>
                    <option value="corretiva">Corretiva</option>
                    <option value="preventiva">Preventiva</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="data">Data</label>
                <input type="date" class="form-control" id="data" name="data" required>
            </div>

            <div class="mb-3">
                <label for="custo">Custo</label>
                <input type="number" step="0.01" class="form-control" id="custo" name="custo" required>
            </div>

            <div class="mb-3">
                <label for="descricao">Descrição</label>
                <textarea class="form-control" id="descricao" name="descricao" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Salvar</button>
        </form>
    </div>

    <script>
    document.getElementById('formManutencao').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const dados = {
            ativo_id: document.getElementById('ativo_id').value,
            tipo: document.getElementById('tipo').value,
            data: document.getElementById('data').value,
            custo: document.getElementById('custo').value,
            descricao: document.getElementById('descricao').value
        };

        fetch('../api/salvar_manutencao.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dados)
        })
        .then(response => response.json())
        .then(data => {
            const mensagem = document.getElementById('mensagem');
            mensagem.className = `alert ${data.success ? 'alert-success' : 'alert-danger'}`;
            mensagem.textContent = data.message;
            
            if (data.success) {
                document.getElementById('formManutencao').reset();
            }
        })
        .catch(error => {
            document.getElementById('mensagem').className = 'alert alert-danger';
            document.getElementById('mensagem').textContent = 'Erro ao salvar. Tente novamente.';
        });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
