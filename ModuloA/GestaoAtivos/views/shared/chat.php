<!-- Youtan Helper Chat Widget -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

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
                Olá! Como posso ajudar? 😊<br>
                Você pode me perguntar sobre:
                <ul>
                    <li>Cadastro de manutenções</li>
                    <li>Localização de ativos</li>
                    <li>Geração de relatórios</li>
                </ul>
            </div>
        </div>
        <div class="chat-input">
            <input type="text" id="chat-input" placeholder="Digite sua pergunta..."
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
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.chat-container {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 300px;
    height: 400px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
}

.message {
    margin: 5px;
    padding: 8px 12px;
    border-radius: 15px;
    max-width: 80%;
}

.message.bot {
    background: #f0f2f5;
    align-self: flex-start;
}

.message.user {
    background: #007bff;
    color: white;
    align-self: flex-end;
}
</style>

<script>
function toggleChat() {
    const container = document.querySelector('.chat-container');
    container.style.display = container.style.display === 'none' ? 'flex' : 'none';
}

async function enviarPergunta() {
    const input = document.getElementById('userInput');
    const mensagem = input.value.trim();
    if (!mensagem) return;

    // Adiciona mensagem do usuário
    adicionarMensagem(mensagem, 'user');
    input.value = '';

    try {
        const response = await fetch('../api/chatbot.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ pergunta: mensagem })
        });

        const data = await response.json();
        adicionarMensagem(data.resposta, 'bot');

        if (data.sugestoes && data.sugestoes.length > 0) {
            adicionarMensagem('Sugestões de perguntas:', 'bot');
            data.sugestoes.forEach(sugestao => {
                adicionarSugestao(sugestao);
            });
        }
    } catch (error) {
        adicionarMensagem('Desculpe, tive um problema. Tente novamente.', 'bot');
    }
}

function adicionarMensagem(texto, tipo) {
    const messages = document.getElementById('chatMessages');
    const div = document.createElement('div');
    div.className = `message ${tipo}`;
    div.textContent = texto;
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
}

function adicionarSugestao(texto) {
    const messages = document.getElementById('chatMessages');
    const div = document.createElement('div');
    div.className = 'message bot sugestao';
    div.textContent = texto;
    div.onclick = () => {
        document.getElementById('userInput').value = texto;
    };
    messages.appendChild(div);
}
</script>
