-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 24-Out-2025 às 16:15
-- Versão do servidor: 10.4.27-MariaDB
-- versão do PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `gestao_ativos`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `alertas`
--

CREATE TABLE `alertas` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `mensagem` text NOT NULL,
  `data_alerta` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_resolucao` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` enum('pendente','visualizado','resolvido') NOT NULL DEFAULT 'pendente',
  `ativo_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `alertas`
--

INSERT INTO `alertas` (`id`, `tipo`, `mensagem`, `data_alerta`, `data_resolucao`, `status`, `ativo_id`) VALUES
(1, 'manutencao_preventiva', 'Manutenção preventiva próxima para Máquina CNC Industrial', '2025-10-24 11:00:00', '0000-00-00 00:00:00', 'pendente', 3),
(2, 'garantia_expirada', 'Garantia expirada para Centrífuga Industrial', '2025-10-23 13:30:00', '0000-00-00 00:00:00', 'visualizado', 9),
(3, 'falha_iminente', 'Possível falha detectada no Servidor Dell R740', '2025-10-24 12:15:00', '2025-10-24 14:00:00', 'resolvido', 1),
(4, 'manutencao_atrasada', 'Manutenção preventiva atrasada para Microscópio Eletrônico', '2025-10-22 17:20:00', '0000-00-00 00:00:00', 'pendente', 5),
(5, 'valor_alto_manutencao', 'Custo de manutenção elevado para Empilhadeira Elétrica', '2025-10-24 10:45:00', '0000-00-00 00:00:00', 'visualizado', 6);

-- --------------------------------------------------------

--
-- Estrutura da tabela `analises_ia`
--

CREATE TABLE `analises_ia` (
  `id` int(11) NOT NULL,
  `ativo_id` int(11) DEFAULT NULL,
  `tipo_analise` enum('previsao_falha','desempenho','custo') NOT NULL,
  `resultado` text NOT NULL,
  `probabilidade` decimal(5,2) DEFAULT NULL,
  `data_analise` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `analises_ia`
--

INSERT INTO `analises_ia` (`id`, `ativo_id`, `tipo_analise`, `resultado`, `probabilidade`, `data_analise`) VALUES
(1, 3, 'previsao_falha', 'Alta probabilidade de falha nos componentes eletrônicos baseado no histórico de manutenções', '85.50', '2025-10-24 13:00:00'),
(2, 1, 'desempenho', 'Servidor operando com 92% de eficiência - recomenda-se upgrade de memória', '92.00', '2025-10-24 13:05:00'),
(3, 6, 'custo', 'Custo de manutenção acumulado representa 45% do valor do ativo - considere substituição', '45.00', '2025-10-24 13:10:00'),
(4, 5, 'previsao_falha', 'Baixo risco de falha - equipamento bem mantido e dentro da vida útil', '15.25', '2025-10-24 13:15:00'),
(5, 2, 'desempenho', 'Notebook com desempenho abaixo do esperado para as tarefas atribuídas', '68.75', '2025-10-24 13:20:00');

-- --------------------------------------------------------

--
-- Estrutura da tabela `ativos`
--

CREATE TABLE `ativos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `categoria` varchar(100) NOT NULL,
  `numero_serie` varchar(100) DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `data_aquisicao` date DEFAULT NULL,
  `data_garantia` date DEFAULT NULL,
  `status` enum('ativo','manutencao','inativo','descartado') NOT NULL,
  `localizacao` varchar(255) DEFAULT NULL,
  `destinatario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `responsavel_id` int(11) DEFAULT NULL,
  `localizacao_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `ativos`
--

