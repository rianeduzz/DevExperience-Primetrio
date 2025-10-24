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

    <!-- Youtan Helper Chat -->
    <div id="youtan-chat" class="chat-widget">
        <button class="chat-button" onclick="toggleChat()">
            <i class="fas fa-robot"></i> Youtan Helper
        </button>
        
        <div id="chat-container" class="chat-container" style="display:none;">
            <div class="chat-header">
                <h5><i class="fas fa-robot"></i> Youtan Helper</h5>
                <button class="close-btn" onclick="toggleChat()">&times;</button>
            </div>
            <div id="chat-messages" class="chat-messages">
                <div class="message bot">
                    Olá! Sou o Youtan Helper, seu assistente virtual. 😊<br>
                    Posso ajudar você com dúvidas sobre:<br>
                    • Cadastro de manutenções<br>
                    • Localização de ativos<br>
                    • Relatórios e muito mais!<br><br>
                    Gostaria de ver a lista de perguntas frequentes?<br>
                    <div class="chat-options">
                        <button onclick="mostrarFAQs()" class="option-btn">Sim, por favor</button>
                        <button onclick="recusarAjuda()" class="option-btn">Não, obrigado</button>
                    </div>
                </div>
            </div>
            <div class="chat-input">
                <input type="text" id="chat-input" 
                       placeholder="Digite sua pergunta..."
                       onkeypress="if(event.key === 'Enter') enviarPergunta()">
                <button onclick="enviarPergunta()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <style>
    .chat-widget {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
    }
    .chat-button {
        background: #007bff;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
    }
    .chat-container {
        position: fixed;
        bottom: 80px;
        right: 20px;
        width: 300px;
        height: 400px;
        background: white;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
    }
    .chat-header {
        padding: 10px;
        background: #007bff;
        color: white;
        display: flex;
        justify-content: space-between;
    }
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 10px;
    }
    .chat-input {
        padding: 10px;
        border-top: 1px solid #ddd;
        display: flex;
    }
    .chat-input input {
        flex: 1;
        margin-right: 10px;
    }
    .message {
        margin: 5px 0;
        padding: 8px;
        border-radius: 5px;
    }
    .message.bot {
        background: #f1f1f1;
    }
    .message.user {
        background: #007bff;
        color: white;
        margin-left: auto;
    }
    .chat-options {
        margin-top: 10px;
        display: flex;
        gap: 10px;
    }
    .option-btn {
        padding: 5px 10px;
        border: 1px solid #007bff;
        background: white;
        color: #007bff;
        border-radius: 15px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .option-btn:hover {
        background: #007bff;
        color: white;
    }
    .faq-category {
        font-weight: bold;
        color: #0056b3;
        margin-top: 10px;
    }
    </style>

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

        function toggleChat() {
            const container = document.getElementById('chat-container');
            if (container.style.display === 'none') {
                container.style.display = 'flex';
                carregarFAQs(); // Carrega as FAQs quando abrir o chat
            } else {
                container.style.display = 'none';
            }
        }

        function enviarPergunta() {
            const input = document.getElementById('chat-input');
            const mensagem = input.value.trim().toLowerCase();
            if (!mensagem) return;

            // Adiciona mensagem do usuário
            addMessage(mensagem, 'user');
            input.value = '';

            // Trata respostas simples sim/não
            if (mensagem === 'sim' || mensagem === 'yes' || mensagem === 's') {
                mostrarFAQs();
                return;
            } else if (mensagem === 'não' || mensagem === 'nao' || mensagem === 'no' || mensagem === 'n') {
                recusarAjuda();
                return;
            }

            // Se não for sim/não, envia para o backend
            fetch('../../api/chatbot.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ pergunta: mensagem })
            })
            .then(response => response.json())
            .then(data => {
                const resposta = data.resposta.replace(/\n/g, '<br>');
                addMessage(resposta, 'bot');
            })
            .catch(() => {
                addMessage('Desculpe, tive um problema. Tente novamente.', 'bot');
            });
        }

        function mostrarFAQs() {
            addMessage('Ótimo! Aqui estão as principais dúvidas por categoria:', 'bot');
            
            // Adiciona mensagem de carregamento
            const loadingMessage = addMessage('Carregando perguntas frequentes...', 'bot');
            
            fetch('../../api/faq.php')
                .then(response => response.json())
                .then(data => {
                    // Remove mensagem de carregamento
                    loadingMessage.remove();
                    
                    let categorias = {};
                    data.faqs.forEach(faq => {
                        if (!categorias[faq.categoria]) {
                            categorias[faq.categoria] = [];
                        }
                        categorias[faq.categoria].push(faq);
                    });

                    Object.keys(categorias).forEach(categoria => {
                        addMessage(`<div class="faq-category">${categoria}:</div>`, 'bot');
                        categorias[categoria].forEach(faq => {
                            const div = document.createElement('div');
                            div.className = 'faq-item';
                            div.textContent = faq.pergunta;
                            div.onclick = () => {
                                addMessage(faq.pergunta, 'user');
                                addMessage(faq.resposta + '<br><br>' + faq.exemplo_pratico, 'bot');
                            };
                            document.getElementById('chat-messages').appendChild(div);
                        });
                    });
                });
        }

        function recusarAjuda() {
            addMessage('Ok! Se precisar de ajuda, é só digitar sua pergunta ou clicar em uma das opções quando elas aparecerem.', 'bot');
        }

        function addMessage(text, type) {
            const messages = document.getElementById('chat-messages');
            const div = document.createElement('div');
            div.className = `message ${type}`;
            div.innerHTML = text;
            messages.appendChild(div);
            messages.scrollTop = messages.scrollHeight;
        }

        // Adicionar após toggleChat()
        function carregarFAQs() {
            fetch('../../api/faq.php')
                .then(response => response.json())
                .then(data => {
                    const faqList = document.getElementById('faq-list');
                    faqList.innerHTML = '';
                    data.perguntas.forEach(pergunta => {
                        const div = document.createElement('div');
                        div.className = 'faq-item';
                        div.textContent = pergunta;
                        div.onclick = () => {
                            document.getElementById('chat-input').value = pergunta;
                            enviarPergunta();
                        };
                        faqList.appendChild(div);
                    });
                });
        }
    </script>
</body>
</html>
