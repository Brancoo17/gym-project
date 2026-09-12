-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: gym_mvc
-- ------------------------------------------------------
-- Server version	8.0.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `asistencias`
--

DROP TABLE IF EXISTS `asistencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asistencias` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `reserva_id` int unsigned NOT NULL,
  `presente` tinyint(1) NOT NULL DEFAULT '0',
  `marcado_por` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reserva_id` (`reserva_id`),
  KEY `marcado_por` (`marcado_por`),
  CONSTRAINT `asistencias_ibfk_1` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asistencias_ibfk_2` FOREIGN KEY (`marcado_por`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencias`
--

/*!40000 ALTER TABLE `asistencias` DISABLE KEYS */;
INSERT INTO `asistencias` VALUES (2,9,1,1),(3,10,1,4),(4,8,1,1),(5,11,1,1),(6,7,1,1),(7,12,1,1),(8,13,1,1),(9,14,1,1),(10,16,1,1);
/*!40000 ALTER TABLE `asistencias` ENABLE KEYS */;

--
-- Table structure for table `avisos_enviados`
--

DROP TABLE IF EXISTS `avisos_enviados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `avisos_enviados` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `membresia_id` int unsigned NOT NULL,
  `tipo` enum('7dias','hoy') COLLATE utf8mb4_unicode_ci NOT NULL,
  `enviado_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `membresia_id` (`membresia_id`),
  CONSTRAINT `avisos_enviados_ibfk_1` FOREIGN KEY (`membresia_id`) REFERENCES `membresias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avisos_enviados`
--

/*!40000 ALTER TABLE `avisos_enviados` DISABLE KEYS */;
/*!40000 ALTER TABLE `avisos_enviados` ENABLE KEYS */;

--
-- Table structure for table `configuracion`
--

DROP TABLE IF EXISTS `configuracion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracion` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'GYM',
  `logo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `portada` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin@gym.com',
  `whatsapp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiktok` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Av. del Entrenamiento 123, Buenos Aires',
  `mapa_url` text COLLATE utf8mb4_unicode_ci,
  `hero_titulo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT 'Entrená con un plan a tu medida',
  `hero_descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'Crossfit, musculación y funcional. Elegí tu membresía y reservá tu turno.',
  `habilitar_turnos` tinyint(1) NOT NULL DEFAULT '1',
  `habilitar_crossfit` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracion`
--

/*!40000 ALTER TABLE `configuracion` DISABLE KEYS */;
INSERT INTO `configuracion` VALUES (1,'GYM Fitness','logo_aeadfbcd64fda667eb8bfa7ff53a4c80.webp','portada_c4b035b39d7eb896ec4b6695a2d12f3f.webp','gym@correo.com','5493875978810','@clanfitness3','','','Juan Esteban Tamayo 675 - Salta Capital','<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3622.9139304624105!2d-65.4122373257337!3d-24.764140006877412!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x941bc3dc7a963aa9%3A0x662b8222d5294e3!2sJuan%20Esteban%20Tamayo%20675%2C%20A4400%20Salta!5e0!3m2!1ses-419!2sar!4v1788840024790!5m2!1ses-419!2sar\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"strict-origin-when-cross-origin\"></iframe>','Donde tu cuerpo se cansa y tu mente descansa','Crossfit, musculación, funcional y programación personal. Elegí tu membresía y reservá tu turno.',1,1);
/*!40000 ALTER TABLE `configuracion` ENABLE KEYS */;

--
-- Table structure for table `ejercicios`
--

DROP TABLE IF EXISTS `ejercicios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ejercicios` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grupo_muscular` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `imagen` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ejercicios`
--

/*!40000 ALTER TABLE `ejercicios` DISABLE KEYS */;
INSERT INTO `ejercicios` VALUES (1,'Press de Banca Plano','Pecho','Tumbado sobre el banco plano con los pies apoyados en el suelo, sujetar la barra con agarre prono ligeramente superior al ancho de los hombros. Bajar de forma controlada hasta rozar la parte media del pecho y empujar con fuerza extendiendo los brazos sin bloquear los codos.','','https://www.youtube.com/watch?v=rT7DgCr-3pg'),(2,'Sentadilla Libre con Barra','Piernas','Barra apoyada sobre los trapecios, pies al ancho de hombros con puntas ligeramente hacia afuera. Descender flexionando rodillas y caderas manteniendo la espalda recta hasta romper el paralelo de 90 grados. Empujar desde los talones para volver a la posición inicial.','','https://www.youtube.com/watch?v=MVMNk0HiQCg'),(3,'Dominadas Pronas','Espalda','Colgado de la barra fija con agarre prono abierto, traccionar el cuerpo hacia arriba activando dorsales y juntando escápulas hasta que la barbilla supere la barra. Descender de forma controlada hasta la extensión completa de brazos.','','https://www.youtube.com/watch?v=eGo4IYlbE5g'),(4,'Press Militar con Barra','Hombros','De pie con los pies al ancho de caderas y core firme. Sujetar la barra a la altura de las clavículas y empujar verticalmente por encima de la cabeza hasta bloquear los brazos, alineando la barra con la columna.','','https://www.youtube.com/watch?v=2yjwXTZQDDI'),(5,'Plancha Abdominal Isométrica','Core / Abdomen','Apoyando los antebrazos y las puntas de los pies en el suelo, mantener el cuerpo completamente alineado en línea recta desde la cabeza hasta los talones, contrayendo fuertemente el abdomen y glúteos sin arquear la zona lumbar.','','https://www.youtube.com/watch?v=ASdvN_XEl_c'),(6,'Bíceps concentrado con mancuernas','Brazos','Sentado con la espalda recta y el cuerpo ligeramente tirado hacia delante, apoyamos el brazo sobre la cara interna de la  pierna del mismo lado. Realizamos una flexión del codo evitando cualquier movimiento o balanceo del tronco. Empieza realizando 2 series de 10 repeticiones por cada brazo.','4107cb3d75fc8346506ec824b80ee382.webp','https://www.youtube.com/watch?v=FrOJpldJWC4');
/*!40000 ALTER TABLE `ejercicios` ENABLE KEYS */;

--
-- Table structure for table `entrenador_clientes`
--

DROP TABLE IF EXISTS `entrenador_clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entrenador_clientes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `entrenador_id` int unsigned NOT NULL,
  `cliente_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entrenador_id` (`entrenador_id`,`cliente_id`),
  KEY `cliente_id` (`cliente_id`),
  CONSTRAINT `entrenador_clientes_ibfk_1` FOREIGN KEY (`entrenador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entrenador_clientes_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entrenador_clientes`
--

/*!40000 ALTER TABLE `entrenador_clientes` DISABLE KEYS */;
/*!40000 ALTER TABLE `entrenador_clientes` ENABLE KEYS */;

--
-- Table structure for table `horarios`
--

DROP TABLE IF EXISTS `horarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `horarios` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `plan_id` int unsigned NOT NULL,
  `entrenador_id` int unsigned NOT NULL,
  `dia_semana` tinyint NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `cupo` int DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `plan_id` (`plan_id`),
  KEY `entrenador_id` (`entrenador_id`),
  CONSTRAINT `horarios_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `planes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `horarios_ibfk_2` FOREIGN KEY (`entrenador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horarios`
--

/*!40000 ALTER TABLE `horarios` DISABLE KEYS */;
INSERT INTO `horarios` VALUES (2,1,1,1,'08:00:00','09:00:00',20,NULL),(3,1,1,1,'09:00:00','10:00:00',20,NULL),(4,1,1,1,'10:00:00','11:00:00',20,NULL),(6,1,1,2,'08:00:00','09:00:00',20,NULL),(7,1,1,2,'09:00:00','10:00:00',20,NULL),(8,1,1,2,'10:00:00','11:00:00',20,NULL),(9,1,1,2,'11:00:00','12:00:00',20,NULL),(10,1,1,3,'08:00:00','09:00:00',20,NULL),(11,1,1,3,'09:00:00','10:00:00',20,NULL),(12,1,1,3,'10:00:00','11:00:00',20,NULL),(13,1,1,3,'11:00:00','12:00:00',20,NULL),(14,1,1,4,'08:00:00','09:00:00',20,NULL),(15,1,1,4,'09:00:00','10:00:00',20,NULL),(16,1,1,4,'10:00:00','11:00:00',20,NULL),(17,1,1,4,'11:00:00','12:00:00',20,NULL),(18,1,1,5,'08:00:00','09:00:00',20,NULL),(19,1,1,5,'09:00:00','10:00:00',20,NULL),(20,1,1,5,'10:00:00','11:00:00',20,NULL),(21,1,1,5,'11:00:00','12:00:00',20,NULL),(28,1,1,1,'15:00:00','16:00:00',20,NULL),(30,1,1,3,'15:00:00','16:00:00',20,NULL),(32,1,1,5,'15:00:00','16:00:00',20,NULL),(33,1,1,2,'16:00:00','17:00:00',20,NULL),(34,1,1,2,'17:00:00','18:00:00',20,NULL),(35,1,1,4,'16:00:00','17:00:00',20,NULL),(36,1,1,4,'17:00:00','18:00:00',20,NULL),(38,1,1,2,'15:00:00','16:00:00',20,NULL),(40,1,1,4,'15:00:00','16:00:00',20,NULL),(48,1,1,1,'19:00:00','20:00:00',20,NULL),(49,1,1,1,'20:00:00','21:00:00',20,NULL),(50,1,1,1,'21:00:00','22:00:00',20,NULL),(51,1,1,2,'18:00:00','19:00:00',20,NULL),(52,4,1,2,'19:00:00','20:00:00',20,NULL),(53,1,1,2,'20:00:00','21:00:00',20,NULL),(54,1,1,2,'21:00:00','22:00:00',20,NULL),(56,1,1,3,'19:00:00','20:00:00',20,NULL),(57,1,1,3,'20:00:00','21:00:00',20,NULL),(58,1,1,3,'21:00:00','22:00:00',20,NULL),(59,1,1,4,'18:00:00','19:00:00',20,NULL),(60,4,1,4,'19:00:00','20:00:00',20,NULL),(61,1,1,4,'20:00:00','21:00:00',20,NULL),(62,1,1,4,'21:00:00','22:00:00',20,NULL),(64,1,1,5,'19:00:00','20:00:00',20,NULL),(65,1,1,5,'20:00:00','21:00:00',20,NULL),(66,1,1,5,'21:00:00','22:00:00',20,NULL),(67,1,4,1,'11:00:00','12:00:00',20,NULL),(68,4,4,1,'18:00:00','19:00:00',20,NULL),(69,4,4,3,'18:00:00','19:00:00',20,NULL),(70,4,4,5,'18:00:00','19:00:00',20,NULL),(71,1,1,1,'22:00:00','23:00:00',20,NULL),(72,1,1,3,'22:00:00','23:00:00',20,NULL),(73,1,1,5,'22:00:00','23:00:00',20,NULL),(77,1,4,1,'17:00:00','18:00:00',20,NULL),(78,1,4,3,'17:00:00','18:00:00',20,NULL),(79,1,4,5,'17:00:00','18:00:00',20,NULL),(80,1,4,1,'16:00:00','17:00:00',20,NULL),(81,1,4,3,'16:00:00','17:00:00',20,NULL),(82,1,4,5,'16:00:00','17:00:00',20,NULL),(90,1,4,2,'22:00:00','23:00:00',20,NULL),(91,1,4,4,'22:00:00','23:00:00',20,NULL),(93,1,4,6,'16:00:00','17:00:00',20,NULL),(94,1,4,6,'17:00:00','18:00:00',20,NULL),(95,1,4,6,'18:00:00','19:00:00',20,NULL),(96,1,4,6,'19:00:00','20:00:00',20,NULL),(97,2,4,6,'16:00:00','20:00:00',NULL,'Turno libre de corrido.'),(99,2,1,1,'08:00:00','23:00:00',NULL,'Turno libre de corrido.'),(100,2,1,2,'08:00:00','23:00:00',NULL,'Turno libre de corrido.'),(101,2,1,3,'08:00:00','23:00:00',NULL,'Turno libre de corrido.'),(102,2,1,4,'08:00:00','23:00:00',NULL,'Turno libre de corrido.'),(103,2,1,5,'08:00:00','23:00:00',NULL,'Turno libre de corrido.'),(104,1,4,1,'12:00:00','15:00:00',30,'Open Box'),(105,1,4,2,'12:00:00','15:00:00',30,'Open Box'),(106,1,4,3,'12:00:00','15:00:00',30,'Open Box'),(107,1,4,4,'12:00:00','15:00:00',30,'Open Box'),(108,1,4,5,'12:00:00','15:00:00',30,'Open Box');
/*!40000 ALTER TABLE `horarios` ENABLE KEYS */;

--
-- Table structure for table `membresias`
--

DROP TABLE IF EXISTS `membresias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `membresias` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int unsigned NOT NULL,
  `plan_id` int unsigned NOT NULL,
  `estado` enum('pendiente','activa','vencida','cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `membresias_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `membresias_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `planes` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `membresias`
--

/*!40000 ALTER TABLE `membresias` DISABLE KEYS */;
INSERT INTO `membresias` VALUES (15,2,1,'activa','2026-09-09','2026-10-09'),(16,2,2,'activa','2026-09-09','2026-10-09'),(17,8,5,'activa','2026-09-10','2026-10-10'),(18,8,3,'activa','2026-09-10','2026-10-10'),(19,8,4,'activa','2026-09-10','2026-10-10'),(21,9,1,'pendiente',NULL,NULL),(22,9,1,'activa','2026-09-10','2026-10-10'),(23,9,2,'activa','2026-09-10','2026-10-10');
/*!40000 ALTER TABLE `membresias` ENABLE KEYS */;

--
-- Table structure for table `pagos`
--

DROP TABLE IF EXISTS `pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `membresia_id` int unsigned NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `estado` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `metodo_pago` enum('mercadopago','efectivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mercadopago',
  `fecha_pago` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mp_preference_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mp_payment_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mp_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `membresia_id` (`membresia_id`),
  CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`membresia_id`) REFERENCES `membresias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos`
--

/*!40000 ALTER TABLE `pagos` DISABLE KEYS */;
INSERT INTO `pagos` VALUES (15,15,25000.00,'aprobado','mercadopago','2026-09-09 12:00:00','3676081453-0acd0f60-1509-48f7-b52c-577aaa8049f5','177250433437','approved'),(16,16,18000.00,'aprobado','mercadopago','2026-09-09 12:00:00','3676081453-3e0ac8a9-6bc1-4fa8-b18a-781be8b03dd9','178219400834','approved'),(17,17,15000.00,'aprobado','mercadopago','2026-09-10 12:00:00','3676081453-04ec54b0-5398-485b-9b8a-5ce0762f65ab','177262991951','approved'),(18,18,20000.00,'aprobado','mercadopago','2026-09-10 12:00:00','3676081453-1517ab29-2d3e-47ce-b9c8-cab0ce274613','177263485207','approved'),(19,19,20000.00,'aprobado','mercadopago','2026-09-10 12:00:00','3676081453-97edbe46-ebed-4b99-89f0-3fc2b4e33fcd','177263702739','approved'),(22,22,25000.00,'aprobado','mercadopago','2026-09-10 12:00:00','3676081453-366b67d5-05fa-40ae-b31d-595af31ad11b','177408079859','approved'),(23,23,18000.00,'aprobado','mercadopago','2026-09-10 12:00:00','3676081453-1fa6752b-d944-4b02-b3e8-78e54fdafa72','178379288062','approved');
/*!40000 ALTER TABLE `pagos` ENABLE KEYS */;

--
-- Table structure for table `planes`
--

DROP TABLE IF EXISTS `planes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `planes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_disciplina` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'musculacion',
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `precio` decimal(10,2) NOT NULL,
  `duracion_dias` int NOT NULL,
  `cantidad_clases` int DEFAULT NULL,
  `imagen` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `planes`
--

/*!40000 ALTER TABLE `planes` DISABLE KEYS */;
INSERT INTO `planes` VALUES (1,'Crossfit','crossfit','Entrenamiento funcional de alta intensidad con clases grupales.',25000.00,30,20,'6293328ecf0954ea42de14ef602d6314.webp',1),(2,'Musculación','musculacion','Acceso libre a sala de pesas y máquinas para hipertrofia y fuerza.',18000.00,30,25,'787ec9447a33a6556c7dc7ffad4ae7a7.webp',1),(3,'Funcional','musculacion','Circuito de movilidad, core y acondicionamiento físico general.',20000.00,30,16,'9a44da3bbbcd954f61d525fdb26d39e0.webp',1),(4,'Crossfit Kids','crossfit','Crossfit para niños desde 6 hasta 12 años.',20000.00,30,15,'452ed0f2179ed983cb4cfa01db168249.webp',1),(5,'Programación','musculacion','Programación semanal personalizada sólo para vos. Planificación estructurada de entrenamientos, ejercicios, cargas y descansos para lograr una meta específica.',15000.00,30,NULL,'a108cabe1a07757fbf72e1bbb78e8153.webp',1),(8,'Pileta Libre','otro','Plan de pileta libre.',25000.00,30,20,'d6fb8340fd937c9034022af7daf786ab.webp',1);
/*!40000 ALTER TABLE `planes` ENABLE KEYS */;

--
-- Table structure for table `reservas`
--

DROP TABLE IF EXISTS `reservas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservas` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int unsigned NOT NULL,
  `horario_id` int unsigned NOT NULL,
  `fecha` date NOT NULL,
  `estado` enum('reservada','cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reservada',
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`,`horario_id`,`fecha`),
  KEY `horario_id` (`horario_id`),
  CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`horario_id`) REFERENCES `horarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservas`
--

/*!40000 ALTER TABLE `reservas` DISABLE KEYS */;
INSERT INTO `reservas` VALUES (2,2,32,'2026-09-04','cancelada'),(4,2,18,'2026-09-04','reservada'),(7,2,28,'2026-09-07','reservada'),(8,2,3,'2026-09-07','reservada'),(9,2,2,'2026-09-07','reservada'),(10,2,104,'2026-09-07','reservada'),(11,8,2,'2026-09-07','reservada'),(12,8,68,'2026-09-07','reservada'),(13,2,100,'2026-09-08','reservada'),(14,2,105,'2026-09-08','reservada'),(15,2,6,'2026-09-08','cancelada'),(16,2,90,'2026-09-08','reservada'),(17,2,101,'2026-09-09','reservada'),(18,8,10,'2026-09-09','reservada'),(19,8,101,'2026-09-09','reservada'),(20,8,11,'2026-09-09','cancelada'),(21,2,10,'2026-09-09','reservada'),(22,2,11,'2026-09-09','reservada'),(23,2,70,'2026-09-11','cancelada'),(24,9,102,'2026-09-10','reservada');
/*!40000 ALTER TABLE `reservas` ENABLE KEYS */;

--
-- Table structure for table `rutina_ejercicios`
--

DROP TABLE IF EXISTS `rutina_ejercicios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rutina_ejercicios` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `rutina_id` int unsigned NOT NULL,
  `ejercicio_id` int unsigned NOT NULL,
  `bloque` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `rondas` int DEFAULT NULL,
  `series` int DEFAULT NULL,
  `reps` int DEFAULT NULL,
  `peso_hombres` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `peso_mujeres` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dia` tinyint DEFAULT NULL,
  `orden` int DEFAULT NULL,
  `notas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rutina_id` (`rutina_id`),
  KEY `ejercicio_id` (`ejercicio_id`),
  CONSTRAINT `rutina_ejercicios_ibfk_1` FOREIGN KEY (`rutina_id`) REFERENCES `rutinas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rutina_ejercicios_ibfk_2` FOREIGN KEY (`ejercicio_id`) REFERENCES `ejercicios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rutina_ejercicios`
--

/*!40000 ALTER TABLE `rutina_ejercicios` DISABLE KEYS */;
INSERT INTO `rutina_ejercicios` VALUES (44,1,5,'core',3,NULL,30,NULL,NULL,1,1,'30 segundos isométrico'),(45,1,3,'warmup',3,NULL,5,NULL,NULL,1,2,'Activación escapular'),(46,1,2,'fuerza',4,4,6,'80 kg','55 kg',1,3,NULL),(47,1,4,'wod',5,NULL,21,NULL,NULL,1,4,'Thrusters / Push Press'),(48,1,3,'wod',5,NULL,21,NULL,NULL,1,5,'Pull-ups unbroken'),(49,11,1,'dia_1',4,4,12,NULL,NULL,1,1,NULL),(50,11,2,'dia_1',4,4,15,NULL,NULL,1,2,NULL),(51,8,6,'dia_1_hombres',NULL,4,10,NULL,NULL,1,1,NULL),(52,8,1,'dia_1_hombres',NULL,4,10,NULL,NULL,1,2,NULL),(53,8,3,'dia_1_hombres',NULL,4,10,NULL,NULL,1,3,NULL),(54,8,4,'dia_1_hombres',NULL,4,10,NULL,NULL,1,4,NULL),(55,8,2,'dia_1_mujeres',NULL,4,10,NULL,NULL,1,5,NULL),(56,8,5,'dia_1_mujeres',NULL,4,10,NULL,NULL,1,6,NULL),(65,19,5,'core',3,NULL,15,NULL,NULL,1,1,NULL),(66,19,2,'warmup',3,NULL,15,NULL,NULL,1,2,NULL),(67,19,1,'fuerza',4,4,10,'60','40',1,3,NULL),(68,19,5,'wod',NULL,NULL,15,NULL,NULL,1,4,NULL),(69,19,2,'wod',NULL,NULL,15,NULL,NULL,1,5,NULL),(102,28,5,'dia_1',NULL,4,10,NULL,NULL,1,1,NULL),(103,28,2,'dia_1',NULL,4,10,NULL,NULL,1,2,'60 kg'),(104,28,1,'dia_1',NULL,4,10,NULL,NULL,1,3,'30 kg'),(105,28,4,'dia_1',NULL,4,10,NULL,NULL,1,4,'25 kg');
/*!40000 ALTER TABLE `rutina_ejercicios` ENABLE KEYS */;

--
-- Table structure for table `rutinas`
--

DROP TABLE IF EXISTS `rutinas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rutinas` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `plan_id` int unsigned NOT NULL,
  `cliente_id` int unsigned DEFAULT NULL,
  `entrenador_id` int unsigned NOT NULL,
  `fecha` date DEFAULT NULL,
  `tipo_formato` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'estandar',
  `wod_formato` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wod_tiempo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wod_descripcion` text COLLATE utf8mb4_unicode_ci,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `entrenador_id` (`entrenador_id`),
  KEY `fk_rutina_plan` (`plan_id`),
  CONSTRAINT `fk_rutina_plan` FOREIGN KEY (`plan_id`) REFERENCES `planes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rutinas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rutinas_ibfk_2` FOREIGN KEY (`entrenador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rutinas`
--

/*!40000 ALTER TABLE `rutinas` DISABLE KEYS */;
INSERT INTO `rutinas` VALUES (1,1,NULL,4,'2026-09-07','crossfit','for_time','Time Cap: 15 min','Completar la secuencia lo más rápido posible dentro del límite de tiempo. Mantener la técnica estricta en las dominadas.','WOD Diario: Fran Evolution'),(8,2,NULL,4,'2026-09-07','estandar',NULL,NULL,NULL,'Musculación Rutina Semanal'),(11,3,NULL,4,'2026-09-07','estandar',NULL,NULL,NULL,'Funcional Día 1'),(19,4,NULL,4,'2026-09-08','crossfit','amrap','Time Cap: 15 min',NULL,'Kids'),(28,5,8,4,'2026-09-10','estandar',NULL,NULL,NULL,'Programación Personalizada');
/*!40000 ALTER TABLE `rutinas` ENABLE KEYS */;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dni` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rol` enum('cliente','admin','entrenador') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cliente',
  `confirmado` tinyint(1) NOT NULL DEFAULT '0',
  `token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Admin','Gym','admin@gym.com','$2y$12$un7L35c/S9jxSpuH.72MBeZB9cVZUnd2gjz1DCThZY8qC9qI/md5S','1111111111','admin',1,''),(2,'Branco','Echazú','brancoechazu@gmail.com','$2y$12$ElDi690wwrLgmYWPURN/ZOuOcSPsw6i4oOOkkVo2h5UMo64hUkVru','3875978810','cliente',1,''),(4,'Carlos','Benítez','carlos@gym.com','$2y$12$92uu5FYLYtNfNkLrKLozRu3EGUx9nNoaQU9UgS4KpDjCaUj/Lu7YK','676328764','entrenador',1,''),(5,'Fabrizio','Viveros','fabri@gym.com','$2y$12$paPpMeSgIrqdZus5ak56fOV/k9eJwAHFjxQimG2tTpTJBCjoD8ewu','643726486','entrenador',1,''),(8,'Paola','Digan','paola@gym.com','$2y$12$T2b7KMZAecIeGEBglNl4tOu8EshQH8.l3ZShBnimds9cljbetskZi','3875086024','cliente',1,''),(9,'Mario','Fernández','mario@gym.com','$2y$12$smIlZfAiv45O5TtEuMb8Q.8Wr35xLsjvKD/i68LwBEdUoMf8AP0pa','123456789','cliente',1,''),(10,'Victoria','Fernández','victoria@gym.com','$2y$12$gN0Quc.d9Y56BAwLuUcn4umgtp7DnFOgPDz0Ubif2HPN/RXvIKSuy','6826368716','cliente',1,'');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-10 21:41:47
