-- Backup Completo do Banco de Dados: univesp_pi

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Estrutura para tabela `funcionarios`
DROP TABLE IF EXISTS `funcionarios`;
CREATE TABLE `funcionarios` (
  `ID_FUNCIONARIO` int NOT NULL AUTO_INCREMENT,
  `NOME` varchar(100) NOT NULL,
  `CARGO` varchar(50) DEFAULT NULL,
  `DEPARTAMENTO` varchar(50) DEFAULT NULL,
  `DATA_ADMISSAO` date DEFAULT NULL,
  `SALARIO` decimal(10,2) DEFAULT NULL,
  `ATIVO` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`ID_FUNCIONARIO`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Despejando dados para a tabela `funcionarios`
INSERT INTO `funcionarios` VALUES('1','AMANDA','TESTE','TESTE','2025-04-23','123.00','1');
INSERT INTO `funcionarios` VALUES('2','amanda','TESTE','TESTE','2025-04-25','12345.00','1');


-- --------------------------------------------------------
-- Estrutura para tabela `tab_apont_horas`
DROP TABLE IF EXISTS `tab_apont_horas`;
CREATE TABLE `tab_apont_horas` (
  `ID_APONT_HORAS` int NOT NULL AUTO_INCREMENT,
  `ID_OS` int NOT NULL,
  `APONT_MANUTENTOR` varchar(100) NOT NULL,
  `APONT_INICIO` datetime NOT NULL,
  `APONT_FIM` datetime NOT NULL,
  `APONT_TOTAL` decimal(10,2) GENERATED ALWAYS AS ((timestampdiff(MINUTE,`APONT_INICIO`,`APONT_FIM`) / 60)) STORED,
  PRIMARY KEY (`ID_APONT_HORAS`),
  KEY `FK_TAB_APONT_OS` (`ID_OS`),
  CONSTRAINT `FK_TAB_APONT_OS` FOREIGN KEY (`ID_OS`) REFERENCES `tab_ordem_servico` (`OS_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Despejando dados para a tabela `tab_apont_horas`
INSERT INTO `tab_apont_horas` VALUES('1','9','ADRIAN EDUARD','2025-11-01 15:38:00','2025-11-01 17:38:00','2.00');
INSERT INTO `tab_apont_horas` VALUES('2','9','ADRIAN','2025-11-01 15:40:00','2025-11-01 15:44:00','0.07');
INSERT INTO `tab_apont_horas` VALUES('3','10','JOSÉ','2025-11-03 20:00:00','2025-11-03 23:00:00','3.00');


-- --------------------------------------------------------
-- Estrutura para tabela `tab_dde`
DROP TABLE IF EXISTS `tab_dde`;
CREATE TABLE `tab_dde` (
  `ID_DDE` int NOT NULL AUTO_INCREMENT,
  `DDE_TITULO` varchar(255) NOT NULL,
  `DDE_TEMA` varchar(255) NOT NULL,
  `DDE_DESENVOLVIMENTO` text NOT NULL,
  `DDE_DATA` date NOT NULL,
  `DDE_RESPONSAVEL` varchar(100) NOT NULL,
  PRIMARY KEY (`ID_DDE`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Despejando dados para a tabela `tab_dde`
INSERT INTO `tab_dde` VALUES('1','Importância da Segurança do Trabalho','Segurança do Trabalho','A segurança do trabalho é essencial para preservar a integridade física e mental dos colaboradores. Usar Equipamentos de Proteção Individual (EPIs), seguir os procedimentos operacionais e manter o ambiente limpo são práticas fundamentais. Pequenas atitudes podem evitar grandes acidentes.','2025-05-08','Amanda Oliveira');
INSERT INTO `tab_dde` VALUES('2','Operação sempre atenta!','Importância da atenção constante para garantir a segurança nos processos operacionais.','Manter a atenção plena durante as atividades diárias na operação é fundamental para o sucesso coletivo da equipe. Pequenas distrações podem gerar grandes impactos, como retrabalho, perdas de matéria-prima, falhas de qualidade e até riscos à segurança.\r\n\r\nEste DDE reforça a importância de cada colaborador estar vigilante aos detalhes de sua função, identificar rapidamente qualquer anomalia no processo e comunicar desvios assim que forem percebidos.\r\n\r\nA \"atenção operacional\" vai além da execução correta da tarefa: envolve percepção do ambiente, cuidado com os colegas, zelo pelo equipamento e compromisso com o resultado final.\r\n\r\nCada setor depende do outro. Quando todos estão atentos, evitamos desperdícios, aumentamos a produtividade e garantimos entregas com qualidade.\r\n\r\nVamos continuar com esse olhar atento e postura responsável — afinal, uma operação forte é construída com atenção e disciplina no dia a dia.','2025-05-08','ADRIAN');
INSERT INTO `tab_dde` VALUES('3','Manutenção Autônoma: Uma Abordagem para a Eficiência Operacional','Implementação e Benefícios da Manutenção Autônoma no Contexto Industrial','A manutenção autônoma é um conceito fundamental na busca pela eficiência operacional nas indústrias, especialmente em processos que envolvem máquinas e equipamentos pesados. Este conceito faz parte do pilar da Manutenção Produtiva Total (TPM), que visa melhorar a confiabilidade dos equipamentos e reduzir o tempo de inatividade. A principal ideia por trás da manutenção autônoma é permitir que os próprios operadores de máquinas se envolvam ativamente na manutenção diária e na identificação de falhas potenciais, sem a necessidade de intervenção constante da equipe de manutenção.','2025-05-09','ADRIAN');
INSERT INTO `tab_dde` VALUES('4','Operação sempre atenta!','Implementação e Benefícios da Manutenção Autônoma no Contexto Industrial','Manter a atenção plena durante as atividades diárias na operação é fundamental para o sucesso coletivo da equipe. Pequenas distrações podem gerar grandes impactos, como retrabalho, perdas de matéria-prima, falhas de qualidade e até riscos à segurança. Este DDE reforça a importância de cada colaborador estar vigilante aos detalhes de sua função, identificar rapidamente qualquer anomalia no processo e comunicar desvios assim que forem percebidos. A \"atenção operacional\" vai além da execução correta da tarefa: envolve percepção do ambiente, cuidado com os colegas, zelo pelo equipamento e compromisso com o resultado final. Cada setor depende do outro. Quando todos estão atentos, evitamos desperdícios, aumentamos a produtividade e garantimos entregas com qualidade. Vamos continuar com esse olhar atento e postura responsável — afinal, uma operação forte é construída com atenção e disciplina no dia a dia.','2025-05-17','ADRIAN');
INSERT INTO `tab_dde` VALUES('5','Operação sempre atenta!','Implementação e Benefícios da Manutenção Autônoma no Contexto Industrial','TESTE','2025-05-18','ADRIAN');
INSERT INTO `tab_dde` VALUES('6','NOVO DDE','MANUTENÇÃO','DDE SOBRE O SISTEMA DE MANUTENÇÃO','2025-11-06','ADRIAN');
INSERT INTO `tab_dde` VALUES('7','123','123','123','2026-04-19','ADRIAN');


-- --------------------------------------------------------
-- Estrutura para tabela `tab_dde_assinatura`
DROP TABLE IF EXISTS `tab_dde_assinatura`;
CREATE TABLE `tab_dde_assinatura` (
  `ID_ASSINATURA` int NOT NULL AUTO_INCREMENT,
  `ID_DDE` int NOT NULL,
  `ASSINANTE_NOME` varchar(100) NOT NULL,
  `DATA_ASSINATURA` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_ASSINATURA`),
  KEY `ID_DDE` (`ID_DDE`),
  CONSTRAINT `tab_dde_assinatura_ibfk_1` FOREIGN KEY (`ID_DDE`) REFERENCES `tab_dde` (`ID_DDE`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Despejando dados para a tabela `tab_dde_assinatura`
INSERT INTO `tab_dde_assinatura` VALUES('1','1','Amanda Fragnan','2025-05-08 08:08:56');
INSERT INTO `tab_dde_assinatura` VALUES('3','1','ADRIAN','2025-05-08 08:49:10');
INSERT INTO `tab_dde_assinatura` VALUES('4','7','ADRIAN','2026-04-19 11:12:05');
INSERT INTO `tab_dde_assinatura` VALUES('5','7','AMANDA FRAGNAN','2026-04-19 11:30:54');


-- --------------------------------------------------------
-- Estrutura para tabela `tab_estrutura_prod`
DROP TABLE IF EXISTS `tab_estrutura_prod`;
CREATE TABLE `tab_estrutura_prod` (
  `ESTRUTURA_ID` bigint NOT NULL AUTO_INCREMENT,
  `ESTRUTURA_ID_FINAL` bigint NOT NULL,
  `ESTRUTURA_ID_NECESSARIO` bigint NOT NULL,
  PRIMARY KEY (`ESTRUTURA_ID`),
  KEY `tab_estrutura_prod_ibfk_1` (`ESTRUTURA_ID_FINAL`),
  KEY `tab_estrutura_prod_ibfk_2` (`ESTRUTURA_ID_NECESSARIO`),
  CONSTRAINT `tab_estrutura_prod_ibfk_1` FOREIGN KEY (`ESTRUTURA_ID_FINAL`) REFERENCES `tab_produtos` (`PROD_ID`),
  CONSTRAINT `tab_estrutura_prod_ibfk_2` FOREIGN KEY (`ESTRUTURA_ID_NECESSARIO`) REFERENCES `tab_produtos` (`PROD_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Despejando dados para a tabela `tab_estrutura_prod`
INSERT INTO `tab_estrutura_prod` VALUES('23','5','3');
INSERT INTO `tab_estrutura_prod` VALUES('24','5','4');
INSERT INTO `tab_estrutura_prod` VALUES('25','6','3');
INSERT INTO `tab_estrutura_prod` VALUES('26','7','4');
INSERT INTO `tab_estrutura_prod` VALUES('27','8','3');
INSERT INTO `tab_estrutura_prod` VALUES('28','8','4');
INSERT INTO `tab_estrutura_prod` VALUES('29','9','3');


-- --------------------------------------------------------
-- Estrutura para tabela `tab_linhas`
DROP TABLE IF EXISTS `tab_linhas`;
CREATE TABLE `tab_linhas` (
  `LINHA_ID` int NOT NULL AUTO_INCREMENT,
  `LINHA_DESC` varchar(100) NOT NULL,
  PRIMARY KEY (`LINHA_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Despejando dados para a tabela `tab_linhas`
INSERT INTO `tab_linhas` VALUES('1','LINHA VINIL - 1');
INSERT INTO `tab_linhas` VALUES('2','LINHA VINIL - 2');
INSERT INTO `tab_linhas` VALUES('3','LINHA VINIL - 3');
INSERT INTO `tab_linhas` VALUES('4','LINHA VINIL - 4');


-- --------------------------------------------------------
-- Estrutura para tabela `tab_login`
DROP TABLE IF EXISTS `tab_login`;
CREATE TABLE `tab_login` (
  `LOGIN_ID` bigint NOT NULL,
  `LOGIN_NOME` varchar(100) NOT NULL,
  `LOGIN_SENHA` varchar(255) NOT NULL,
  `LOGIN_ATIVO` varchar(100) NOT NULL,
  `LOGIN_SEGMENTO` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`LOGIN_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Despejando dados para a tabela `tab_login`
INSERT INTO `tab_login` VALUES (1,'AMANDA FRAGNAN','$2y$12$41hp.o31IJDZTcSvkkl/iOkcmX79bYrajDWcobfWmwE32L47Wv5aa','Ativo','PRODUÇÃO');
INSERT INTO `tab_login` VALUES (2,'ADRIAN','$2y$12$p3I2OZzHALnO8JtME1O.SuX1OH5CUvnV/UJzkyS61dxGcYlvKjyQu','Ativo','PCP');


-- --------------------------------------------------------
-- Estrutura para tabela `tab_lote_op_produtos`
DROP TABLE IF EXISTS `tab_lote_op_produtos`;
CREATE TABLE `tab_lote_op_produtos` (
  `ID` bigint NOT NULL AUTO_INCREMENT,
  `LOTE_ID_OP` int NOT NULL,
  `LOTE_ID_PROD_NECESSARIO` bigint NOT NULL,
  `LOTE_APONTADO` varchar(100) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `LOTE_ID_OP` (`LOTE_ID_OP`),
  KEY `tab_lote_op_produtos_ibfk_2` (`LOTE_ID_PROD_NECESSARIO`),
  CONSTRAINT `tab_lote_op_produtos_ibfk_1` FOREIGN KEY (`LOTE_ID_OP`) REFERENCES `tab_op` (`OP_ID`),
  CONSTRAINT `tab_lote_op_produtos_ibfk_2` FOREIGN KEY (`LOTE_ID_PROD_NECESSARIO`) REFERENCES `tab_estrutura_prod` (`ESTRUTURA_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Despejando dados para a tabela `tab_lote_op_produtos`
INSERT INTO `tab_lote_op_produtos` VALUES('13','9','26','86');
INSERT INTO `tab_lote_op_produtos` VALUES('14','9','29','67');
INSERT INTO `tab_lote_op_produtos` VALUES('15','9','26','26');
INSERT INTO `tab_lote_op_produtos` VALUES('16','9','29','64');
INSERT INTO `tab_lote_op_produtos` VALUES('17','13','27','8678');
INSERT INTO `tab_lote_op_produtos` VALUES('18','13','28','6867');
INSERT INTO `tab_lote_op_produtos` VALUES('19','9','26','245');
INSERT INTO `tab_lote_op_produtos` VALUES('20','9','29','346');
INSERT INTO `tab_lote_op_produtos` VALUES('21','13','27','4545');
INSERT INTO `tab_lote_op_produtos` VALUES('22','13','28','4544');
INSERT INTO `tab_lote_op_produtos` VALUES('23','13','27','2345');
INSERT INTO `tab_lote_op_produtos` VALUES('24','13','28','243');


-- --------------------------------------------------------
-- Estrutura para tabela `tab_maq`
DROP TABLE IF EXISTS `tab_maq`;
CREATE TABLE `tab_maq` (
  `MAQ_ID` int NOT NULL AUTO_INCREMENT,
  `MAQ_DESC` varchar(100) NOT NULL,
  `MAQ_TAG` varchar(100) NOT NULL,
  `MAQ_ATIVO` varchar(100) NOT NULL,
  `MAQ_IMPACTO_PRODUCAO` tinyint DEFAULT '1',
  PRIMARY KEY (`MAQ_ID`),
  UNIQUE KEY `MAQ_TAG` (`MAQ_TAG`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Despejando dados para a tabela `tab_maq`
INSERT INTO `tab_maq` VALUES('1','EXTRUSORA DA LINHA 1','EXT001','ATIVO','5');
INSERT INTO `tab_maq` VALUES('2','EXTRUSORA DA LINHA 2','EXT002','ATIVO','4');
INSERT INTO `tab_maq` VALUES('3','EXTRUSORA DA LINHA 3','EXT003','ATIVO','3');
INSERT INTO `tab_maq` VALUES('5','EXTRUSORA DA LINHA 4','EXT006','ATIVO','1');


-- --------------------------------------------------------
-- Estrutura para tabela `tab_nr12`
DROP TABLE IF EXISTS `tab_nr12`;
CREATE TABLE `tab_nr12` (
  `ID_TAB_NR12` int NOT NULL AUTO_INCREMENT,
  `NR12_DATA` date NOT NULL,
  `NR12_LINHA` varchar(50) NOT NULL,
  `NR12_INICIO` time NOT NULL,
  `NR12_FIM` time NOT NULL,
  `NR12_OPERADOR` varchar(100) NOT NULL,
  `NR12_OP` int NOT NULL,
  `Q1` enum('CONFORME','NÃO CONFORME','NÃO APLICÁVEL') NOT NULL,
  `Q2` enum('CONFORME','NÃO CONFORME','NÃO APLICÁVEL') NOT NULL,
  `Q3` enum('CONFORME','NÃO CONFORME','NÃO APLICÁVEL') NOT NULL,
  `Q4` enum('CONFORME','NÃO CONFORME','NÃO APLICÁVEL') NOT NULL,
  `Q5` enum('CONFORME','NÃO CONFORME','NÃO APLICÁVEL') NOT NULL,
  `Q6` enum('CONFORME','NÃO CONFORME','NÃO APLICÁVEL') NOT NULL,
  `Q7` enum('CONFORME','NÃO CONFORME','NÃO APLICÁVEL') NOT NULL,
  `Q8` enum('CONFORME','NÃO CONFORME','NÃO APLICÁVEL') NOT NULL,
  `Q9` enum('CONFORME','NÃO CONFORME','NÃO APLICÁVEL') NOT NULL,
  `Q10` enum('CONFORME','NÃO CONFORME','NÃO APLICÁVEL') NOT NULL,
  `Q11` enum('CONFORME','NÃO CONFORME','NÃO APLICÁVEL') NOT NULL,
  `Q12` enum('CONFORME','NÃO CONFORME','NÃO APLICÁVEL') NOT NULL,
  `Q13` enum('CONFORME','NÃO CONFORME','NÃO APLICÁVEL') NOT NULL,
  `Q14` enum('CONFORME','NÃO CONFORME','NÃO APLICÁVEL') NOT NULL,
  `Q15` enum('CONFORME','NÃO CONFORME','NÃO APLICÁVEL') NOT NULL,
  `NR12_OBS` text,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_TAB_NR12`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Despejando dados para a tabela `tab_nr12`
INSERT INTO `tab_nr12` VALUES('1','2025-05-13','LINHA VINIL - 1','20:13:00','21:14:00','Adrian','10','CONFORME','NÃO CONFORME','NÃO CONFORME','NÃO CONFORME','CONFORME','NÃO APLICÁVEL','CONFORME','NÃO CONFORME','NÃO CONFORME','NÃO APLICÁVEL','NÃO CONFORME','CONFORME','NÃO CONFORME','CONFORME','NÃO APLICÁVEL','teste','2025-05-13 20:14:29');
INSERT INTO `tab_nr12` VALUES('2','2025-05-14','LINHA VINIL - 1','13:21:00','13:21:00','Adrian','12','CONFORME','NÃO CONFORME','NÃO APLICÁVEL','NÃO CONFORME','CONFORME','NÃO CONFORME','NÃO APLICÁVEL','NÃO CONFORME','CONFORME','NÃO CONFORME','NÃO APLICÁVEL','NÃO CONFORME','CONFORME','NÃO CONFORME','CONFORME','teste de nr12 op 12','2025-05-14 13:21:51');
INSERT INTO `tab_nr12` VALUES('3','2025-05-14','LINHA VINIL - 1','19:26:00','19:26:00','dsaa','13','CONFORME','CONFORME','CONFORME','CONFORME','CONFORME','CONFORME','CONFORME','CONFORME','CONFORME','CONFORME','CONFORME','CONFORME','CONFORME','CONFORME','CONFORME','teste','2025-05-14 19:26:59');
INSERT INTO `tab_nr12` VALUES('4','2025-05-14','LINHA VINIL - 1','20:28:00','20:28:00','Adrian','14','CONFORME','CONFORME','CONFORME','CONFORME','CONFORME','CONFORME','NÃO CONFORME','NÃO CONFORME','NÃO CONFORME','NÃO CONFORME','NÃO APLICÁVEL','NÃO APLICÁVEL','NÃO APLICÁVEL','NÃO APLICÁVEL','CONFORME','teste','2025-05-14 20:29:20');


-- --------------------------------------------------------
-- Estrutura para tabela `tab_op`
DROP TABLE IF EXISTS `tab_op`;
CREATE TABLE `tab_op` (
  `OP_ID` int NOT NULL AUTO_INCREMENT,
  `OP_DATA_PROG` date NOT NULL,
  `OP_ID_PRODUTO` int NOT NULL,
  `OP_QTD_PRODUTO` int NOT NULL,
  `OP_LINHA` varchar(100) NOT NULL,
  `OP_PROGRAMADOR` varchar(100) NOT NULL,
  `OP_TAG_MAQ` varchar(100) NOT NULL,
  `OP_RECEBEDOR` varchar(100) DEFAULT NULL,
  `OP_STATUS` varchar(100) DEFAULT NULL,
  `OP_HORA_INICIO` datetime DEFAULT NULL,
  `OP_HORA_FIM` datetime DEFAULT NULL,
  `OP_QTD_REAL` int DEFAULT NULL,
  `OP_DIARIO_BORDO` varchar(5000) DEFAULT NULL,
  PRIMARY KEY (`OP_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Despejando dados para a tabela `tab_op`
INSERT INTO `tab_op` VALUES('1','2025-04-26','1','124','LINHA VINIL - 1','ADRIAN','EXT001','AMANDA FRAGNAN','Aberto','2025-04-26 07:00:00','2025-04-26 19:36:00','0','');
INSERT INTO `tab_op` VALUES('4','2025-04-26','2','1234','LINHA VINIL - 1','ADRIAN','EXT001',NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `tab_op` VALUES('5','2025-04-27','2','12','LINHA VINIL - 1','ADRIAN','EXT001','','Aberto','2025-04-27 07:00:00','2025-04-27 10:20:00','0','');
INSERT INTO `tab_op` VALUES('6','2025-04-27','1','12','LINHA VINIL - 1','ADRIAN','EXT001',NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `tab_op` VALUES('7','2025-05-07','2','123','LINHA VINIL - 1','ADRIAN','EXT001','AMANDA FRAGNAN','Finalizado','2025-05-07 15:17:00','2025-05-07 15:30:00','100','TESTE');
INSERT INTO `tab_op` VALUES('8','2025-05-07','1','677','LINHA VINIL - 1','ADRIAN','EXT001',NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `tab_op` VALUES('9','2025-05-12','6','45','LINHA VINIL - 1','ADRIAN','EXT001',NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `tab_op` VALUES('10','2025-05-13','4','213','LINHA VINIL - 1','ADRIAN','EXT001',NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `tab_op` VALUES('11','2025-05-13','8','45','LINHA VINIL - 1','ADRIAN','EXT001',NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `tab_op` VALUES('12','2025-05-14','6','45','LINHA VINIL - 1','ADRIAN','EXT001',NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `tab_op` VALUES('13','2025-05-14','7','7','LINHA VINIL - 1','ADRIAN','EXT001',NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `tab_op` VALUES('14','2025-05-14','6','4651','LINHA VINIL - 1','ADRIAN','EXT001',NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `tab_op` VALUES('15','2025-05-17','7','134','LINHA VINIL - 1','ADRIAN','EXT001',NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `tab_op` VALUES('16','2026-04-19','5','200','LINHA VINIL - 2','ADRIAN','EXT001',NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `tab_op` VALUES('17','2026-04-19','7','200','LINHA VINIL - 1','ADRIAN','EXT001',NULL,NULL,NULL,NULL,NULL,NULL);


-- --------------------------------------------------------
-- Estrutura para tabela `tab_ordem_servico`
DROP TABLE IF EXISTS `tab_ordem_servico`;
CREATE TABLE `tab_ordem_servico` (
  `OS_ID` int NOT NULL AUTO_INCREMENT,
  `OS_SOLICITANTE` varchar(100) NOT NULL,
  `OS_STATUS` varchar(50) NOT NULL,
  `OS_TAG_MAQ` varchar(100) NOT NULL,
  `OS_LINHA` varchar(50) NOT NULL,
  `OS_CLASSIFICACAO` varchar(50) NOT NULL,
  `OS_RECEBIDO_POR` varchar(50) DEFAULT NULL,
  `OS_SITUACAO` varchar(50) NOT NULL,
  `OS_MANUTENTOR` varchar(100) DEFAULT NULL,
  `OS_DATA_PROMESSA` datetime DEFAULT NULL,
  `OS_DATA_SOLICITACAO` datetime NOT NULL,
  `OS_DATA_INICIO` datetime DEFAULT NULL,
  `OS_DATA_FIM` datetime DEFAULT NULL,
  `OS_DESC_PEDIDO` varchar(5000) DEFAULT NULL,
  `OS_GRAVIDADE` int NOT NULL,
  `OS_URGENCIA` int NOT NULL,
  `OS_TENDENCIA` int NOT NULL,
  `OS_SOMA_GUT` int NOT NULL,
  `OS_ASSINATURA` varchar(100) DEFAULT NULL,
  `OS_OBSERVACOES` varchar(5000) DEFAULT NULL,
  `OS_HORAS_PLAN` decimal(10,2) DEFAULT NULL,
  `OS_AVALIACAO_COMENTARIO` varchar(5000) DEFAULT NULL,
  `OS_AVALIACAO_NOTA` int DEFAULT NULL,
  `OS_ID_OP` int DEFAULT NULL,
  `OS_TIPO_MANUT` enum('PREVENTIVA','CORRETIVA','PREDITIVA') NOT NULL DEFAULT 'CORRETIVA',
  `OS_CUSTO_PECAS` decimal(10,2) DEFAULT '0.00',
  `OS_CUSTO_MAO_OBRA` decimal(10,2) DEFAULT '0.00',
  `OS_DATA_PLANEJADA` date DEFAULT NULL,
  PRIMARY KEY (`OS_ID`),
  KEY `fk_os_maq` (`OS_TAG_MAQ`),
  CONSTRAINT `fk_os_maq` FOREIGN KEY (`OS_TAG_MAQ`) REFERENCES `tab_maq` (`MAQ_TAG`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Despejando dados para a tabela `tab_ordem_servico`
INSERT INTO `tab_ordem_servico` VALUES('1','AMANDA FRAGNAN','Fechado','EXT001','LINHA VINIL - 1','MECANICA',NULL,'Linha parada',NULL,NULL,'2025-05-08 18:59:08','2025-05-08 19:00:00','2025-05-08 22:00:00','TESTE O.S','5','5','5','15',NULL,NULL,NULL,NULL,NULL,'9','CORRETIVA','150.00','300.00',NULL);
INSERT INTO `tab_ordem_servico` VALUES('2','AMANDA FRAGNAN','Aberto','EXT003','LINHA VINIL - 2','PREDIAL',NULL,'Linha segue em atuação',NULL,NULL,'2025-05-08 19:08:03',NULL,NULL,'TESTE 2 O.S','4','4','4','12',NULL,NULL,NULL,NULL,NULL,'9','PREVENTIVA','0.00','0.00','2025-05-15');
INSERT INTO `tab_ordem_servico` VALUES('3','AMANDA FRAGNAN','Aberto','EXT001','LINHA VINIL - 3','MECANICA',NULL,'Linha segue em atuação',NULL,NULL,'2025-05-08 19:10:21',NULL,NULL,'TESTE 3 O.S','3','3','3','9',NULL,NULL,NULL,NULL,NULL,'9','CORRETIVA','0.00','0.00',NULL);
INSERT INTO `tab_ordem_servico` VALUES('4','ADRIAN','Aberto','EXT001','LINHA VINIL - 1','ELETRICA','','Linha segue em atuação','',NULL,'2025-11-01 05:36:00',NULL,NULL,'TESTE','2','1','2','5','','0','0.50','',NULL,NULL,'CORRETIVA','0.00','0.00',NULL);
INSERT INTO `tab_ordem_servico` VALUES('5','ADRIAN','Fechado','EXT002','LINHA VINIL - 2','MECANICA',NULL,'Linha parada',NULL,NULL,'2025-11-01 05:38:00','2025-11-01 06:00:00','2025-11-01 10:00:00','TESTE 2','5','5','5','15',NULL,'0','1.00',NULL,NULL,NULL,'CORRETIVA','450.00','800.00',NULL);
INSERT INTO `tab_ordem_servico` VALUES('6','ADRIAN','Aberto','EXT001','LINHA VINIL - 1','PREDIAL',NULL,'Linha parada',NULL,NULL,'2025-11-01 05:42:00',NULL,NULL,'TESTE AMANDA','2','5','5','12',NULL,'0','1.00',NULL,NULL,NULL,'CORRETIVA','0.00','0.00',NULL);
INSERT INTO `tab_ordem_servico` VALUES('7','ADRIAN','Fechado','EXT003','LINHA VINIL - 3','PREDIAL',NULL,'Linha parada',NULL,NULL,'2025-11-01 05:46:00','2025-11-01 06:00:00','2025-11-01 07:30:00','TESTE AMANDA 2','2','3','3','8',NULL,'0','2.00',NULL,NULL,NULL,'CORRETIVA','120.00','100.00',NULL);
INSERT INTO `tab_ordem_servico` VALUES('8','ADRIAN','Aberto','EXT001','LINHA VINIL - 4','ELETRICA','','Linha segue em atuação','',NULL,'2025-11-01 05:47:00',NULL,NULL,'TESTE','5','5','2','12','','0','3.00','',NULL,NULL,'CORRETIVA','0.00','0.00',NULL);
INSERT INTO `tab_ordem_servico` VALUES('9','ADRIAN','Fechado','EXT001','LINHA VINIL - 1','MECANICA','','Linha parada','',NULL,'2025-11-01 05:48:00','2025-11-01 06:00:00','2025-11-01 18:00:00','TESTE','5','5','5','15','','0','5.00','teste',NULL,NULL,'CORRETIVA','1000.00','1500.00',NULL);
INSERT INTO `tab_ordem_servico` VALUES('10','ADRIAN','Fechado','EXT002','LINHA VINIL - 2','MECANICA','','Linha parada','',NULL,'2025-11-02 23:58:00','2025-11-03 00:00:00','2025-11-03 04:00:00','APRESENTAÇÃO','4','2','5','11','','0','12.00','ORDEM REALIZADA COM SUCESSO','3',NULL,'PREVENTIVA','300.00','500.00','2025-11-05');
INSERT INTO `tab_ordem_servico` VALUES('11','ADRIAN','Fechado','EXT001','LINHA VINIL - 1','ELETRICA','','Linha parada','',NULL,'2025-11-03 08:00:00','2025-11-03 08:30:00','2025-11-03 14:30:00','Preventiva Atrasada','5','5','5','15','','0','6.00','ok',NULL,NULL,'PREVENTIVA','150.00','300.00','2025-11-01');


-- --------------------------------------------------------
-- Estrutura para tabela `tab_paradas_produtivas`
DROP TABLE IF EXISTS `tab_paradas_produtivas`;
CREATE TABLE `tab_paradas_produtivas` (
  `PARADA_ID_OP` bigint NOT NULL,
  `PARADA_INICIO` datetime NOT NULL,
  `PARADA_FIM` datetime NOT NULL,
  `PARADA_TIPO` varchar(100) NOT NULL,
  PRIMARY KEY (`PARADA_ID_OP`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Despejando dados para a tabela `tab_paradas_produtivas`
INSERT INTO `tab_paradas_produtivas` VALUES('1','2025-04-26 22:00:00','2025-04-26 22:22:00','TESTE');
INSERT INTO `tab_paradas_produtivas` VALUES('4','2025-04-26 17:00:00','2025-04-26 17:12:00','TESTE');
INSERT INTO `tab_paradas_produtivas` VALUES('5','2025-04-27 10:21:00','2025-04-27 10:30:00','TESTE');
INSERT INTO `tab_paradas_produtivas` VALUES('7','2025-05-07 15:18:00','2025-05-07 15:19:00','TESTE');


-- --------------------------------------------------------
-- Estrutura para tabela `tab_produtos`
DROP TABLE IF EXISTS `tab_produtos`;
CREATE TABLE `tab_produtos` (
  `PROD_ID` bigint NOT NULL AUTO_INCREMENT,
  `PROD_DESC` varchar(255) NOT NULL,
  PRIMARY KEY (`PROD_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Despejando dados para a tabela `tab_produtos`
INSERT INTO `tab_produtos` VALUES('3','Fibra de Poliéster');
INSERT INTO `tab_produtos` VALUES('4','Polipropileno (PP) virgem');
INSERT INTO `tab_produtos` VALUES('5','CARPETE REFILADO VINIL');
INSERT INTO `tab_produtos` VALUES('6','CARPETE REFILADO COMPOSTO');
INSERT INTO `tab_produtos` VALUES('7','CARPETE REFILADO MOIDO');
INSERT INTO `tab_produtos` VALUES('8','CARPETE MOIDO CONFECÇÃO');
INSERT INTO `tab_produtos` VALUES('9','CARPETE REFILADO x');


-- --------------------------------------------------------
-- Estrutura para tabela `tab_refugo`
DROP TABLE IF EXISTS `tab_refugo`;
CREATE TABLE `tab_refugo` (
  `REFUGO_ID_OP` bigint NOT NULL,
  `REFUGO_QTD` int NOT NULL,
  `REFUGO_TIPO` varchar(100) NOT NULL,
  `REFUGO_UNIDADE` varchar(50) NOT NULL,
  PRIMARY KEY (`REFUGO_ID_OP`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Despejando dados para a tabela `tab_refugo`
INSERT INTO `tab_refugo` VALUES('1','1','TESTE','');
INSERT INTO `tab_refugo` VALUES('4','12','Metros de Carpete','');
INSERT INTO `tab_refugo` VALUES('5','12','TESTE','');
INSERT INTO `tab_refugo` VALUES('7','12','Metros de Carpete','');
INSERT INTO `tab_refugo` VALUES('13','23','ESTE','UN');


-- --------------------------------------------------------
-- Estrutura para tabela `tab_sensor_log`
DROP TABLE IF EXISTS `tab_sensor_log`;
CREATE TABLE `tab_sensor_log` (
  `LOG_ID` int NOT NULL AUTO_INCREMENT,
  `LOG_TAG_MAQ` varchar(50) NOT NULL,
  `LOG_EVENTO` varchar(50) NOT NULL,
  `LOG_TIMESTAMP` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LOG_TIMESTAMP_FIM` datetime DEFAULT NULL,
  `LOG_TOTAL_INPUTS` int DEFAULT '0',
  `LOG_TEMPO_CONECTADO` int DEFAULT '0',
  PRIMARY KEY (`LOG_ID`),
  KEY `idx_tag_maq` (`LOG_TAG_MAQ`),
  KEY `idx_timestamp` (`LOG_TIMESTAMP`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Despejando dados para a tabela `tab_sensor_log`
INSERT INTO `tab_sensor_log` VALUES('1','MAQ-001','DESCONEXAO','2026-04-30 09:41:05','2026-04-30 11:51:13','7318','7808');
INSERT INTO `tab_sensor_log` VALUES('2','MAQ-001','DESCONEXAO','2026-04-30 11:43:05','2026-04-30 14:05:28','3860','8543');
INSERT INTO `tab_sensor_log` VALUES('3','MAQ-001','DESCONEXAO','2026-04-30 13:57:05','2026-04-30 15:19:08','7314','4923');
INSERT INTO `tab_sensor_log` VALUES('4','MAQ-002','DESCONEXAO','2026-04-30 07:23:05','2026-04-30 10:07:08','2240','9843');
INSERT INTO `tab_sensor_log` VALUES('5','MAQ-002','DESCONEXAO','2026-04-30 15:28:05','2026-04-30 17:49:18','3171','8473');
INSERT INTO `tab_sensor_log` VALUES('6','MAQ-003','DESCONEXAO','2026-04-30 07:04:05','2026-04-30 08:48:17','2451','6252');
INSERT INTO `tab_sensor_log` VALUES('7','MAQ-004','DESCONEXAO','2026-04-30 06:42:05','2026-04-30 09:24:31','1576','9746');
INSERT INTO `tab_sensor_log` VALUES('8','MAQ-004','DESCONEXAO','2026-04-30 08:41:05','2026-04-30 11:31:22','3272','10217');
INSERT INTO `tab_sensor_log` VALUES('9','MAQ-004','DESCONEXAO','2026-04-30 09:51:05','2026-04-30 10:59:48','795','4123');
INSERT INTO `tab_sensor_log` VALUES('10','MAQ-005','DESCONEXAO','2026-04-30 15:19:05','2026-04-30 16:34:48','3209','4543');
INSERT INTO `tab_sensor_log` VALUES('11','MAQ-001','DESCONEXAO','2026-05-01 06:55:05','2026-05-01 08:58:53','6290','7428');
INSERT INTO `tab_sensor_log` VALUES('12','MAQ-001','DESCONEXAO','2026-05-01 15:44:05','2026-05-01 16:22:56','6736','2331');
INSERT INTO `tab_sensor_log` VALUES('13','MAQ-001','DESCONEXAO','2026-05-01 12:57:05','2026-05-01 15:45:15','3868','10090');
INSERT INTO `tab_sensor_log` VALUES('14','MAQ-002','DESCONEXAO','2026-05-01 14:38:05','2026-05-01 17:03:30','2761','8725');
INSERT INTO `tab_sensor_log` VALUES('15','MAQ-003','DESCONEXAO','2026-05-01 10:00:05','2026-05-01 11:03:43','1559','3818');
INSERT INTO `tab_sensor_log` VALUES('16','MAQ-003','DESCONEXAO','2026-05-01 12:16:05','2026-05-01 14:40:45','623','8680');
INSERT INTO `tab_sensor_log` VALUES('17','MAQ-003','DESCONEXAO','2026-05-01 13:26:05','2026-05-01 14:49:25','818','5000');
INSERT INTO `tab_sensor_log` VALUES('18','MAQ-004','DESCONEXAO','2026-05-01 09:01:05','2026-05-01 10:20:29','3017','4764');
INSERT INTO `tab_sensor_log` VALUES('19','MAQ-005','DESCONEXAO','2026-05-01 13:16:05','2026-05-01 15:17:59','3879','7314');
INSERT INTO `tab_sensor_log` VALUES('20','MAQ-005','DESCONEXAO','2026-05-01 13:17:05','2026-05-01 13:59:20','3315','2535');
INSERT INTO `tab_sensor_log` VALUES('21','MAQ-001','DESCONEXAO','2026-05-02 06:43:05','2026-05-02 07:48:49','6820','3944');
INSERT INTO `tab_sensor_log` VALUES('22','MAQ-002','DESCONEXAO','2026-05-02 09:21:05','2026-05-02 12:09:58','1493','10133');
INSERT INTO `tab_sensor_log` VALUES('23','MAQ-002','DESCONEXAO','2026-05-02 07:13:05','2026-05-02 07:27:13','3136','848');
INSERT INTO `tab_sensor_log` VALUES('24','MAQ-002','DESCONEXAO','2026-05-02 09:39:05','2026-05-02 11:48:43','3770','7778');
INSERT INTO `tab_sensor_log` VALUES('25','MAQ-003','DESCONEXAO','2026-05-02 14:26:05','2026-05-02 17:21:33','2437','10528');
INSERT INTO `tab_sensor_log` VALUES('26','MAQ-004','DESCONEXAO','2026-05-02 07:02:05','2026-05-02 08:18:36','1547','4591');
INSERT INTO `tab_sensor_log` VALUES('27','MAQ-005','DESCONEXAO','2026-05-02 14:55:05','2026-05-02 15:30:37','3969','2132');
INSERT INTO `tab_sensor_log` VALUES('28','MAQ-005','DESCONEXAO','2026-05-02 09:34:05','2026-05-02 12:01:51','1143','8866');
INSERT INTO `tab_sensor_log` VALUES('29','MAQ-005','DESCONEXAO','2026-05-02 07:10:05','2026-05-02 07:37:34','3224','1649');
INSERT INTO `tab_sensor_log` VALUES('30','MAQ-001','DESCONEXAO','2026-05-03 15:30:05','2026-05-03 16:30:40','3151','3635');
INSERT INTO `tab_sensor_log` VALUES('31','MAQ-001','DESCONEXAO','2026-05-03 10:03:05','2026-05-03 10:14:01','6681','656');
INSERT INTO `tab_sensor_log` VALUES('32','MAQ-001','DESCONEXAO','2026-05-03 07:15:05','2026-05-03 07:58:21','4607','2596');
INSERT INTO `tab_sensor_log` VALUES('33','MAQ-002','DESCONEXAO','2026-05-03 12:58:05','2026-05-03 15:10:19','3376','7934');
INSERT INTO `tab_sensor_log` VALUES('34','MAQ-002','DESCONEXAO','2026-05-03 10:30:05','2026-05-03 13:18:09','840','10084');
INSERT INTO `tab_sensor_log` VALUES('35','MAQ-002','DESCONEXAO','2026-05-03 15:37:05','2026-05-03 16:39:30','435','3745');
INSERT INTO `tab_sensor_log` VALUES('36','MAQ-003','DESCONEXAO','2026-05-03 09:45:05','2026-05-03 11:08:30','305','5005');
INSERT INTO `tab_sensor_log` VALUES('37','MAQ-003','DESCONEXAO','2026-05-03 07:16:05','2026-05-03 10:10:36','2791','10471');
INSERT INTO `tab_sensor_log` VALUES('38','MAQ-004','DESCONEXAO','2026-05-03 11:20:05','2026-05-03 12:02:24','2684','2539');
INSERT INTO `tab_sensor_log` VALUES('39','MAQ-005','DESCONEXAO','2026-05-03 07:25:05','2026-05-03 07:41:29','3943','984');
INSERT INTO `tab_sensor_log` VALUES('40','MAQ-001','DESCONEXAO','2026-05-04 15:39:05','2026-05-04 17:46:44','7610','7659');
INSERT INTO `tab_sensor_log` VALUES('41','MAQ-001','DESCONEXAO','2026-05-04 14:44:05','2026-05-04 16:38:28','6654','6863');
INSERT INTO `tab_sensor_log` VALUES('42','MAQ-001','DESCONEXAO','2026-05-04 12:20:05','2026-05-04 12:45:09','6255','1504');
INSERT INTO `tab_sensor_log` VALUES('43','MAQ-002','DESCONEXAO','2026-05-04 08:17:05','2026-05-04 09:54:57','2343','5872');
INSERT INTO `tab_sensor_log` VALUES('44','MAQ-002','DESCONEXAO','2026-05-04 08:27:05','2026-05-04 09:59:16','2992','5531');
INSERT INTO `tab_sensor_log` VALUES('45','MAQ-003','DESCONEXAO','2026-05-04 14:23:05','2026-05-04 17:17:50','3313','10485');
INSERT INTO `tab_sensor_log` VALUES('46','MAQ-004','DESCONEXAO','2026-05-04 10:12:05','2026-05-04 12:34:58','3712','8573');
INSERT INTO `tab_sensor_log` VALUES('47','MAQ-005','DESCONEXAO','2026-05-04 12:49:05','2026-05-04 14:22:37','2346','5612');
INSERT INTO `tab_sensor_log` VALUES('48','MAQ-005','DESCONEXAO','2026-05-04 14:51:05','2026-05-04 17:23:58','631','9173');
INSERT INTO `tab_sensor_log` VALUES('49','MAQ-005','DESCONEXAO','2026-05-04 14:39:05','2026-05-04 16:29:37','1325','6632');
INSERT INTO `tab_sensor_log` VALUES('50','MAQ-001','DESCONEXAO','2026-05-05 09:48:05','2026-05-05 12:38:05','5138','10200');
INSERT INTO `tab_sensor_log` VALUES('51','MAQ-001','DESCONEXAO','2026-05-05 15:52:05','2026-05-05 17:02:36','5600','4231');
INSERT INTO `tab_sensor_log` VALUES('52','MAQ-002','DESCONEXAO','2026-05-05 14:17:05','2026-05-05 17:13:35','1005','10590');
INSERT INTO `tab_sensor_log` VALUES('53','MAQ-003','DESCONEXAO','2026-05-05 12:53:05','2026-05-05 14:54:46','3752','7301');
INSERT INTO `tab_sensor_log` VALUES('54','MAQ-004','DESCONEXAO','2026-05-05 07:10:05','2026-05-05 09:28:19','1979','8294');
INSERT INTO `tab_sensor_log` VALUES('55','MAQ-004','DESCONEXAO','2026-05-05 08:21:05','2026-05-05 09:25:21','1465','3856');
INSERT INTO `tab_sensor_log` VALUES('56','MAQ-004','DESCONEXAO','2026-05-05 14:26:05','2026-05-05 15:22:39','1301','3394');
INSERT INTO `tab_sensor_log` VALUES('57','MAQ-005','DESCONEXAO','2026-05-05 14:44:05','2026-05-05 15:12:17','2403','1692');
INSERT INTO `tab_sensor_log` VALUES('58','MAQ-005','DESCONEXAO','2026-05-05 14:47:05','2026-05-05 17:40:46','1526','10421');
INSERT INTO `tab_sensor_log` VALUES('59','MAQ-005','DESCONEXAO','2026-05-05 15:47:05','2026-05-05 16:16:43','1084','1778');
INSERT INTO `tab_sensor_log` VALUES('60','MAQ-001','DESCONEXAO','2026-05-06 15:37:05','2026-05-06 16:46:42','7380','4177');
INSERT INTO `tab_sensor_log` VALUES('61','MAQ-001','DESCONEXAO','2026-05-06 13:54:05','2026-05-06 16:06:43','4764','7958');
INSERT INTO `tab_sensor_log` VALUES('62','MAQ-002','DESCONEXAO','2026-05-06 11:36:05','2026-05-06 12:47:35','3936','4290');
INSERT INTO `tab_sensor_log` VALUES('63','MAQ-002','DESCONEXAO','2026-05-06 06:58:05','2026-05-06 08:08:29','2168','4224');
INSERT INTO `tab_sensor_log` VALUES('64','MAQ-002','DESCONEXAO','2026-05-06 13:09:05','2026-05-06 13:35:02','3345','1557');
INSERT INTO `tab_sensor_log` VALUES('65','MAQ-003','DESCONEXAO','2026-05-06 14:12:05','2026-05-06 16:14:48','2211','7363');
INSERT INTO `tab_sensor_log` VALUES('66','MAQ-003','DESCONEXAO','2026-05-06 08:17:05','2026-05-06 08:33:16','3722','971');
INSERT INTO `tab_sensor_log` VALUES('67','MAQ-003','DESCONEXAO','2026-05-06 07:20:05','2026-05-06 10:14:06','1622','10441');
INSERT INTO `tab_sensor_log` VALUES('68','MAQ-004','DESCONEXAO','2026-05-06 13:23:05','2026-05-06 15:19:02','1428','6957');
INSERT INTO `tab_sensor_log` VALUES('69','MAQ-004','DESCONEXAO','2026-05-06 09:55:05','2026-05-06 10:30:42','1028','2137');
INSERT INTO `tab_sensor_log` VALUES('70','MAQ-004','DESCONEXAO','2026-05-06 11:36:05','2026-05-06 13:12:17','1248','5772');
INSERT INTO `tab_sensor_log` VALUES('71','MAQ-005','DESCONEXAO','2026-05-06 09:21:05','2026-05-06 11:27:04','986','7559');
INSERT INTO `tab_sensor_log` VALUES('72','MAQ-001','DESCONEXAO','2026-05-07 12:37:05','2026-05-07 14:41:02','3744','7437');
INSERT INTO `tab_sensor_log` VALUES('73','MAQ-002','DESCONEXAO','2026-05-07 07:17:05','2026-05-07 08:30:59','2464','4434');
INSERT INTO `tab_sensor_log` VALUES('74','MAQ-002','DESCONEXAO','2026-05-07 15:04:05','2026-05-07 17:18:41','1630','8076');
INSERT INTO `tab_sensor_log` VALUES('75','MAQ-003','DESCONEXAO','2026-05-07 12:09:05','2026-05-07 14:16:23','3085','7638');
INSERT INTO `tab_sensor_log` VALUES('76','MAQ-004','DESCONEXAO','2026-05-07 09:33:05','2026-05-07 10:28:59','643','3354');
INSERT INTO `tab_sensor_log` VALUES('77','MAQ-004','DESCONEXAO','2026-05-07 10:56:05','2026-05-07 11:20:07','2609','1442');
INSERT INTO `tab_sensor_log` VALUES('78','MAQ-004','DESCONEXAO','2026-05-07 14:07:05','2026-05-07 15:45:37','2324','5912');
INSERT INTO `tab_sensor_log` VALUES('79','MAQ-005','DESCONEXAO','2026-05-07 11:15:05','2026-05-07 11:34:25','3486','1160');
INSERT INTO `tab_sensor_log` VALUES('80','MAQ-001','DESCONEXAO','2026-05-06 17:40:27','2026-05-06 17:40:27','0','0');
INSERT INTO `tab_sensor_log` VALUES('81','MAQ-001','DESCONEXAO','2026-05-06 17:40:27','2026-05-06 17:41:50','780','83');
INSERT INTO `tab_sensor_log` VALUES('82','MAQ-001','CONEXAO','2026-05-06 22:29:59','2026-05-06 22:31:35','317','95');


SET FOREIGN_KEY_CHECKS=1;
COMMIT;
