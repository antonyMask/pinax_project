CREATE DATABASE  IF NOT EXISTS `sistema_contable_pinax` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `sistema_contable_pinax`;
-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: sistema_contable_pinax
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

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
-- Table structure for table `cc_catalogo_cuenta`
--

DROP TABLE IF EXISTS `cc_catalogo_cuenta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_catalogo_cuenta` (
  `cod_cuenta` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'codigo unico de la cuenta contable',
  `cod_num_cuenta` varchar(30) NOT NULL COMMENT 'codigo visible de la cuenta contable',
  `nom_cuenta` varchar(150) NOT NULL COMMENT 'nombre de la cuenta contable',
  `cod_tipo_cuenta` bigint(20) NOT NULL COMMENT 'codigo del tipo de cuenta relacionado',
  `cod_cuenta_padre` bigint(20) DEFAULT NULL COMMENT 'codigo de la cuenta padre en la jerarquia',
  `num_nivel_jerarquia` tinyint(4) NOT NULL COMMENT 'nivel jerarquico de la cuenta',
  `ind_naturaleza` enum('deudora','acreedora') NOT NULL COMMENT 'naturaleza de la cuenta contable',
  `ind_acepta_movimiento` enum('si','no') NOT NULL DEFAULT 'no' COMMENT 'indica si la cuenta acepta movimientos',
  `des_cuenta` varchar(255) DEFAULT NULL COMMENT 'descripcion de la cuenta contable',
  `ind_estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo' COMMENT 'estado de la cuenta contable',
  `usr_adicion` varchar(100) NOT NULL DEFAULT 'sistema' COMMENT 'usuario que adiciono el registro',
  `fec_adicion` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'fecha de adicion del registro',
  `usr_modificacion` varchar(100) DEFAULT NULL COMMENT 'usuario que modifico el registro',
  `fec_modificacion` datetime DEFAULT NULL COMMENT 'fecha de modificacion del registro',
  PRIMARY KEY (`cod_cuenta`),
  UNIQUE KEY `uk_cc_catalogo_cuenta_cod` (`cod_num_cuenta`),
  KEY `idx_cc_catalogo_cuenta_tipo` (`cod_tipo_cuenta`),
  KEY `idx_cc_catalogo_cuenta_padre` (`cod_cuenta_padre`),
  CONSTRAINT `fk_cc_catalogo_cuenta_padre` FOREIGN KEY (`cod_cuenta_padre`) REFERENCES `cc_catalogo_cuenta` (`cod_cuenta`),
  CONSTRAINT `fk_cc_catalogo_cuenta_tipo` FOREIGN KEY (`cod_tipo_cuenta`) REFERENCES `cc_tipo_cuenta` (`cod_tipo_cuenta`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='plan de cuentas contables del sistema';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cc_catalogo_cuenta`
--

LOCK TABLES `cc_catalogo_cuenta` WRITE;
/*!40000 ALTER TABLE `cc_catalogo_cuenta` DISABLE KEYS */;
INSERT INTO `cc_catalogo_cuenta` VALUES (1,'1','activo',1,NULL,1,'deudora','no','cuenta principal de activos','activo','sistema','2026-06-05 20:41:15',NULL,NULL),(2,'2','pasivo',2,NULL,1,'acreedora','no','cuenta principal de pasivos','activo','sistema','2026-06-05 20:41:15',NULL,NULL),(3,'3','patrimonio',3,NULL,1,'acreedora','no','cuenta principal de patrimonio','activo','sistema','2026-06-05 20:41:15',NULL,NULL),(4,'4','ingreso',4,NULL,1,'acreedora','no','cuenta principal de ingresos','activo','sistema','2026-06-05 20:41:15',NULL,NULL),(5,'5.1','gastos administrativos',5,NULL,1,'deudora','si','gastos administrativos de operacion','activo','sistema','2026-06-05 20:41:15',NULL,NULL),(6,'1.1.01','caja general',1,1,2,'deudora','si','efectivo disponible en caja','activo','sistema','2026-06-05 20:41:15',NULL,NULL),(7,'1.1.02','bancos',1,1,2,'deudora','si','dinero disponible en cuentas bancarias','activo','sistema','2026-06-05 20:41:15',NULL,NULL),(8,'2.1.01','proveedores nacionales',2,2,2,'acreedora','si','deudas con proveedores locales','activo','sistema','2026-06-05 20:41:15',NULL,NULL),(9,'3.1.01','capital social',3,3,2,'acreedora','si','capital aportado por los socios','activo','sistema','2026-06-05 20:41:15',NULL,NULL),(10,'4.1.01','ingresos por servicios',4,4,2,'acreedora','si','ingresos generados por servicios prestados','activo','sistema','2026-06-05 20:41:15',NULL,NULL),(11,'1.9.20260611202350','cuenta prueba procedimiento inactiva',1,NULL,1,'deudora','si','cuenta inactivada logicamente mediante soft delete','inactivo','sistema','2026-06-11 20:23:56','sistema','2026-06-11 20:24:13'),(12,'1.9.20260611204804','cuenta prueba procedimiento inactiva',1,NULL,1,'deudora','si','cuenta inactivada logicamente mediante soft delete','inactivo','sistema','2026-06-11 20:48:04','sistema','2026-06-11 20:48:04'),(13,'1.9.20260611205605','cuenta prueba procedimiento inactiva',1,NULL,1,'deudora','si','cuenta inactivada logicamente mediante soft delete','inactivo','sistema','2026-06-11 20:56:05','sistema','2026-06-11 20:56:05'),(14,'1.9.20260611205929','cuenta prueba procedimiento inactiva',1,NULL,1,'deudora','si','cuenta inactivada logicamente mediante soft delete','inactivo','sistema','2026-06-11 20:59:29','sistema','2026-06-11 20:59:29'),(15,'1.9.20260611205941','cuenta prueba procedimiento inactiva',1,NULL,1,'deudora','si','cuenta inactivada logicamente mediante soft delete','inactivo','sistema','2026-06-11 20:59:41','sistema','2026-06-11 20:59:41'),(16,'1.9.20260611205954','cuenta prueba procedimiento inactiva',1,NULL,1,'deudora','si','cuenta inactivada logicamente mediante soft delete','inactivo','sistema','2026-06-11 20:59:54','sistema','2026-06-11 20:59:54'),(17,'1.9.20260611210013','cuenta prueba procedimiento inactiva',1,NULL,1,'deudora','si','cuenta inactivada logicamente mediante soft delete','inactivo','sistema','2026-06-11 21:00:13','sistema','2026-06-11 21:00:13'),(18,'1.9.20260611210603','cuenta prueba procedimiento inactiva',1,NULL,1,'deudora','si','cuenta inactivada logicamente mediante soft delete','inactivo','sistema','2026-06-11 21:06:03','sistema','2026-06-11 21:06:03'),(19,'1.9.20260611210620','cuenta prueba procedimiento inactiva',1,NULL,1,'deudora','si','cuenta inactivada logicamente mediante soft delete','inactivo','sistema','2026-06-11 21:06:20','sistema','2026-06-11 21:06:20'),(20,'1.9.20260611210643','cuenta prueba procedimiento inactiva',1,NULL,1,'deudora','si','cuenta inactivada logicamente mediante soft delete','inactivo','sistema','2026-06-11 21:06:43','sistema','2026-06-11 21:06:43'),(21,'1.9.20260611211012','cuenta prueba procedimiento inactiva',1,NULL,1,'deudora','si','cuenta inactivada logicamente mediante soft delete','inactivo','sistema','2026-06-11 21:10:12','sistema','2026-06-11 21:10:12'),(22,'1.1.99','cuenta prueba api actualizada',1,1,2,'deudora','si','cuenta actualizada desde Thunder Client','inactivo','admin_api','2026-06-29 12:14:58','admin_api','2026-06-29 14:35:05');
/*!40000 ALTER TABLE `cc_catalogo_cuenta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_tipo_cuenta`
--

DROP TABLE IF EXISTS `cc_tipo_cuenta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_tipo_cuenta` (
  `cod_tipo_cuenta` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'codigo unico del tipo de cuenta',
  `nom_tipo_cuenta` varchar(80) NOT NULL COMMENT 'nombre del tipo de cuenta contable',
  `ind_naturaleza` enum('deudora','acreedora') NOT NULL COMMENT 'naturaleza normal del tipo de cuenta',
  `des_tipo_cuenta` varchar(255) DEFAULT NULL COMMENT 'descripcion del tipo de cuenta',
  `ind_estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo' COMMENT 'estado del tipo de cuenta',
  `usr_adicion` varchar(100) NOT NULL DEFAULT 'sistema' COMMENT 'usuario que adiciono el registro',
  `fec_adicion` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'fecha de adicion del registro',
  `usr_modificacion` varchar(100) DEFAULT NULL COMMENT 'usuario que modifico el registro',
  `fec_modificacion` datetime DEFAULT NULL COMMENT 'fecha de modificacion del registro',
  PRIMARY KEY (`cod_tipo_cuenta`),
  UNIQUE KEY `uk_cc_tipo_cuenta_nom` (`nom_tipo_cuenta`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='clasificacion principal de cuentas contables';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cc_tipo_cuenta`
--

LOCK TABLES `cc_tipo_cuenta` WRITE;
/*!40000 ALTER TABLE `cc_tipo_cuenta` DISABLE KEYS */;
INSERT INTO `cc_tipo_cuenta` VALUES (1,'activo','deudora','bienes y derechos controlados por la empresa','activo','sistema','2026-06-05 20:41:15',NULL,NULL),(2,'pasivo','acreedora','obligaciones de la empresa con terceros','activo','sistema','2026-06-05 20:41:15',NULL,NULL),(3,'patrimonio','acreedora','capital y recursos propios de la empresa','activo','sistema','2026-06-05 20:41:15',NULL,NULL),(4,'ingreso','acreedora','entradas economicas por ventas o servicios','activo','sistema','2026-06-05 20:41:15',NULL,NULL),(5,'gasto','deudora','salidas economicas por operaciones administrativas','activo','sistema','2026-06-05 20:41:15',NULL,NULL);
/*!40000 ALTER TABLE `cc_tipo_cuenta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cm_saldo_cuenta_periodo`
--

DROP TABLE IF EXISTS `cm_saldo_cuenta_periodo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cm_saldo_cuenta_periodo` (
  `cod_saldo` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'codigo unico del saldo',
  `cod_cuenta` bigint(20) NOT NULL COMMENT 'codigo de la cuenta contable',
  `cod_periodo` bigint(20) NOT NULL COMMENT 'codigo del periodo contable',
  `sal_inicial` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'saldo inicial de la cuenta',
  `tot_debe` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'total del debe en el periodo',
  `tot_haber` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'total del haber en el periodo',
  `sal_final` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'saldo final de la cuenta',
  `ind_estado` enum('abierto','cerrado','recalculado','inactivo') NOT NULL DEFAULT 'abierto' COMMENT 'estado contable y eliminacion logica del saldo',
  `fec_actualizacion` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'fecha de actualizacion del saldo',
  PRIMARY KEY (`cod_saldo`),
  UNIQUE KEY `uk_cm_saldo_cuenta_periodo` (`cod_cuenta`,`cod_periodo`),
  KEY `idx_cm_saldo_cuenta_periodo_periodo` (`cod_periodo`),
  CONSTRAINT `fk_cm_saldo_cuenta_periodo_cuenta` FOREIGN KEY (`cod_cuenta`) REFERENCES `cc_catalogo_cuenta` (`cod_cuenta`),
  CONSTRAINT `fk_cm_saldo_cuenta_periodo_periodo` FOREIGN KEY (`cod_periodo`) REFERENCES `ga_periodo_contable` (`cod_periodo`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='saldos acumulados por cuenta y periodo';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cm_saldo_cuenta_periodo`
--

LOCK TABLES `cm_saldo_cuenta_periodo` WRITE;
/*!40000 ALTER TABLE `cm_saldo_cuenta_periodo` DISABLE KEYS */;
INSERT INTO `cm_saldo_cuenta_periodo` VALUES (1,5,1,0.00,2000.00,0.00,2000.00,'abierto','2026-06-05 20:41:15'),(2,6,1,0.00,15000.00,3000.00,12000.00,'abierto','2026-06-05 20:41:15'),(3,7,1,0.00,3000.00,1200.00,1800.00,'abierto','2026-06-05 20:41:15'),(4,8,1,0.00,0.00,800.00,800.00,'abierto','2026-06-05 20:41:15'),(5,9,1,0.00,0.00,10000.00,10000.00,'abierto','2026-06-05 20:41:15'),(6,10,1,0.00,0.00,5000.00,5000.00,'abierto','2026-06-05 20:41:15');
/*!40000 ALTER TABLE `cm_saldo_cuenta_periodo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ga_asiento_contable`
--

DROP TABLE IF EXISTS `ga_asiento_contable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ga_asiento_contable` (
  `cod_asiento` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'codigo unico del asiento contable',
  `num_asiento` varchar(50) NOT NULL COMMENT 'numero correlativo del asiento contable',
  `cod_periodo` bigint(20) NOT NULL COMMENT 'codigo del periodo contable',
  `cod_user` bigint(20) NOT NULL COMMENT 'codigo del usuario que registra el asiento',
  `fec_asiento` date NOT NULL COMMENT 'fecha contable del asiento',
  `des_asiento` varchar(255) DEFAULT NULL COMMENT 'descripcion general del asiento',
  `tip_asiento` enum('manual','ajuste','apertura','cierre','reversion') NOT NULL COMMENT 'tipo de asiento contable',
  `tot_debe` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'total del debe',
  `tot_haber` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'total del haber',
  `ind_estado` enum('borrador','aprobado','anulado') NOT NULL DEFAULT 'borrador' COMMENT 'estado del asiento contable',
  `usr_adicion` varchar(100) NOT NULL DEFAULT 'sistema' COMMENT 'usuario que adiciono el registro',
  `fec_adicion` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'fecha de adicion del registro',
  PRIMARY KEY (`cod_asiento`),
  UNIQUE KEY `uk_ga_asiento_contable_num` (`num_asiento`),
  KEY `idx_ga_asiento_contable_periodo` (`cod_periodo`),
  KEY `idx_ga_asiento_contable_user` (`cod_user`),
  CONSTRAINT `fk_ga_asiento_contable_periodo` FOREIGN KEY (`cod_periodo`) REFERENCES `ga_periodo_contable` (`cod_periodo`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='cabecera de asientos contables';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ga_asiento_contable`
--

LOCK TABLES `ga_asiento_contable` WRITE;
/*!40000 ALTER TABLE `ga_asiento_contable` DISABLE KEYS */;
INSERT INTO `ga_asiento_contable` VALUES (1,'asi-2026-0001',1,1,'2026-06-01','aporte inicial de capital en efectivo','apertura',10000.00,10000.00,'aprobado','sistema','2026-06-05 20:41:15'),(2,'asi-2026-0002',1,1,'2026-06-05','venta de servicios al contado','manual',5000.00,5000.00,'aprobado','sistema','2026-06-05 20:41:15'),(3,'asi-2026-0003',1,1,'2026-06-10','deposito de efectivo en banco','manual',3000.00,3000.00,'aprobado','sistema','2026-06-05 20:41:15'),(4,'asi-2026-0004',1,1,'2026-06-15','pago de gastos administrativos con banco','manual',1200.00,1200.00,'aprobado','sistema','2026-06-05 20:41:15'),(5,'asi-2026-0005',1,1,'2026-06-20','registro de gasto administrativo pendiente de pago','manual',800.00,800.00,'aprobado','sistema','2026-06-05 20:41:15'),(6,'asi-test-20260611204804',1,1,'2026-06-11','asiento anulado logicamente mediante soft delete','manual',1000.00,1000.00,'anulado','sistema','2026-06-11 20:48:04'),(7,'asi-test-20260611205941',1,1,'2026-06-11','asiento anulado logicamente mediante soft delete','manual',1000.00,1000.00,'anulado','sistema','2026-06-11 20:59:41'),(8,'asi-test-20260611205954',1,1,'2026-06-11','asiento anulado logicamente mediante soft delete','manual',1000.00,1000.00,'anulado','sistema','2026-06-11 20:59:54'),(9,'asi-test-20260611211012',1,1,'2026-06-11','asiento anulado logicamente mediante soft delete','manual',1000.00,1000.00,'anulado','sistema','2026-06-11 21:10:12');
/*!40000 ALTER TABLE `ga_asiento_contable` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ga_detalle_asiento`
--

DROP TABLE IF EXISTS `ga_detalle_asiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ga_detalle_asiento` (
  `cod_detalle_asiento` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'codigo unico del detalle de asiento',
  `cod_asiento` bigint(20) NOT NULL COMMENT 'codigo del asiento contable',
  `cod_cuenta` bigint(20) NOT NULL COMMENT 'codigo de la cuenta contable afectada',
  `num_linea` int(11) NOT NULL COMMENT 'numero de linea dentro del asiento',
  `des_linea` varchar(255) DEFAULT NULL COMMENT 'descripcion de la linea del asiento',
  `mon_debe` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'monto registrado en el debe',
  `mon_haber` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'monto registrado en el haber',
  `usr_adicion` varchar(100) NOT NULL DEFAULT 'sistema' COMMENT 'usuario que adiciono el registro',
  `fec_adicion` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'fecha de adicion del registro',
  PRIMARY KEY (`cod_detalle_asiento`),
  UNIQUE KEY `uk_ga_detalle_asiento_linea` (`cod_asiento`,`num_linea`),
  KEY `idx_ga_detalle_asiento_cuenta` (`cod_cuenta`),
  CONSTRAINT `fk_ga_detalle_asiento_asiento` FOREIGN KEY (`cod_asiento`) REFERENCES `ga_asiento_contable` (`cod_asiento`),
  CONSTRAINT `fk_ga_detalle_asiento_cuenta` FOREIGN KEY (`cod_cuenta`) REFERENCES `cc_catalogo_cuenta` (`cod_cuenta`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='detalle de movimientos de cada asiento';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ga_detalle_asiento`
--

LOCK TABLES `ga_detalle_asiento` WRITE;
/*!40000 ALTER TABLE `ga_detalle_asiento` DISABLE KEYS */;
INSERT INTO `ga_detalle_asiento` VALUES (1,1,6,1,'entrada de efectivo por aporte inicial',10000.00,0.00,'sistema','2026-06-05 20:41:15'),(2,1,9,2,'registro de capital social aportado',0.00,10000.00,'sistema','2026-06-05 20:41:15'),(3,2,6,1,'ingreso de efectivo por venta de servicios',5000.00,0.00,'sistema','2026-06-05 20:41:15'),(4,2,10,2,'reconocimiento de ingreso por servicios',0.00,5000.00,'sistema','2026-06-05 20:41:15'),(5,3,7,1,'deposito de efectivo en cuenta bancaria',3000.00,0.00,'sistema','2026-06-05 20:41:15'),(6,3,6,2,'salida de efectivo de caja por deposito',0.00,3000.00,'sistema','2026-06-05 20:41:15'),(7,4,5,1,'registro de gasto administrativo pagado',1200.00,0.00,'sistema','2026-06-05 20:41:15'),(8,4,7,2,'salida de banco por pago de gasto',0.00,1200.00,'sistema','2026-06-05 20:41:15'),(9,5,5,1,'registro de gasto administrativo pendiente',800.00,0.00,'sistema','2026-06-05 20:41:15'),(10,5,8,2,'obligacion pendiente con proveedor',0.00,800.00,'sistema','2026-06-05 20:41:15'),(11,6,12,1,'registro al debe en cuenta de prueba',1000.00,0.00,'sistema','2026-06-11 20:48:04'),(12,6,9,2,'registro al haber en cuenta contraparte',0.00,1000.00,'sistema','2026-06-11 20:48:04'),(13,7,15,1,'registro al debe en cuenta de prueba',1000.00,0.00,'sistema','2026-06-11 20:59:41'),(14,7,9,2,'registro al haber en cuenta contraparte',0.00,1000.00,'sistema','2026-06-11 20:59:41'),(15,8,16,1,'registro al debe en cuenta de prueba',1000.00,0.00,'sistema','2026-06-11 20:59:54'),(16,8,9,2,'registro al haber en cuenta contraparte',0.00,1000.00,'sistema','2026-06-11 20:59:54'),(17,9,21,1,'registro al debe en cuenta de prueba',1000.00,0.00,'sistema','2026-06-11 21:10:12'),(18,9,9,2,'registro al haber en cuenta contraparte',0.00,1000.00,'sistema','2026-06-11 21:10:12');
/*!40000 ALTER TABLE `ga_detalle_asiento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ga_periodo_contable`
--

DROP TABLE IF EXISTS `ga_periodo_contable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ga_periodo_contable` (
  `cod_periodo` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'codigo unico del periodo contable',
  `nom_periodo` varchar(80) NOT NULL COMMENT 'nombre del periodo contable',
  `fec_inicio` date NOT NULL COMMENT 'fecha inicial del periodo contable',
  `fec_fin` date NOT NULL COMMENT 'fecha final del periodo contable',
  `tip_periodo` enum('mensual','trimestral','semestral','anual') NOT NULL COMMENT 'tipo de periodo contable',
  `ind_estado` enum('abierto','cerrado','anulado') NOT NULL DEFAULT 'abierto' COMMENT 'estado del periodo contable',
  `fec_cierre` datetime DEFAULT NULL COMMENT 'fecha de cierre del periodo',
  `des_observacion` varchar(255) DEFAULT NULL COMMENT 'observacion del periodo contable',
  `usr_adicion` varchar(100) NOT NULL DEFAULT 'sistema' COMMENT 'usuario que adiciono el registro',
  `fec_adicion` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'fecha de adicion del registro',
  PRIMARY KEY (`cod_periodo`),
  UNIQUE KEY `uk_ga_periodo_contable_fechas` (`fec_inicio`,`fec_fin`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='periodos contables del sistema';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ga_periodo_contable`
--

LOCK TABLES `ga_periodo_contable` WRITE;
/*!40000 ALTER TABLE `ga_periodo_contable` DISABLE KEYS */;
INSERT INTO `ga_periodo_contable` VALUES (1,'junio 2026','2026-06-01','2026-06-30','mensual','abierto',NULL,'periodo de prueba para el sistema pinax','sistema','2026-06-05 20:41:15'),(2,'julio 2026','2026-07-01','2026-07-31','mensual','abierto',NULL,'periodo disponible para registros futuros','sistema','2026-06-05 20:41:15');
/*!40000 ALTER TABLE `ga_periodo_contable` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pa_address`
--

DROP TABLE IF EXISTS `pa_address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pa_address` (
  `COD_ADDRESS` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'THE PRIMARY KEY OF THE ADDRESS',
  `COD_CITY` bigint(20) DEFAULT NULL COMMENT 'COD OF THE CITY',
  `ADDRESS` varchar(2000) NOT NULL COMMENT 'THE ADDRESS',
  `TYP_ADDRESS` enum('H','W','A') NOT NULL COMMENT 'TYPE OF ADDRESS H:HOME W:WORK A:ADDRESS',
  `USR_ADD` varchar(255) NOT NULL COMMENT 'USER THAT ADDED THIS ROW',
  `DAT_ADD` datetime NOT NULL COMMENT 'DATE THAT THIS ROW WERE ADDED',
  PRIMARY KEY (`COD_ADDRESS`),
  KEY `FK_ADDRESS_CITIES` (`COD_CITY`),
  CONSTRAINT `FK_ADDRESS_CITIES` FOREIGN KEY (`COD_CITY`) REFERENCES `pa_cities` (`COD_CITY`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pa_address`
--

LOCK TABLES `pa_address` WRITE;
/*!40000 ALTER TABLE `pa_address` DISABLE KEYS */;
/*!40000 ALTER TABLE `pa_address` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pa_cities`
--

DROP TABLE IF EXISTS `pa_cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pa_cities` (
  `COD_CITY` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'COD OF THE CITY',
  `COD_STATE` bigint(20) DEFAULT NULL COMMENT 'COD OF THE STATE',
  `NAM_CITY` varchar(100) DEFAULT NULL COMMENT 'NAME OF THE CITY',
  `ZIP_CODE` int(32) DEFAULT NULL COMMENT 'ZIP CODE OF THE CITY',
  `POS_CODE` int(32) DEFAULT NULL COMMENT 'POSTAL CODE OF THE CITY',
  `POPULATION` int(10) DEFAULT NULL COMMENT 'POPULATION OF THE CITY',
  `CURRENCY` varchar(20) DEFAULT NULL COMMENT 'CURRENCY OF THE CITY',
  `TIMEZONE` varchar(20) DEFAULT NULL COMMENT 'TIMEZONE OF THE CITY',
  `DES_CITY` varchar(2000) DEFAULT NULL COMMENT 'DESCRIPTION OF THE CITY',
  `USR_ADD` varchar(255) NOT NULL COMMENT 'USER THAT ADDED THIS ROW',
  `DAT_ADD` datetime NOT NULL COMMENT 'DATE THAT THIS ROW WERE ADDED',
  PRIMARY KEY (`COD_CITY`),
  KEY `FK_REL_STATES_CITIES` (`COD_STATE`),
  CONSTRAINT `FK_REL_STATES_CITIES` FOREIGN KEY (`COD_STATE`) REFERENCES `pa_states` (`COD_STATE`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pa_cities`
--

LOCK TABLES `pa_cities` WRITE;
/*!40000 ALTER TABLE `pa_cities` DISABLE KEYS */;
INSERT INTO `pa_cities` VALUES (1,1,'La Ceiba',504,31101,449882,'LPS','-6','8 Municipios','lfiallos','2016-11-02 00:00:00'),(2,2,'Trujillo',504,32101,319786,'LPS','-6','Tiene 10 Municipios','lfiallos','2016-11-02 00:00:00'),(3,3,'Comayagua',504,12101,382722,'LPS','-6','Tiene 21 Municipios','lfiallos','2016-11-02 00:00:00'),(4,4,'Santa Rosa de Copan',504,41101,382722,'LPS','-6','Tiene 23 Municipios','lfiallos','2016-11-02 00:00:00'),(5,5,'San Pedro Sula',504,21101,1621762,'LPS','-6','Tiene 12 Municipios','lfiallos','2016-11-02 00:00:00'),(6,6,'Choluteca',504,51101,447854,'LPS','-6','Tiene 16 municipios','lfiallos','2016-11-02 00:00:00'),(7,7,'Yuscaran',504,13101,458472,'LPS','-6','Tiene 19 Municipios','lfiallos','2016-11-02 00:00:00'),(8,8,'Tegucigalpa',504,11101,1553379,'LPS','-6','Tiene 28 Municipios','lfiallos','2016-11-02 00:00:00'),(9,9,'Puerto Lempira',504,33101,94450,'LPS','-6','Tiene 6 Municipios','lfiallos','2016-11-02 00:00:00'),(10,10,'La Esperanza',504,14101,241568,'LPS','-6','Tiene 17 Municipios','lfiallos','2016-11-02 00:00:00'),(11,11,'La Paz',504,15101,206065,'LPS','-6','Tiene 19 Municipios','lfiallos','2016-11-02 00:00:00'),(12,12,'Gracias',504,42101,333125,'LPS','-6','Tiene 28 Municipios','lfiallos','2016-11-02 00:00:00'),(13,13,'Nueva Ocotepeque',504,43101,151516,'LPS','-6','Tiene 16 Municipios','lfiallos','2016-11-02 00:00:00'),(14,14,'Juticalpa',504,16101,537306,'LPS','-6','Tiene 23 Municipios','lfiallos','2016-11-02 00:00:00'),(15,15,'SantaBarbara',504,22101,434896,'LPS','-6','Tiene 23 Municipios','lfiallos','2016-11-02 00:00:00'),(16,16,'Nacaome',504,52101,185227,'LPS','-6','Tiene 9 Municipios','lfiallos','2016-11-02 00:00:00'),(17,17,'Yoro',504,53101,613473,'LPS','-6','Tiene 11 Municipios','lfiallos','2016-11-02 00:00:00'),(18,18,'Roatan',504,34101,65932,'LPS','-6','Tiene 4 Municipios','lfiallos','2016-11-02 00:00:00'),(19,19,'Ahuachapan',503,2101,319503,'$','UTC-06:00','Ahuachapán','lfiallos','2016-11-03 00:00:00'),(20,20,'Sensuntepeque',503,1201,149326,'$','UTC-06:00','Sensuntepeque','lfiallos','2016-11-03 00:00:00'),(21,21,'Chalatenango',503,1301,192788,'$','UTC-06:00','Chalatenango','lfiallos','2016-11-03 00:00:00'),(22,22,'Cojutepeque',503,1401,231480,'$','UTC-06:00','Cojutepeque','lfiallos','2016-11-03 00:00:00'),(23,23,'Santa Tecla',503,1501,660652,'$','UTC-06:00','Santa Tecla','lfiallos','2016-11-03 00:00:00'),(24,24,'Zacatecoluca',503,1601,308087,'$','UTC-06:00','Zacatecoluca','lfiallos','2016-11-03 00:00:00'),(25,25,'La Union',503,3101,238217,'$','UTC-06:00','La Unión','lfiallos','2016-11-03 00:00:00'),(26,26,'San Francisco',503,NULL,174426,'$','UTC-06:00','San Francisco','lfiallos','2016-11-03 00:00:00'),(27,27,'San Miguel',503,3301,434003,'$','UTC-06:00','San Miguel','lfiallos','2016-11-03 00:00:00'),(28,28,'San Salvador',503,1101,1567156,'$','UTC-06:00','San Salvador','lfiallos','2016-11-03 00:00:00'),(29,29,'San Vicente',503,1701,161645,'$','UTC-06:00','San Vicente','lfiallos','2016-11-03 00:00:00'),(30,30,'Santa Ana',503,2201,523655,'$','UTC-06:00','Santa Ana','lfiallos','2016-11-03 00:00:00'),(31,31,'Sonsonate',503,2301,438960,'$','UTC-06:00','Sonsonate','lfiallos','2016-11-03 00:00:00'),(32,32,'Usulutan',302,3401,344235,'$','UTC-06:00','Usulután','lfiallos','2016-11-03 00:00:00');
/*!40000 ALTER TABLE `pa_cities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pa_countries`
--

DROP TABLE IF EXISTS `pa_countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pa_countries` (
  `COD_COUNTRY` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'COD OF THE COUNTRY',
  `NAM_COUNTRY` varchar(50) DEFAULT NULL COMMENT 'NAME OF THE COUNTRY',
  `DES_COUNTRY` varchar(2000) DEFAULT NULL COMMENT 'DESCRIPTION OF THE COUNTRY',
  `USR_ADD` varchar(255) NOT NULL COMMENT 'USER THAT ADDED THIS ROW',
  `DAT_ADD` datetime NOT NULL COMMENT 'DATE THAT THIS ROW WERE ADDED',
  PRIMARY KEY (`COD_COUNTRY`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pa_countries`
--

LOCK TABLES `pa_countries` WRITE;
/*!40000 ALTER TABLE `pa_countries` DISABLE KEYS */;
INSERT INTO `pa_countries` VALUES (1,'Honduras','Pais Centroamericano','lfiallos','2016-11-02 00:00:00'),(2,'El Salvador','Pais Centroamericano','lfiallos','2016-11-02 00:00:00');
/*!40000 ALTER TABLE `pa_countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pa_customers`
--

DROP TABLE IF EXISTS `pa_customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pa_customers` (
  `COD_CUSTOMER` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'THE PRYMARY KEY OF THE CUSTOMERS',
  `COD_PEOPLE` bigint(20) DEFAULT NULL COMMENT 'COD OF PEOPLE',
  `COD_TYPCUST` bigint(20) DEFAULT NULL COMMENT 'COD OF TYPE OF CUSTOMER',
  `DES_COMPANY` varchar(255) DEFAULT NULL COMMENT 'IF THE CUSTOMER WORKS ON A COMPANY',
  `IND_CUSTOMER` enum('1','0') NOT NULL COMMENT 'IND OF THE CUSTOMER',
  `USR_ADD` varchar(255) NOT NULL COMMENT 'USER THAT ADDED THIS ROW',
  `DAT_ADD` datetime NOT NULL COMMENT 'DATE THAT THIS ROW WERE ADDED',
  PRIMARY KEY (`COD_CUSTOMER`),
  KEY `FK_CUST_PEOP` (`COD_PEOPLE`),
  KEY `FK_TYCU_CUST` (`COD_TYPCUST`),
  CONSTRAINT `FK_CUST_PEOP` FOREIGN KEY (`COD_PEOPLE`) REFERENCES `pa_people` (`COD_PEOPLE`) ON DELETE CASCADE,
  CONSTRAINT `FK_TYCU_CUST` FOREIGN KEY (`COD_TYPCUST`) REFERENCES `pa_typcustomers` (`COD_TYPCUST`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pa_customers`
--

LOCK TABLES `pa_customers` WRITE;
/*!40000 ALTER TABLE `pa_customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `pa_customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pa_emails`
--

DROP TABLE IF EXISTS `pa_emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pa_emails` (
  `COD_EMAIL` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'RELATIONSHIP BETWEEN PEOPLE AND PHONES',
  `USEREMAIL` varchar(200) DEFAULT NULL COMMENT 'USER OF THE EMAIL ADDRESS',
  `SERVEREMAIL` varchar(200) DEFAULT NULL COMMENT 'SERVER OF THE EMAIL ADDRESS',
  `TYP_EMAIL` enum('P','O') DEFAULT NULL COMMENT 'P:personal O:office',
  `USR_ADD` varchar(255) NOT NULL COMMENT 'USER THAT ADDED THIS ROW',
  `DAT_ADD` datetime NOT NULL COMMENT 'DATE THAT THIS ROW WERE ADDED',
  PRIMARY KEY (`COD_EMAIL`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pa_emails`
--

LOCK TABLES `pa_emails` WRITE;
/*!40000 ALTER TABLE `pa_emails` DISABLE KEYS */;
/*!40000 ALTER TABLE `pa_emails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pa_people`
--

DROP TABLE IF EXISTS `pa_people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pa_people` (
  `COD_PEOPLE` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'COD OF THE PERSON',
  `DNI` varchar(255) NOT NULL COMMENT 'THE ID OF THE PERSON, IT COULD THE PASSPORT OR NATIONAL ID',
  `FIRSTNAME` varchar(255) NOT NULL COMMENT 'THE FIRTS NAME OF A PERSON',
  `MIDDLENAME` varchar(255) NOT NULL COMMENT 'THE MIDDLE NAME OF A PERSON',
  `LASTNAME` varchar(255) NOT NULL COMMENT 'THE LAST NAME OF A PERSON',
  `SEX` enum('M','W','F','D') NOT NULL COMMENT 'THE SEX OF A PERSON',
  `IND_CIVIL` enum('S','M','W') NOT NULL COMMENT 'THE CIVILIAN STATUS OF A PERSON',
  `AGE` tinyint(4) NOT NULL COMMENT 'THE AGE OF A PERSON',
  `TIP_PERSON` enum('N','J') NOT NULL COMMENT 'Natural or Juridical',
  `USR_ADD` varchar(255) NOT NULL COMMENT 'USER THAT ADDED THIS ROW',
  `DAT_ADD` datetime NOT NULL COMMENT 'DATE THAT THIS ROW WERE ADDED',
  `ind_people` enum('activo','inactivo') NOT NULL DEFAULT 'activo' COMMENT 'estado logico de la persona',
  PRIMARY KEY (`COD_PEOPLE`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pa_people`
--

LOCK TABLES `pa_people` WRITE;
/*!40000 ALTER TABLE `pa_people` DISABLE KEYS */;
INSERT INTO `pa_people` VALUES (1,'0801199900001','Lester','Josue','Fiallos','M','S',35,'N','lfiallos','2016-11-04 00:00:00','activo'),(2,'0801199900002','Gabriel','Antonio','Rivera','M','S',28,'N','lfiallos','2016-11-07 00:00:00','activo'),(3,'0801199900003','Natalia','Maria','Zuniga','F','S',26,'N','lfiallos','2016-11-08 00:00:00','activo'),(4,'0801199900201','Carlos','Andres','Mejia','M','S',25,'N','admin_api','2026-06-21 19:57:02','inactivo'),(5,'0801199900301','LUIS','FERNANDO','MARTINEZ','M','S',24,'N','admin_api','2026-06-24 17:21:49','activo'),(6,'0801199900302','MARIA','FERNANDA','RODRIGUEZ','F','M',27,'N','admin_api','2026-06-24 17:23:43','inactivo'),(7,'0801199900303','CARLOS','CARLOS','RODRIGUEZ','M','S',34,'N','admin_api','2026-06-24 20:27:20','inactivo'),(8,'0000111112123','Malena','Annie','Banegas','F','S',20,'N','laravel_frontend','2026-07-14 10:39:51','activo'),(9,'0801200517790','Jorge','Antonio','Mendoza','M','S',20,'N','laravel_frontend','2026-07-14 11:08:19','inactivo');
/*!40000 ALTER TABLE `pa_people` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pa_phones`
--

DROP TABLE IF EXISTS `pa_phones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pa_phones` (
  `COD_PHONE` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'COD OF A PHONE NUMBER',
  `NUM_AREA` tinyint(4) NOT NULL COMMENT 'NUMBER OF AREA TO CALL',
  `NUM_PHONE` int(10) NOT NULL COMMENT 'PHONE NUMBER OF THE PERSON',
  `TYP_PHONE` enum('H','O','C') NOT NULL COMMENT 'THE TYPE OF PHONE NUMBER H:HOME O:OFFICE C:CELLPHONE',
  `USR_ADD` varchar(255) NOT NULL COMMENT 'USER THAT ADDED THIS ROW',
  `DAT_ADD` datetime NOT NULL COMMENT 'DATE THAT THIS ROW WERE ADDED',
  PRIMARY KEY (`COD_PHONE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pa_phones`
--

LOCK TABLES `pa_phones` WRITE;
/*!40000 ALTER TABLE `pa_phones` DISABLE KEYS */;
/*!40000 ALTER TABLE `pa_phones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pa_states`
--

DROP TABLE IF EXISTS `pa_states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pa_states` (
  `COD_STATE` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'COD OF THE CITY',
  `COD_COUNTRY` bigint(20) DEFAULT NULL COMMENT 'COD OF THE COUNTRY',
  `NAME_STATE` varchar(300) DEFAULT NULL COMMENT 'NAME OF THE STATE',
  `DES_STATE` varchar(2000) DEFAULT NULL COMMENT 'DESCRIPTION OF THE STATE',
  `USR_ADD` varchar(255) NOT NULL COMMENT 'USER THAT ADDED THIS ROW',
  `DAT_ADD` datetime NOT NULL COMMENT 'DATE THAT THIS ROW WERE ADDED',
  PRIMARY KEY (`COD_STATE`),
  KEY `FK_COUNTRY_STATE` (`COD_COUNTRY`),
  CONSTRAINT `FK_COUNTRY_STATE` FOREIGN KEY (`COD_COUNTRY`) REFERENCES `pa_countries` (`COD_COUNTRY`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pa_states`
--

LOCK TABLES `pa_states` WRITE;
/*!40000 ALTER TABLE `pa_states` DISABLE KEYS */;
INSERT INTO `pa_states` VALUES (1,1,'Atlantida','Atlantida','lfiallos','2016-11-02 00:00:00'),(2,1,'Colon','Colon','lfiallos','2016-11-02 00:00:00'),(3,1,'Comayagua','Comayagua','lfiallos','2016-11-02 00:00:00'),(4,1,'Copan','Copan','lfiallos','2016-11-02 00:00:00'),(5,1,'Cortes','Cortes','lfiallos','2016-11-02 00:00:00'),(6,1,'Choluteca','Choluteca','lfiallos','2016-11-02 00:00:00'),(7,1,'El Paraiso','El Paraíso','lfiallos','2016-11-02 00:00:00'),(8,1,'Francisco Morazan','Francisco Morazán','lfiallos','2016-11-02 00:00:00'),(9,1,'Gracias a Dios','Gracias a Dios','lfiallos','2016-11-02 00:00:00'),(10,1,'Intibuca','Intibucá','lfiallos','2016-11-02 00:00:00'),(11,1,'La Paz','La Paz','lfiallos','2016-11-02 00:00:00'),(12,1,'Lempira','Lempira','lfiallos','2016-11-02 00:00:00'),(13,1,'Ocotepeque','Ocotepeque','lfiallos','2016-11-02 00:00:00'),(14,1,'Olancho','Olancho','lfiallos','2016-11-02 00:00:00'),(15,1,'Santa Barbara','Santa Barbara','lfiallos','2016-11-02 00:00:00'),(16,1,'Valle','Valle','lfiallos','2016-11-02 00:00:00'),(17,1,'Yoro','Yoro','lfiallos','2016-11-02 00:00:00'),(18,1,'Isla de la Bahia','Isla de la Bahía','lfiallos','2016-11-02 00:00:00'),(19,2,'Ahuachapan','Ahuachapán','lfiallos','2016-11-03 00:00:00'),(20,2,'Cabanas','Cabañas','lfiallos','2016-11-03 00:00:00'),(21,2,'Chalatenango','Chalatenango','lfiallos','2016-11-03 00:00:00'),(22,2,'Cuscatlan','Cuscatlán','lfiallos','2016-11-03 00:00:00'),(23,2,'La Libertad','La Libertad','lfiallos','2016-11-03 00:00:00'),(24,2,'La Paz','La Paz','lfiallos','2016-11-03 00:00:00'),(25,2,'La Union','La Unión','lfiallos','2016-11-03 00:00:00'),(26,2,'Morazan','Morazán','lfiallos','2016-11-03 00:00:00'),(27,2,'San Miguel','San Miguel','lfiallos','2016-11-03 00:00:00'),(28,2,'San Salvador','San Salvador','lfiallos','2016-11-03 00:00:00'),(29,2,'San Vicente','San Vicente','lfiallos','2016-11-03 00:00:00'),(30,2,'Santa Ana','Santa Ana','lfiallos','2016-11-03 00:00:00'),(31,2,'Sonsonate','Sonsonate','lfiallos','2016-11-03 00:00:00'),(32,2,'Usulutan','Usulután','lfiallos','2016-11-03 00:00:00');
/*!40000 ALTER TABLE `pa_states` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pa_tipusers`
--

DROP TABLE IF EXISTS `pa_tipusers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pa_tipusers` (
  `COD_TIPUSERS` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'THE PRIMARY KEY OF TYPE OF USER',
  `COD_USER` bigint(20) DEFAULT NULL COMMENT 'COD OF USER',
  `NOM_TYPE` varchar(255) NOT NULL COMMENT 'THE NAME OF THE TYPE OF USER',
  `DES_TYPE` varchar(2000) NOT NULL COMMENT 'DESCRIPTION OF THE TYPE OF USER',
  PRIMARY KEY (`COD_TIPUSERS`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pa_tipusers`
--

LOCK TABLES `pa_tipusers` WRITE;
/*!40000 ALTER TABLE `pa_tipusers` DISABLE KEYS */;
INSERT INTO `pa_tipusers` VALUES (1,NULL,'administrator','usuario administrador del sistema'),(2,NULL,'outsourcing','usuario externo o de apoyo');
/*!40000 ALTER TABLE `pa_tipusers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pa_typcustomers`
--

DROP TABLE IF EXISTS `pa_typcustomers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pa_typcustomers` (
  `COD_TYPCUST` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'THE PRIMARY KEY OF THE TYPE OF CUSTOMERS',
  `NOM_TYPCUST` varchar(255) DEFAULT NULL COMMENT 'NOMBRE OF THE TYPE OF CUSTOMER',
  `DES_TYPCUST` varchar(255) DEFAULT NULL COMMENT 'DESCRIPTION OF THE TYPE OF CUSTOMER',
  `IND_TYPCUST` enum('1','0') NOT NULL COMMENT 'IND OF THE TYPE OF CUSTOMER',
  `USR_ADD` varchar(255) NOT NULL COMMENT 'USER THAT ADDED THIS ROW',
  `DAT_ADD` datetime NOT NULL COMMENT 'DATE THAT THIS ROW WERE ADDED',
  PRIMARY KEY (`COD_TYPCUST`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pa_typcustomers`
--

LOCK TABLES `pa_typcustomers` WRITE;
/*!40000 ALTER TABLE `pa_typcustomers` DISABLE KEYS */;
/*!40000 ALTER TABLE `pa_typcustomers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rel_people_address`
--

DROP TABLE IF EXISTS `rel_people_address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rel_people_address` (
  `COD_PEOADD` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'THE PRIMARY KEY OF THE PEOPLE ADDRESS',
  `COD_PEOPLE` bigint(20) DEFAULT NULL COMMENT 'COD OF THE PERSON',
  `COD_ADDRESS` bigint(20) DEFAULT NULL COMMENT 'COD OF THE ADRRESS',
  `USR_ADD` varchar(255) NOT NULL COMMENT 'USER THAT ADDED THIS ROW',
  `DAT_ADD` datetime NOT NULL COMMENT 'DATE THAT THIS ROW WERE ADDED',
  PRIMARY KEY (`COD_PEOADD`),
  KEY `FK_PEOADD_PEOPLE` (`COD_PEOPLE`),
  KEY `FK_PEOADD_ADDRESS` (`COD_ADDRESS`),
  CONSTRAINT `FK_PEOADD_ADDRESS` FOREIGN KEY (`COD_ADDRESS`) REFERENCES `pa_address` (`COD_ADDRESS`) ON DELETE CASCADE,
  CONSTRAINT `FK_PEOADD_PEOPLE` FOREIGN KEY (`COD_PEOPLE`) REFERENCES `pa_people` (`COD_PEOPLE`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rel_people_address`
--

LOCK TABLES `rel_people_address` WRITE;
/*!40000 ALTER TABLE `rel_people_address` DISABLE KEYS */;
/*!40000 ALTER TABLE `rel_people_address` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rel_people_emails`
--

DROP TABLE IF EXISTS `rel_people_emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rel_people_emails` (
  `COD_REL_PEOEMA` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'RELATIONSHIP BETWEEN PEOPLE AND EMAILS',
  `COD_PEOPLE` bigint(20) DEFAULT NULL COMMENT 'COD OF THE PERSON',
  `COD_EMAIL` bigint(20) DEFAULT NULL COMMENT 'RELATIONSHIP BETWEEN PEOPLE AND PHONES',
  `USR_ADD` varchar(255) NOT NULL COMMENT 'USER THAT ADDED THIS ROW',
  `DAT_ADD` datetime NOT NULL COMMENT 'DATE THAT THIS ROW WERE ADDED',
  PRIMARY KEY (`COD_REL_PEOEMA`),
  KEY `FK_REL_PEOPLE_EMAILS` (`COD_PEOPLE`),
  KEY `FK_REL_EMAILS_PEOPLE` (`COD_EMAIL`),
  CONSTRAINT `FK_REL_EMAILS_PEOPLE` FOREIGN KEY (`COD_EMAIL`) REFERENCES `pa_emails` (`COD_EMAIL`) ON DELETE CASCADE,
  CONSTRAINT `FK_REL_PEOPLE_EMAILS` FOREIGN KEY (`COD_PEOPLE`) REFERENCES `pa_people` (`COD_PEOPLE`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rel_people_emails`
--

LOCK TABLES `rel_people_emails` WRITE;
/*!40000 ALTER TABLE `rel_people_emails` DISABLE KEYS */;
/*!40000 ALTER TABLE `rel_people_emails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rel_people_phones`
--

DROP TABLE IF EXISTS `rel_people_phones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rel_people_phones` (
  `COD_REL_PEOPHO` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'RELATIONSHIP BETWEEN PEOPLE AND PHONES',
  `COD_PEOPLE` bigint(20) DEFAULT NULL COMMENT 'COD OF THE PERSON',
  `COD_PHONE` bigint(20) DEFAULT NULL COMMENT 'COD OF A PHONE NUMBER',
  `USR_ADD` varchar(255) NOT NULL COMMENT 'USER THAT ADDED THIS ROW',
  `DAT_ADD` datetime NOT NULL COMMENT 'DATE THAT THIS ROW WERE ADDED',
  PRIMARY KEY (`COD_REL_PEOPHO`),
  KEY `FK_REL_PEOPLE_PHONE` (`COD_PEOPLE`),
  KEY `FK_REL_PHONE_PEOPLE` (`COD_PHONE`),
  CONSTRAINT `FK_REL_PEOPLE_PHONE` FOREIGN KEY (`COD_PEOPLE`) REFERENCES `pa_people` (`COD_PEOPLE`) ON DELETE CASCADE,
  CONSTRAINT `FK_REL_PHONE_PEOPLE` FOREIGN KEY (`COD_PHONE`) REFERENCES `pa_phones` (`COD_PHONE`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rel_people_phones`
--

LOCK TABLES `rel_people_phones` WRITE;
/*!40000 ALTER TABLE `rel_people_phones` DISABLE KEYS */;
/*!40000 ALTER TABLE `rel_people_phones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rf_detalle_reporte_financiero`
--

DROP TABLE IF EXISTS `rf_detalle_reporte_financiero`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_detalle_reporte_financiero` (
  `cod_detalle_reporte` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'codigo unico del detalle del reporte',
  `cod_reporte` bigint(20) NOT NULL COMMENT 'codigo del reporte financiero',
  `cod_cuenta` bigint(20) DEFAULT NULL COMMENT 'codigo de la cuenta contable',
  `tip_grupo` enum('activo','pasivo','patrimonio','ingreso','costo','gasto','resultado') NOT NULL COMMENT 'grupo contable del reporte',
  `nom_linea` varchar(150) NOT NULL COMMENT 'nombre de la linea del reporte',
  `mon_linea` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'monto de la linea del reporte',
  `num_orden` int(11) NOT NULL COMMENT 'orden de presentacion',
  `num_nivel_jerarquia` tinyint(4) NOT NULL DEFAULT 1 COMMENT 'nivel visual de la linea',
  PRIMARY KEY (`cod_detalle_reporte`),
  KEY `idx_rf_detalle_reporte_financiero_reporte` (`cod_reporte`),
  KEY `idx_rf_detalle_reporte_financiero_cuenta` (`cod_cuenta`),
  CONSTRAINT `fk_rf_detalle_reporte_financiero_cuenta` FOREIGN KEY (`cod_cuenta`) REFERENCES `cc_catalogo_cuenta` (`cod_cuenta`),
  CONSTRAINT `fk_rf_detalle_reporte_financiero_reporte` FOREIGN KEY (`cod_reporte`) REFERENCES `rf_reporte_financiero` (`cod_reporte`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='detalle de reportes financieros';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rf_detalle_reporte_financiero`
--

LOCK TABLES `rf_detalle_reporte_financiero` WRITE;
/*!40000 ALTER TABLE `rf_detalle_reporte_financiero` DISABLE KEYS */;
INSERT INTO `rf_detalle_reporte_financiero` VALUES (1,1,6,'activo','caja general',12000.00,1,2),(2,1,7,'activo','bancos',1800.00,2,2),(3,1,8,'pasivo','proveedores nacionales',800.00,3,2),(4,1,9,'patrimonio','capital social',10000.00,4,2),(5,1,NULL,'resultado','utilidad del periodo',3000.00,5,2),(6,2,10,'ingreso','ingresos por servicios',5000.00,1,2),(7,2,5,'gasto','gastos administrativos',2000.00,2,2),(8,2,NULL,'resultado','utilidad neta del periodo',3000.00,3,1),(9,3,6,'activo','caja general',12000.00,6,2),(10,3,7,'activo','bancos',1800.00,7,2),(11,3,12,'activo','cuenta prueba procedimiento inactiva',1700.00,12,1),(12,3,8,'pasivo','proveedores nacionales',800.00,8,2),(13,3,9,'patrimonio','capital social',10000.00,9,2),(16,3,NULL,'resultado','utilidad del periodo',0.00,999,1),(17,4,6,'activo','caja general',12000.00,6,2),(18,4,7,'activo','bancos',1800.00,7,2),(19,4,12,'activo','cuenta prueba procedimiento inactiva',1700.00,12,1),(20,4,15,'activo','cuenta prueba procedimiento inactiva',1700.00,15,1),(21,4,8,'pasivo','proveedores nacionales',800.00,8,2),(22,4,9,'patrimonio','capital social',10000.00,9,2),(24,4,NULL,'resultado','utilidad del periodo',0.00,999,1),(25,5,6,'activo','caja general',12000.00,6,2),(26,5,7,'activo','bancos',1800.00,7,2),(27,5,12,'activo','cuenta prueba procedimiento inactiva',1700.00,12,1),(28,5,15,'activo','cuenta prueba procedimiento inactiva',1700.00,15,1),(29,5,16,'activo','cuenta prueba procedimiento inactiva',1700.00,16,1),(30,5,8,'pasivo','proveedores nacionales',800.00,8,2),(31,5,9,'patrimonio','capital social',10000.00,9,2),(32,5,NULL,'resultado','utilidad del periodo',0.00,999,1),(33,6,6,'activo','caja general',12000.00,6,2),(34,6,7,'activo','bancos',1800.00,7,2),(35,6,12,'activo','cuenta prueba procedimiento inactiva',1700.00,12,1),(36,6,15,'activo','cuenta prueba procedimiento inactiva',1700.00,15,1),(37,6,16,'activo','cuenta prueba procedimiento inactiva',1700.00,16,1),(38,6,21,'activo','cuenta prueba procedimiento inactiva',1700.00,21,1),(39,6,8,'pasivo','proveedores nacionales',800.00,8,2),(40,6,9,'patrimonio','capital social',10000.00,9,2),(48,6,NULL,'resultado','utilidad del periodo',0.00,999,1),(49,7,6,'activo','caja general',12000.00,6,2),(50,7,7,'activo','bancos',1800.00,7,2),(51,7,12,'activo','cuenta prueba procedimiento inactiva',1700.00,12,1),(52,7,15,'activo','cuenta prueba procedimiento inactiva',1700.00,15,1),(53,7,16,'activo','cuenta prueba procedimiento inactiva',1700.00,16,1),(54,7,21,'activo','cuenta prueba procedimiento inactiva',1700.00,21,1),(55,7,8,'pasivo','proveedores nacionales',800.00,8,2),(56,7,9,'patrimonio','capital social',10000.00,9,2),(64,7,NULL,'resultado','utilidad del periodo',3000.00,999,1),(65,8,5,'gasto','gastos administrativos',2000.00,5,1),(66,8,10,'ingreso','ingresos por servicios',5000.00,10,2),(68,8,NULL,'resultado','utilidad neta del periodo',3000.00,999,1);
/*!40000 ALTER TABLE `rf_detalle_reporte_financiero` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rf_reporte_financiero`
--

DROP TABLE IF EXISTS `rf_reporte_financiero`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_reporte_financiero` (
  `cod_reporte` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'codigo unico del reporte financiero',
  `cod_periodo` bigint(20) NOT NULL COMMENT 'codigo del periodo contable',
  `cod_user` bigint(20) NOT NULL COMMENT 'codigo del usuario que genero el reporte',
  `tip_reporte` enum('balance_general','estado_resultados') NOT NULL COMMENT 'tipo de reporte financiero',
  `fec_generacion` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'fecha de generacion del reporte',
  `tot_activo` decimal(14,2) DEFAULT NULL COMMENT 'total de activos',
  `tot_pasivo` decimal(14,2) DEFAULT NULL COMMENT 'total de pasivos',
  `tot_patrimonio` decimal(14,2) DEFAULT NULL COMMENT 'total de patrimonio',
  `mon_utilidad_perdida` decimal(14,2) DEFAULT NULL COMMENT 'utilidad o perdida',
  `ind_estado` enum('generado','confirmado','anulado') NOT NULL DEFAULT 'generado' COMMENT 'estado del reporte',
  PRIMARY KEY (`cod_reporte`),
  KEY `idx_rf_reporte_financiero_periodo` (`cod_periodo`),
  KEY `idx_rf_reporte_financiero_user` (`cod_user`),
  CONSTRAINT `fk_rf_reporte_financiero_periodo` FOREIGN KEY (`cod_periodo`) REFERENCES `ga_periodo_contable` (`cod_periodo`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='cabecera de reportes financieros';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rf_reporte_financiero`
--

LOCK TABLES `rf_reporte_financiero` WRITE;
/*!40000 ALTER TABLE `rf_reporte_financiero` DISABLE KEYS */;
INSERT INTO `rf_reporte_financiero` VALUES (1,1,1,'balance_general','2026-06-05 20:41:15',10000.00,4000.00,6000.00,1500.00,'generado'),(2,1,1,'estado_resultados','2026-06-05 20:41:15',NULL,NULL,NULL,3000.00,'generado'),(3,1,1,'balance_general','2026-06-11 20:48:04',1700.00,0.00,1700.00,0.00,'anulado'),(4,1,1,'balance_general','2026-06-11 20:59:41',1700.00,0.00,1700.00,0.00,'anulado'),(5,1,1,'balance_general','2026-06-11 20:59:54',1700.00,0.00,1700.00,0.00,'anulado'),(6,1,1,'balance_general','2026-06-11 21:10:12',1700.00,0.00,1700.00,0.00,'anulado'),(7,1,1,'balance_general','2026-06-29 17:50:23',20600.00,800.00,13000.00,3000.00,'generado'),(8,1,1,'estado_resultados','2026-06-29 17:50:35',20600.00,800.00,10000.00,3000.00,'generado');
/*!40000 ALTER TABLE `rf_reporte_financiero` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `COD_USER` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'THE PRIMARY KEY OF THE USER',
  `COD_PEOPLE` bigint(20) DEFAULT NULL COMMENT 'COD OF THE PERSON',
  `COD_TIPUSERS` bigint(20) DEFAULT NULL COMMENT 'COD OF TYPE OF USERS',
  `NAME` varchar(255) NOT NULL COMMENT 'THE NAME OF THE USER',
  `CLAVE` varchar(2000) NOT NULL COMMENT 'THE PASSWORD OF THE USER',
  `NUMERICO` varchar(6) NOT NULL COMMENT 'TOKEN DE ACCESO',
  `IND_USR` enum('1','0') NOT NULL COMMENT 'IND OF THE USER TO ACCESS THE SYSTEM, 0=INACTIVE 1=ACTIVE',
  `IND_INS` enum('1','0') NOT NULL COMMENT 'IND OF THE FIRST TIME IN THE SYSTEM.',
  `USR_ADD` varchar(255) NOT NULL COMMENT 'USER THAT ADDED THIS ROW',
  `DAT_ADD` datetime NOT NULL COMMENT 'DATE THAT THIS ROW WERE ADDED',
  PRIMARY KEY (`COD_USER`),
  KEY `FK_PEOADD_PEOUSR` (`COD_PEOPLE`),
  KEY `FK_TIPUSE_USERS` (`COD_TIPUSERS`),
  CONSTRAINT `FK_PEOADD_PEOUSR` FOREIGN KEY (`COD_PEOPLE`) REFERENCES `pa_people` (`COD_PEOPLE`) ON DELETE CASCADE,
  CONSTRAINT `FK_TIPUSE_USERS` FOREIGN KEY (`COD_TIPUSERS`) REFERENCES `pa_tipusers` (`COD_TIPUSERS`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,1,'lfiallos','1e28fe23fb146a883e01981724113f33','111111','1','0','lfiallos','2016-11-04 00:00:00'),(2,2,2,'grivera','1e28fe23fb146a883e01981724113f33','111111','1','0','lfiallos','2016-11-07 00:00:00'),(3,3,2,'nzuniga','1e28fe23fb146a883e01981724113f33','111111','1','0','lfiallos','2016-11-08 00:00:00'),(4,5,1,'Luis Fernando','$2b$12$6h.ikHV2qM/tgdbPFuxP6OrmbAxibqklpsf93eSrt1j5ppukgJ32i','759293','1','0','bootstrap_local','2026-07-14 15:41:41');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-20 15:43:05
