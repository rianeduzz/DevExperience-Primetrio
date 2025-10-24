CREATE TABLE IF NOT EXISTS chatbot_respostas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    palavra_chave VARCHAR(100) NOT NULL,
    resposta TEXT NOT NULL
);

INSERT INTO chatbot_respostas (palavra_chave, resposta) VALUES
('manutenção', 'Para cadastrar uma manutenção, acesse o menu "Manutenções" e clique em "Nova Manutenção". Preencha os campos necessários como tipo, data, responsável e descrição.'),
('localização', 'Para verificar a localização de um ativo, use a página de Monitoramento. Você pode usar os filtros para encontrar ativos específicos.'),
('relatório', 'Para gerar relatórios, acesse a página de Histórico. Lá você pode filtrar por período e tipo de manutenção, além de exportar os dados.'),
('ajuda', 'Posso ajudar com informações sobre:\n- Cadastro de manutenções\n- Localização de ativos\n- Geração de relatórios');
