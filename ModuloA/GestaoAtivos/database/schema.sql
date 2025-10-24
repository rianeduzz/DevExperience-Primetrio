CREATE DATABASE IF NOT EXISTS gestao_ativos;
USE gestao_ativos;

-- Tabela de usuários administrativos
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel_acesso ENUM('admin', 'gestor', 'usuario') NOT NULL,
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de destinatários (setores/colaboradores)
CREATE TABLE destinatarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    setor VARCHAR(100),
    telefone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de ativos
CREATE TABLE ativos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    categoria VARCHAR(100) NOT NULL,
    numero_serie VARCHAR(100),
    valor DECIMAL(10,2),
    data_aquisicao DATE,
    data_garantia DATE,
    status ENUM('ativo', 'manutencao', 'inativo', 'descartado') NOT NULL,
    localizacao VARCHAR(255),
    destinatario_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (destinatario_id) REFERENCES destinatarios(id)
);

-- Tabela de manutenções
CREATE TABLE manutencoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ativo_id INT,
    tipo ENUM('preventiva', 'corretiva') NOT NULL,
    data_manutencao DATE NOT NULL,
    data_proxima DATE,
    responsavel_tecnico VARCHAR(100),
    custo DECIMAL(10,2),
    descricao TEXT,
    status ENUM('agendada', 'em_andamento', 'concluida', 'cancelada') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ativo_id) REFERENCES ativos(id)
);

-- Tabela de movimentações
CREATE TABLE movimentacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ativo_id INT,
    destinatario_origem_id INT,
    destinatario_destino_id INT,
    data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tipo_movimentacao ENUM('transferencia', 'emprestimo', 'devolucao') NOT NULL,
    observacao TEXT,
    FOREIGN KEY (ativo_id) REFERENCES ativos(id),
    FOREIGN KEY (destinatario_origem_id) REFERENCES destinatarios(id),
    FOREIGN KEY (destinatario_destino_id) REFERENCES destinatarios(id)
);

-- Tabela de alertas
CREATE TABLE alertas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL,
    mensagem TEXT NOT NULL,
    data_alerta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_resolucao TIMESTAMP,
    status ENUM('pendente', 'visualizado', 'resolvido') NOT NULL DEFAULT 'pendente',
    ativo_id INT,
    FOREIGN KEY (ativo_id) REFERENCES ativos(id)
);

-- Tabela para análises de IA
CREATE TABLE analises_ia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ativo_id INT,
    tipo_analise ENUM('previsao_falha', 'desempenho', 'custo') NOT NULL,
    resultado TEXT NOT NULL,
    probabilidade DECIMAL(5,2),
    data_analise TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ativo_id) REFERENCES ativos(id)
);

-- Tabela de logs do sistema
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    acao VARCHAR(100) NOT NULL,
    descricao TEXT,
    data_log TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
