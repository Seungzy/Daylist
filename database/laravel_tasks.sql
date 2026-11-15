-- Daylist database export: 2026-11-16T04:11:02+08:00
-- Import into the database selected in phpMyAdmin.

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
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `membership_number` varchar(40) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `membership_category_id` bigint(20) unsigned NOT NULL,
  `membership_level_id` bigint(20) unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `location_consent_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `members_membership_number_unique` (`membership_number`),
  UNIQUE KEY `members_email_unique` (`email`),
  UNIQUE KEY `members_user_id_unique` (`user_id`),
  KEY `members_membership_category_id_foreign` (`membership_category_id`),
  KEY `members_membership_level_id_foreign` (`membership_level_id`),
  KEY `members_last_name_first_name_index` (`last_name`,`first_name`),
  CONSTRAINT `members_membership_category_id_foreign` FOREIGN KEY (`membership_category_id`) REFERENCES `membership_categories` (`id`),
  CONSTRAINT `members_membership_level_id_foreign` FOREIGN KEY (`membership_level_id`) REFERENCES `membership_levels` (`id`),
  CONSTRAINT `members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `membership_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `membership_applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `membership_category_id` bigint(20) unsigned NOT NULL,
  `membership_level_id` bigint(20) unsigned NOT NULL,
  `member_id` bigint(20) unsigned DEFAULT NULL,
  `reference_no` varchar(50) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `fee` decimal(10,2) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `location_consent_at` timestamp NULL DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `verification_queued_at` timestamp NULL DEFAULT NULL,
  `verification_sent_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `membership_applications_reference_no_unique` (`reference_no`),
  UNIQUE KEY `membership_applications_member_id_unique` (`member_id`),
  KEY `membership_applications_membership_category_id_foreign` (`membership_category_id`),
  KEY `membership_applications_membership_level_id_foreign` (`membership_level_id`),
  KEY `membership_applications_status_index` (`status`),
  CONSTRAINT `membership_applications_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  CONSTRAINT `membership_applications_membership_category_id_foreign` FOREIGN KEY (`membership_category_id`) REFERENCES `membership_categories` (`id`),
  CONSTRAINT `membership_applications_membership_level_id_foreign` FOREIGN KEY (`membership_level_id`) REFERENCES `membership_levels` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `membership_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `membership_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `membership_categories_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `membership_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `membership_levels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `fee` decimal(10,2) NOT NULL,
  `validity_months` int(10) unsigned NOT NULL,
  `benefits` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `membership_levels_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `membership_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `membership_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reference_number` varchar(50) NOT NULL,
  `member_id` bigint(20) unsigned NOT NULL,
  `membership_level_id` bigint(20) unsigned NOT NULL,
  `type` varchar(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `validity_months` int(10) unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `membership_transactions_reference_number_unique` (`reference_number`),
  KEY `membership_transactions_member_id_foreign` (`member_id`),
  KEY `membership_transactions_membership_level_id_foreign` (`membership_level_id`),
  KEY `membership_transactions_created_by_foreign` (`created_by`),
  KEY `membership_transactions_approved_by_foreign` (`approved_by`),
  KEY `membership_transactions_status_index` (`status`),
  CONSTRAINT `membership_transactions_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `membership_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `membership_transactions_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`),
  CONSTRAINT `membership_transactions_membership_level_id_foreign` FOREIGN KEY (`membership_level_id`) REFERENCES `membership_levels` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `receipt_number` varchar(50) NOT NULL,
  `membership_transaction_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` varchar(30) NOT NULL,
  `external_reference` varchar(255) DEFAULT NULL,
  `paid_at` date NOT NULL,
  `recorded_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_receipt_number_unique` (`receipt_number`),
  KEY `payments_membership_transaction_id_foreign` (`membership_transaction_id`),
  KEY `payments_recorded_by_foreign` (`recorded_by`),
  CONSTRAINT `payments_membership_transaction_id_foreign` FOREIGN KEY (`membership_transaction_id`) REFERENCES `membership_transactions` (`id`),
  CONSTRAINT `payments_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `member_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `is_personal` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `tasks_member_id_foreign` (`member_id`),
  KEY `tasks_created_by_foreign` (`created_by`),
  KEY `tasks_is_personal_member_id_index` (`is_personal`,`member_id`),
  CONSTRAINT `tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;



/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

