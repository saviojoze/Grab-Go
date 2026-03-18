-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: grab_and_go
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart`
--

LOCK TABLES `cart` WRITE;
/*!40000 ALTER TABLE `cart` DISABLE KEYS */;
INSERT INTO `cart` VALUES (60,1,29,1,'2026-03-02 16:46:12');
/*!40000 ALTER TABLE `cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_display_order` (`display_order`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,NULL,'Fresh Produce','🥬',1,'2025-12-22 10:47:46'),(2,NULL,'Fruits','🍎',2,'2025-12-22 10:47:46'),(3,NULL,'Vegetables','🥕',3,'2025-12-22 10:47:46'),(4,NULL,'Dairy','🥛',4,'2025-12-22 10:47:46'),(5,NULL,'Bakery','🍞',5,'2025-12-22 10:47:46'),(7,NULL,'Beverages','🥤',7,'2025-12-22 10:47:46'),(12,5,'Cakes','🔹',0,'2026-01-10 08:15:59'),(13,7,'Soft Drinks','🔹',0,'2026-01-10 08:15:59'),(14,7,'Juices','🔹',0,'2026-01-10 08:15:59'),(15,NULL,'Electronics','🔌',10,'2026-01-15 06:35:49'),(16,NULL,'Home Appliances','🏠',11,'2026-01-15 06:35:49'),(17,NULL,'Beauty & Personal Care','🧴',12,'2026-01-15 06:35:49'),(18,NULL,'Home & Kitchen','🍳',13,'2026-01-15 06:35:49'),(19,NULL,'Automotive','🚗',14,'2026-01-15 06:35:49'),(20,NULL,'Pet Supplies','🐾',15,'2026-01-15 06:35:49'),(21,NULL,'Toys & Games','🧸',16,'2026-01-15 06:35:49');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_requests`
--

