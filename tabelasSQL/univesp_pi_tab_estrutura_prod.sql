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
-- Table structure for table `tab_estrutura_prod`
--

DROP TABLE IF EXISTS `tab_estrutura_prod`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tab_estrutura_prod`
--

LOCK TABLES `tab_estrutura_prod` WRITE;
/*!40000 ALTER TABLE `tab_estrutura_prod` DISABLE KEYS */;
INSERT INTO `tab_estrutura_prod` VALUES (23,5,3),(24,5,4),(25,6,3),(26,7,4),(27,8,3),(28,8,4),(29,9,3);
/*!40000 ALTER TABLE `tab_estrutura_prod` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-17 16:53:07
