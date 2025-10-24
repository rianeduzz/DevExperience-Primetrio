CREATE TABLE conhecimento_base (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pergunta_padrao TEXT NOT NULL,
    resposta TEXT NOT NULL,
    palavras_chave TEXT NOT NULL,
    categoria VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO conhecimento_base (pergunta_padrao, resposta, palavras_chave, categoria) VALUES
('Como cadastrar uma manutenção?', 'Para cadastrar uma manutenção, acesse o menu "Manutenções" e clique no botão "Nova Manutenção". Preencha os campos necessários como tipo, data, responsável e descrição.', 'cadastrar,manutenção,nova', 'manutencao'),
('Como verificar a localização de um ativo?', 'Acesse o menu "Monitoramento" para ver a localização atual de todos os ativos. Você pode usar os filtros para encontrar um ativo específico.', 'localização,ativo,monitoramento', 'monitoramento'),
('Como gerar relatórios?', 'Na página de Histórico, selecione o período desejado e clique em "Gerar Relatório". Você pode filtrar por tipo de manutenção ou ativo específico.', 'relatório,histórico,gerar', 'relatorios');
