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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Gestão de Ativos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
    </script>
</body>
</html>