INSERT INTO `ativos` (`id`, `nome`, `descricao`, `categoria`, `numero_serie`, `valor`, `data_aquisicao`, `data_garantia`, `status`, `localizacao`, `destinatario_id`, `created_at`, `updated_at`, `responsavel_id`, `localizacao_id`) VALUES
(1, 'Servidor Dell R740', 'Servidor para processamento de dados críticos', 'TI', 'SRV-DELL-2023-001', '25000.00', '2023-01-15', '2026-01-15', 'ativo', 'Data Center', 1, '2025-10-24 15:15:00', '2025-10-24 12:47:26', NULL, 1),
(2, 'Notebook Lenovo ThinkPad', 'Notebook para equipe de desenvolvimento', 'TI', 'NTB-LEN-2023-002', '4500.00', '2023-02-20', '2025-02-20', 'ativo', 'Setor de TI', 1, '2025-10-24 15:15:00', '2025-10-24 12:47:26', NULL, 1),
(3, 'Máquina CNC Industrial', 'Máquina para corte e usinagem de peças', 'Produção', 'MCNC-IND-2022-001', '150000.00', '2022-11-10', '2024-11-10', 'manutencao', 'Linha de Produção A', 2, '2025-10-24 15:15:00', '2025-10-24 12:47:26', NULL, 1),
(4, 'Impressora Multifuncional', 'Impressora para setor administrativo', 'Escritório', 'IMP-HP-2023-003', '3200.00', '2023-03-05', '2025-03-05', 'ativo', 'Sala de Reuniões', 3, '2025-10-24 15:15:00', '2025-10-24 12:47:26', NULL, 1),
(5, 'Microscópio Eletrônico', 'Equipamento para análises laboratoriais', 'Laboratório', 'MIC-ZEISS-2021-001', '85000.00', '2021-08-12', '2024-08-12', 'ativo', 'Laboratório Principal', 4, '2025-10-24 15:15:00', '2025-10-24 12:47:26', NULL, 1),
(6, 'Empilhadeira Elétrica', 'Empilhadeira para movimentação de carga', 'Logística', 'EMP-TOY-2022-002', '42000.00', '2022-05-18', '2025-05-18', 'ativo', 'Área de Estoque', 5, '2025-10-24 15:15:00', '2025-10-24 12:47:26', NULL, 1),
(7, 'Switch Cisco Catalyst', 'Switch para rede corporativa', 'TI', 'SW-CISCO-2023-004', '12000.00', '2023-04-22', '2026-04-22', 'ativo', 'Rack de Rede', 1, '2025-10-24 15:15:00', '2025-10-24 12:47:26', NULL, 1),
(8, 'Computador Desktop', 'Computador para atendimento ao cliente', 'TI', 'DESK-DELL-2023-005', '2800.00', '2023-06-30', '2025-06-30', 'inativo', 'Almoxarifado', NULL, '2025-10-24 15:15:00', '2025-10-24 12:47:26', NULL, 1),
(9, 'Centrífuga Industrial', 'Equipamento para processamento químico', 'Laboratório', 'CENT-IND-2020-001', '65000.00', '2020-12-05', '2023-12-05', 'descartado', 'Laboratório Secundário', 4, '2025-10-24 15:15:00', '2025-10-24 12:47:26', NULL, 1),
(10, 'Projetor Epson', 'Projetor para apresentações', 'Escritório', 'PROJ-EPS-2023-006', '1800.00', '2023-07-14', '2025-07-14', 'ativo', 'Sala de Treinamento', 3, '2025-10-24 15:15:00', '2025-10-24 12:47:26', NULL, 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `chatbot_conhecimento`
--

CREATE TABLE `chatbot_conhecimento` (
  `id` int(11) NOT NULL,
  `pergunta` text NOT NULL,
  `resposta` text NOT NULL,
  `palavras_chave` varchar(255) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `chatbot_conhecimento`
--

INSERT INTO `chatbot_conhecimento` (`id`, `pergunta`, `resposta`, `palavras_chave`, `categoria`, `created_at`) VALUES
(1, 'Como faço para cadastrar uma manutenção?', 'Para cadastrar uma manutenção, acesse o menu \"Manutenções\" e clique no botão \"Nova Manutenção\". Preencha todos os campos necessários.', 'cadastrar,manutenção,nova', 'manutencao', '2025-10-24 13:07:43'),
(2, 'Como verifico a localização de um ativo?', 'Na página de Monitoramento, você pode ver a localização atual de todos os ativos. Use os filtros para encontrar um ativo específico.', 'localização,ativo,onde,encontrar', 'monitoramento', '2025-10-24 13:07:43'),
(3, 'Como gero relatórios?', 'Acesse a página de Histórico, selecione o período desejado e clique em \"Gerar Relatório\". Você pode filtrar por tipo de manutenção.', 'relatório,histórico,gerar', 'relatorios', '2025-10-24 13:07:43');

-- --------------------------------------------------------

--
-- Estrutura da tabela `chatbot_respostas`
--

CREATE TABLE `chatbot_respostas` (
  `id` int(11) NOT NULL,
  `palavra_chave` varchar(100) NOT NULL,
  `resposta` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `chatbot_respostas`
--

INSERT INTO `chatbot_respostas` (`id`, `palavra_chave`, `resposta`) VALUES
(1, 'manutenção', 'Para cadastrar uma manutenção, acesse o menu \"Manutenções\" e clique em \"Nova Manutenção\". Preencha os campos necessários como tipo, data, responsável e descrição.'),
(2, 'localização', 'Para verificar a localização de um ativo, use a página de Monitoramento. Você pode usar os filtros para encontrar ativos específicos.'),
(3, 'relatório', 'Para gerar relatórios, acesse a página de Histórico. Lá você pode filtrar por período e tipo de manutenção, além de exportar os dados.'),
(4, 'ajuda', 'Posso ajudar com informações sobre:\n- Cadastro de manutenções\n- Localização de ativos\n- Geração de relatórios');

-- --------------------------------------------------------

--
-- Estrutura da tabela `conhecimento_base`
--

CREATE TABLE `conhecimento_base` (
  `id` int(11) NOT NULL,
  `pergunta_padrao` text NOT NULL,
  `resposta` text NOT NULL,
  `palavras_chave` text NOT NULL,
  `categoria` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `conhecimento_base`
--

INSERT INTO `conhecimento_base` (`id`, `pergunta_padrao`, `resposta`, `palavras_chave`, `categoria`) VALUES
(1, 'Como cadastrar uma manutenção?', 'Para cadastrar uma manutenção, acesse o menu \"Manutenções\" e clique no botão \"Nova Manutenção\". Preencha os campos necessários como tipo, data, responsável e descrição.', 'cadastrar,manutenção,nova', 'manutencao'),
(2, 'Como verificar a localização de um ativo?', 'Acesse o menu \"Monitoramento\" para ver a localização atual de todos os ativos. Você pode usar os filtros para encontrar um ativo específico.', 'localização,ativo,monitoramento', 'monitoramento'),
(3, 'Como gerar relatórios?', 'Na página de Histórico, selecione o período desejado e clique em \"Gerar Relatório\". Você pode filtrar por tipo de manutenção ou ativo específico.', 'relatório,histórico,gerar', 'relatorios');

-- --------------------------------------------------------

--
-- Estrutura da tabela `destinatarios`
--

CREATE TABLE `destinatarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `setor` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `destinatarios`
--

INSERT INTO `destinatarios` (`id`, `nome`, `email`, `setor`, `telefone`, `created_at`) VALUES
(1, 'Carlos Oliveira', 'carlos.oliveira@empresa.com', 'TI', '(11) 99999-1001', '2025-10-24 15:10:00'),
(2, 'Ana Paula Silva', 'ana.silva@empresa.com', 'Produção', '(11) 99999-1002', '2025-10-24 15:10:00'),
(3, 'Roberto Santos', 'roberto.santos@empresa.com', 'Administrativo', '(11) 99999-1003', '2025-10-24 15:10:00'),
(4, 'Mariana Costa', 'mariana.costa@empresa.com', 'Laboratório', '(11) 99999-1004', '2025-10-24 15:10:00'),
(5, 'Fernando Lima', 'fernando.lima@empresa.com', 'Logística', '(11) 99999-1005', '2025-10-24 15:10:00'),
(6, 'Patrícia Almeida', 'patricia.almeida@empresa.com', 'RH', '(11) 99999-1006', '2025-10-24 15:10:00'),
(7, 'Ricardo Pereira', 'ricardo.pereira@empresa.com', 'Financeiro', '(11) 99999-1007', '2025-10-24 15:10:00'),
(8, 'Juliana Rodrigues', 'juliana.rodrigues@empresa.com', 'Marketing', '(11) 99999-1008', '2025-10-24 15:10:00');

-- --------------------------------------------------------

--
-- Estrutura da tabela `faq`
--

CREATE TABLE `faq` (
  `id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `pergunta` varchar(255) NOT NULL,
  `resposta` text NOT NULL,
  `exemplo_pratico` text DEFAULT NULL,
  `ordem` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `faq`
--

INSERT INTO `faq` (`id`, `categoria_id`, `pergunta`, `resposta`, `exemplo_pratico`, `ordem`, `created_at`) VALUES
(1, 1, 'Como cadastro uma nova manutenção?', 'Para cadastrar uma manutenção:\n\r\n1. Acesse o menu \"Manutenções\"\n\r\n2. Clique em \"Nova Manutenção\"\n\r\n3. Preencha os campos obrigatórios\n\r\n4. Salve o registro', 'Exemplo: Para cadastrar a troca de óleo de um veículo:\n\r\n- Tipo: Preventiva\n\r\n- Data: 01/07/2024\n\r\n- Custo: R$ 250,00\n\r\n- Descrição: Troca de óleo e filtros', 1, '2025-10-24 13:17:13'),
(2, 2, 'Como localizo um ativo específico?', 'Para localizar um ativo:\n\r\n1. Acesse \"Monitoramento\"\n\r\n2. Use a barra de busca\n\r\n3. Ou filtre por categoria\n\r\n4. O sistema mostrará a localização atual', 'Exemplo: Para encontrar a empilhadeira 001:\n\r\n- Digite \"001\" na busca ou\n\r\n- Selecione categoria \"Equipamentos\"\n\r\n- A localização atual será exibida', 1, '2025-10-24 13:17:13'),
(3, 3, 'Como gero um relatório personalizado?', 'Para gerar relatórios:\n\r\n1. Acesse \"Histórico\"\n\r\n2. Selecione o período\n\r\n3. Aplique os filtros desejados\n\r\n4. Clique em \"Gerar\"', 'Exemplo: Relatório de custos de manutenção:\n\r\n- Período: Último trimestre\n\r\n- Filtro: Manutenções corretivas\n\r\n- Agrupar por: Categoria de ativo', 1, '2025-10-24 13:17:13'),
(4, 4, 'Como funciona o sistema de alertas?', 'O sistema de alertas:\n\r\n1. Monitora prazos de manutenção\n\r\n2. Verifica garantias próximas do fim\n\r\n3. Notifica sobre pendências\n\r\n4. Envia e-mails automáticos', 'Exemplo: Alerta de manutenção preventiva:\n\r\n- 7 dias antes do prazo\n\r\n- Notificação no painel\n\r\n- E-mail para responsável', 1, '2025-10-24 13:17:13');

-- --------------------------------------------------------

--
-- Estrutura da tabela `faq_categorias`
--

CREATE TABLE `faq_categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descricao` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `faq_categorias`
--

INSERT INTO `faq_categorias` (`id`, `nome`, `descricao`) VALUES
(1, 'Manutenções', 'Dúvidas sobre cadastro e gestão de manutenções preventivas e corretivas'),
(2, 'Monitoramento', 'Questões sobre localização e controle de ativos'),
(3, 'Relatórios', 'Informações sobre geração e análise de relatórios'),
(4, 'Sistema', 'Dúvidas gerais sobre o funcionamento do sistema');

-- --------------------------------------------------------

--
-- Estrutura da tabela `localizacoes`
--

CREATE TABLE `localizacoes` (
  `id` int(11) NOT NULL,
  `descricao` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `localizacoes`
--

INSERT INTO `localizacoes` (`id`, `descricao`, `created_at`) VALUES
(1, 'Almoxarifado Principal', '2025-10-24 12:46:32'),
(2, 'Setor de Produção', '2025-10-24 12:46:32'),
(3, 'Escritório Administrativo', '2025-10-24 12:46:32'),
(4, 'Laboratório', '2025-10-24 12:46:32'),
(5, 'Depósito', '2025-10-24 12:46:32');

-- --------------------------------------------------------

--
-- Estrutura da tabela `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `acao` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_log` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `logs`
--

INSERT INTO `logs` (`id`, `usuario_id`, `acao`, `descricao`, `data_log`) VALUES
(1, 1, 'login', 'Usuário rian fez login no sistema', '2025-10-24 11:30:00'),
(2, 1, 'cadastro_ativo', 'Novo ativo cadastrado: Servidor Dell R740', '2025-10-24 11:45:00'),
(3, 4, 'manutencao_agendada', 'Manutenção agendada para Máquina CNC Industrial', '2025-10-24 12:00:00'),
(4, 5, 'movimentacao', 'Transferência de Notebook Lenovo para setor de Marketing', '2025-10-24 12:30:00'),
(5, 1, 'alerta_resolvido', 'Alerta de falha resolvido para Servidor Dell R740', '2025-10-24 14:00:00'),
(6, 4, 'analise_ia', 'Análise de IA gerada para Empilhadeira Elétrica', '2025-10-24 14:15:00'),
(7, 5, 'atualizacao_ativo', 'Status atualizado para Centrífuga Industrial: descartado', '2025-10-24 14:30:00');

-- --------------------------------------------------------

--
-- Estrutura da tabela `manutencoes`
--

CREATE TABLE `manutencoes` (
  `id` int(11) NOT NULL,
  `ativo_id` int(11) DEFAULT NULL,
  `tipo` enum('preventiva','corretiva') NOT NULL,
  `data_manutencao` date NOT NULL,
  `data_proxima` date DEFAULT NULL,
  `responsavel_tecnico` varchar(100) DEFAULT NULL,
  `custo` decimal(10,2) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `status` enum('agendada','em_andamento','concluida','cancelada') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `localizacao_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `manutencoes`
--

INSERT INTO `manutencoes` (`id`, `ativo_id`, `tipo`, `data_manutencao`, `data_proxima`, `responsavel_tecnico`, `custo`, `descricao`, `status`, `created_at`, `localizacao_id`) VALUES
(1, 3, 'corretiva', '2025-10-15', '2025-11-15', 'Técnico João - Manutec', '1500.00', 'Troca de componentes eletrônicos e calibração', 'concluida', '2025-10-24 15:20:00', NULL),
(2, 1, 'preventiva', '2025-10-20', '2026-01-20', 'Técnico Marcos - Dell', '800.00', 'Limpeza interna, atualização de firmware e testes', 'concluida', '2025-10-24 15:20:00', NULL),
(3, 5, 'preventiva', '2025-10-18', '2026-04-18', 'Técnico Carlos - Zeiss', '2500.00', 'Calibração e troca de lentes', 'concluida', '2025-10-24 15:20:00', NULL),
(4, 6, 'preventiva', '2025-10-22', '2025-11-22', 'Técnico Roberto - Toyota', '1200.00', 'Troca de bateria e verificação do sistema hidráulico', 'em_andamento', '2025-10-24 15:20:00', NULL),
(5, 2, 'corretiva', '2025-10-25', '2025-11-25', 'Técnico Ana - Lenovo', '650.00', 'Troca de teclado e atualização de drivers', 'agendada', '2025-10-24 15:20:00', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `movimentacoes`
--

CREATE TABLE `movimentacoes` (
  `id` int(11) NOT NULL,
  `ativo_id` int(11) DEFAULT NULL,
  `destinatario_origem_id` int(11) DEFAULT NULL,
  `destinatario_destino_id` int(11) DEFAULT NULL,
  `data_movimentacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipo_movimentacao` enum('transferencia','emprestimo','devolucao') NOT NULL,
  `observacao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `movimentacoes`
--

INSERT INTO `movimentacoes` (`id`, `ativo_id`, `destinatario_origem_id`, `destinatario_destino_id`, `data_movimentacao`, `tipo_movimentacao`, `observacao`) VALUES
(1, 2, NULL, 1, '2025-10-10 11:30:00', 'transferencia', 'Alocação inicial para setor de TI'),
(2, 4, NULL, 3, '2025-10-11 12:15:00', 'transferencia', 'Instalação no setor administrativo'),
(3, 8, 1, NULL, '2025-10-12 17:20:00', 'devolucao', 'Equipamento recolhido para manutenção'),
(4, 10, NULL, 3, '2025-10-13 13:00:00', 'transferencia', 'Instalação na sala de treinamento'),
(5, 2, 1, 8, '2025-10-14 14:30:00', 'emprestimo', 'Empréstimo temporário para evento de marketing');

-- --------------------------------------------------------

--
-- Estrutura da tabela `previsoes_manutencao`
--

CREATE TABLE `previsoes_manutencao` (
  `id` int(11) NOT NULL,
  `ativo_id` int(11) NOT NULL,
  `probabilidade_falha` decimal(5,2) DEFAULT NULL,
  `data_previsao` date NOT NULL,
  `razoes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nivel_acesso` enum('admin','gestor','usuario') NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `nivel_acesso`, `status`, `created_at`, `updated_at`) VALUES
(1, 'rian', 'adm@gmail.com', '$2y$10$4FOYm6z951BWgMZbPKS8L.WRwkA9I1CCtqR/Vgbo0FYjd0eexTPbu', 'admin', 1, '2025-10-24 11:56:13', '2025-10-24 11:56:13'),
(3, 'user', 'user@gmail.com', '$2y$10$xwh3aimS9p5WkfmOhL8L7OKPHqj/Gm6G5.cMiWgQnKezqy61ppPwW', 'admin', 1, '2025-10-24 11:56:58', '2025-10-24 11:56:58'),
(4, 'Admin', 'admin@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gestor', 1, '2025-10-24 12:06:54', '2025-10-24 12:06:54'),
(5, 'João Silva', 'joao@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, '2025-10-24 12:06:54', '2025-10-24 12:06:54'),
(6, 'Maria Santos', 'maria@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, '2025-10-24 12:06:54', '2025-10-24 12:06:54');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `alertas`
--
ALTER TABLE `alertas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ativo_id` (`ativo_id`);

--
-- Índices para tabela `analises_ia`
--
ALTER TABLE `analises_ia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ativo_id` (`ativo_id`);

--
-- Índices para tabela `ativos`
--
ALTER TABLE `ativos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destinatario_id` (`destinatario_id`),
  ADD KEY `fk_ativo_localizacao` (`localizacao_id`);

--
-- Índices para tabela `chatbot_conhecimento`
--
ALTER TABLE `chatbot_conhecimento`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `chatbot_respostas`
--
ALTER TABLE `chatbot_respostas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `conhecimento_base`
--
ALTER TABLE `conhecimento_base`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `destinatarios`
--
ALTER TABLE `destinatarios`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Índices para tabela `faq_categorias`
--
ALTER TABLE `faq_categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `localizacoes`
--
ALTER TABLE `localizacoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices para tabela `manutencoes`
--
ALTER TABLE `manutencoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ativo_id` (`ativo_id`);

--
-- Índices para tabela `movimentacoes`
--
ALTER TABLE `movimentacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ativo_id` (`ativo_id`),
  ADD KEY `destinatario_origem_id` (`destinatario_origem_id`),
  ADD KEY `destinatario_destino_id` (`destinatario_destino_id`);

--
-- Índices para tabela `previsoes_manutencao`
--
ALTER TABLE `previsoes_manutencao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ativo_id` (`ativo_id`);

--
-- Índices para tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `alertas`
--
ALTER TABLE `alertas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `analises_ia`
--
ALTER TABLE `analises_ia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `ativos`
--
ALTER TABLE `ativos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `chatbot_conhecimento`
--
ALTER TABLE `chatbot_conhecimento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `chatbot_respostas`
--
ALTER TABLE `chatbot_respostas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `conhecimento_base`
--
ALTER TABLE `conhecimento_base`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `destinatarios`
--
ALTER TABLE `destinatarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `faq`
--
ALTER TABLE `faq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `faq_categorias`
--
ALTER TABLE `faq_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `localizacoes`
--
ALTER TABLE `localizacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `manutencoes`
--
ALTER TABLE `manutencoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `movimentacoes`
--
ALTER TABLE `movimentacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `previsoes_manutencao`
--
ALTER TABLE `previsoes_manutencao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `alertas`
--
ALTER TABLE `alertas`
  ADD CONSTRAINT `alertas_ibfk_1` FOREIGN KEY (`ativo_id`) REFERENCES `ativos` (`id`);

--
-- Limitadores para a tabela `analises_ia`
--
ALTER TABLE `analises_ia`
  ADD CONSTRAINT `analises_ia_ibfk_1` FOREIGN KEY (`ativo_id`) REFERENCES `ativos` (`id`);

--
-- Limitadores para a tabela `ativos`
--
ALTER TABLE `ativos`
  ADD CONSTRAINT `ativos_ibfk_1` FOREIGN KEY (`destinatario_id`) REFERENCES `destinatarios` (`id`),
  ADD CONSTRAINT `fk_ativo_localizacao` FOREIGN KEY (`localizacao_id`) REFERENCES `localizacoes` (`id`);

--
-- Limitadores para a tabela `faq`
--
ALTER TABLE `faq`
  ADD CONSTRAINT `faq_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `faq_categorias` (`id`);

--
-- Limitadores para a tabela `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Limitadores para a tabela `manutencoes`
--
ALTER TABLE `manutencoes`
  ADD CONSTRAINT `manutencoes_ibfk_1` FOREIGN KEY (`ativo_id`) REFERENCES `ativos` (`id`);

--
-- Limitadores para a tabela `movimentacoes`
--
ALTER TABLE `movimentacoes`
  ADD CONSTRAINT `movimentacoes_ibfk_1` FOREIGN KEY (`ativo_id`) REFERENCES `ativos` (`id`),
  ADD CONSTRAINT `movimentacoes_ibfk_2` FOREIGN KEY (`destinatario_origem_id`) REFERENCES `destinatarios` (`id`),
  ADD CONSTRAINT `movimentacoes_ibfk_3` FOREIGN KEY (`destinatario_destino_id`) REFERENCES `destinatarios` (`id`);

--
-- Limitadores para a tabela `previsoes_manutencao`
--
ALTER TABLE `previsoes_manutencao`
  ADD CONSTRAINT `previsoes_manutencao_ibfk_1` FOREIGN KEY (`ativo_id`) REFERENCES `ativos` (`id`);

-- 1) adicionar coluna (aceita NULL)
ALTER TABLE `manutencoes`
  ADD COLUMN `localizacao_id` INT(11) DEFAULT NULL;

-- 2) popular localizacao_id a partir da informação já presente em ativos.localizacao_id
--    (assume que manutencoes.ativo_id está preenchido corretamente)
UPDATE manutencoes m
JOIN ativos a ON m.ativo_id = a.id
SET m.localizacao_id = a.localizacao_id
WHERE a.localizacao_id IS NOT NULL;

-- 3) garantir que todos os valores atuais existam em localizacoes (opcional: revisar linhas que ficaram com localizacao_id NULL)
--    Ex.: listar manutenções sem localização definida
-- SELECT * FROM manutencoes WHERE localizacao_id IS NULL;

-- 4) criar índice para performance
ALTER TABLE `manutencoes`
  ADD KEY `localizacao_id` (`localizacao_id`);

-- 5) adicionar chave estrangeira (falhará se houver valores inválidos em localizacao_id)
ALTER TABLE `manutencoes`
  ADD CONSTRAINT `manutencoes_localizacao_fk` FOREIGN KEY (`localizacao_id`) REFERENCES `localizacoes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
