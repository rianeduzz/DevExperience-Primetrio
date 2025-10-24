CREATE TABLE IF NOT EXISTS faq (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pergunta VARCHAR(255) NOT NULL,
    resposta TEXT NOT NULL,
    categoria VARCHAR(50) NOT NULL
);

INSERT INTO faq (pergunta, resposta, categoria) VALUES
('Como cadastro uma nova manutenção?', 'Para cadastrar uma manutenção:\n1. Clique em "Manutenções"\n2. Clique em "Nova Manutenção"\n3. Preencha os dados necessários\n4. Clique em "Salvar"', 'manutenção'),
('Como encontro um ativo específico?', 'Para localizar um ativo:\n1. Acesse "Monitoramento"\n2. Use o campo de busca no topo da página\n3. Ou use o filtro por categoria', 'monitoramento'),
('Como gero um relatório de manutenções?', 'Para gerar relatórios:\n1. Acesse "Histórico"\n2. Selecione o período desejado\n3. Clique em "Gerar Relatório"\n4. Escolha o formato (PDF/Excel)', 'relatórios'),
('Como atualizo a localização de um ativo?', 'Para atualizar a localização:\n1. Vá para "Monitoramento"\n2. Encontre o ativo desejado\n3. Clique no botão "Atualizar"\n4. Digite a nova localização', 'monitoramento');
