CREATE TABLE chatbot_conhecimento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pergunta TEXT NOT NULL,
    resposta TEXT NOT NULL,
    palavras_chave VARCHAR(255) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO chatbot_conhecimento (pergunta, resposta, palavras_chave, categoria) VALUES
('Como faço para cadastrar uma manutenção?', 'Para cadastrar uma manutenção, acesse o menu "Manutenções" e clique no botão "Nova Manutenção". Preencha todos os campos necessários.', 'cadastrar,manutenção,nova', 'manutencao'),
('Como verifico a localização de um ativo?', 'Na página de Monitoramento, você pode ver a localização atual de todos os ativos. Use os filtros para encontrar um ativo específico.', 'localização,ativo,onde,encontrar', 'monitoramento'),
('Como gero relatórios?', 'Acesse a página de Histórico, selecione o período desejado e clique em "Gerar Relatório". Você pode filtrar por tipo de manutenção.', 'relatório,histórico,gerar', 'relatorios');
