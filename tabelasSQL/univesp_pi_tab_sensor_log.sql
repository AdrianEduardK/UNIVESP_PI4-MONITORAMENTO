-- MySQL dump - TAB_SENSOR_LOG
-- Tabela de logs de sensores/controladores USB para monitoramento de máquinas

CREATE TABLE IF NOT EXISTS `TAB_SENSOR_LOG` (
    `LOG_ID` INT AUTO_INCREMENT PRIMARY KEY,
    `LOG_TAG_MAQ` VARCHAR(50) NOT NULL COMMENT 'Tag da máquina monitorada',
    `LOG_EVENTO` VARCHAR(50) NOT NULL COMMENT 'Tipo: CONEXAO, DESCONEXAO, INPUT',
    `LOG_TIMESTAMP` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LOG_TOTAL_INPUTS` INT DEFAULT 0 COMMENT 'Total de inputs detectados na sessão',
    `LOG_TEMPO_CONECTADO` INT DEFAULT 0 COMMENT 'Tempo conectado em segundos',
    INDEX idx_tag_maq (`LOG_TAG_MAQ`),
    INDEX idx_timestamp (`LOG_TIMESTAMP`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
