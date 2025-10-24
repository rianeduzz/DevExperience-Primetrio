USE gestao_ativos;

CREATE TABLE IF NOT EXISTS previsoes_manutencao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ativo_id INT NOT NULL,
    probabilidade_falha DECIMAL(5,2),
    data_previsao DATE NOT NULL,
    razoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ativo_id) REFERENCES ativos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
