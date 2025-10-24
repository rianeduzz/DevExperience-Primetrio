CREATE TABLE alertas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL,
    mensagem TEXT NOT NULL,
    ativo_id INT,
    data_alerta DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ativo_id) REFERENCES ativos(id)
);

CREATE TABLE logs_notificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alerta_id INT,
    acao VARCHAR(50) NOT NULL,
    data_acao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT,
    FOREIGN KEY (alerta_id) REFERENCES alertas(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
