-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: localhost    Database: univesp_pi
-- ------------------------------------------------------
-- Server version	8.0.42

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `tab_dde`
--

DROP TABLE IF EXISTS `tab_dde`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tab_dde` (
  `ID_DDE` int NOT NULL AUTO_INCREMENT,
  `DDE_TITULO` varchar(255) NOT NULL,
  `DDE_TEMA` varchar(255) NOT NULL,
  `DDE_DESENVOLVIMENTO` text NOT NULL,
  `DDE_DATA` date NOT NULL,
  `DDE_RESPONSAVEL` varchar(100) NOT NULL,
  PRIMARY KEY (`ID_DDE`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tab_dde`
--

LOCK TABLES `tab_dde` WRITE;
/*!40000 ALTER TABLE `tab_dde` DISABLE KEYS */;
INSERT INTO `tab_dde` VALUES (1,'Importância da Segurança do Trabalho','Segurança do Trabalho','A segurança do trabalho é essencial para preservar a integridade física e mental dos colaboradores. Usar Equipamentos de Proteção Individual (EPIs), seguir os procedimentos operacionais e manter o ambiente limpo são práticas fundamentais. Pequenas atitudes podem evitar grandes acidentes.','2025-05-08','Amanda Oliveira'),(2,'Operação sempre atenta!','Importância da atenção constante para garantir a segurança nos processos operacionais.','Manter a atenção plena durante as atividades diárias na operação é fundamental para o sucesso coletivo da equipe. Pequenas distrações podem gerar grandes impactos, como retrabalho, perdas de matéria-prima, falhas de qualidade e até riscos à segurança.\r\n\r\nEste DDE reforça a importância de cada colaborador estar vigilante aos detalhes de sua função, identificar rapidamente qualquer anomalia no processo e comunicar desvios assim que forem percebidos.\r\n\r\nA \"atenção operacional\" vai além da execução correta da tarefa: envolve percepção do ambiente, cuidado com os colegas, zelo pelo equipamento e compromisso com o resultado final.\r\n\r\nCada setor depende do outro. Quando todos estão atentos, evitamos desperdícios, aumentamos a produtividade e garantimos entregas com qualidade.\r\n\r\nVamos continuar com esse olhar atento e postura responsável — afinal, uma operação forte é construída com atenção e disciplina no dia a dia.','2025-05-08','ADRIAN'),(3,'Manutenção Autônoma: Uma Abordagem para a Eficiência Operacional','Implementação e Benefícios da Manutenção Autônoma no Contexto Industrial','A manutenção autônoma é um conceito fundamental na busca pela eficiência operacional nas indústrias, especialmente em processos que envolvem máquinas e equipamentos pesados. Este conceito faz parte do pilar da Manutenção Produtiva Total (TPM), que visa melhorar a confiabilidade dos equipamentos e reduzir o tempo de inatividade. A principal ideia por trás da manutenção autônoma é permitir que os próprios operadores de máquinas se envolvam ativamente na manutenção diária e na identificação de falhas potenciais, sem a necessidade de intervenção constante da equipe de manutenção.','2025-05-09','ADRIAN'),(4,'Operação sempre atenta!','Implementação e Benefícios da Manutenção Autônoma no Contexto Industrial','Manter a atenção plena durante as atividades diárias na operação é fundamental para o sucesso coletivo da equipe. Pequenas distrações podem gerar grandes impactos, como retrabalho, perdas de matéria-prima, falhas de qualidade e até riscos à segurança. Este DDE reforça a importância de cada colaborador estar vigilante aos detalhes de sua função, identificar rapidamente qualquer anomalia no processo e comunicar desvios assim que forem percebidos. A \"atenção operacional\" vai além da execução correta da tarefa: envolve percepção do ambiente, cuidado com os colegas, zelo pelo equipamento e compromisso com o resultado final. Cada setor depende do outro. Quando todos estão atentos, evitamos desperdícios, aumentamos a produtividade e garantimos entregas com qualidade. Vamos continuar com esse olhar atento e postura responsável — afinal, uma operação forte é construída com atenção e disciplina no dia a dia.','2025-05-17','ADRIAN'),(5,'Operação sempre atenta!','Implementação e Benefícios da Manutenção Autônoma no Contexto Industrial','TESTE','2025-05-18','ADRIAN');
/*!40000 ALTER TABLE `tab_dde` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-17 16:53:06
