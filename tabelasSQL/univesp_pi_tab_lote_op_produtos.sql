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
-- Table structure for table `tab_lote_op_produtos`
--

DROP TABLE IF EXISTS `tab_lote_op_produtos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tab_lote_op_produtos`
--

LOCK TABLES `tab_lote_op_produtos` WRITE;
/*!40000 ALTER TABLE `tab_lote_op_produtos` DISABLE KEYS */;
INSERT INTO `tab_lote_op_produtos` VALUES (13,9,26,'86'),(14,9,29,'67'),(15,9,26,'26'),(16,9,29,'64'),(17,13,27,'8678'),(18,13,28,'6867'),(19,9,26,'245'),(20,9,29,'346'),(21,13,27,'4545'),(22,13,28,'4544'),(23,13,27,'2345'),(24,13,28,'243');
/*!40000 ALTER TABLE `tab_lote_op_produtos` ENABLE KEYS */;
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
