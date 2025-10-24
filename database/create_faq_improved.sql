-- Tabela de categorias de FAQ
CREATE TABLE faq_categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    descricao TEXT NOT NULL
);

-- Tabela de perguntas e respostas
CREATE TABLE faq (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    pergunta VARCHAR(255) NOT NULL,
    resposta TEXT NOT NULL,
    exemplo_pratico TEXT,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES faq_categorias(id)
);

-- Inserir categorias
INSERT INTO faq_categorias (nome, descricao) VALUES
('Manutenções', 'Dúvidas sobre cadastro e gestão de manutenções preventivas e corretivas'),
('Monitoramento', 'Questões sobre localização e controle de ativos'),
('Relatórios', 'Informações sobre geração e análise de relatórios'),
('Sistema', 'Dúvidas gerais sobre o funcionamento do sistema');

-- Inserir FAQs com exemplos práticos
INSERT INTO faq (categoria_id, pergunta, resposta, exemplo_pratico, ordem) VALUES
-- Manutenções
(1, 'Como cadastro uma nova manutenção?', 
'Para cadastrar uma manutenção:\n
1. Acesse o menu "Manutenções"\n
2. Clique em "Nova Manutenção"\n
3. Preencha os campos obrigatórios\n
4. Salve o registro', 
'Exemplo: Para cadastrar a troca de óleo de um veículo:\n
- Tipo: Preventiva\n
- Data: 01/07/2024\n
- Custo: R$ 250,00\n
- Descrição: Troca de óleo e filtros', 1),

-- Monitoramento
(2, 'Como localizo um ativo específico?', 
'Para localizar um ativo:\n
1. Acesse "Monitoramento"\n
2. Use a barra de busca\n
3. Ou filtre por categoria\n
4. O sistema mostrará a localização atual', 
'Exemplo: Para encontrar a empilhadeira 001:\n
- Digite "001" na busca ou\n
- Selecione categoria "Equipamentos"\n
- A localização atual será exibida', 1),

-- Relatórios
(3, 'Como gero um relatório personalizado?', 
'Para gerar relatórios:\n
1. Acesse "Histórico"\n
2. Selecione o período\n
3. Aplique os filtros desejados\n
4. Clique em "Gerar"', 
'Exemplo: Relatório de custos de manutenção:\n
- Período: Último trimestre\n
- Filtro: Manutenções corretivas\n
- Agrupar por: Categoria de ativo', 1),

-- Sistema
(4, 'Como funciona o sistema de alertas?', 
'O sistema de alertas:\n
1. Monitora prazos de manutenção\n
2. Verifica garantias próximas do fim\n
3. Notifica sobre pendências\n
4. Envia e-mails automáticos', 
'Exemplo: Alerta de manutenção preventiva:\n
- 7 dias antes do prazo\n
- Notificação no painel\n
- E-mail para responsável', 1);