DROP TABLE IF EXISTS `leave_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `leave_type` varchar(50) DEFAULT 'Sick Leave',
  `reason` text NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `admin_remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff_directory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_requests`
--

LOCK TABLES `leave_requests` WRITE;
/*!40000 ALTER TABLE `leave_requests` DISABLE KEYS */;
INSERT INTO `leave_requests` VALUES (1,1,'2026-02-11','2026-02-13','Test','Automated Test Leave Request','Approved','Approved by Test Script','2026-02-10 02:33:23','2026-02-10 02:33:23'),(2,12,'2026-02-10','2026-02-10','Sick Leave','sdfgn','Approved','','2026-02-10 04:19:27','2026-02-10 04:20:06'),(3,12,'2026-03-06','2026-03-07','Sick Leave','Ghui','Approved','','2026-03-05 08:42:18','2026-03-05 08:44:03');
/*!40000 ALTER TABLE `leave_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `idx_order` (`order_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,10,'Croissants 6pk',1,5.99),(2,2,8,'Cheddar Cheese',1,4.99),(3,3,4,'Hass Avocado',1,5.90),(5,5,8,'Cheddar Cheese',1,4.99),(6,6,8,'Cheddar Cheese',1,4.99),(7,7,8,'Cheddar Cheese',1,49.00),(8,7,3,'Vine Ripe Tomatoes',1,100.00),(9,8,31,'Iphone 17 128GB',1,56999.00),(10,9,32,'asus tuff i5 16GB RAM',1,75000.00),(11,10,51,'Body Lotion 400ml',1,399.00),(12,11,32,'asus tuff i5 16GB RAM',1,75000.00),(13,12,51,'Body Lotion 400ml',1,399.00),(14,13,32,'asus tuff i5 16GB RAM',1,75000.00),(15,14,32,'asus tuff i5 16GB RAM',1,75000.00),(16,15,8,'Cheddar Cheese',1,49.00),(17,16,18,'Chocolate Lava Cake',1,35.00),(18,17,32,'asus tuff i5 16GB RAM',1,75000.00),(19,18,51,'Body Lotion 400ml',1,399.00),(20,19,32,'asus tuff i5 16GB RAM',1,75000.00),(21,20,53,'Car Air Freshener',1,299.00),(22,21,31,'Iphone 17 128GB',1,56999.00),(23,21,32,'asus tuff i5 16GB RAM',1,75000.00),(24,22,31,'Iphone 17 128GB',2,56999.00),(25,23,51,'Body Lotion 400ml',1,399.00),(26,23,20,'Classic Cola 2L',1,49.00),(27,24,18,'Chocolate Lava Cake',1,35.00),(28,25,32,'asus tuff i5 16GB RAM',1,75000.00),(29,26,31,'Iphone 17 128GB',1,56999.00),(30,27,3,'Vine Ripe Tomatoes',1,100.00),(31,27,4,'Hass Avocado',1,98.00),(32,28,4,'Hass Avocado',1,98.00),(33,29,4,'Hass Avocado',1,98.00),(34,30,2,'Organic Bananas',1,50.00),(35,31,6,'Gala Apples',1,70.00),(36,32,6,'Gala Apples',18,70.00),(37,33,2,'Organic Bananas',168,50.00),(38,33,32,'asus tuff i5 16GB RAM',133,75000.00),(39,33,3,'Vine Ripe Tomatoes',166,100.00),(40,34,2,'Organic Bananas',1,50.00),(41,35,4,'Hass Avocado',1,98.00),(42,36,4,'Hass Avocado',1,98.00),(43,37,51,'Body Lotion 400ml',1,399.00),(44,38,18,'Chocolate Lava Cake',1,35.00),(45,39,31,'Iphone 17 128GB',1,56999.00),(46,40,2,'Organic Bananas',1,50.00),(47,40,33,'Smart Watch Series 5',1,12999.00),(48,41,51,'Body Lotion 400ml',1,399.00),(49,42,8,'Cheddar Cheese',1,49.00),(50,43,32,'asus tuff i5 16GB RAM',1,75000.00),(51,44,28,'Cotton Bedhseet Double',1,999.00),(52,45,34,'Wireless Mouse',1,599.00),(53,46,10,'Croissants 6pk',1,60.00),(54,47,56,'Abner',1,2.50),(55,48,56,'Abner',1,2.50),(56,49,56,'Abner',2,2.50),(57,50,31,'Iphone 17 128GB',1,56999.00),(58,51,32,'asus tuff i5 16GB RAM',1,75000.00),(59,52,8,'Cheddar Cheese',1,49.00),(60,52,51,'Body Lotion 400ml',1,399.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pickup_date` date NOT NULL,
  `pickup_time` time NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `payment_method` enum('cash','card','online') DEFAULT 'cash',
  `subtotal` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','ready','completed','cancelled') DEFAULT 'pending',
  `qr_code_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `delivery_otp` varchar(6) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `razorpay_order_id` varchar(255) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `idx_order_number` (`order_number`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_pickup_date` (`pickup_date`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'ORD-816E864A',5,'2025-12-31','09:00:00','SAVIO JOSE INT MCA 2023-2028','saviojose2028@mca.ajce.in','6243567889','cash',5.99,0.00,0.00,5.99,'pending',NULL,'2025-12-22 13:05:38','2026-01-31 07:41:34','286648','pending',NULL,NULL),(2,'ORD-7CBB6C4E',5,'3345-03-12','10:00:00','SAVIO JOSE INT MCA 2023-2028','saviojose2028@mca.ajce.in','1234567890','cash',4.99,0.00,0.00,4.99,'ready',NULL,'2025-12-22 13:07:40','2026-01-31 07:41:34','648890','pending',NULL,NULL),(3,'ORD-3377211F',4,'2025-12-31','10:00:00','AUGUSTINE JOYAL JOSE INT MCA 2023-2028','augustinejoyaljose2028@mca.ajce.in','1234567890','cash',5.90,0.00,0.00,5.90,'ready',NULL,'2025-12-22 14:21:31','2026-01-31 07:41:34','106574','pending',NULL,NULL),(4,'ORD-C30BA336',5,'2026-01-07','09:00:00','SAVIO JOSE INT MCA 2023-2028','saviojose2028@mca.ajce.in','06282805044','cash',5.29,0.00,0.00,5.29,'ready',NULL,'2026-01-07 07:43:00','2026-01-31 07:41:34','484497','pending',NULL,NULL),(5,'ORD-E7451B73',5,'2026-01-09','09:00:00','SAVIO JOSE INT MCA 2023-2028','saviojose2028@mca.ajce.in','6282805044','cash',4.99,0.00,0.00,4.99,'ready',NULL,'2026-01-09 12:33:55','2026-01-31 07:41:34','572831','pending',NULL,NULL),(6,'ORD-19B9271E',5,'2026-01-09','09:00:00','SAVIO JOSE INT MCA 2023-2028','saviojose2028@mca.ajce.in','6282805044','online',4.99,0.00,0.00,4.99,'completed',NULL,'2026-01-09 12:35:05','2026-01-31 07:41:34','220475','pending',NULL,NULL),(7,'ORD-A68156A1',5,'2026-01-11','09:00:00','SAVIO JOSE INT MCA 2023-2028','saviojose2028@mca.ajce.in','1234567890','cash',149.00,0.00,0.00,149.00,'cancelled',NULL,'2026-01-10 13:02:44','2026-01-31 07:41:34','283133','pending',NULL,NULL),(8,'ORD-4F8F82C1',5,'2026-02-16','09:00:00','SAVIO JOSE INT MCA 2023-2028','saviojose2028@mca.ajce.in','6282805044','online',56999.00,0.00,0.00,56999.00,'pending',NULL,'2026-01-30 13:00:06','2026-01-31 07:41:34','698974','pending',NULL,NULL),(9,'ORD-188C1580',9,'2026-01-31','10:00:00','Test User','test@example.com','1234567890','online',75000.00,0.00,0.00,75000.00,'pending',NULL,'2026-01-31 06:31:45','2026-01-31 07:41:34','609835','pending',NULL,NULL),(10,'ORD-CA85625D',6,'2026-02-01','13:00:00','savio jose','saviojosekvr@gmail.com','1234567890','cash',399.00,0.00,0.00,399.00,'completed',NULL,'2026-01-31 06:34:11','2026-01-31 07:41:34','238109','pending',NULL,NULL),(11,'ORD-A892E440',6,'2026-01-31','11:00:00','savio jose','saviojosekvr@gmail.com','1234567890','cash',75000.00,0.00,0.00,75000.00,'completed',NULL,'2026-01-31 06:44:16','2026-01-31 07:41:34','355726','pending',NULL,NULL),(12,'ORD-C846C7F1',6,'2026-01-31','11:00:00','savio jose','saviojosekvr@gmail.com','1234567890','cash',399.00,0.00,0.00,399.00,'completed',NULL,'2026-01-31 06:52:25','2026-01-31 07:41:34','449053','pending',NULL,NULL),(13,'ORD-E2B93C97',6,'2026-01-31','10:00:00','savio jose','saviojosekvr@gmail.com','1234567890','cash',75000.00,0.00,0.00,75000.00,'completed',NULL,'2026-01-31 06:56:56','2026-01-31 07:41:34','579547','pending',NULL,NULL),(14,'ORD-31D0FAC3',6,'2026-01-31','14:00:00','savio jose','saviojosekvr@gmail.com','1234567890','cash',75000.00,0.00,0.00,75000.00,'completed',NULL,'2026-01-31 07:00:18','2026-01-31 07:41:34','295085','pending',NULL,NULL),(15,'ORD-A8E70CB6',6,'2026-01-31','12:00:00','savio jose','saviojosekvr@gmail.com','1234567890','cash',49.00,0.00,0.00,49.00,'pending',NULL,'2026-01-31 07:19:52','2026-01-31 07:41:34','278327','pending',NULL,NULL),(16,'ORD-BF323AD1',6,'2026-01-31','12:00:00','savio jose','saviojosekvr@gmail.com','1234567890','cash',35.00,0.00,0.00,35.00,'pending',NULL,'2026-01-31 07:31:18','2026-01-31 07:41:34','694949','pending',NULL,NULL),(17,'ORD-C35240B5',6,'2026-01-31','09:00:00','savio jose','saviojosekvr@gmail.com','1234567890','cash',75000.00,0.00,0.00,75000.00,'ready',NULL,'2026-01-31 07:47:12','2026-01-31 07:48:48','215670','pending',NULL,NULL),(18,'ORD-32BD7CDA',6,'2026-02-01','16:00:00','savio jose','saviojosekvr@gmail.com','1234567890','cash',399.00,0.00,0.00,399.00,'completed',NULL,'2026-01-31 07:47:32','2026-01-31 07:48:41','604937','pending',NULL,NULL),(19,'ORD-25E85263',6,'2026-01-31','16:00:00','savio jose','saviojosekvr@gmail.com','1234567890','online',75000.00,0.00,0.00,75000.00,'pending',NULL,'2026-01-31 08:18:08','2026-01-31 08:19:10','796424','paid','order_S9yrCULf6Cmlgx','pay_S9ys0OqeSfTdqm'),(20,'ORD-AF871395',6,'2026-01-31','11:00:00','savio jose','saviojosekvr@gmail.com','1234567890','card',299.00,0.00,0.00,299.00,'pending',NULL,'2026-01-31 08:20:27','2026-01-31 08:20:27','375993','pending',NULL,NULL),(21,'ORD-4A72AE26',6,'2026-01-30','11:00:00','savio jose','saviojosekvr@gmail.com','7907397205','online',131999.00,0.00,0.00,131999.00,'pending',NULL,'2026-01-30 08:17:03','2026-01-30 08:17:03','214799','pending','order_SA1MVv5665mS1C',NULL),(22,'ORD-4FEA7D55',6,'2026-02-01','12:00:00','savio jose','saviojosekvr@gmail.com','7907397205','online',113998.00,0.00,0.00,113998.00,'pending',NULL,'2026-01-30 08:18:19','2026-01-30 08:18:48','769746','paid','order_SA1NqUMFVQOG3R','pay_SA1O45nbYhsL8A'),(23,'ORD-3B84B8E0',5,'2026-02-02','11:00:00','SAVIO JOSE INT MCA 2023-2028','saviojose2028@mca.ajce.in','06282805044','online',448.00,0.00,0.00,448.00,'ready',NULL,'2026-02-02 05:10:01','2026-02-02 05:27:35','982939','pending','order_SB9mNEDGY3DRG0',NULL),(24,'ORD-DAC3B6B3',5,'2026-02-02','11:00:00','SAVIO JOSE INT MCA 2023-2028','saviojose2028@mca.ajce.in','06282805044','online',35.00,0.00,0.00,35.00,'completed',NULL,'2026-02-02 05:10:40','2026-02-02 05:11:53','722540','paid','order_SB9n44CMaNxDaJ','pay_SB9nHZXXXPlJiT'),(25,'ORD-544B42FF',5,'2026-02-04','09:00:00','SAVIO JOSE INT MCA 2023-2028','saviojose2028@mca.ajce.in','06282805044','online',75000.00,0.00,0.00,75000.00,'pending',NULL,'2026-02-04 07:07:45','2026-02-04 07:07:45','547179','pending','order_SByr1hdmzfH5gf',NULL),(26,'GBG-7B292C39',1,'2026-03-02','14:30:00','Savio Joe','savio@example.com','','cash',56999.00,0.00,0.00,56999.00,'pending',NULL,'2026-03-02 06:28:32','2026-03-02 10:30:30','526373','pending',NULL,NULL),(27,'GBG-80781415',1,'2026-03-02','14:00:00','Savio Joe','savio@example.com','9876543210','online',198.00,0.00,0.00,198.00,'pending',NULL,'2026-03-02 06:34:13','2026-03-02 10:30:30','495122','pending','order_SMGBAKYJy425no',NULL),(28,'GBG-0F9D2849',1,'2026-03-02','14:00:00','Savio Joe','savio@example.com','9876543210','online',98.00,0.00,0.00,98.00,'pending',NULL,'2026-03-02 06:38:22','2026-03-02 10:30:30','402112','pending','order_SMGFYVXQZP9teh',NULL),(29,'GBG-3070B899',1,'2026-03-02','14:00:00','Savio Joe','savio@example.com','9876543210','online',98.00,0.00,0.00,98.00,'pending',NULL,'2026-03-02 06:39:43','2026-03-02 10:30:30','515120','pending','order_SMGGyqO7u9bAqU',NULL),(30,'GBG-33A37A47',1,'2026-03-02','14:00:00','Savio Joe','savio@example.com','9876543210','online',50.00,0.00,0.00,50.00,'pending',NULL,'2026-03-02 06:40:32','2026-03-02 10:30:30','704458','pending','order_SMGHqeLUkFpKqC',NULL),(31,'GBG-76F4CC13',1,'2026-03-02','14:00:00','Savio Joe','savio@example.com','9876543210','online',70.00,0.00,0.00,70.00,'pending',NULL,'2026-03-02 06:43:35','2026-03-02 10:30:30','757251','paid','order_SMGL40EYfu33Vt','pay_SMGLbBV2YO6OmF'),(32,'GBG-B324A491',1,'2026-03-02','14:00:00','Savio Joe','savio@example.com','9876543210','online',1260.00,0.00,0.00,1260.00,'pending',NULL,'2026-03-02 06:45:53','2026-03-02 10:30:30','721445','paid','order_SMGNV25JGH218f','pay_SMGNiUev1qMZlU'),(33,'GBG-1CF8F7A5',1,'2026-03-02','14:00:00','Savio Joe','savio@example.com','9876543210','cash',10000000.00,0.00,0.00,10000000.00,'pending',NULL,'2026-03-02 06:52:49','2026-03-02 10:30:30','842407','pending',NULL,NULL),(34,'GBG-3DBC8ABC',1,'2026-03-02','14:00:00','Savio Joe','savio@example.com','9876543210','online',50.00,0.00,0.00,50.00,'pending',NULL,'2026-03-02 06:53:25','2026-03-02 10:30:30','814522','paid','order_SMGVS5982c29K1','pay_SMGVbsID6BSs83'),(35,'GBG-EE2115BD',1,'2026-03-02','14:00:00','Savio Joe','savio@example.com','9876543210','online',98.00,0.00,0.00,98.00,'ready',NULL,'2026-03-02 10:11:21','2026-03-02 10:33:52','582531','paid','order_SMJsYlBGDx1Ofd','pay_SMJssokAorJU8c'),(36,'GBG-77885DCD',1,'2026-03-02','14:00:00','Savio Joe','savio@example.com','9876543210','online',98.00,0.00,0.00,98.00,'completed',NULL,'2026-03-02 10:23:02','2026-03-02 10:33:08','776237','paid','order_SMK4uMb7EmzBCl','pay_SMK531V0VFkbxX'),(37,'ORD-067B34C1',5,'2026-03-02','10:00:00','SAVIO JOSE INT MCA 2023-2028','saviojose2028@mca.ajce.in','1234567890','online',399.00,0.00,0.00,399.00,'pending',NULL,'2026-03-02 10:35:03','2026-03-02 10:35:37','385775','paid','order_SMKHbTpw29ZNf7','pay_SMKHsxL8SwzCkz'),(38,'GBG-FB0ECB91',12,'2026-03-03','14:00:00','Savio','saviojosekvr@gmail.coms','6282805044','online',35.00,0.00,0.00,35.00,'pending',NULL,'2026-03-03 11:06:04','2026-03-03 11:06:04','110217','pending','order_SMjLTi9Fx3EmCH',NULL),(39,'GBG-6CEAC12C',14,'2026-03-05','14:00:00','Abner sam','abnersamjose2028@mca.ajce.in','9999999999','online',56999.00,0.00,0.00,56999.00,'completed',NULL,'2026-03-05 06:48:36','2026-03-05 06:53:26','679840','paid','order_SNS1nDoZPTKdPo','pay_SNS2OJqhfo2bOF'),(40,'GBG-0CB7B7AD',14,'2026-03-05','14:00:00','Abner sam','abnersamjose2028@mca.ajce.in','9999999999','cash',13049.00,0.00,0.00,13049.00,'pending',NULL,'2026-03-05 06:54:53','2026-03-05 06:54:53','212572','pending',NULL,NULL),(41,'ORD-EA17826C',5,'2026-03-05','11:00:00','SAVIO JOSE INT MCA 2023-2028','saviojose2028@mca.ajce.in','06282805044','online',399.00,0.00,0.00,399.00,'pending',NULL,'2026-03-05 07:05:36','2026-03-05 07:06:07','335754','paid','order_SNSJkWCIJThWR7','pay_SNSK0HU5cBEUIr'),(42,'ORD-5C661313',5,'2026-03-13','11:00:00','SAVIO JOSE INT MCA 2023-2028','saviojose2028@mca.ajce.in','06282805044','cash',49.00,0.00,0.00,49.00,'pending',NULL,'2026-03-05 07:28:56','2026-03-05 07:28:56','426614','pending',NULL,NULL),(43,'ORD-FB938CCA',5,'2026-03-06','10:00:00','SAVIO JOSE INT MCA 2023-2028','saviojose2028@mca.ajce.in','06282805044','cash',75000.00,0.00,0.00,75000.00,'pending',NULL,'2026-03-05 07:33:27','2026-03-05 07:33:27','168562','pending',NULL,NULL),(44,'ORD-3FEA59C9',5,'2026-03-06','11:00:00','SAVIO JOSE INT MCA 2023-2028','saviojose2028@mca.ajce.in','06282805044','cash',999.00,0.00,0.00,999.00,'ready',NULL,'2026-03-05 08:29:32','2026-03-06 10:54:33','484401','pending',NULL,NULL),(45,'GBG-5CE4A7FF',14,'2026-03-05','14:00:00','Abner sam','abnersamjose2028@mca.ajce.in','9999999999','cash',599.00,0.00,0.00,599.00,'ready',NULL,'2026-03-05 08:30:53','2026-03-05 08:31:55','701972','pending',NULL,NULL),(46,'ORD-1EBA02C6',5,'2026-03-06','09:00:00','SAVIO JOSE INT MCA 2023-2028','saviojose2028@mca.ajce.in','06282805044','cash',60.00,0.00,0.00,60.00,'completed',NULL,'2026-03-05 08:38:16','2026-03-05 08:38:37','796641','pending',NULL,NULL),(47,'ORD-0797D2E5',5,'2026-03-06','09:00:00','SAVIO JOSE INT MCA 2023-2028','saviojose2028@mca.ajce.in','06282805044','cash',2.50,0.00,0.00,2.50,'completed',NULL,'2026-03-05 09:32:33','2026-03-05 09:33:13','458813','pending',NULL,NULL),(48,'ORD-6A248DE5',5,'2026-03-06','09:00:00','SAVIO JOSE INT MCA 2023-2028','saviojose2028@mca.ajce.in','06282805044','cash',2.50,0.00,0.00,2.50,'completed',NULL,'2026-03-05 09:54:49','2026-03-05 09:55:28','518905','pending',NULL,NULL),(49,'GBG-3FC11947',14,'2026-03-06','14:00:00','Abner sam','abnersamjose2028@mca.ajce.in','9999999999','online',5.00,0.00,0.00,5.00,'ready',NULL,'2026-03-06 09:44:11','2026-03-06 10:54:31','176719','paid','order_SNtYQCjaOFNVXu','pay_SNtYtisU27XyHV'),(50,'GBG-3FA3F5BB',14,'2026-03-06','14:00:00','Abner sam','abnersamjose2028@mca.ajce.in','9999999999','online',56999.00,0.00,0.00,56999.00,'completed',NULL,'2026-03-06 10:41:26','2026-03-06 10:42:53','257627','pending','order_SNuWuvqsY2VPXa',NULL),(51,'ORD-7E3F75FC',5,'2026-03-07','09:00:00','SAVIO JOSE INT MCA 2023-2028','saviojose2028@mca.ajce.in','06282805044','cash',75000.00,0.00,0.00,75000.00,'completed',NULL,'2026-03-06 10:53:54','2026-03-06 10:54:44','635039','pending',NULL,NULL),(52,'ORD-26C55CE9',5,'2026-03-07','09:00:00','SAVIO JOSE INT MCA 2023-2028','saviojose2028@mca.ajce.in','06282805044','cash',448.00,0.00,0.00,448.00,'ready',NULL,'2026-03-06 11:03:30','2026-03-11 06:33:32','564616','pending',NULL,NULL);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `unit` varchar(50) DEFAULT 'units',
  `image_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `dietary_tags` varchar(255) DEFAULT NULL,
  `is_sale` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_price` (`price`),
  KEY `idx_sale` (`is_sale`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (2,'Organic Bananas',2,50.00,NULL,100,'kg','images/products/bananas.jpg','Fresh organic bananas','Vegan,Organic',0,'2025-12-22 10:47:46','2026-01-10 07:41:43'),(3,'Vine Ripe Tomatoes',3,100.00,NULL,75,'kg','images/products/tomatoes.jpg','Fresh vine ripe tomatoes','Vegan,Organic',0,'2025-12-22 10:47:46','2026-01-10 07:41:43'),(4,'Hass Avocado',1,98.00,NULL,60,'kg','images/products/avocado.jpg','Fresh Hass avocados','Vegan,Organic',0,'2025-12-22 10:47:46','2026-01-10 07:41:43'),(5,'Large Lemons',2,30.00,NULL,80,'kg','images/products/lemons.jpg','Fresh large lemons','Vegan,Organic',0,'2025-12-22 10:47:46','2026-01-10 07:41:43'),(6,'Gala Apples',2,70.00,90.49,90,'kg','images/products/apples.jpg','Fresh Gala apples','0',1,'2025-12-22 10:47:46','2026-01-10 10:33:07'),(7,'Fresh Milk 1L',4,48.00,NULL,40,'L','images/products/milk.jpg','Fresh whole milk','Vegetarian',0,'2025-12-22 10:47:46','2026-01-10 07:41:43'),(8,'Cheddar Cheese',4,49.00,NULL,30,'units','images/products/cheese.jpg','Aged cheddar cheese','Vegetarian',0,'2025-12-22 10:47:46','2026-01-09 13:10:47'),(9,'Whole Wheat Bread',5,40.00,NULL,25,'loaf','images/products/bread.jpg','Fresh whole wheat bread','Vegetarian',0,'2025-12-22 10:47:46','2026-01-10 07:41:43'),(10,'Croissants 6pk',5,60.00,NULL,20,'pack','images/products/croissants.jpg','Butter croissants','Vegetarian',0,'2025-12-22 10:47:46','2026-01-10 07:41:43'),(11,'Orange Juice 1L',7,75.00,NULL,50,'L','images/products/orange-juice.jpg','Fresh orange juice','Vegan',0,'2025-12-22 10:47:46','2026-01-10 07:41:43'),(12,'Sparkling Water 6pk',7,60.00,NULL,2,'pack','images/products/water.jpg','Sparkling mineral water','0',0,'2025-12-22 10:47:46','2026-01-14 09:50:16'),(16,'Fresh Apple Juice',14,50.00,69.00,40,'Bottle (1L)','images/products/apple-juice.png','0','Vegan,Organic',1,'2026-01-10 12:02:30','2026-01-10 13:05:53'),(17,'Mango Nectar',14,45.00,NULL,30,'Bottle (1L)','images/products/mango-juice.png','0','Vegetarian',0,'2026-01-10 12:02:30','2026-01-10 13:00:43'),(18,'Chocolate Lava Cake',12,35.00,50.00,15,'Piece','images/products/choco-cake.png','0','Vegetarian',1,'2026-01-10 12:02:30','2026-01-10 13:06:01'),(19,'Red Velvet Slice',12,30.00,NULL,20,'Slice','images/products/red-velvet.png','0','Vegetarian',0,'2026-01-10 12:02:30','2026-01-10 13:01:06'),(20,'Classic Cola 2L',13,49.00,70.00,100,'Bottle','images/products/cola.png','0','Vegan',1,'2026-01-10 12:02:30','2026-01-10 13:06:16'),(21,'Lemon-Lime Soda',13,25.00,NULL,80,'Bottle (1.5L)','images/products/lemon-soda.png','0','Vegan',0,'2026-01-10 12:02:30','2026-01-10 13:01:22'),(22,'Wireless Bluetooth Headphones',15,2999.00,NULL,50,'units','images/products/6968a54c6a5b2.jpg','Noise cancelling over-ear headphones.','0',0,'2026-01-15 06:35:49','2026-01-15 08:29:00'),(23,'Smart LED TV 43 Inch',15,24999.00,NULL,20,'units','images/products/6968a59143cc0.webp','Full HD Smart TV with apps.','0',0,'2026-01-15 06:35:49','2026-01-15 08:30:09'),(24,'Power Bank 20000mAh',15,1499.00,NULL,100,'units','images/products/6968a5b7db371.webp','Fast charging portable battery.','0',0,'2026-01-15 06:35:49','2026-01-15 08:30:47'),(25,'Microwave Oven 20L',16,5999.00,NULL,30,'units','images/products/6968a67f07787.webp','Solo microwave for reheating/cooking.','0',0,'2026-01-15 06:35:49','2026-01-15 08:34:07'),(26,'Electric Kettle 1.5L',16,899.00,NULL,60,'units','images/products/6968a6a8d4bca.webp','Boil water in minutes.','0',0,'2026-01-15 06:35:49','2026-01-15 08:34:48'),(28,'Cotton Bedhseet Double',18,999.00,NULL,45,'units','images/products/6968a6dcc8012.webp','Premium cotton double bedsheet with 2 pillow covers.','0',0,'2026-01-15 06:35:49','2026-01-15 08:35:40'),(29,'Moisturizing Face Cream',17,399.00,NULL,100,'units','images/products/6968a762b0cf5.webp','Gentle moisturizer for daily use.','0',0,'2026-01-15 06:35:49','2026-01-15 08:37:54'),(30,'Herbal Shampoo 1L',17,699.00,NULL,80,'units','images/products/6968a71a95016.webp','For strong and shiny hair.','0',0,'2026-01-15 06:35:49','2026-01-15 08:36:42'),(31,'Iphone 17 128GB',15,56999.00,NULL,30,'units','images/products/6968962910812.webp','Latest 5G smartphone with dual camera.','0',0,'2026-01-15 06:56:31','2026-01-15 07:47:52'),(32,'asus tuff i5 16GB RAM',15,75000.00,NULL,10,'units','images/products/69689c5c846c9.webp','Work and gaming laptop.','0',0,'2026-01-15 06:56:31','2026-01-15 07:50:52'),(33,'Smart Watch Series 5',15,12999.00,NULL,50,'units','images/products/69689cb3ceda7.webp','Fitness tracker and smart notifications.','0',0,'2026-01-15 06:56:31','2026-01-15 07:52:19'),(34,'Wireless Mouse',15,599.00,NULL,200,'units','images/products/69689ce5830f2.webp','Ergonomic optical mouse.','0',0,'2026-01-15 06:56:31','2026-01-15 07:53:09'),(35,'Gaming Keyboard',15,1599.00,NULL,40,'units','images/products/69689d5e127e1.webp','RGB mechanical feel keyboard.','0',0,'2026-01-15 06:56:31','2026-01-15 07:55:10'),(36,'USB-C Fast Charger',15,499.00,1000.00,150,'units','images/products/69689d876e482.webp','20W adapter for phones.','0',0,'2026-01-15 06:56:31','2026-01-15 07:55:51'),(37,'samsung tab A',15,12999.00,NULL,25,'units','images/products/69689dc177cc9.webp','Perfect for movies and reading.','0',0,'2026-01-15 06:56:31','2026-01-15 07:56:49'),(38,'Mixer Grinder 750W',16,2999.00,NULL,40,'units','images/products/69689f426d85f.webp','Powerful mixer with 3 jars.','0',0,'2026-01-15 06:56:31','2026-01-15 08:03:14'),(40,'Vacuum Cleaner',16,4999.00,NULL,15,'units','images/products/6968a1104d3e3.webp','Powerful suction for home cleaning.','0',0,'2026-01-15 06:56:31','2026-01-15 08:10:56'),(42,'Water Purifier RO+UV',16,8999.00,NULL,10,'units','images/products/6968a17b89bb5.webp','Advanced 7-stage purification.','0',0,'2026-01-15 06:56:31','2026-01-15 08:12:43'),(44,'Double Bedsheet Cotton',18,999.00,NULL,45,'units','images/products/6968a1c6f04aa.webp','Soft cotton with 2 pillow covers.','0',0,'2026-01-15 06:56:31','2026-01-15 08:13:58'),(45,'Water Bottle Set (4pc)',18,499.00,NULL,100,'units','images/products/6968a20f45623.webp','BPA-free fridge bottles.','0',0,'2026-01-15 06:56:31','2026-01-15 08:15:11'),(46,'Stainless Steel Knife Set',18,699.00,NULL,60,'units','images/products/6968a26382f30.webp','Chef knives with wooden block.','0',0,'2026-01-15 06:56:31','2026-01-15 08:16:35'),(47,'Dinner Set 32 Pcs',18,2499.00,NULL,20,'units','images/products/6968a30516499.webp','Melamine dinnerware set.','0',0,'2026-01-15 06:56:31','2026-01-15 08:19:17'),(48,'Storage Containers (6pc)',18,899.00,NULL,80,'units','images/products/6968a34783aec.webp','Airtight kitchen organizers.','0',0,'2026-01-15 06:56:31','2026-01-15 08:20:23'),(49,'Daily Face Wash',17,199.00,NULL,150,'units','images/products/6968a38d04b7d.webp','Neem and turmeric face wash.','0',0,'2026-01-15 06:56:31','2026-01-15 08:21:33'),(50,'Sunscreen SPF 50',17,450.00,NULL,100,'units','images/products/6968a3dbb533e.webp','Matte finish UV protection.','0',0,'2026-01-15 06:56:31','2026-01-15 08:22:51'),(51,'Body Lotion 400ml',17,399.00,NULL,120,'units','images/products/6968a408c25b2.webp','Cocoa butter deep moisture.','0',0,'2026-01-15 06:56:31','2026-01-15 08:23:36'),(52,'Perfume 100ml',17,1299.00,NULL,50,'units','images/products/6968a4427c856.webp','Long lasting luxury fragrance.','0',0,'2026-01-15 06:56:31','2026-01-15 08:24:34'),(53,'Car Air Freshener',19,299.00,NULL,100,'units','images/products/6968a4a7b9406.webp','Lemon scent car perfume.','0',0,'2026-01-15 06:56:31','2026-01-15 08:26:15'),(54,'Microfiber Cleaning Cloth',19,199.00,NULL,200,'units','images/products/6968a4f1a68cd.webp','Lint-free cloth for car wash.','0',0,'2026-01-15 06:56:31','2026-01-15 08:27:29'),(56,'Abner',12,2.50,NULL,1,'units','images/products/prod_1772702989_8fd780f2.jpg','No purpose',NULL,0,'2026-03-05 09:29:49','2026-03-05 09:29:49');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_attendance`
--

DROP TABLE IF EXISTS `staff_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `staff_name` varchar(255) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `status` enum('Present','Absent','Late','Half Day') DEFAULT 'Absent',
  `check_in_time` time DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_attendance` (`user_id`,`date`),
  UNIQUE KEY `unique_emp_date` (`employee_id`,`date`),
  CONSTRAINT `staff_attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_attendance`
--

LOCK TABLES `staff_attendance` WRITE;
/*!40000 ALTER TABLE `staff_attendance` DISABLE KEYS */;
INSERT INTO `staff_attendance` VALUES (44,NULL,'Alice Johnson',1,'2026-01-17','Absent',NULL,NULL,'2026-01-17 08:18:18'),(45,NULL,'Bob Smith',2,'2026-01-17','Present','09:00:00',NULL,'2026-01-17 08:18:18'),(46,NULL,'Charlie Brown',3,'2026-01-17','Present','09:00:00',NULL,'2026-01-17 08:18:18'),(47,NULL,'David Miller',4,'2026-01-17','Present','09:00:00',NULL,'2026-01-17 08:18:18'),(52,NULL,'Alice Johnson',1,'2026-01-22','Present','09:00:00',NULL,'2026-01-22 08:11:20'),(53,NULL,'Bob Smith',2,'2026-01-22','Present','09:00:00',NULL,'2026-01-22 08:11:20'),(54,NULL,'Charlie Brown',3,'2026-01-22','Present','09:00:00',NULL,'2026-01-22 08:11:20'),(55,NULL,'David Miller',4,'2026-01-22','Present','09:00:00',NULL,'2026-01-22 08:11:20');
/*!40000 ALTER TABLE `staff_attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_directory`
--

DROP TABLE IF EXISTS `staff_directory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_directory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `position` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_directory`
--

LOCK TABLES `staff_directory` WRITE;
/*!40000 ALTER TABLE `staff_directory` DISABLE KEYS */;
INSERT INTO `staff_directory` VALUES (1,NULL,'Alice Johnson','Cashier',1,'2026-01-15 11:39:35'),(2,NULL,'Bob Smith','Stocker',0,'2026-01-15 11:39:35'),(3,NULL,'Charlie Brown','Manager',1,'2026-01-15 11:39:35'),(4,NULL,'David Miller','Security',1,'2026-01-15 11:39:35'),(5,NULL,'Test User 1769069719','Tester',0,'2026-01-22 08:15:19'),(6,NULL,'Anna christina','cashier',1,'2026-01-22 08:17:33'),(7,NULL,'Test Staff 1769070121','Tester',1,'2026-01-22 08:22:01'),(8,NULL,'krishnaveni','cashier',1,'2026-01-22 08:23:38'),(9,NULL,'agnus','cashier',1,'2026-01-22 08:23:51'),(10,10,'savioJ','Staff',1,'2026-01-22 13:24:52'),(11,11,'akshay','stoker',1,'2026-01-23 04:10:51'),(12,2,'Staff User','Staff',1,'2026-02-10 04:19:07'),(13,NULL,'Alpha','Manager',1,'2026-03-11 06:38:04');
/*!40000 ALTER TABLE `staff_directory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `role` enum('customer','admin','staff') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_blocked` tinyint(1) DEFAULT 0,
  `verification_pin` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin@grabandgo.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,'Admin User',NULL,NULL,'admin','2025-12-22 10:47:46','2025-12-22 10:47:46',0,NULL),(2,'staff@grabandgo.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,'Staff User',NULL,NULL,'staff','2025-12-22 10:47:46','2025-12-22 10:47:46',0,NULL),(3,'savio@gmail.com','$2y$10$WUL76B57D3nDXVjR0q73WOPGKAeThYZj/XNFEJoiZZLOwbHfPENzq',NULL,NULL,'savio','+91 9877897789',NULL,'customer','2025-12-22 10:50:43','2026-01-31 06:58:49',0,'131785'),(4,'augustinejoyaljose2028@mca.ajce.in','$2y$10$eun2E7jWlb1dHvca2igWceBmu28ug0tI3NUa7vrLm.F5biyUYC9Gm',NULL,NULL,'AUGUSTINE JOYAL JOSE INT MCA 2023-2028',NULL,NULL,'customer','2025-12-22 11:09:35','2026-01-31 06:58:49',0,'706029'),(5,'saviojose2028@mca.ajce.in','$2y$10$3.4C4wWiG0tJ5bSVzmVJ3.5N4u/lIS3zLcIBPn641feUyBAoG0Ha.','9f997a5af21e9bd32c721a5dcf6aae3576783b87d4071e1054329a2eafec5fd6','2026-03-11 05:57:18','SAVIO JOSE INT MCA 2023-2028','','images/profiles/user_5_1768641136.jpg','customer','2025-12-22 11:13:01','2026-03-11 04:00:06',0,'648372'),(6,'saviojosekvr@gmail.com','$2y$10$QpuvMy3uv9Q4VpYVdMKfluwv5H3pZZ0.zoKi1UYd0spOuWUVNncbK','f35b5964bfcac02d2cce2da62eb86563113e60b50dc5fb931446a0e97867cc32','2026-03-11 05:49:44','savio jose',NULL,NULL,'customer','2025-12-24 06:27:10','2026-03-11 03:49:44',0,'933262'),(7,'sonatjoseph2028@mca.ajce.in','$2y$10$GCw4pgF5iQaFf1L5X3BJBOHI1y1/kHmA/oTfyY6hcubPVstJjFTxS',NULL,NULL,'SONAT JOSEPH',NULL,NULL,'customer','2025-12-24 07:38:41','2026-01-31 06:58:49',0,'374996'),(8,'abelanilmathew2028@mca.ajce.in','$2y$10$MDenOJTZJWgIJGOaE.0pMe5X6sdv.RpTVKfRZs5PQp.aD.FRSd5dS',NULL,NULL,'abel',NULL,NULL,'customer','2025-12-24 07:41:10','2026-01-31 06:58:49',0,'207360'),(9,'test@example.com','$2y$10$lZqSOkiU6jeVRNzX53YEG.rrOL3Ky1TZAsyFNzRyDZtbixvFFU5TO',NULL,NULL,'Test User',NULL,NULL,'customer','2026-01-10 08:23:56','2026-01-31 06:58:49',0,'911809'),(10,'student-04-527cf70939a0@qwiklabs.net','$2y$10$a.zFwCzSrYjOgBuDD.SmjeNHKNw/uGBECisn1BQjL7f.iXsfuhn4m',NULL,NULL,'savioJ',NULL,NULL,'staff','2026-01-22 13:24:52','2026-01-22 13:24:52',0,NULL),(11,'eventstreammundakayam@gmail.com','$2y$10$ouSIg4IhdhkO1oU5fQvyzOlGmHPHa7nAtLxVUW9so2n3F9IvbGj9C',NULL,NULL,'akshay',NULL,NULL,'staff','2026-01-23 04:10:51','2026-01-23 04:10:51',0,NULL),(12,'saviojosekvr@gmail.coms','$2y$10$fvEVujiT7qnd/CrSFCwHIO3BgBqcObvtUdvgi2X7t3rC.4XkBgP..',NULL,NULL,'Savio','6282805044',NULL,'customer','2026-03-03 11:05:14','2026-03-03 11:05:14',0,NULL),(13,'testcustomer@gmail.com','$2y$10$Gr6QJbZgflGiMz87d3g4N.dTrQaZZIjfUjZ6zlV8XqOzA/nP4.mq2',NULL,NULL,'Test Customer',NULL,NULL,'customer','2026-03-05 06:31:56','2026-03-05 06:31:56',0,NULL),(14,'abnersamjose2028@mca.ajce.in','$2y$10$JdAwmlLUPoIQdwSUVe.YTOA6L/v1Ii6MWwhZBcDsMyrltOIjAPYei',NULL,NULL,'Abner sam','',NULL,'customer','2026-03-05 06:48:13','2026-03-05 06:48:13',0,NULL),(15,'alpha@grabandgo.com','$2y$10$lcG6kHSXL9DAC.DqUp7jau0mZIkdrQZgDdtEzdO4.Lua7/OPE5822',NULL,NULL,'Alpha',NULL,NULL,'staff','2026-03-11 06:38:04','2026-03-11 06:38:04',0,NULL);
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

-- Dump completed on 2026-03-18 17:01:42