LOCK TABLES `members` WRITE;
/*!40000 ALTER TABLE `members` DISABLE KEYS */;
INSERT INTO `members` VALUES (1,'MEM-20261028-01M6135FJNP5664HJ1W38BP0XB','Maya (Demo)','Reyes','maya.demo@example.test',NULL,'Fictional member for classroom demonstration.',1,1,1,'2026-10-28 11:08:46','2026-11-15 18:54:23',2,NULL,NULL,NULL),(2,'MEM-20261028-01M6135FKKXA43HWW8R9JZ7MG6','Daniel (Demo)','Cruz','daniel.demo@example.test',NULL,'Fictional member for classroom demonstration.',2,2,1,'2026-10-28 11:08:46','2026-10-28 11:08:46',NULL,NULL,NULL,NULL),(3,'MEM-20261028-01M6135FKT3BSXGYNF3DK8SBK9','Elena (Demo)','Santos','elena.demo@example.test',NULL,'Fictional member for classroom demonstration.',3,3,1,'2026-10-28 11:08:46','2026-10-28 11:08:46',NULL,NULL,NULL,NULL),(4,'MEM-20261028-01M6135FM4SR0ECX0936SX84QB','Alex (Demo)','Rivera','alex.demo@example.test',NULL,'Fictional member for classroom demonstration.',4,4,1,'2026-10-28 11:08:46','2026-10-28 11:08:46',NULL,NULL,NULL,NULL),(5,'MEM-20261028-01M6135FMHK4FXK7GHFH59SG3Z','Nina (Demo)','Garcia','nina.demo@example.test',NULL,'Fictional member for classroom demonstration.',2,1,1,'2026-10-28 11:08:46','2026-10-28 11:08:46',NULL,NULL,NULL,NULL),(7,'MEM-20261028-01M614GF20ZZTBV0H71K15BGDV','re','ro','admin@daylist.local','091234','street',2,3,1,'2026-10-28 11:32:14','2026-10-28 11:32:27',NULL,NULL,NULL,NULL),(8,'MEM-20261116-01M7GBBQGVPKKGBQRZ58BXG7EC','Olivia','Rodrigo','romantasy.myst@gmail.com','09123456789','DaisyChain street',2,4,1,'2026-11-15 19:36:17','2026-11-15 19:36:17',3,NULL,NULL,NULL);
/*!40000 ALTER TABLE `members` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `membership_applications` WRITE;
/*!40000 ALTER TABLE `membership_applications` DISABLE KEYS */;
INSERT INTO `membership_applications` VALUES (1,1,1,NULL,'APP-20261116-01M7G8ZF7P72JHXCCEM7JVKABW','Tutorial Demo Applicant','application.demo@example.test',NULL,NULL,500.00,14.5995000,120.9842000,'2026-11-15 18:54:38','pending','2026-11-15 18:54:38','2026-11-15 18:54:57',NULL,'2026-11-15 18:54:38','2026-11-15 18:54:57'),(2,2,4,8,'APP-20261116-01M7GADQZBH0ZHY282BN80Z0QG','Olivia Rodrigo','romantasy.myst@gmail.com','09123456789','DaisyChain street',3500.00,NULL,NULL,NULL,'registered','2026-11-15 19:19:55','2026-11-15 19:20:02','2026-11-15 19:35:15','2026-11-15 19:19:55','2026-11-15 19:36:17');
/*!40000 ALTER TABLE `membership_applications` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `membership_categories` WRITE;
/*!40000 ALTER TABLE `membership_categories` DISABLE KEYS */;
INSERT INTO `membership_categories` VALUES (1,'Student',NULL,'2026-10-28 03:03:52','2026-10-28 03:03:52'),(2,'Regular',NULL,'2026-10-28 03:03:52','2026-10-28 03:03:52'),(3,'Senior Citizen',NULL,'2026-10-28 03:03:52','2026-10-28 03:03:52'),(4,'Corporate',NULL,'2026-10-28 03:03:52','2026-10-28 03:03:52');
/*!40000 ALTER TABLE `membership_categories` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `membership_levels` WRITE;
/*!40000 ALTER TABLE `membership_levels` DISABLE KEYS */;
INSERT INTO `membership_levels` VALUES (1,'Basic',500.00,12,'Member directory and community updates.','2026-10-28 03:03:52','2026-10-28 03:03:52'),(2,'Silver',1000.00,12,'Basic benefits plus event discounts.','2026-10-28 03:03:52','2026-10-28 03:03:52'),(3,'Gold',2000.00,12,'Silver benefits plus priority event registration.','2026-10-28 03:03:52','2026-10-28 03:03:52'),(4,'Platinum',3500.00,12,'Gold benefits plus premium member assistance.','2026-10-28 03:03:52','2026-10-28 03:03:52');
/*!40000 ALTER TABLE `membership_levels` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `membership_transactions` WRITE;
/*!40000 ALTER TABLE `membership_transactions` DISABLE KEYS */;
INSERT INTO `membership_transactions` VALUES (1,'TXN-20261028-01M6135FK10Z9TRBAC3MDB2HKC',1,1,'application',500.00,12,'approved','2026-10-28','2027-10-27','2026-10-28 11:08:46',1,1,'2026-10-28 11:08:46','2026-10-28 11:08:46'),(2,'TXN-20261028-01M6135FKR14EH5AWSFMH7TCCA',2,2,'application',1000.00,12,'pending',NULL,NULL,NULL,1,NULL,'2026-10-28 11:08:46','2026-10-28 11:08:46'),(3,'TXN-20261028-01M6135FKYP1RXN34RFRKFFBT6',3,3,'application',2000.00,12,'paid',NULL,NULL,NULL,1,NULL,'2026-10-28 11:08:46','2026-10-28 11:08:46'),(4,'TXN-20261028-01M6135FM9AX9BFR8PBVYJXTAB',4,4,'application',3500.00,12,'approved','2025-09-28','2026-09-28','2026-10-28 11:08:46',1,1,'2026-10-28 11:08:46','2026-10-28 11:08:46'),(5,'TXN-20261028-01M6135FMN72QJQAG35QHH2APQ',5,1,'application',500.00,12,'cancelled',NULL,NULL,NULL,1,NULL,'2026-10-28 11:08:46','2026-10-28 11:08:46'),(8,'TXN-20261028-01M614GF2B6EVTB7P2STA22JRY',7,2,'application',1000.00,12,'pending',NULL,NULL,NULL,1,NULL,'2026-10-28 11:32:14','2026-10-28 11:32:14'),(9,'TXN-20261116-01M7GBBQH60CCEEXCVDHY3VDP7',8,4,'application',3500.00,12,'approved','2026-11-16','2027-11-15','2026-11-15 19:39:45',1,1,'2026-11-15 19:36:17','2026-11-15 19:39:45');
/*!40000 ALTER TABLE `membership_transactions` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_10_21_000000_create_tasks_table',1),(5,'2026_10_28_000000_create_membership_tables',2),(6,'2026_11_16_000000_add_member_portal_and_applications',3),(7,'2026_11_16_000001_add_personal_tasks',4);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,'RCT-20261028-01M6135FK95PHKHNG6FF57ADEJ',1,500.00,'cash',NULL,'2026-10-28',1,'2026-10-28 11:08:46','2026-10-28 11:08:46'),(2,'RCT-20261028-01M6135FM1K21SX6TPE0AWQZVV',3,2000.00,'cash',NULL,'2026-10-28',1,'2026-10-28 11:08:46','2026-10-28 11:08:46'),(3,'RCT-20261028-01M6135FMCAK7SCHYCGXG7HGTS',4,3500.00,'cash',NULL,'2026-10-28',1,'2026-10-28 11:08:46','2026-10-28 11:08:46'),(7,'RCT-20261116-01M7GBD11502G14JEY7VVR2M0V',9,3500.00,'cash','123','2026-11-16',1,'2026-11-15 19:37:00','2026-11-15 19:37:00');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES (2,'Cooking','Cooking Fish',1,'2026-10-21 03:05:30','2026-10-21 03:10:23',NULL,NULL,NULL,0),(4,'Welcome to your member workspace (Demo)','Open My profile to review your membership, then check My tasks for community activities and assignments. Contact your administrator if any details need updating.',0,'2026-11-15 18:54:23','2026-11-15 18:54:23',NULL,1,NULL,0),(5,'Print papers','46 of them print',1,'2026-11-15 20:10:18','2026-11-15 20:10:21',8,3,NULL,1);
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Administrator','admin@gmail.com',NULL,'$2y$12$48lMmlikZ1AFMF2xJ5RaVOlyYEGpZhHcoQgpRwYcgpNzcMa9Xqizi',NULL,'2026-10-28 03:04:04','2026-10-28 11:30:56',1),(2,'Maya (Demo) Reyes','maya.demo@example.test',NULL,'$2y$12$/wy2bNmy5SU39hhQO7TfFegrLkRPQOOvWbdtAZFdRGxw0JpHpzeGy',NULL,'2026-11-15 18:54:23','2026-11-15 18:54:23',0),(3,'Olivia Rodrigo','romantasy.myst@gmail.com',NULL,'$2y$12$jlSkbi.7Mvvkjr09XJ3Lj.cET/JlWlIH.kNEuGoC7LIPi8fwh2hve',NULL,'2026-11-15 19:36:17','2026-11-15 19:48:11',0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

