<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../../controllers/AlertaController.php';
require_once '../../controllers/PrevisaoController.php';

$alertaController = new AlertaController();
$previsaoController = new PrevisaoController();

$alertas = $alertaController->listarAlertasPendentes();
try {
    $previsoes = $previsaoController->listarPrevisoes();
} catch (Exception $e) {
    $previsoes = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Gestão de Ativos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Manutenções</h5>
                        <p class="card-text">Registre e acompanhe manutenções preventivas e corretivas.</p>
                        <a href="../manutencao/index.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Monitoramento</h5>
                        <p class="card-text">Visualize a localização e responsáveis pelos ativos.</p>
                        <a href="../monitoramento/index.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Histórico</h5>
                        <p class="card-text">Consulte o histórico completo de manutenções.</p>
                        <a href="../historico/index.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção de Análise Inteligente -->
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Análise Inteligente</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Insights Automáticos</h6>
                        <ul class="list-group">
                            <li class="list-group-item">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                3 ativos necessitam de manutenção preventiva nos próximos 30 dias
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-chart-line text-info"></i>
                                Custo de manutenção 15% acima da média no último mês
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <canvas id="custosManutencoesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção de Alertas -->
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5>Alertas e Notificações</h5>
            </div>
            <div class="card-body">
                <?php if (empty($alertas)): ?>
                    <p class="text-muted">Nenhum alerta pendente.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($alertas as $alerta): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= $alerta['tipo'] ?></h6>
                                    <small><?= date('d/m/Y', strtotime($alerta['data_alerta'])) ?></small>
                                </div>
                                <p class="mb-1"><?= $alerta['mensagem'] ?></p>
                                <small>Ativo: <?= $alerta['nome_ativo'] ?></small>
                                <button class="btn btn-sm btn-success float-end" 
                                        onclick="marcarResolvido(<?= $alerta['id'] ?>)">
                                    Marcar como resolvido
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Seção de Previsões de Manutenção -->
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5>Previsão de Manutenção (IA)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($previsoes)): ?>
                    <p class="text-muted">Sistema de previsões em manutenção. Tente novamente mais tarde.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Ativo</th>
                                    <th>Probabilidade de Falha</th>
                                    <th>Previsão</th>
                                    <th>Razões</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($previsoes as $previsao): ?>
                                    <tr class="<?= $previsao['probabilidade_falha'] > 80 ? 'table-danger' : 'table-warning' ?>">
                                        <td><?= $previsao['nome_ativo'] ?></td>
                                        <td><?= number_format($previsao['probabilidade_falha'], 1) ?>%</td>
                                        <td><?= date('d/m/Y', strtotime($previsao['data_previsao'])) ?></td>
                                        <td><?= $previsao['razoes'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Youtan Helper Chatbot -->
    <div class="position-fixed bottom-0 end-0 m-3">
        <div class="card" style="width: 300px;" id="chatbot" style="display: none;">
            <div class="card-header bg-primary text-white">
                Youtan Helper
                <button type="button" class="btn-close float-end" onclick="toggleChat()"></button>
            </div>
            <div class="card-body" style="height: 300px; overflow-y: auto;" id="chatMessages">
                <div class="chat-message">
                    <strong>Youtan:</strong> Olá! Como posso ajudar?
                </div>
            </div>
            <div class="card-footer">
                <div class="input-group">
                    <input type="text" class="form-control" id="chatInput" placeholder="Digite sua pergunta...">
                    <button class="btn btn-primary" onclick="enviarMensagem()">Enviar</button>
                </div>
            </div>
        </div>
        <button class="btn btn-primary rounded-circle" onclick="toggleChat()" id="chatButton">
            <i class="fas fa-comments"></i>
        </button>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://kit.fontawesome.com/your-code.js"></script>
    <script>
        // Gráfico de custos
        const ctx = document.getElementById('custosManutencoesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr'],
                datasets: [{
                    label: 'Custos de Manutenção',
                    data: [350, 1200, 800, 150],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            }
        });

        // Funções do chatbot
        function toggleChat() {
            const chatbot = document.getElementById('chatbot');
            chatbot.style.display = chatbot.style.display === 'none' ? 'block' : 'none';
        }

        function enviarMensagem() {
            const input = document.getElementById('chatInput');
            const mensagem = input.value;
            if (mensagem.trim()) {
                const chatMessages = document.getElementById('chatMessages');
                chatMessages.innerHTML += `<div class="chat-message"><strong>Você:</strong> ${mensagem}</div>`;
                // Simular resposta do chatbot
                setTimeout(() => {
                    chatMessages.innerHTML += `<div class="chat-message"><strong>Youtan:</strong> Entendi sua pergunta sobre "${mensagem}". Como posso ajudar?</div>`;
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }, 1000);
                input.value = '';
            }
        }

        function marcarResolvido(alertaId) {
            if (confirm('Marcar este alerta como resolvido?')) {
                fetch('../../controllers/marcar_resolvido.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ alerta_id: alertaId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        }
    </script>
</body>
</html>
