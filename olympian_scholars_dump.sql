-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: olympian_scholars
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
-- Table structure for table `academic_events`
--

DROP TABLE IF EXISTS `academic_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `academic_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_type` enum('public_holiday','academic_term','school_break','exam_period','special_event') NOT NULL DEFAULT 'public_holiday',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_all_day` tinyint(1) NOT NULL DEFAULT 1,
  `color` varchar(30) DEFAULT NULL,
  `state_or_region` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `academic_events_created_by_foreign` (`created_by`),
  KEY `academic_events_start_date_end_date_index` (`start_date`,`end_date`),
  KEY `academic_events_event_type_index` (`event_type`),
  CONSTRAINT `academic_events_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_events`
--

LOCK TABLES `academic_events` WRITE;
/*!40000 ALTER TABLE `academic_events` DISABLE KEYS */;
INSERT INTO `academic_events` VALUES (1,'New Year\'s Day','Official public holiday celebrating the first day of the Gregorian year.','public_holiday','2025-01-01','2025-01-01',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(2,'Chinese New Year','Spring Festival national holiday.','public_holiday','2025-01-29','2025-01-30',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(3,'Thaipusam','Celebrated during the full moon in the Tamil month of Thai.','public_holiday','2025-02-11','2025-02-11',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(4,'Hari Raya Aidilfitri','Eid al-Fitr marks the end of Ramadan.','public_holiday','2025-03-31','2025-04-01',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(5,'Labour Day','National Worker\'s Holiday.','public_holiday','2025-05-01','2025-05-01',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(6,'Wesak Day','Commemorating the birth, enlightenment, and death of Buddha.','public_holiday','2025-05-12','2025-05-12',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(7,'King\'s Birthday (Yang di-Pertuan Agong)','Official birthday of His Majesty the Yang di-Pertuan Agong.','public_holiday','2025-06-02','2025-06-02',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(8,'Hari Raya Haji','Feast of Sacrifice (Eid al-Adha).','public_holiday','2025-06-07','2025-06-07',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(9,'Awal Muharram (Maal Hijrah)','Islamic New Year 1447H.','public_holiday','2025-06-27','2025-06-27',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(10,'National Day (Hari Merdeka)','Commemoration of the Malayan Declaration of Independence 1957.','public_holiday','2025-08-31','2025-08-31',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(11,'Malaysia Day','Commemorates the establishment of the Malaysian federation.','public_holiday','2025-09-16','2025-09-16',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(12,'Deepavali','Festival of Lights.','public_holiday','2025-10-20','2025-10-20',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(13,'Christmas Day','National Christmas Holiday.','public_holiday','2025-12-25','2025-12-25',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(14,'New Year\'s Day','First day of the Gregorian year 2026.','public_holiday','2026-01-01','2026-01-01',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(15,'Chinese New Year','Year of the Horse - National Holiday.','public_holiday','2026-02-17','2026-02-18',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(16,'Hari Raya Aidilfitri','Celebration concluding Ramadan 1447H.','public_holiday','2026-03-20','2026-03-21',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(17,'Labour Day','National Workers\' Day.','public_holiday','2026-05-01','2026-05-01',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(18,'Wesak Day','Buddha Day celebration.','public_holiday','2026-05-31','2026-05-31',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(19,'Agong\'s Official Birthday','Official birthday of the Yang di-Pertuan Agong.','public_holiday','2026-06-01','2026-06-01',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(20,'National Day (Hari Merdeka)','69th National Day of Malaysia.','public_holiday','2026-08-31','2026-08-31',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(21,'Malaysia Day','Federation of Malaysia celebration.','public_holiday','2026-09-16','2026-09-16',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(22,'Mid-Term Examination Week','Institution-wide mid-term revision and mock examination window.','exam_period','2026-09-21','2026-09-25',1,'#8b5cf6',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(23,'Deepavali','Festival of Lights celebration.','public_holiday','2026-11-08','2026-11-08',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(24,'Annual Final Exams Window','Semester final assessments, Cambridge IGCSE & SPM mock sprints.','exam_period','2026-11-23','2026-12-04',1,'#8b5cf6',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(25,'Year-End School Holidays','MOE Official Term 3 Year-End School Break.','school_break','2026-12-19','2027-01-10',1,'#f59e0b',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(26,'Christmas Day','Christmas celebration.','public_holiday','2026-12-25','2026-12-25',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(27,'New Year\'s Day 2027','New Year celebration.','public_holiday','2027-01-01','2027-01-01',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(28,'Academic Term 1 Begins','Commencement of 2027 academic year and new masterclasses.','academic_term','2027-01-11','2027-01-11',1,'#3b82f6',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(29,'Chinese New Year','Lunar New Year 2027.','public_holiday','2027-02-06','2027-02-07',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(30,'Hari Raya Aidilfitri','Hari Raya celebration 1448H.','public_holiday','2027-03-10','2027-03-11',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(31,'Labour Day','Workers\' Day.','public_holiday','2027-05-01','2027-05-01',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(32,'National Day (Hari Merdeka)','70th National Day.','public_holiday','2027-08-31','2027-08-31',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(33,'Malaysia Day','Malaysia Day celebration.','public_holiday','2027-09-16','2027-09-16',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(34,'Christmas Day','Christmas celebration.','public_holiday','2027-12-25','2027-12-25',1,'#ef4444',NULL,NULL,'2026-09-16 18:18:01','2026-09-16 18:18:01'),(35,'Test Mid-Term Break 2026','Special mid-term break for test verification','school_break','2026-10-15','2026-10-18',1,'#f59e0b',NULL,1,'2026-09-16 18:56:27','2026-09-16 18:56:27');
/*!40000 ALTER TABLE `academic_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assignments`
--

DROP TABLE IF EXISTS `assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `is_global` tinyint(1) NOT NULL DEFAULT 0,
  `target_level` varchar(255) DEFAULT NULL,
  `created_by_admin` tinyint(1) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'Assignment',
  `topic` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `answer_key` text DEFAULT NULL,
  `section_b_answer_key` text DEFAULT NULL,
  `section_b_questions` int(11) DEFAULT 0,
  `questions_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`questions_data`)),
  `is_interactive_pdf` tinyint(1) NOT NULL DEFAULT 0,
  `total_questions` int(11) DEFAULT 10,
  `time_limit_minutes` int(11) DEFAULT 20,
  `due_date` date DEFAULT NULL,
  `total_marks` int(11) NOT NULL DEFAULT 100,
  `duration_minutes` int(11) DEFAULT NULL,
  `exam_date` date DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assignments_course_id_foreign` (`course_id`),
  KEY `assignments_is_interactive_pdf_index` (`is_interactive_pdf`),
  CONSTRAINT `assignments_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignments`
--

LOCK TABLES `assignments` WRITE;
/*!40000 ALTER TABLE `assignments` DISABLE KEYS */;
INSERT INTO `assignments` VALUES (18,NULL,1,'Year 4',1,'Y4 BM DIAGNOSTIC ASSESSMENT','Diagnostic Test','General Diagnostic',NULL,NULL,'{\"1\":\"B\",\"2\":\"A\",\"3\":\"B\",\"4\":\"B\",\"5\":\"C\",\"6\":\"B\",\"7\":\"C\",\"8\":\"B\",\"9\":\"B\",\"10\":\"B\"}','[]',0,'[{\"number\":1,\"type\":\"mcq\",\"title\":\"Question 1\",\"correct_answer\":\"B\",\"image\":null,\"prompt\":\"Ibu membeli se_____ pisang di pasar.\",\"page_number\":2,\"options_labels\":{\"A\":\"buah\",\"B\":\"sikat\",\"C\":\"biji\",\"D\":\"helai\"},\"passage\":null},{\"number\":2,\"type\":\"mcq\",\"title\":\"Question 2\",\"correct_answer\":\"A\",\"image\":null,\"prompt\":\"Aina sedang ______ buku cerita di ruang tamu.\",\"page_number\":2,\"options_labels\":{\"A\":\"membaca\",\"B\":\"menulis\",\"C\":\"melukis\",\"D\":\"memasak\"},\"passage\":null},{\"number\":3,\"type\":\"mcq\",\"title\":\"Question 3\",\"correct_answer\":\"B\",\"image\":null,\"prompt\":\"Lawan perkataan tinggi ialah\",\"page_number\":2,\"options_labels\":{\"A\":\"kecil\",\"B\":\"rendah\",\"C\":\"pendek\",\"D\":\"nipis\"},\"passage\":null},{\"number\":4,\"type\":\"mcq\",\"title\":\"Question 4\",\"correct_answer\":\"B\",\"image\":null,\"prompt\":\"Murid-murid beratur dengan ______ sebelum masuk ke kelas.\",\"page_number\":2,\"options_labels\":{\"A\":\"bising\",\"B\":\"tertib\",\"C\":\"laju\",\"D\":\"garang\"},\"passage\":null},{\"number\":5,\"type\":\"mcq\",\"title\":\"Question 5\",\"correct_answer\":\"C\",\"image\":null,\"prompt\":\"Ayah membaca surat khabar ______ ibu memasak di dapur.\",\"page_number\":2,\"options_labels\":{\"A\":\"tetapi\",\"B\":\"sambil\",\"C\":\"manakala\",\"D\":\"kerana\"},\"passage\":null},{\"number\":6,\"type\":\"mcq\",\"title\":\"Question 6\",\"correct_answer\":\"B\",\"image\":null,\"prompt\":\"Pilih ayat yang betul.\",\"page_number\":2,\"options_labels\":{\"A\":\"Kami makan nasi dengan sudu dan garfu.\",\"B\":\"Kami makan nasi dengan sudu dan garpu.\",\"C\":\"Kami makan nasi dengan suduh dan garpu.\",\"D\":\"Kami makan nasi dengan sudi dan garpu.\"},\"passage\":null},{\"number\":7,\"type\":\"mcq\",\"title\":\"Question 7\",\"correct_answer\":\"C\",\"image\":null,\"prompt\":\"\\u201cSiti seorang murid yang rajin. Dia sentiasa menyiapkan kerja sekolah tepat pada waktunya.\\u201d Perkataan rajin dalam petikan di atas menunjukkan bahawa Siti\",\"page_number\":2,\"options_labels\":{\"A\":\"malas belajar\",\"B\":\"suka bermain\",\"C\":\"tekun belajar\",\"D\":\"takut kepada guru\"},\"passage\":null},{\"number\":8,\"type\":\"mcq\",\"title\":\"Question 8\",\"correct_answer\":\"B\",\"image\":null,\"prompt\":\"\\u201cWalaupun hujan lebat, Amir tetap pergi ke sekolah kerana dia tidak mahu ketinggalan pelajaran.\\u201d Mengapakah Amir tetap pergi ke sekolah?\",\"page_number\":2,\"options_labels\":{\"A\":\"Dia mahu bermain dengan kawan\",\"B\":\"Dia tidak mahu ketinggalan pelajaran\",\"C\":\"Dia mahu membeli makanan\",\"D\":\"Dia disuruh oleh jirannya\"},\"passage\":null},{\"number\":9,\"type\":\"mcq\",\"title\":\"Question 9\",\"correct_answer\":\"B\",\"image\":null,\"prompt\":\"Pilih ayat yang paling sesuai.\",\"page_number\":2,\"options_labels\":{\"A\":\"Sarah menutup lampu supaya bilik menjadi cerah.\",\"B\":\"Sarah menutup lampu kerana hendak menjimatkan elektrik.\",\"C\":\"Sarah menutup lampu walaupun bilik menjadi panas.\",\"D\":\"Sarah menutup lampu sambil tidur di dapur.\"},\"passage\":null},{\"number\":10,\"type\":\"mcq\",\"title\":\"Question 10\",\"correct_answer\":\"B\",\"image\":null,\"prompt\":\"\\u201cPasukan Rumah Biru berjaya menjuarai acara sukan tahunan sekolah.\\u201d Perkataan menjuarai boleh digantikan dengan\",\"page_number\":2,\"options_labels\":{\"A\":\"menyertai\",\"B\":\"memenangi\",\"C\":\"memulakan\",\"D\":\"menamatkan\"},\"passage\":null}]',0,10,20,NULL,100,NULL,NULL,'assignments/gpTdDVwQjuoq3fyoLRIY27MdNRxAJFo9PjoSPblx.pdf','1. Y4 BM DIAGNOSTIC ASSESSMENT.pdf','1289182','2026-09-23 01:24:33','2026-09-23 01:24:33'),(19,9,0,NULL,0,'Test je ni','Assignment',NULL,NULL,NULL,NULL,NULL,0,NULL,1,10,20,'2026-10-07',100,NULL,NULL,'task_attachments/P8mZ88JWnLKqGfxOHT4cSMqyAQw186tnONK5LOCj.pdf','Booklet - Menara Olympia (Draft 4) - Meeting-compressed.pdf','7362938','2026-09-30 07:10:53','2026-09-30 07:10:53'),(21,NULL,1,'All Levels',1,'MANUAL QA TEST FLOW vertical','Assignment',NULL,NULL,NULL,'[]','{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"}',8,'[{\"number\":1,\"type\":\"fill_in_blank\",\"title\":\"Question 1\",\"correct_answer\":\"\",\"image\":null,\"prompt\":\"\\/ 8\",\"page_number\":2,\"options_labels\":[],\"passage\":null},{\"number\":2,\"type\":\"fill_in_blank\",\"title\":\"Question 2\",\"correct_answer\":\"\",\"image\":null,\"prompt\":\"\\/ 8\",\"page_number\":3,\"options_labels\":[],\"passage\":null},{\"number\":3,\"type\":\"fill_in_blank\",\"title\":\"Question 3\",\"correct_answer\":\"\",\"image\":null,\"prompt\":\"\\/ 8\",\"page_number\":4,\"options_labels\":[],\"passage\":null},{\"number\":4,\"type\":\"fill_in_blank\",\"title\":\"Question 4\",\"correct_answer\":\"\",\"image\":null,\"prompt\":\"\\/ 8\",\"page_number\":5,\"options_labels\":[],\"passage\":null},{\"number\":5,\"type\":\"fill_in_blank\",\"title\":\"Question 5\",\"correct_answer\":\"\",\"image\":null,\"prompt\":\"\\/ 8\",\"page_number\":6,\"options_labels\":[],\"passage\":null},{\"number\":6,\"type\":\"fill_in_blank\",\"title\":\"Question 6\",\"correct_answer\":\"\",\"image\":null,\"prompt\":\"\\/ 8\",\"page_number\":7,\"options_labels\":[],\"passage\":null},{\"number\":7,\"type\":\"fill_in_blank\",\"title\":\"Question 7\",\"correct_answer\":\"\",\"image\":null,\"prompt\":\"\\/ 8\",\"page_number\":8,\"options_labels\":[],\"passage\":null},{\"number\":8,\"type\":\"fill_in_blank\",\"title\":\"Question 8\",\"correct_answer\":\"\",\"image\":null,\"prompt\":\"\\/ 8\",\"page_number\":9,\"options_labels\":[],\"passage\":null}]',1,8,20,NULL,100,NULL,NULL,'assignments/XyXkfOntbQ6xoB3YBMG3alI6jAyG6LAqDhV2GSJF.pdf','MANUAL_QA_TEST_FLOW_vertical.pdf','6547077','2026-10-01 06:42:35','2026-10-01 06:42:35');
/*!40000 ALTER TABLE `assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `live_class_id` bigint(20) unsigned NOT NULL,
  `status` enum('present','absent','late') NOT NULL DEFAULT 'present',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_student_id_foreign` (`student_id`),
  KEY `attendance_live_class_id_foreign` (`live_class_id`),
  CONSTRAINT `attendance_live_class_id_foreign` FOREIGN KEY (`live_class_id`) REFERENCES `live_classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_correction_requests`
--

DROP TABLE IF EXISTS `attendance_correction_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance_correction_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `live_class_id` bigint(20) unsigned NOT NULL,
  `student_attendance_id` bigint(20) unsigned DEFAULT NULL,
  `requested_status` varchar(32) NOT NULL DEFAULT 'attended',
  `reason` text NOT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'pending',
  `reviewer_id` bigint(20) unsigned DEFAULT NULL,
  `reviewer_remarks` text DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_correction_requests_live_class_id_foreign` (`live_class_id`),
  KEY `attendance_correction_requests_student_attendance_id_foreign` (`student_attendance_id`),
  KEY `attendance_correction_requests_reviewer_id_foreign` (`reviewer_id`),
  KEY `attendance_correction_requests_student_id_live_class_id_index` (`student_id`,`live_class_id`),
  KEY `attendance_correction_requests_status_index` (`status`),
  CONSTRAINT `attendance_correction_requests_live_class_id_foreign` FOREIGN KEY (`live_class_id`) REFERENCES `live_classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_correction_requests_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendance_correction_requests_student_attendance_id_foreign` FOREIGN KEY (`student_attendance_id`) REFERENCES `student_attendances` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendance_correction_requests_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_correction_requests`
--

LOCK TABLES `attendance_correction_requests` WRITE;
/*!40000 ALTER TABLE `attendance_correction_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendance_correction_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_events`
--

DROP TABLE IF EXISTS `attendance_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_attendance_id` bigint(20) unsigned DEFAULT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `live_class_id` bigint(20) unsigned NOT NULL,
  `actor_id` bigint(20) unsigned DEFAULT NULL,
  `actor_role` varchar(32) NOT NULL DEFAULT 'system',
  `event_type` varchar(64) NOT NULL,
  `event_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_events_student_attendance_id_foreign` (`student_attendance_id`),
  KEY `attendance_events_live_class_id_foreign` (`live_class_id`),
  KEY `attendance_events_actor_id_foreign` (`actor_id`),
  KEY `attendance_events_student_id_live_class_id_index` (`student_id`,`live_class_id`),
  KEY `attendance_events_event_type_index` (`event_type`),
  KEY `attendance_events_event_timestamp_index` (`event_timestamp`),
  CONSTRAINT `attendance_events_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendance_events_live_class_id_foreign` FOREIGN KEY (`live_class_id`) REFERENCES `live_classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_events_student_attendance_id_foreign` FOREIGN KEY (`student_attendance_id`) REFERENCES `student_attendances` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendance_events_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_events`
--

LOCK TABLES `attendance_events` WRITE;
/*!40000 ALTER TABLE `attendance_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendance_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blog_posts`
--

DROP TABLE IF EXISTS `blog_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blog_posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` varchar(500) DEFAULT NULL,
  `body` longtext NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blog_posts_slug_unique` (`slug`),
  KEY `blog_posts_user_id_foreign` (`user_id`),
  CONSTRAINT `blog_posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_posts`
--

LOCK TABLES `blog_posts` WRITE;
/*!40000 ALTER TABLE `blog_posts` DISABLE KEYS */;
INSERT INTO `blog_posts` VALUES (1,1,'10 Proven Study Techniques to Ace SPM Mathematics','10-proven-study-techniques-spm-mathematics','Discover evidence-backed strategies that top students use to conquer SPM Add Maths and score straight A+.','<p>Discover evidence-backed strategies that top students use to conquer SPM Add Maths and score straight A+.</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>',NULL,'Study Tips','2026-08-07 17:41:41','2026-08-11 17:41:41','2026-08-11 17:41:41'),(2,1,'How to Write a Perfect Karangan for SPM Bahasa Melayu','perfect-karangan-spm-bahasa-melayu','From choosing the right format to polishing your vocabulary — a step-by-step guide for Band 6 essays.','<p>From choosing the right format to polishing your vocabulary — a step-by-step guide for Band 6 essays.</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>',NULL,'Writing','2026-07-22 17:41:41','2026-08-11 17:41:41','2026-08-11 17:41:41'),(3,1,'IGCSE vs SPM: Which Path is Right for Your Child?','igcse-vs-spm-which-path','We break down the key differences, costs, and future prospects of both major Malaysian exam systems.','<p>We break down the key differences, costs, and future prospects of both major Malaysian exam systems.</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>',NULL,'Parents Guide','2026-08-01 17:41:41','2026-08-11 17:41:41','2026-08-11 17:41:41');
/*!40000 ALTER TABLE `blog_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

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

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('olympian-scholars-cache-135debd4837026bf06c7bfc5d1e0c6a31611af1d','i:5;',1790844971),('olympian-scholars-cache-135debd4837026bf06c7bfc5d1e0c6a31611af1d:timer','i:1790844971;',1790844971),('olympian-scholars-cache-app_settings_all','a:47:{s:13:\"platform_name\";s:14:\"OlympiaScholar\";s:16:\"platform_tagline\";s:19:\"Excel Beyond Limits\";s:13:\"support_email\";s:26:\"support@olympiascholar.com\";s:13:\"support_phone\";s:15:\"+60 12-345 6789\";s:21:\"support_whatsapp_link\";s:25:\"https://wa.me/60123456789\";s:15:\"contact_address\";s:22:\"Kuala Lumpur, Malaysia\";s:15:\"teacher_cut_pct\";s:2:\"65\";s:13:\"currency_code\";s:3:\"MYR\";s:15:\"currency_symbol\";s:2:\"RM\";s:19:\"allow_registrations\";s:1:\"1\";s:16:\"allow_gig_claims\";s:1:\"1\";s:16:\"maintenance_mode\";s:1:\"0\";s:18:\"free_trial_enabled\";s:1:\"1\";s:21:\"default_temp_password\";s:9:\"Temp@1234\";s:18:\"plan_monthly_price\";s:3:\"109\";s:16:\"plan_monthly_per\";s:12:\"/ 4 subjects\";s:17:\"plan_monthly_desc\";s:46:\"Access to 1 subject with live classes 4x/month\";s:18:\"plan_subject_price\";s:3:\"250\";s:16:\"plan_subject_per\";s:12:\"/ 6 subjects\";s:17:\"plan_subject_desc\";s:42:\"2-4 subjects bundled with priority support\";s:18:\"plan_premium_price\";s:3:\"530\";s:16:\"plan_premium_per\";s:14:\"for all access\";s:17:\"plan_premium_desc\";s:61:\"Unlimited subjects, 1-on-1 tutoring and 24/7 WhatsApp support\";s:17:\"company_bank_name\";s:10:\"Bank Islam\";s:25:\"company_bank_account_name\";s:23:\"Olympia Academy Sdn Bhd\";s:27:\"company_bank_account_number\";s:14:\"5642 8901 2345\";s:25:\"company_bank_instructions\";s:72:\"Please include your Student Name & Invoice No in the transfer reference.\";s:16:\"gateway_provider\";s:8:\"mock_fpx\";s:19:\"gateway_merchant_id\";s:17:\"OSC_MERCHANT_8892\";s:20:\"gateway_sandbox_mode\";s:1:\"0\";s:24:\"billing.invoice_due_days\";s:1:\"7\";s:25:\"billing.email_sender_name\";s:16:\"Olympia Scholars\";s:31:\"billing.reminder_invoice_issued\";s:1:\"1\";s:27:\"billing.reminder_due_7_days\";s:1:\"1\";s:27:\"billing.reminder_due_3_days\";s:1:\"1\";s:26:\"billing.reminder_due_today\";s:1:\"1\";s:30:\"billing.reminder_overdue_1_day\";s:1:\"1\";s:31:\"billing.reminder_overdue_3_days\";s:1:\"1\";s:31:\"billing.reminder_overdue_7_days\";s:1:\"1\";s:17:\"billing.bank_name\";s:22:\"Maybank Islamic Berhad\";s:27:\"billing.bank_account_number\";s:14:\"5642 8901 2345\";s:25:\"billing.bank_account_name\";s:32:\"Olympia Scholars Academy Sdn Bhd\";s:28:\"billing.payment_instructions\";s:102:\"Please include your Student Name & Invoice No in the transfer reference, then upload proof of payment.\";s:23:\"parent_greeting_enabled\";s:1:\"1\";s:21:\"parent_greeting_image\";s:34:\"images/welcome-parent-greeting.jpg\";s:21:\"parent_greeting_title\";s:34:\"Selamat Datang Ke Olympia Scholars\";s:20:\"parent_greeting_body\";s:180:\"We are thrilled to partner with you in nurturing your child\'s academic growth, curiosity, and confidence. Here is everything you need to stay connected with their learning journey.\";}',1790849005),('olympian-scholars-cache-pdf_parsed_meta_v4_1580697da72865780a3798ce55d1e275','a:3:{s:11:\"total_pages\";i:9;s:9:\"questions\";a:8:{i:1;a:7:{s:6:\"number\";i:1;s:4:\"page\";i:2;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:2;a:7:{s:6:\"number\";i:2;s:4:\"page\";i:3;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:3;a:7:{s:6:\"number\";i:3;s:4:\"page\";i:4;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:4;a:7:{s:6:\"number\";i:4;s:4:\"page\";i:5;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:5;a:7:{s:6:\"number\";i:5;s:4:\"page\";i:6;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:6;a:7:{s:6:\"number\";i:6;s:4:\"page\";i:7;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:7;a:7:{s:6:\"number\";i:7;s:4:\"page\";i:8;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:8;a:7:{s:6:\"number\";i:8;s:4:\"page\";i:9;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}}s:8:\"passages\";a:0:{}}',1790923351),('olympian-scholars-cache-pdf_parsed_meta_v4_c86207b3355a6759df2f181a16915598','a:3:{s:11:\"total_pages\";i:9;s:9:\"questions\";a:8:{i:1;a:7:{s:6:\"number\";i:1;s:4:\"page\";i:2;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:2;a:7:{s:6:\"number\";i:2;s:4:\"page\";i:3;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:3;a:7:{s:6:\"number\";i:3;s:4:\"page\";i:4;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:4;a:7:{s:6:\"number\";i:4;s:4:\"page\";i:5;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:5;a:7:{s:6:\"number\";i:5;s:4:\"page\";i:6;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:6;a:7:{s:6:\"number\";i:6;s:4:\"page\";i:7;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:7;a:7:{s:6:\"number\";i:7;s:4:\"page\";i:8;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:8;a:7:{s:6:\"number\";i:8;s:4:\"page\";i:9;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}}s:8:\"passages\";a:0:{}}',1790923322),('olympian-scholars-cache-pdf_parsed_meta_v4_ca2fc75f39a05f3cabdcc5bbe4185813','a:3:{s:11:\"total_pages\";i:9;s:9:\"questions\";a:8:{i:1;a:7:{s:6:\"number\";i:1;s:4:\"page\";i:2;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:2;a:7:{s:6:\"number\";i:2;s:4:\"page\";i:3;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:3;a:7:{s:6:\"number\";i:3;s:4:\"page\";i:4;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:4;a:7:{s:6:\"number\";i:4;s:4:\"page\";i:5;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:5;a:7:{s:6:\"number\";i:5;s:4:\"page\";i:6;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:6;a:7:{s:6:\"number\";i:6;s:4:\"page\";i:7;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:7;a:7:{s:6:\"number\";i:7;s:4:\"page\";i:8;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}i:8;a:7:{s:6:\"number\";i:8;s:4:\"page\";i:9;s:6:\"prompt\";s:3:\"/ 8\";s:8:\"raw_text\";s:3:\"/ 8\";s:7:\"options\";a:0:{}s:6:\"is_mcq\";b:0;s:7:\"passage\";N;}}s:8:\"passages\";a:0:{}}',1790923199);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

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

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `certificates`
--

DROP TABLE IF EXISTS `certificates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `certificates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `cert_number` varchar(255) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `issued_at` date DEFAULT NULL,
  `issued_by` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `certificates_cert_number_unique` (`cert_number`),
  KEY `certificates_student_id_foreign` (`student_id`),
  KEY `certificates_course_id_foreign` (`course_id`),
  CONSTRAINT `certificates_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `certificates_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `certificates`
--

LOCK TABLES `certificates` WRITE;
/*!40000 ALTER TABLE `certificates` DISABLE KEYS */;
/*!40000 ALTER TABLE `certificates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_audit_logs`
--

DROP TABLE IF EXISTS `chat_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viewer_id` bigint(20) unsigned NOT NULL,
  `child_id` bigint(20) unsigned NOT NULL,
  `conversation_id` bigint(20) unsigned DEFAULT NULL,
  `action` enum('conversation_viewed','message_viewed','attachment_viewed','monitoring_session_started','monitoring_session_ended') NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `chat_audit_logs_conversation_id_foreign` (`conversation_id`),
  KEY `chat_audit_logs_viewer_id_created_at_index` (`viewer_id`,`created_at`),
  KEY `chat_audit_logs_child_id_created_at_index` (`child_id`,`created_at`),
  CONSTRAINT `chat_audit_logs_child_id_foreign` FOREIGN KEY (`child_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_audit_logs_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_audit_logs_viewer_id_foreign` FOREIGN KEY (`viewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_audit_logs`
--

LOCK TABLES `chat_audit_logs` WRITE;
/*!40000 ALTER TABLE `chat_audit_logs` DISABLE KEYS */;
INSERT INTO `chat_audit_logs` VALUES (1,211,210,NULL,'monitoring_session_started',NULL,'127.0.0.1','Symfony','2026-09-28 10:27:14'),(2,211,210,NULL,'monitoring_session_started',NULL,'127.0.0.1','Symfony','2026-09-28 10:28:52'),(3,211,210,NULL,'monitoring_session_started',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-28 10:42:30'),(4,211,210,2,'conversation_viewed',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-28 10:42:32'),(5,211,210,2,'conversation_viewed',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-28 10:42:35'),(6,211,210,NULL,'monitoring_session_started',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-28 10:46:23'),(7,211,210,2,'conversation_viewed',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-28 10:46:24'),(8,211,210,2,'conversation_viewed',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-28 10:46:27'),(9,211,210,2,'conversation_viewed',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-28 10:46:30'),(10,211,210,2,'conversation_viewed',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-28 10:46:33'),(11,211,210,NULL,'monitoring_session_started',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-28 10:49:19'),(12,211,210,2,'conversation_viewed',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-28 10:49:20'),(13,211,210,2,'conversation_viewed',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-28 10:49:23'),(14,211,210,2,'conversation_viewed',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-28 10:49:26'),(15,211,210,2,'conversation_viewed',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-28 10:49:29'),(16,211,210,NULL,'monitoring_session_started',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-28 11:06:25'),(17,211,210,2,'conversation_viewed',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-28 11:06:26'),(18,211,210,2,'conversation_viewed',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-28 11:06:29'),(19,211,210,2,'conversation_viewed',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','2026-09-28 11:06:32'),(20,211,210,NULL,'monitoring_session_started',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-09-30 06:31:43'),(21,211,212,NULL,'monitoring_session_started',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-09-30 06:31:47'),(22,211,210,NULL,'monitoring_session_started',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-09-30 09:55:59'),(23,211,212,NULL,'monitoring_session_started',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-09-30 09:56:02');
/*!40000 ALTER TABLE `chat_audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_message_attachments`
--

DROP TABLE IF EXISTS `chat_message_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_message_attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `message_id` bigint(20) unsigned NOT NULL,
  `disk` varchar(255) NOT NULL DEFAULT 'local',
  `path` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(255) NOT NULL,
  `size_bytes` int(10) unsigned NOT NULL,
  `checksum` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chat_message_attachments_message_id_foreign` (`message_id`),
  CONSTRAINT `chat_message_attachments_message_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `chat_messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_message_attachments`
--

LOCK TABLES `chat_message_attachments` WRITE;
/*!40000 ALTER TABLE `chat_message_attachments` DISABLE KEYS */;
INSERT INTO `chat_message_attachments` VALUES (1,7,'local','chat_attachments/1/ylt61nNRbsA5Yg9TDv0W47OMYucmLGAniW8atN0A.pdf','homework_notes.pdf','application/pdf',76,'7fc7914e5a98f51c0327e90b8e19f2f91d2a48fb583180be51f71c94e8f776b9','2026-09-27 18:28:52','2026-09-27 18:28:52'),(2,16,'local','chat_attachments/3/cNnuuZARHa2yG2Wf3gYNnFOejVKs3ZOuI494YBnq.jpg','WhatsApp Image 2026-09-27 at 8.14.42 PM.jpeg','image/jpeg',64281,'c2fd49e575c56e9b09e401a6d9686b75261b77e17885c7194947fc8a4b4c68b8','2026-09-28 03:44:18','2026-09-28 03:44:18'),(3,28,'local','chat_attachments/3/RZ8BEELJFUHrxBojtTGJltuhACpxYF6XUbXCo299.jpg','WhatsApp Image 2026-09-27 at 8.14.42 PM.jpeg','image/jpeg',64281,'c2fd49e575c56e9b09e401a6d9686b75261b77e17885c7194947fc8a4b4c68b8','2026-09-28 04:58:01','2026-09-28 04:58:01');
/*!40000 ALTER TABLE `chat_message_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_message_reads`
--

DROP TABLE IF EXISTS `chat_message_reads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_message_reads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `message_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `chat_message_reads_message_id_user_id_unique` (`message_id`,`user_id`),
  KEY `chat_message_reads_user_id_read_at_index` (`user_id`,`read_at`),
  CONSTRAINT `chat_message_reads_message_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `chat_messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_message_reads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_message_reads`
--

LOCK TABLES `chat_message_reads` WRITE;
/*!40000 ALTER TABLE `chat_message_reads` DISABLE KEYS */;
INSERT INTO `chat_message_reads` VALUES (1,1,34,'2026-09-28 02:18:58'),(2,2,10,'2026-09-28 02:18:58'),(3,3,34,'2026-09-28 02:27:14'),(4,4,10,'2026-09-28 02:27:14'),(5,5,34,'2026-09-28 02:28:52'),(6,6,10,'2026-09-28 02:28:52'),(7,8,1,'2026-09-28 02:36:47'),(8,9,210,'2026-09-28 02:37:01'),(9,10,210,'2026-09-28 02:39:07'),(10,11,210,'2026-09-28 03:07:03'),(11,12,1,'2026-09-28 03:07:20'),(12,13,210,'2026-09-28 03:07:46'),(13,14,1,'2026-09-28 03:35:24'),(14,7,34,'2026-09-28 03:45:45'),(15,15,34,'2026-09-28 03:45:57'),(16,16,34,'2026-09-28 03:45:57'),(17,17,34,'2026-09-28 03:45:57'),(18,18,34,'2026-09-28 03:45:57'),(19,19,1,'2026-09-28 03:47:07'),(20,20,1,'2026-09-28 03:47:07'),(21,21,1,'2026-09-28 03:47:07'),(22,22,1,'2026-09-28 03:47:07'),(23,23,34,'2026-09-28 03:47:20'),(24,24,34,'2026-09-28 03:47:32'),(25,25,1,'2026-09-28 03:49:55'),(26,26,34,'2026-09-28 04:57:50'),(27,27,1,'2026-09-28 04:58:12'),(28,28,1,'2026-09-28 04:58:12'),(29,29,34,'2026-09-30 06:31:02'),(30,30,34,'2026-09-30 06:31:02'),(31,31,34,'2026-09-30 06:31:02'),(32,32,1,'2026-09-30 09:54:34');
/*!40000 ALTER TABLE `chat_message_reads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_messages`
--

DROP TABLE IF EXISTS `chat_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint(20) unsigned NOT NULL,
  `sender_id` bigint(20) unsigned NOT NULL,
  `body` text DEFAULT NULL,
  `type` enum('text','attachment','system','context_card') NOT NULL DEFAULT 'text',
  `reply_to_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('sending','sent','delivered','read','failed') NOT NULL DEFAULT 'sent',
  `edited_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chat_messages_reply_to_id_foreign` (`reply_to_id`),
  KEY `chat_messages_conversation_id_id_index` (`conversation_id`,`id`),
  KEY `chat_messages_sender_id_index` (`sender_id`),
  CONSTRAINT `chat_messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_messages_reply_to_id_foreign` FOREIGN KEY (`reply_to_id`) REFERENCES `chat_messages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chat_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_messages`
--

LOCK TABLES `chat_messages` WRITE;
/*!40000 ALTER TABLE `chat_messages` DISABLE KEYS */;
INSERT INTO `chat_messages` VALUES (1,1,10,'Hello teacher, I have a question about today\'s class.','text',NULL,'read',NULL,'2026-09-28 04:22:46','2026-09-28 02:18:58','2026-09-28 04:22:46'),(2,1,34,'Hi! Of course, which topic do you need help with?','text',NULL,'read',NULL,'2026-09-28 04:22:46','2026-09-28 02:18:58','2026-09-28 04:22:46'),(3,1,10,'Hello teacher, I have a question about today\'s class.','text',NULL,'read',NULL,'2026-09-28 04:22:46','2026-09-28 02:27:14','2026-09-28 04:22:46'),(4,1,34,'Hi! Of course, which topic do you need help with?','text',NULL,'read',NULL,'2026-09-28 04:22:46','2026-09-28 02:27:14','2026-09-28 04:22:46'),(5,1,10,'Hello teacher, I have a question about today\'s class.','text',NULL,'read',NULL,'2026-09-28 04:22:46','2026-09-28 02:28:52','2026-09-28 04:22:46'),(6,1,34,'Hi! Of course, which topic do you need help with?','text',NULL,'sent',NULL,'2026-09-28 04:22:46','2026-09-28 02:28:52','2026-09-28 04:22:46'),(7,1,10,'homework_notes.pdf','attachment',NULL,'read',NULL,'2026-09-28 04:22:46','2026-09-28 02:28:52','2026-09-28 04:22:46'),(8,2,210,'Hai, admin','text',NULL,'read',NULL,'2026-09-28 04:00:32','2026-09-28 02:36:21','2026-09-28 04:00:32'),(9,2,1,'Ye saya','text',NULL,'read',NULL,'2026-09-28 04:00:32','2026-09-28 02:36:57','2026-09-28 04:00:32'),(10,2,1,'ni saya admin','text',NULL,'read',NULL,'2026-09-28 04:00:32','2026-09-28 02:39:00','2026-09-28 04:00:32'),(11,2,1,'aidill eh nama?','text',NULL,'read',NULL,'2026-09-28 04:00:32','2026-09-28 03:06:55','2026-09-28 04:00:32'),(12,2,210,'haah aidill kenapa?','text',NULL,'read',NULL,'2026-09-28 04:00:32','2026-09-28 03:07:15','2026-09-28 04:00:32'),(13,2,1,'hensem nama tu','text',NULL,'read',NULL,'2026-09-28 04:00:32','2026-09-28 03:07:38','2026-09-28 04:00:32'),(14,2,210,'terima kasih cikgu','text',NULL,'read',NULL,'2026-09-28 04:00:32','2026-09-28 03:33:10','2026-09-28 04:00:32'),(15,3,1,'teacher, cantik la harinii','text',NULL,'read',NULL,NULL,'2026-09-28 03:40:16','2026-09-30 06:31:02'),(16,3,1,'WhatsApp Image 2026-09-27 at 8.14.42 PM.jpeg','attachment',NULL,'read',NULL,NULL,'2026-09-28 03:44:18','2026-09-30 06:31:02'),(17,3,1,'meh la datang','text',NULL,'read',NULL,NULL,'2026-09-28 03:44:29','2026-09-30 06:31:02'),(18,3,1,'tak seen pun','text',NULL,'read',NULL,NULL,'2026-09-28 03:44:39','2026-09-30 06:31:02'),(19,3,34,'ouh admin,sorry la','text',NULL,'read',NULL,NULL,'2026-09-28 03:46:05','2026-09-30 06:30:12'),(20,3,34,'sibuk sikit hehe','text',NULL,'read',NULL,NULL,'2026-09-28 03:46:11','2026-09-30 06:30:12'),(21,3,34,'min kahwin ke','text',NULL,'read',NULL,NULL,'2026-09-28 03:46:39','2026-09-30 06:30:12'),(22,3,34,'ish dah terlepas','text',NULL,'read',NULL,NULL,'2026-09-28 03:46:45','2026-09-30 06:30:12'),(23,3,1,'EH BUKAN LAAAAAAA HAHAHAHA','text',NULL,'read',NULL,NULL,'2026-09-28 03:47:18','2026-09-30 06:31:02'),(24,3,1,'min nak kat teacher hihi','text',NULL,'read',NULL,NULL,'2026-09-28 03:47:29','2026-09-30 06:31:02'),(25,3,34,'ish min ni','text',NULL,'read',NULL,NULL,'2026-09-28 03:49:46','2026-09-30 06:30:12'),(26,3,1,'cikgu buat apa tu','text',NULL,'read',NULL,NULL,'2026-09-28 04:57:45','2026-09-30 06:31:02'),(27,3,34,'ihik','text',NULL,'read',NULL,NULL,'2026-09-28 04:57:55','2026-09-30 06:30:12'),(28,3,34,'WhatsApp Image 2026-09-27 at 8.14.42 PM.jpeg','attachment',NULL,'read',NULL,NULL,'2026-09-28 04:58:01','2026-09-30 06:30:12'),(29,3,1,'hi teacherqa','text',NULL,'read',NULL,NULL,'2026-09-30 06:30:18','2026-09-30 06:31:02'),(30,3,1,'aqaqaqaqaqaqaqaqaqaqzqaawdrtgyukop;l-[\'nhbgdr687t','text',NULL,'read',NULL,NULL,'2026-09-30 06:30:24','2026-09-30 06:31:02'),(31,3,1,'\\=','text',NULL,'read',NULL,NULL,'2026-09-30 06:30:25','2026-09-30 06:31:02'),(32,4,10,'hi admin','text',NULL,'read',NULL,NULL,'2026-09-30 09:54:20','2026-09-30 09:54:34');
/*!40000 ALTER TABLE `chat_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_typing_indicators`
--

DROP TABLE IF EXISTS `chat_typing_indicators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_typing_indicators` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `chat_typing_indicators_conversation_id_user_id_unique` (`conversation_id`,`user_id`),
  KEY `chat_typing_indicators_user_id_foreign` (`user_id`),
  CONSTRAINT `chat_typing_indicators_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_typing_indicators_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_typing_indicators`
--

LOCK TABLES `chat_typing_indicators` WRITE;
/*!40000 ALTER TABLE `chat_typing_indicators` DISABLE KEYS */;
INSERT INTO `chat_typing_indicators` VALUES (1,2,210,'2026-09-28 03:34:16'),(2,2,1,'2026-09-27 19:07:36'),(3,3,1,'2026-09-30 06:30:22'),(4,3,34,'2026-09-28 04:57:54'),(5,4,10,'2026-09-30 09:54:17');
/*!40000 ALTER TABLE `chat_typing_indicators` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_requests`
--

DROP TABLE IF EXISTS `class_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `live_class_id` bigint(20) unsigned NOT NULL,
  `teacher_id` bigint(20) unsigned NOT NULL,
  `type` enum('cancel','reschedule') NOT NULL,
  `reason` text NOT NULL,
  `requested_at` datetime DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `class_requests_live_class_id_foreign` (`live_class_id`),
  KEY `class_requests_teacher_id_foreign` (`teacher_id`),
  CONSTRAINT `class_requests_live_class_id_foreign` FOREIGN KEY (`live_class_id`) REFERENCES `live_classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_requests_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_requests`
--

LOCK TABLES `class_requests` WRITE;
/*!40000 ALTER TABLE `class_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `class_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversation_participants`
--

DROP TABLE IF EXISTS `conversation_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversation_participants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `role` enum('member','admin','observer') NOT NULL DEFAULT 'member',
  `last_read_message_id` bigint(20) unsigned DEFAULT NULL,
  `last_read_at` timestamp NULL DEFAULT NULL,
  `muted_at` timestamp NULL DEFAULT NULL,
  `archived_at` timestamp NULL DEFAULT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `left_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conversation_participants_conversation_id_user_id_unique` (`conversation_id`,`user_id`),
  KEY `conversation_participants_user_id_archived_at_index` (`user_id`,`archived_at`),
  CONSTRAINT `conversation_participants_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conversation_participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversation_participants`
--

LOCK TABLES `conversation_participants` WRITE;
/*!40000 ALTER TABLE `conversation_participants` DISABLE KEYS */;
INSERT INTO `conversation_participants` VALUES (1,1,10,'member',7,'2026-09-27 18:28:52',NULL,NULL,'2026-09-27 18:18:58',NULL),(2,1,34,'member',7,'2026-09-28 04:22:30',NULL,NULL,'2026-09-27 18:18:58',NULL),(3,2,210,'member',NULL,'2026-09-28 04:00:32',NULL,NULL,'2026-09-27 18:36:02',NULL),(4,2,1,'member',NULL,'2026-09-28 04:00:32',NULL,NULL,'2026-09-27 18:36:02',NULL),(5,3,1,'member',31,'2026-09-30 06:30:25',NULL,NULL,'2026-09-28 03:40:05',NULL),(6,3,34,'member',31,'2026-09-30 06:31:02',NULL,NULL,'2026-09-28 03:40:05',NULL),(7,4,10,'member',32,'2026-09-30 09:54:20',NULL,NULL,'2026-09-30 09:54:15',NULL),(8,4,1,'member',32,'2026-09-30 09:54:34',NULL,NULL,'2026-09-30 09:54:15',NULL);
/*!40000 ALTER TABLE `conversation_participants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversations`
--

DROP TABLE IF EXISTS `conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('direct','student_teacher','parent_teacher','parent_admin','student_admin','teacher_admin','support','system') NOT NULL DEFAULT 'direct',
  `context_type` varchar(255) DEFAULT NULL,
  `context_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `last_message_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('open','pending','resolved','closed') NOT NULL DEFAULT 'open',
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `conversations_created_by_foreign` (`created_by`),
  KEY `conversations_context_type_context_id_index` (`context_type`,`context_id`),
  CONSTRAINT `conversations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversations`
--

LOCK TABLES `conversations` WRITE;
/*!40000 ALTER TABLE `conversations` DISABLE KEYS */;
INSERT INTO `conversations` VALUES (1,'student_teacher',NULL,NULL,NULL,7,'pending',10,'2026-09-28 02:18:58','2026-09-28 04:22:46','2026-09-28 04:22:46'),(2,'student_admin',NULL,NULL,NULL,NULL,'open',210,'2026-09-28 02:36:02','2026-09-28 04:20:53','2026-09-28 04:20:53'),(3,'teacher_admin',NULL,NULL,NULL,31,'resolved',1,'2026-09-28 03:40:05','2026-09-30 06:30:25',NULL),(4,'student_admin',NULL,NULL,NULL,32,'open',10,'2026-09-30 09:54:15','2026-09-30 09:54:20',NULL);
/*!40000 ALTER TABLE `conversations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` bigint(20) unsigned NOT NULL,
  `teacher_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `level` varchar(50) NOT NULL DEFAULT 'SPM',
  `price` decimal(8,2) NOT NULL DEFAULT 99.00,
  `status` enum('published','draft','archived') NOT NULL DEFAULT 'published',
  `total_lessons` int(11) NOT NULL DEFAULT 0,
  `schedule` varchar(255) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `courses_subject_id_foreign` (`subject_id`),
  KEY `courses_teacher_id_foreign` (`teacher_id`),
  CONSTRAINT `courses_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `courses_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (9,11,6,'Bahasa Melayu (KSSR)','Comprehensive learning course for Bahasa Melayu covering official KPM syllabus, interactive exercises, and guided problem solving.','Primary',99.00,'published',12,'Flexible Schedule',NULL,'2026-09-02 00:12:56','2026-09-02 00:12:56'),(10,14,7,'English (KSSR)','Comprehensive learning course for English covering official KPM syllabus, interactive exercises, and guided problem solving.','Primary',99.00,'published',12,'Flexible Schedule',NULL,'2026-09-02 00:12:56','2026-09-02 00:12:56'),(11,15,8,'Mathematics (KSSR)','Comprehensive learning course for Mathematics covering official KPM syllabus, interactive exercises, and guided problem solving.','Primary',99.00,'published',12,'Flexible Schedule',NULL,'2026-09-02 00:12:56','2026-09-02 00:12:56'),(23,27,6,'Physics (SPM)','Comprehensive learning course for Physics covering official KPM syllabus, interactive exercises, and guided problem solving.','Secondary',120.00,'published',12,'Flexible Schedule',NULL,'2026-09-02 00:12:56','2026-09-02 00:12:56'),(24,28,7,'Chemistry (SPM)','Comprehensive learning course for Chemistry covering official KPM syllabus, interactive exercises, and guided problem solving.','Secondary',120.00,'published',12,'Flexible Schedule',NULL,'2026-09-02 00:12:56','2026-09-02 00:12:56'),(25,29,8,'Biology (SPM)','Comprehensive learning course for Biology covering official KPM syllabus, interactive exercises, and guided problem solving.','Secondary',120.00,'published',12,'Flexible Schedule',NULL,'2026-09-02 00:12:56','2026-09-02 00:12:56'),(30,1,6,'Bahasa Melayu','Comprehensive learning course for Bahasa Melayu','Year 4',99.00,'published',12,NULL,NULL,'2026-09-03 01:12:09','2026-09-03 01:12:09'),(31,2,6,'English','Comprehensive learning course for English','Year 4',99.00,'published',12,NULL,NULL,'2026-09-03 01:12:09','2026-09-03 01:12:09'),(32,16,6,'Science','Comprehensive learning course for Science','Year 4',99.00,'published',12,NULL,NULL,'2026-09-03 01:12:09','2026-09-03 01:12:09'),(33,34,6,'bahasa melayu (Primary School (KSSR))','belajar','Primary',99.00,'published',0,NULL,NULL,'2026-09-08 01:36:07','2026-09-08 01:36:07');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enrollments`
--

DROP TABLE IF EXISTS `enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enrollments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `progress_pct` int(11) NOT NULL DEFAULT 0,
  `completed_lessons` int(11) NOT NULL DEFAULT 0,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `enrollments_student_id_course_id_unique` (`student_id`,`course_id`),
  KEY `enrollments_course_id_foreign` (`course_id`),
  CONSTRAINT `enrollments_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `enrollments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enrollments`
--

LOCK TABLES `enrollments` WRITE;
/*!40000 ALTER TABLE `enrollments` DISABLE KEYS */;
INSERT INTO `enrollments` VALUES (4,116,11,'active',0,0,'2026-09-02 00:56:54','2026-09-02 00:56:54','2026-09-02 00:56:54'),(5,1,9,'active',0,0,'2026-09-03 04:21:45','2026-09-02 20:21:45','2026-09-02 20:21:45'),(7,118,30,'active',0,0,'2026-09-03 01:12:09','2026-09-03 01:12:09','2026-09-03 01:12:09'),(8,118,31,'active',0,0,'2026-09-03 01:12:09','2026-09-03 01:12:09','2026-09-03 01:12:09'),(9,118,11,'active',0,0,'2026-09-03 01:12:09','2026-09-03 01:12:09','2026-09-03 01:12:09'),(10,118,32,'active',0,0,'2026-09-03 01:12:09','2026-09-03 01:12:09','2026-09-03 01:12:09'),(13,120,9,'active',0,0,'2026-09-08 18:53:40','2026-09-08 18:53:40','2026-09-08 18:53:40'),(14,116,9,'active',0,0,'2026-09-28 06:59:14','2026-09-28 06:59:14','2026-09-28 06:59:14'),(15,116,10,'active',0,0,'2026-09-28 06:59:14','2026-09-28 06:59:14','2026-09-28 06:59:14'),(16,116,31,'active',0,0,'2026-09-28 06:59:14','2026-09-28 06:59:14','2026-09-28 06:59:14'),(17,116,32,'active',0,0,'2026-09-28 06:59:14','2026-09-28 06:59:14','2026-09-28 06:59:14'),(18,118,9,'active',0,0,'2026-09-28 06:59:14','2026-09-28 06:59:14','2026-09-28 06:59:14'),(19,118,10,'active',0,0,'2026-09-28 06:59:14','2026-09-28 06:59:14','2026-09-28 06:59:14'),(24,120,10,'active',0,0,'2026-09-28 07:01:04','2026-09-28 07:01:04','2026-09-28 07:01:04'),(25,120,11,'active',0,0,'2026-09-28 07:01:04','2026-09-28 07:01:04','2026-09-28 07:01:04'),(26,120,31,'active',0,0,'2026-09-28 07:01:04','2026-09-28 07:01:04','2026-09-28 07:01:04'),(27,120,32,'active',0,0,'2026-09-28 07:01:04','2026-09-28 07:01:04','2026-09-28 07:01:04'),(28,1,23,'active',0,0,'2026-09-28 07:01:04','2026-09-28 07:01:04','2026-09-28 07:01:04'),(29,1,24,'active',0,0,'2026-09-28 07:01:04','2026-09-28 07:01:04','2026-09-28 07:01:04'),(30,1,25,'active',0,0,'2026-09-28 07:01:04','2026-09-28 07:01:04','2026-09-28 07:01:04'),(31,116,30,'active',0,0,'2026-09-28 07:21:24','2026-09-28 07:21:24','2026-09-28 07:21:24'),(32,1,11,'active',0,0,'2026-09-28 09:17:14','2026-09-28 09:17:14','2026-09-28 09:17:14'),(33,1,32,'active',0,0,'2026-09-28 09:17:14','2026-09-28 09:17:14','2026-09-28 09:17:14'),(34,1,31,'active',0,0,'2026-09-28 09:17:14','2026-09-28 09:17:14','2026-09-28 09:17:14'),(35,1,30,'active',0,0,'2026-09-28 09:17:14','2026-09-28 09:17:14','2026-09-28 09:17:14'),(36,120,30,'active',0,0,'2026-09-30 07:24:24','2026-09-30 07:24:24','2026-09-30 07:24:24');
/*!40000 ALTER TABLE `enrollments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

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

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fee_structure_items`
--

DROP TABLE IF EXISTS `fee_structure_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fee_structure_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fee_structure_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `item_type` enum('registration','tuition','material','resource','assessment','deposit','other') NOT NULL DEFAULT 'tuition',
  `amount` decimal(10,2) NOT NULL,
  `quantity` smallint(5) unsigned NOT NULL DEFAULT 1,
  `billing_frequency` enum('one_time','monthly','term','annual') NOT NULL DEFAULT 'monthly',
  `is_optional` tinyint(1) NOT NULL DEFAULT 0,
  `is_refundable` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fee_structure_items_fee_structure_id_sort_order_index` (`fee_structure_id`,`sort_order`),
  KEY `fee_structure_items_item_type_index` (`item_type`),
  CONSTRAINT `fee_structure_items_fee_structure_id_foreign` FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fee_structure_items`
--

LOCK TABLES `fee_structure_items` WRITE;
/*!40000 ALTER TABLE `fee_structure_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `fee_structure_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fee_structure_price_tiers`
--

DROP TABLE IF EXISTS `fee_structure_price_tiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fee_structure_price_tiers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fee_structure_id` bigint(20) unsigned NOT NULL,
  `minimum_subjects` tinyint(3) unsigned NOT NULL,
  `maximum_subjects` tinyint(3) unsigned NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fee_structure_price_tiers_fee_structure_id_sort_order_index` (`fee_structure_id`,`sort_order`),
  CONSTRAINT `fee_structure_price_tiers_fee_structure_id_foreign` FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fee_structure_price_tiers`
--

LOCK TABLES `fee_structure_price_tiers` WRITE;
/*!40000 ALTER TABLE `fee_structure_price_tiers` DISABLE KEYS */;
/*!40000 ALTER TABLE `fee_structure_price_tiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fee_structure_subjects`
--

DROP TABLE IF EXISTS `fee_structure_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fee_structure_subjects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fee_structure_id` bigint(20) unsigned NOT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fee_structure_subjects_fee_structure_id_subject_id_unique` (`fee_structure_id`,`subject_id`),
  KEY `fee_structure_subjects_fee_structure_id_index` (`fee_structure_id`),
  KEY `fee_structure_subjects_subject_id_index` (`subject_id`),
  CONSTRAINT `fee_structure_subjects_fee_structure_id_foreign` FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fee_structure_subjects_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fee_structure_subjects`
--

LOCK TABLES `fee_structure_subjects` WRITE;
/*!40000 ALTER TABLE `fee_structure_subjects` DISABLE KEYS */;
/*!40000 ALTER TABLE `fee_structure_subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fee_structures`
--

DROP TABLE IF EXISTS `fee_structures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fee_structures` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `education_level` varchar(255) NOT NULL DEFAULT 'All',
  `frequency` enum('monthly','term','one_time','yearly') NOT NULL DEFAULT 'monthly',
  `package_type` enum('individual','fixed_bundle','pick_n','tiered','custom') NOT NULL DEFAULT 'individual',
  `minimum_subjects` tinyint(3) unsigned DEFAULT NULL,
  `maximum_subjects` tinyint(3) unsigned DEFAULT NULL,
  `effective_from` date DEFAULT NULL,
  `effective_until` date DEFAULT NULL,
  `status` enum('draft','active','inactive','archived') NOT NULL DEFAULT 'active',
  `version` smallint(5) unsigned NOT NULL DEFAULT 1,
  `parent_fee_structure_id` bigint(20) unsigned DEFAULT NULL,
  `is_stackable_discount` tinyint(1) NOT NULL DEFAULT 0,
  `student_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fee_structures_student_id_foreign` (`student_id`),
  KEY `fee_structures_parent_fee_structure_id_foreign` (`parent_fee_structure_id`),
  KEY `fee_structures_package_type_status_is_active_index` (`package_type`,`status`,`is_active`),
  KEY `fee_structures_education_level_status_index` (`education_level`,`status`),
  KEY `fee_structures_effective_from_effective_until_index` (`effective_from`,`effective_until`),
  CONSTRAINT `fee_structures_parent_fee_structure_id_foreign` FOREIGN KEY (`parent_fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fee_structures_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fee_structures`
--

LOCK TABLES `fee_structures` WRITE;
/*!40000 ALTER TABLE `fee_structures` DISABLE KEYS */;
INSERT INTO `fee_structures` VALUES (1,'SPM Core Monthly Tuition','Monthly standard tuition covering Bahasa Melayu, English, Mathematics & Science.',250.00,'Form 4–5','monthly','individual',NULL,NULL,NULL,NULL,'active',1,NULL,0,NULL,1,'2026-09-01 20:55:51','2026-09-01 20:55:51',NULL),(2,'PT3 Lower Secondary Monthly Tuition','Comprehensive tuition for Form 1 to Form 3 core subjects.',200.00,'Form 1–3','monthly','individual',NULL,NULL,NULL,NULL,'active',1,NULL,0,NULL,1,'2026-09-01 20:55:51','2026-09-01 20:55:51',NULL),(3,'Primary UPSR Foundation Tuition','Primary mastery foundation program for Year 4 to Year 6.',160.00,'Year 4–6','monthly','individual',NULL,NULL,NULL,NULL,'active',1,NULL,0,NULL,1,'2026-09-01 20:55:51','2026-09-01 20:55:51',NULL),(4,'Annual Digital Resource & Exam Drill Access','Access to digital past-year SPM papers, video replay archive, and AI practice drills.',120.00,'All','yearly','individual',NULL,NULL,NULL,NULL,'active',1,NULL,0,NULL,1,'2026-09-01 20:55:51','2026-09-01 20:55:51',NULL),(5,'Package 4 in 1',NULL,200.00,'All','one_time','individual',NULL,NULL,NULL,NULL,'active',1,NULL,0,NULL,1,'2026-09-28 05:02:36','2026-09-28 05:02:36',NULL);
/*!40000 ALTER TABLE `fee_structures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `financial_audit_logs`
--

DROP TABLE IF EXISTS `financial_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `financial_audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `action` varchar(255) NOT NULL,
  `target_type` varchar(255) DEFAULT NULL,
  `target_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `financial_audit_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `financial_audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `financial_audit_logs`
--

LOCK TABLES `financial_audit_logs` WRITE;
/*!40000 ALTER TABLE `financial_audit_logs` DISABLE KEYS */;
INSERT INTO `financial_audit_logs` VALUES (1,1,'update_settings','FinanceSettings',NULL,'127.0.0.1','Updated company banking & payment gateway settings.','2026-09-01 22:53:25','2026-09-01 22:53:25'),(2,10,'invoice_issued','Invoice',43,'127.0.0.1','{\"invoice_number\":\"INV-2026-01163\",\"total_amount\":60,\"subjects\":\"Bahasa Melayu\"}','2026-09-02 20:21:45','2026-09-02 20:21:45'),(3,10,'payment_initiated','Payment',60,'127.0.0.1','{\"amount\":60,\"payment_method\":\"bank_transfer\",\"invoices\":[\"INV-2026-01163\"]}','2026-09-02 20:21:45','2026-09-02 20:21:45'),(4,10,'invoice_issued','Invoice',44,'127.0.0.1','{\"invoice_number\":\"INV-2026-01164\",\"total_amount\":60,\"subjects\":\"Bahasa Melayu\"}','2026-09-02 20:22:14','2026-09-02 20:22:14'),(5,10,'payment_initiated','Payment',61,'127.0.0.1','{\"amount\":60,\"payment_method\":\"bank_transfer\",\"invoices\":[\"INV-2026-01164\"]}','2026-09-02 20:22:14','2026-09-02 20:22:14'),(7,1,'payment_approved','Payment',61,'127.0.0.1','{\"receipt_number\":\"RCP-2026-00001\",\"approved_by\":\"admin@olympianscholars.my\"}','2026-09-02 20:22:15','2026-09-02 20:22:15'),(8,211,'payment_initiated','Payment',62,'127.0.0.1','{\"amount\":99,\"payment_method\":\"bank_transfer\",\"invoices\":[\"INV-2026-01161\"]}','2026-09-02 22:48:57','2026-09-02 22:48:57'),(11,1,'payment_approved','Payment',60,'127.0.0.1','{\"receipt_number\":\"RCP-2026-00002\",\"approved_by\":\"admin@olympianscholars.my\"}','2026-09-02 23:21:31','2026-09-02 23:21:31'),(13,1,'payment_approved','Payment',62,'127.0.0.1','{\"receipt_number\":\"RCP-2026-00003\",\"approved_by\":\"admin@olympianscholars.my\"}','2026-09-02 23:21:57','2026-09-02 23:21:57'),(14,211,'payment_initiated','Payment',63,'127.0.0.1','{\"amount\":123,\"payment_method\":\"bank_transfer\",\"invoices\":[\"INV-2026-01165\"]}','2026-09-02 23:31:23','2026-09-02 23:31:23'),(17,1,'payment_approved','Payment',63,'127.0.0.1','{\"receipt_number\":\"RCP-2026-00004\",\"approved_by\":\"admin@olympianscholars.my\"}','2026-09-02 23:32:31','2026-09-02 23:32:31'),(18,211,'payment_initiated','Payment',65,'127.0.0.1','{\"amount\":160,\"payment_method\":\"bank_transfer\",\"invoices\":[\"INV-2026-01166\"]}','2026-09-03 00:03:40','2026-09-03 00:03:40'),(21,1,'payment_approved','Payment',65,'127.0.0.1','{\"receipt_number\":\"RCP-2026-00006\",\"approved_by\":\"admin@olympianscholars.my\"}','2026-09-03 00:05:20','2026-09-03 00:05:20'),(22,212,'invoice_issued','Invoice',47,'127.0.0.1','{\"invoice_number\":\"INV-2026-00001\",\"total_amount\":220,\"subjects\":\"Bahasa Melayu, English, Mathematics, Science\"}','2026-09-03 01:12:10','2026-09-03 01:12:10'),(23,1,'student_status_changed','App\\Models\\Student',118,'127.0.0.1','Student \'MUHAMMAD ADAM DANISH\' (ID: 118) status set to inactive by Admin.','2026-09-06 18:52:04','2026-09-06 18:52:04'),(24,1,'student_status_changed','App\\Models\\Student',118,'127.0.0.1','Student \'MUHAMMAD ADAM DANISH\' (ID: 118) status set to active by Admin.','2026-09-06 18:52:07','2026-09-06 18:52:07'),(25,1,'update_invoice_due_date','Invoice',47,'127.0.0.1','Updated due date for invoice #INV-2026-00001 from 10 Sep 2026 to 09 Sep 2026. Reason: Updated via full invoice editor','2026-09-07 22:35:52','2026-09-07 22:35:52'),(27,214,'invoice_issued','Invoice',49,'127.0.0.1','{\"invoice_number\":\"INV-2026-00003\",\"total_amount\":60,\"subjects\":\"Bahasa Melayu\"}','2026-09-08 18:53:40','2026-09-08 18:53:40'),(28,1,'teacher_status_changed','App\\Models\\Teacher',6,'127.0.0.1','Teacher \'CIKGU AISYAH SOFIA\' (ID: 6) status set to inactive by Admin for company record archiving.','2026-09-08 19:28:03','2026-09-08 19:28:03'),(29,1,'student_status_changed','App\\Models\\Student',119,'127.0.0.1','Student \'TRY JE\' (ID: 119) status set to inactive by Admin.','2026-09-08 19:28:39','2026-09-08 19:28:39'),(30,1,'teacher_status_changed','App\\Models\\Teacher',6,'127.0.0.1','Teacher \'CIKGU AISYAH SOFIA\' (ID: 6) status set to active by Admin for company record archiving.','2026-09-10 20:17:29','2026-09-10 20:17:29'),(31,212,'payment_initiated','Payment',66,'127.0.0.1','{\"amount\":220,\"payment_method\":\"bank_transfer\",\"invoices\":[\"INV-2026-00001\"]}','2026-09-17 01:28:01','2026-09-17 01:28:01'),(34,1,'payment_approved','Payment',66,'127.0.0.1','{\"receipt_number\":\"RCP-2026-00001\",\"approved_by\":\"admin@olympianscholars.my\"}','2026-09-17 01:40:02','2026-09-17 01:40:02'),(37,1,'payment_approved','Payment',67,'127.0.0.1','{\"receipt_number\":\"RCP-2026-00002\",\"approved_by\":\"admin@olympianscholars.my\"}','2026-09-17 18:10:31','2026-09-17 18:10:31'),(38,214,'payment_initiated','Payment',68,'127.0.0.1','{\"amount\":60,\"payment_method\":\"bank_transfer\",\"invoices\":[\"INV-2026-00003\"]}','2026-09-17 18:21:34','2026-09-17 18:21:34'),(41,1,'payment_approved','Payment',68,'127.0.0.1','{\"receipt_number\":\"RCP-2026-00003\",\"approved_by\":\"admin@olympianscholars.my\"}','2026-09-17 18:22:24','2026-09-17 18:22:24'),(42,1,'student_status_changed','App\\Models\\Student',119,'127.0.0.1','Student \'TRY JE\' (ID: 119) status set to active by Admin.','2026-09-20 18:50:55','2026-09-20 18:50:55'),(43,1,'student_status_changed','App\\Models\\Student',119,'127.0.0.1','Student \'TRY JE\' (ID: 119) status set to inactive by Admin.','2026-09-20 18:51:00','2026-09-20 18:51:00'),(44,1,'package_created','FeeStructure',NULL,'127.0.0.1','Created fee structure \'Package 4 in 1\' at RM 200.00','2026-09-28 05:02:36','2026-09-28 05:02:36');
/*!40000 ALTER TABLE `financial_audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `item_type` varchar(50) DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `billing_frequency` varchar(50) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` smallint(5) unsigned NOT NULL DEFAULT 1,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_items_invoice_id_foreign` (`invoice_id`),
  KEY `invoice_items_subject_id_foreign` (`subject_id`),
  CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_items_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
INSERT INTO `invoice_items` VALUES (9,47,1,'Bahasa Melayu',NULL,NULL,NULL,50.00,1,50.00,'2026-09-03 01:12:09','2026-09-03 01:12:09'),(10,47,2,'English',NULL,NULL,NULL,50.00,1,50.00,'2026-09-03 01:12:09','2026-09-03 01:12:09'),(11,47,15,'Mathematics',NULL,NULL,NULL,60.00,1,60.00,'2026-09-03 01:12:09','2026-09-03 01:12:09'),(12,47,16,'Science',NULL,NULL,NULL,60.00,1,60.00,'2026-09-03 01:12:09','2026-09-03 01:12:09'),(14,49,11,'Bahasa Melayu',NULL,NULL,NULL,60.00,1,60.00,'2026-09-08 18:53:40','2026-09-08 18:53:40');
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_reminders`
--

DROP TABLE IF EXISTS `invoice_reminders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_reminders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `reminder_type` enum('invoice_issued','due_7_days','due_3_days','due_today','overdue_1_day','overdue_3_days','overdue_7_days') NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `recipient_type` enum('student','parent') NOT NULL,
  `scheduled_at` datetime NOT NULL,
  `sent_at` datetime DEFAULT NULL,
  `status` enum('pending','sent','failed','skipped') NOT NULL DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_reminders_unique` (`invoice_id`,`reminder_type`,`recipient_email`),
  CONSTRAINT `invoice_reminders_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_reminders`
--

LOCK TABLES `invoice_reminders` WRITE;
/*!40000 ALTER TABLE `invoice_reminders` DISABLE KEYS */;
INSERT INTO `invoice_reminders` VALUES (92,47,'invoice_issued','aqashayunus09@gmail.com','student','2026-09-08 00:00:00','2026-09-17 09:29:21','skipped',NULL,'2026-09-07 22:35:52','2026-09-17 01:29:21'),(93,47,'invoice_issued','ttokwangs@gmail.com','parent','2026-09-08 00:00:00','2026-09-17 09:29:21','skipped',NULL,'2026-09-07 22:35:52','2026-09-17 01:29:21'),(94,47,'due_7_days','aqashayunus09@gmail.com','student','2026-09-02 00:00:00','2026-09-17 09:29:21','skipped',NULL,'2026-09-07 22:35:52','2026-09-17 01:29:21'),(95,47,'due_7_days','ttokwangs@gmail.com','parent','2026-09-02 00:00:00','2026-09-17 09:29:21','skipped',NULL,'2026-09-07 22:35:52','2026-09-17 01:29:21'),(96,47,'due_3_days','aqashayunus09@gmail.com','student','2026-09-06 00:00:00','2026-09-17 09:29:21','skipped',NULL,'2026-09-07 22:35:52','2026-09-17 01:29:21'),(97,47,'due_3_days','ttokwangs@gmail.com','parent','2026-09-06 00:00:00','2026-09-17 09:29:21','skipped',NULL,'2026-09-07 22:35:52','2026-09-17 01:29:21'),(98,47,'due_today','aqashayunus09@gmail.com','student','2026-09-09 00:00:00','2026-09-17 09:29:21','skipped',NULL,'2026-09-07 22:35:52','2026-09-17 01:29:21'),(99,47,'due_today','ttokwangs@gmail.com','parent','2026-09-09 00:00:00','2026-09-17 09:29:21','skipped',NULL,'2026-09-07 22:35:52','2026-09-17 01:29:21'),(100,47,'overdue_1_day','aqashayunus09@gmail.com','student','2026-09-10 00:00:00','2026-09-17 09:29:21','skipped',NULL,'2026-09-07 22:35:52','2026-09-17 01:29:21'),(101,47,'overdue_1_day','ttokwangs@gmail.com','parent','2026-09-10 00:00:00','2026-09-17 09:29:21','skipped',NULL,'2026-09-07 22:35:52','2026-09-17 01:29:21'),(102,47,'overdue_3_days','aqashayunus09@gmail.com','student','2026-09-12 00:00:00','2026-09-17 09:29:21','skipped',NULL,'2026-09-07 22:35:52','2026-09-17 01:29:21'),(103,47,'overdue_3_days','ttokwangs@gmail.com','parent','2026-09-12 00:00:00','2026-09-17 09:29:21','skipped',NULL,'2026-09-07 22:35:52','2026-09-17 01:29:21'),(104,47,'overdue_7_days','aqashayunus09@gmail.com','student','2026-09-16 00:00:00','2026-09-17 09:29:21','skipped',NULL,'2026-09-07 22:35:52','2026-09-17 01:29:21'),(105,47,'overdue_7_days','ttokwangs@gmail.com','parent','2026-09-16 00:00:00','2026-09-17 09:29:21','skipped',NULL,'2026-09-07 22:35:52','2026-09-17 01:29:21'),(113,49,'invoice_issued','aqashayunus05@gmail.com','student','2026-09-09 00:00:00','2026-09-18 02:22:24','skipped',NULL,'2026-09-08 18:53:40','2026-09-17 18:22:24'),(114,49,'due_7_days','aqashayunus05@gmail.com','student','2026-09-09 00:00:00','2026-09-18 02:22:24','skipped',NULL,'2026-09-08 18:53:40','2026-09-17 18:22:24'),(115,49,'due_3_days','aqashayunus05@gmail.com','student','2026-09-13 00:00:00','2026-09-18 02:22:24','skipped',NULL,'2026-09-08 18:53:40','2026-09-17 18:22:24'),(116,49,'due_today','aqashayunus05@gmail.com','student','2026-09-16 00:00:00','2026-09-18 02:22:24','skipped',NULL,'2026-09-08 18:53:40','2026-09-17 18:22:24'),(117,49,'overdue_1_day','aqashayunus05@gmail.com','student','2026-09-17 00:00:00','2026-09-18 02:22:24','skipped',NULL,'2026-09-08 18:53:40','2026-09-17 18:22:24'),(118,49,'overdue_3_days','aqashayunus05@gmail.com','student','2026-09-19 00:00:00','2026-09-18 02:22:24','skipped',NULL,'2026-09-08 18:53:40','2026-09-17 18:22:24'),(119,49,'overdue_7_days','aqashayunus05@gmail.com','student','2026-09-23 00:00:00','2026-09-18 02:22:24','skipped',NULL,'2026-09-08 18:53:40','2026-09-17 18:22:24');
/*!40000 ALTER TABLE `invoice_reminders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `fee_structure_id` bigint(20) unsigned DEFAULT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Running total of successful payments applied to this invoice',
  `due_date` date NOT NULL,
  `issued_date` date DEFAULT NULL COMMENT 'Date the invoice was formally issued (status changed to issued)',
  `billing_period` varchar(30) DEFAULT NULL COMMENT 'Human-readable billing period, e.g. "September 2026"',
  `status` enum('draft','issued','partially_paid','paid','overdue','cancelled') NOT NULL DEFAULT 'issued',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  KEY `invoices_student_id_foreign` (`student_id`),
  KEY `invoices_fee_structure_id_foreign` (`fee_structure_id`),
  KEY `invoices_course_id_foreign` (`course_id`),
  CONSTRAINT `invoices_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_fee_structure_id_foreign` FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (47,118,NULL,NULL,'INV-2026-00001','Tuition Fee September 2026 MUHAMMAD ADAM DANISH',NULL,NULL,220.00,0.00,220.00,220.00,'2026-09-09','2026-09-03','September 2026','paid','2026-09-03 01:12:09','2026-09-17 17:13:16'),(49,120,NULL,NULL,'INV-2026-00003','Tuition Fee September 2026 SYAZRIL IZHAN BIN SHUHAIMI',NULL,NULL,60.00,0.00,60.00,60.00,'2026-09-16','2026-09-09','September 2026','paid','2026-09-08 18:53:40','2026-09-17 18:22:24');
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

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

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
INSERT INTO `jobs` VALUES (1,'emails','{\"uuid\":\"76b35a9a-b7f5-443f-9341-2c531215f679\",\"displayName\":\"App\\\\Jobs\\\\SendInvoiceReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendInvoiceReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendInvoiceReminderJob\\\":2:{s:8:\\\"reminder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:26:\\\"App\\\\Models\\\\InvoiceReminder\\\";s:2:\\\"id\\\";i:1;s:9:\\\"relations\\\";a:1:{i:0;s:7:\\\"invoice\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:5:\\\"queue\\\";s:6:\\\"emails\\\";}\",\"batchId\":null},\"createdAt\":1788409178,\"delay\":null}',0,NULL,1788409178,1788409178),(2,'emails','{\"uuid\":\"84a19901-4f3d-4860-8dfc-787c2d66c049\",\"displayName\":\"App\\\\Jobs\\\\SendInvoiceReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendInvoiceReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendInvoiceReminderJob\\\":2:{s:8:\\\"reminder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:26:\\\"App\\\\Models\\\\InvoiceReminder\\\";s:2:\\\"id\\\";i:15;s:9:\\\"relations\\\";a:1:{i:0;s:7:\\\"invoice\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:5:\\\"queue\\\";s:6:\\\"emails\\\";}\",\"batchId\":null},\"createdAt\":1788409178,\"delay\":null}',0,NULL,1788409178,1788409178),(3,'emails','{\"uuid\":\"46df95ad-e671-4ae7-aaf4-0f687220067e\",\"displayName\":\"App\\\\Jobs\\\\SendInvoiceReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendInvoiceReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendInvoiceReminderJob\\\":2:{s:8:\\\"reminder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:26:\\\"App\\\\Models\\\\InvoiceReminder\\\";s:2:\\\"id\\\";i:16;s:9:\\\"relations\\\";a:1:{i:0;s:7:\\\"invoice\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:5:\\\"queue\\\";s:6:\\\"emails\\\";}\",\"batchId\":null},\"createdAt\":1788409178,\"delay\":null}',0,NULL,1788409178,1788409178),(4,'emails','{\"uuid\":\"a58097dc-0f24-4ba6-a3a2-89dd721bc8fe\",\"displayName\":\"App\\\\Jobs\\\\SendInvoiceReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendInvoiceReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendInvoiceReminderJob\\\":2:{s:8:\\\"reminder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:26:\\\"App\\\\Models\\\\InvoiceReminder\\\";s:2:\\\"id\\\";i:43;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:5:\\\"queue\\\";s:6:\\\"emails\\\";}\",\"batchId\":null},\"createdAt\":1788409305,\"delay\":null}',0,NULL,1788409305,1788409305),(5,'emails','{\"uuid\":\"f3f9abdd-1766-435a-acb5-3b3dd31a3135\",\"displayName\":\"App\\\\Jobs\\\\SendInvoiceReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendInvoiceReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendInvoiceReminderJob\\\":2:{s:8:\\\"reminder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:26:\\\"App\\\\Models\\\\InvoiceReminder\\\";s:2:\\\"id\\\";i:50;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:5:\\\"queue\\\";s:6:\\\"emails\\\";}\",\"batchId\":null},\"createdAt\":1788409334,\"delay\":null}',0,NULL,1788409334,1788409334),(6,'default','{\"uuid\":\"57b90435-f786-44bb-beb6-c1dc4f3cb60a\",\"displayName\":\"App\\\\Mail\\\\PaymentReceivedMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:28:\\\"App\\\\Mail\\\\PaymentReceivedMail\\\":5:{s:7:\\\"invoice\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Invoice\\\";s:2:\\\"id\\\";i:44;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"student\\\";i:1;s:12:\\\"student.user\\\";i:2;s:15:\\\"student.parents\\\";i:3;s:5:\\\"items\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:14:\\\"recipientEmail\\\";s:28:\\\"student1@olympianscholars.my\\\";s:13:\\\"recipientType\\\";s:7:\\\"student\\\";s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:28:\\\"student1@olympianscholars.my\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1788409335,\"delay\":null}',0,NULL,1788409335,1788409335),(7,'default','{\"uuid\":\"311c6268-76d7-4807-8484-f5d516f13a75\",\"displayName\":\"App\\\\Mail\\\\PaymentReceivedMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:28:\\\"App\\\\Mail\\\\PaymentReceivedMail\\\":5:{s:7:\\\"invoice\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Invoice\\\";s:2:\\\"id\\\";i:43;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"student\\\";i:1;s:12:\\\"student.user\\\";i:2;s:15:\\\"student.parents\\\";i:3;s:5:\\\"items\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:14:\\\"recipientEmail\\\";s:28:\\\"student1@olympianscholars.my\\\";s:13:\\\"recipientType\\\";s:7:\\\"student\\\";s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:28:\\\"student1@olympianscholars.my\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1788420091,\"delay\":null}',0,NULL,1788420091,1788420091),(8,'default','{\"uuid\":\"68647f18-661a-46fd-830c-2362375033be\",\"displayName\":\"App\\\\Mail\\\\PaymentReceivedMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:28:\\\"App\\\\Mail\\\\PaymentReceivedMail\\\":5:{s:7:\\\"invoice\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Invoice\\\";s:2:\\\"id\\\";i:41;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"student\\\";i:1;s:12:\\\"student.user\\\";i:2;s:15:\\\"student.parents\\\";i:3;s:5:\\\"items\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:14:\\\"recipientEmail\\\";s:23:\\\"aqashayunus03@gmail.com\\\";s:13:\\\"recipientType\\\";s:7:\\\"student\\\";s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:23:\\\"aqashayunus03@gmail.com\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1788420117,\"delay\":null}',0,NULL,1788420117,1788420117),(9,'default','{\"uuid\":\"974e4327-0c3d-4c3d-880d-901e78f1c8bb\",\"displayName\":\"App\\\\Mail\\\\PaymentReceivedMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:28:\\\"App\\\\Mail\\\\PaymentReceivedMail\\\":5:{s:7:\\\"invoice\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Invoice\\\";s:2:\\\"id\\\";i:41;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"student\\\";i:1;s:12:\\\"student.user\\\";i:2;s:15:\\\"student.parents\\\";i:3;s:5:\\\"items\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:14:\\\"recipientEmail\\\";s:19:\\\"ttokwangs@gmail.com\\\";s:13:\\\"recipientType\\\";s:6:\\\"parent\\\";s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:19:\\\"ttokwangs@gmail.com\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1788420117,\"delay\":null}',0,NULL,1788420117,1788420117),(10,'default','{\"uuid\":\"0e785fe1-349c-4fbb-ab9b-d08b761332b6\",\"displayName\":\"App\\\\Mail\\\\PaymentReceivedMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:28:\\\"App\\\\Mail\\\\PaymentReceivedMail\\\":5:{s:7:\\\"invoice\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Invoice\\\";s:2:\\\"id\\\";i:45;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"student\\\";i:1;s:12:\\\"student.user\\\";i:2;s:15:\\\"student.parents\\\";i:3;s:5:\\\"items\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:14:\\\"recipientEmail\\\";s:23:\\\"aqashayunus03@gmail.com\\\";s:13:\\\"recipientType\\\";s:7:\\\"student\\\";s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:23:\\\"aqashayunus03@gmail.com\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1788420751,\"delay\":null}',0,NULL,1788420751,1788420751),(11,'default','{\"uuid\":\"e0e9e9da-17c5-420c-8996-6f0191e81745\",\"displayName\":\"App\\\\Mail\\\\PaymentReceivedMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:28:\\\"App\\\\Mail\\\\PaymentReceivedMail\\\":5:{s:7:\\\"invoice\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Invoice\\\";s:2:\\\"id\\\";i:45;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"student\\\";i:1;s:12:\\\"student.user\\\";i:2;s:15:\\\"student.parents\\\";i:3;s:5:\\\"items\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:14:\\\"recipientEmail\\\";s:19:\\\"ttokwangs@gmail.com\\\";s:13:\\\"recipientType\\\";s:6:\\\"parent\\\";s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:19:\\\"ttokwangs@gmail.com\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1788420751,\"delay\":null}',0,NULL,1788420751,1788420751),(12,'default','{\"uuid\":\"4ead301e-9537-44b6-85ff-84719b0cb58f\",\"displayName\":\"App\\\\Mail\\\\PaymentReceivedMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:28:\\\"App\\\\Mail\\\\PaymentReceivedMail\\\":5:{s:7:\\\"invoice\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Invoice\\\";s:2:\\\"id\\\";i:46;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"student\\\";i:1;s:12:\\\"student.user\\\";i:2;s:15:\\\"student.parents\\\";i:3;s:5:\\\"items\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:14:\\\"recipientEmail\\\";s:23:\\\"aqashayunus03@gmail.com\\\";s:13:\\\"recipientType\\\";s:7:\\\"student\\\";s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:23:\\\"aqashayunus03@gmail.com\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1788422720,\"delay\":null}',0,NULL,1788422720,1788422720),(13,'default','{\"uuid\":\"0b71d974-91ff-4f4b-a71b-f2370250572d\",\"displayName\":\"App\\\\Mail\\\\PaymentReceivedMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:28:\\\"App\\\\Mail\\\\PaymentReceivedMail\\\":5:{s:7:\\\"invoice\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Invoice\\\";s:2:\\\"id\\\";i:46;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"student\\\";i:1;s:12:\\\"student.user\\\";i:2;s:15:\\\"student.parents\\\";i:3;s:5:\\\"items\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:14:\\\"recipientEmail\\\";s:19:\\\"ttokwangs@gmail.com\\\";s:13:\\\"recipientType\\\";s:6:\\\"parent\\\";s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:19:\\\"ttokwangs@gmail.com\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1788422720,\"delay\":null}',0,NULL,1788422720,1788422720),(14,'emails','{\"uuid\":\"b312d2ce-d855-414f-a92f-731723762818\",\"displayName\":\"App\\\\Jobs\\\\SendInvoiceReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendInvoiceReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendInvoiceReminderJob\\\":2:{s:8:\\\"reminder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:26:\\\"App\\\\Models\\\\InvoiceReminder\\\";s:2:\\\"id\\\";i:85;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:5:\\\"queue\\\";s:6:\\\"emails\\\";}\",\"batchId\":null},\"createdAt\":1788426729,\"delay\":null}',0,NULL,1788426730,1788426730),(15,'emails','{\"uuid\":\"1de12d4c-2e03-407d-95d1-7621631be327\",\"displayName\":\"App\\\\Jobs\\\\SendInvoiceReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendInvoiceReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendInvoiceReminderJob\\\":2:{s:8:\\\"reminder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:26:\\\"App\\\\Models\\\\InvoiceReminder\\\";s:2:\\\"id\\\";i:106;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:5:\\\"queue\\\";s:6:\\\"emails\\\";}\",\"batchId\":null},\"createdAt\":1788860699,\"delay\":null}',0,NULL,1788860699,1788860699),(16,'emails','{\"uuid\":\"d896577c-8ed2-437f-9cd8-01980c6ac6c3\",\"displayName\":\"App\\\\Jobs\\\\SendInvoiceReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":\"60\",\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendInvoiceReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendInvoiceReminderJob\\\":2:{s:8:\\\"reminder\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:26:\\\"App\\\\Models\\\\InvoiceReminder\\\";s:2:\\\"id\\\";i:113;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:5:\\\"queue\\\";s:6:\\\"emails\\\";}\",\"batchId\":null},\"createdAt\":1788922420,\"delay\":null}',0,NULL,1788922420,1788922420),(17,'default','{\"uuid\":\"68acca0f-3303-4dd7-8501-499a71d00583\",\"displayName\":\"App\\\\Mail\\\\PaymentReceivedMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:28:\\\"App\\\\Mail\\\\PaymentReceivedMail\\\":5:{s:7:\\\"invoice\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Invoice\\\";s:2:\\\"id\\\";i:47;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"student\\\";i:1;s:12:\\\"student.user\\\";i:2;s:15:\\\"student.parents\\\";i:3;s:5:\\\"items\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:14:\\\"recipientEmail\\\";s:23:\\\"aqashayunus09@gmail.com\\\";s:13:\\\"recipientType\\\";s:7:\\\"student\\\";s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:23:\\\"aqashayunus09@gmail.com\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1789638002,\"delay\":null}',0,NULL,1789638002,1789638002),(18,'default','{\"uuid\":\"5b623097-df29-4100-8e23-477077e6bc7c\",\"displayName\":\"App\\\\Mail\\\\PaymentReceivedMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:28:\\\"App\\\\Mail\\\\PaymentReceivedMail\\\":5:{s:7:\\\"invoice\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Invoice\\\";s:2:\\\"id\\\";i:47;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"student\\\";i:1;s:12:\\\"student.user\\\";i:2;s:15:\\\"student.parents\\\";i:3;s:5:\\\"items\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:14:\\\"recipientEmail\\\";s:19:\\\"ttokwangs@gmail.com\\\";s:13:\\\"recipientType\\\";s:6:\\\"parent\\\";s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:19:\\\"ttokwangs@gmail.com\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1789638002,\"delay\":null}',0,NULL,1789638002,1789638002),(19,'default','{\"uuid\":\"26e85708-c8e3-4204-9c8b-35b7f6b83ef7\",\"displayName\":\"App\\\\Mail\\\\PaymentReceivedMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:28:\\\"App\\\\Mail\\\\PaymentReceivedMail\\\":5:{s:7:\\\"invoice\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Invoice\\\";s:2:\\\"id\\\";i:49;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"student\\\";i:1;s:12:\\\"student.user\\\";i:2;s:15:\\\"student.parents\\\";i:3;s:5:\\\"items\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:14:\\\"recipientEmail\\\";s:23:\\\"aqashayunus05@gmail.com\\\";s:13:\\\"recipientType\\\";s:7:\\\"student\\\";s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:23:\\\"aqashayunus05@gmail.com\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1789698144,\"delay\":null}',0,NULL,1789698144,1789698144),(20,'default','{\"uuid\":\"1ffc9201-af37-4f3f-9616-b85f4e9a4d79\",\"displayName\":\"App\\\\Mail\\\\PaymentReceivedMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":17:{s:8:\\\"mailable\\\";O:28:\\\"App\\\\Mail\\\\PaymentReceivedMail\\\":5:{s:7:\\\"invoice\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Invoice\\\";s:2:\\\"id\\\";i:49;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"student\\\";i:1;s:12:\\\"student.user\\\";i:2;s:15:\\\"student.parents\\\";i:3;s:5:\\\"items\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:14:\\\"recipientEmail\\\";s:20:\\\"maksyazril@gmail.com\\\";s:13:\\\"recipientType\\\";s:6:\\\"parent\\\";s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:20:\\\"maksyazril@gmail.com\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:3:\\\"job\\\";N;}\",\"batchId\":null},\"createdAt\":1789698144,\"delay\":null}',0,NULL,1789698144,1789698144);
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `learning_materials`
--

DROP TABLE IF EXISTS `learning_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `learning_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) unsigned NOT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `topic_id` bigint(20) unsigned DEFAULT NULL,
  `teacher_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'Notes',
  `topic` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` bigint(20) unsigned NOT NULL DEFAULT 0,
  `file_type` varchar(20) NOT NULL DEFAULT 'pdf',
  `visibility` varchar(255) NOT NULL DEFAULT 'all',
  `status` varchar(255) NOT NULL DEFAULT 'Published',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `learning_materials_course_id_foreign` (`course_id`),
  KEY `learning_materials_teacher_id_foreign` (`teacher_id`),
  KEY `learning_materials_subject_id_foreign` (`subject_id`),
  KEY `learning_materials_topic_id_foreign` (`topic_id`),
  CONSTRAINT `learning_materials_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `learning_materials_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `learning_materials_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `learning_materials_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `subject_topics` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `learning_materials`
--

LOCK TABLES `learning_materials` WRITE;
/*!40000 ALTER TABLE `learning_materials` DISABLE KEYS */;
INSERT INTO `learning_materials` VALUES (1,33,NULL,NULL,6,'test','Notes','test','test','materials/jDIGhjLHNa3JG7gnoKOoXJIXBhNhhUHZ2euJn2OF.docx','OLYMPIA - BORANG MAKLUMAT PERBANKAN - 2026 - Rev 1.docx',597889,'docx','all','Published','2026-09-08 01:48:50','2026-09-08 01:48:50');
/*!40000 ALTER TABLE `learning_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `live_classes`
--

DROP TABLE IF EXISTS `live_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `live_classes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) unsigned NOT NULL,
  `teacher_id` bigint(20) unsigned DEFAULT NULL,
  `education_level` varchar(255) DEFAULT NULL,
  `target_age` varchar(255) DEFAULT NULL,
  `topic` varchar(255) DEFAULT NULL,
  `subtopic` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `scheduled_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `duration_minutes` int(11) NOT NULL DEFAULT 60,
  `meeting_url` varchar(255) DEFAULT NULL,
  `status` enum('upcoming','live','completed') NOT NULL DEFAULT 'upcoming',
  `is_rescheduled` tinyint(1) NOT NULL DEFAULT 0,
  `original_scheduled_at` datetime DEFAULT NULL,
  `reschedule_reason` text DEFAULT NULL,
  `rescheduled_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `live_classes_course_id_foreign` (`course_id`),
  KEY `live_classes_teacher_id_foreign` (`teacher_id`),
  KEY `live_classes_rescheduled_by_foreign` (`rescheduled_by`),
  CONSTRAINT `live_classes_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `live_classes_rescheduled_by_foreign` FOREIGN KEY (`rescheduled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `live_classes_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `live_classes`
--

LOCK TABLES `live_classes` WRITE;
/*!40000 ALTER TABLE `live_classes` DISABLE KEYS */;
INSERT INTO `live_classes` VALUES (3,33,6,'Primary School','Year 4','Ch. 01: belajar','belajar','Ch. 01: belajar — belajar','2026-09-12 10:38:00',60,'https://meet.google.com/abc-defg-hij','upcoming',0,NULL,NULL,NULL,'2026-09-08 01:39:17','2026-09-08 01:39:17'),(4,11,8,NULL,NULL,NULL,NULL,'Maths: Algebraic Expressions & Equations','2026-09-18 02:00:00',60,'https://meet.google.com/abc-defg-hij','upcoming',0,NULL,NULL,NULL,'2026-09-16 18:47:13','2026-09-16 18:47:13'),(5,30,6,NULL,NULL,NULL,NULL,'BM: Pengukuhan Tatabahasa & Karangan','2026-09-18 06:00:00',60,'https://meet.google.com/xyz-uvwx-rst','upcoming',0,NULL,NULL,NULL,'2026-09-16 18:47:13','2026-09-16 18:47:13'),(6,31,6,NULL,NULL,NULL,NULL,'English: Creative Writing & Vocabulary','2026-09-21 03:00:00',60,'https://meet.google.com/klm-nopq-rst','upcoming',0,NULL,NULL,NULL,'2026-09-16 18:47:14','2026-09-16 18:47:14'),(23,10,7,NULL,NULL,NULL,NULL,'English class','2026-09-30 02:00:00',60,NULL,'upcoming',0,NULL,NULL,NULL,'2026-09-29 07:50:46','2026-09-29 07:50:46');
/*!40000 ALTER TABLE `live_classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` bigint(20) unsigned NOT NULL,
  `receiver_id` bigint(20) unsigned NOT NULL,
  `body` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_sender_id_receiver_id_index` (`sender_id`,`receiver_id`),
  KEY `messages_receiver_id_sender_id_index` (`receiver_id`,`sender_id`),
  CONSTRAINT `messages_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2024_01_01_000010_create_subjects_table',1),(5,'2024_01_01_000011_create_teachers_table',1),(6,'2024_01_01_000012_create_courses_table',1),(7,'2024_01_01_000013_create_students_table',1),(8,'2024_01_01_000014_create_assignments_table',1),(9,'2024_01_01_000015_create_payments_table',1),(10,'2024_01_01_000016_create_blog_posts_table',1),(11,'2026_08_10_000001_add_gig_fields_to_teachers_table',1),(12,'2026_08_10_000002_create_teacher_applications_table',1),(13,'2026_08_10_000003_create_teaching_gigs_table',1),(14,'2026_08_10_000004_create_teacher_earnings_table',1),(15,'2026_08_10_000005_create_recorded_classes_table',1),(16,'2026_08_10_000006_create_student_topic_masteries_table',1),(17,'2026_08_17_000001_create_messages_table',2),(18,'2026_08_18_000001_create_settings_table',3),(19,'2026_08_19_020312_create_questions_table',4),(20,'2026_08_19_111200_create_user_notifications_table',5),(21,'2026_08_19_150000_add_streak_fields_to_students_table',6),(22,'2026_08_19_160000_create_subject_topics_and_subtopics_tables',7),(23,'2026_08_19_170000_create_student_attendances_table',8),(24,'2026_08_19_082336_create_class_requests_table',9),(25,'2026_08_20_100000_create_learning_materials_table',10),(26,'2026_08_20_022625_add_submission_details_to_student_assignments_table',11),(27,'2026_08_20_022929_add_file_attachment_to_assignments_table',12),(28,'2026_08_20_025500_add_teacher_feedback_to_student_assignments_table',13),(29,'2026_08_20_081346_alter_courses_level_column_to_string',14),(30,'2026_08_21_000001_add_kpm_curriculum_fields_to_subjects_table',15),(31,'2026_08_21_000002_add_subject_and_topic_to_learning_materials_table',15),(32,'2026_08_21_000001_add_type_and_metadata_to_assignments_table',16),(33,'2026_08_26_000001_create_registrations_table',17),(34,'2026_08_26_000002_add_link_code_to_students_table',18),(35,'2026_08_27_033940_alter_students_level_to_string',19),(36,'2026_08_27_200001_create_parent_student_table',20),(37,'2026_08_27_200002_add_status_to_enrollments_table',20),(38,'2026_08_27_200003_add_application_status_to_teachers_table',20),(39,'2026_08_28_023041_add_school_name_to_students_table',21),(40,'2026_09_02_000001_add_ic_number_hash_drop_link_code',22),(41,'2026_09_02_000002_create_certificates_table',23),(42,'2026_09_02_000003_create_fee_structures_table',24),(43,'2026_09_02_000004_create_invoices_table',24),(44,'2026_09_02_000005_enhance_payments_and_create_financial_audit_logs',24),(45,'2026_09_02_000006_add_address_and_nationality_to_users_table',25),(46,'2026_09_03_000001_add_monthly_fee_to_subjects_table',26),(47,'2026_09_03_000002_create_invoice_items_table',27),(48,'2026_09_03_000003_alter_invoices_table_for_billing',28),(49,'2026_09_03_000004_alter_payments_table_for_billing',28),(50,'2026_09_03_000005_create_payment_invoices_table',28),(51,'2026_09_03_000006_create_invoice_reminders_table',28),(52,'2026_09_03_000007_seed_billing_settings',28),(53,'2026_09_07_000001_add_status_to_students_table',29),(54,'2026_09_08_000002_add_notes_to_invoices_table',30),(55,'2026_09_08_000003_add_curriculum_fields_to_live_classes_table',31),(56,'2026_09_08_000004_add_education_level_to_live_classes_table',32),(57,'2026_09_08_000005_add_teacher_id_to_live_classes_table',33),(58,'2026_09_08_093408_make_teacher_id_nullable_on_courses_table',34),(59,'2026_09_11_000001_split_subjects_per_individual_year',35),(60,'2026_09_11_000002_add_is_active_to_users_table',36),(61,'2026_09_18_000001_create_academic_events_table',37),(62,'2026_09_18_000002_add_reschedule_fields_to_live_classes_table',37),(63,'2026_09_18_000003_make_course_id_nullable_and_add_global_to_assignments',38),(64,'2026_09_18_000004_make_due_date_nullable_on_assignments',39),(65,'2026_09_21_000001_add_diagnostic_fields_to_assignments_table',40),(66,'2026_09_21_000002_add_section_b_to_assignments_table',41),(67,'2026_09_21_000003_add_questions_data_to_assignments_table',42),(68,'2026_09_24_000001_add_student_id_to_fee_structures_table',43),(69,'2026_09_24_100001_upgrade_fee_structures_to_package_engine',44),(70,'2026_09_24_100002_create_fee_structure_subjects_table',44),(71,'2026_09_24_100003_create_fee_structure_price_tiers_table',44),(72,'2026_09_24_100004_create_fee_structure_items_table',44),(73,'2026_09_24_100005_add_snapshot_fields_to_invoice_items',44),(74,'2026_09_24_100006_add_soft_deletes_to_fee_structures',45),(75,'2026_09_28_100001_create_conversations_table',46),(76,'2026_09_28_021922_make_conversation_id_nullable_in_chat_audit_logs',47),(77,'2026_09_29_100001_enhance_student_attendances_table',48),(78,'2026_09_29_100002_create_attendance_events_table',48),(79,'2026_09_29_100003_create_attendance_correction_requests_table',48),(80,'2026_09_30_100001_add_interactive_pdf_to_assignments_table',49),(81,'2026_09_30_100002_create_student_assignment_submissions_table',49),(82,'2026_09_30_100003_create_submission_pages_table',49);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parent_student`
--

DROP TABLE IF EXISTS `parent_student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parent_student` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_user_id` bigint(20) unsigned NOT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `relationship` varchar(30) NOT NULL DEFAULT 'guardian',
  `is_primary` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parent_student_parent_user_id_student_id_unique` (`parent_user_id`,`student_id`),
  KEY `parent_student_student_id_foreign` (`student_id`),
  CONSTRAINT `parent_student_parent_user_id_foreign` FOREIGN KEY (`parent_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `parent_student_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parent_student`
--

LOCK TABLES `parent_student` WRITE;
/*!40000 ALTER TABLE `parent_student` DISABLE KEYS */;
INSERT INTO `parent_student` VALUES (91,211,116,'Father',1,'active','2026-09-01 19:57:55','2026-09-01 19:57:55'),(92,211,118,'Father',1,'active','2026-09-03 01:24:39','2026-09-03 01:24:39'),(93,215,120,'Mother',1,'active','2026-09-08 19:00:25','2026-09-08 19:00:25');
/*!40000 ALTER TABLE `parent_student` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

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

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_invoices`
--

DROP TABLE IF EXISTS `payment_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` bigint(20) unsigned NOT NULL,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `amount_applied` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_invoices_payment_id_invoice_id_unique` (`payment_id`,`invoice_id`),
  KEY `payment_invoices_invoice_id_foreign` (`invoice_id`),
  CONSTRAINT `payment_invoices_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payment_invoices_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_invoices`
--

LOCK TABLES `payment_invoices` WRITE;
/*!40000 ALTER TABLE `payment_invoices` DISABLE KEYS */;
INSERT INTO `payment_invoices` VALUES (7,66,47,220.00,'2026-09-17 01:28:01','2026-09-17 01:28:01'),(9,68,49,60.00,'2026-09-17 18:21:34','2026-09-17 18:21:34');
/*!40000 ALTER TABLE `payment_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned DEFAULT NULL,
  `student_id` bigint(20) unsigned DEFAULT NULL,
  `payer_id` bigint(20) unsigned DEFAULT NULL,
  `payer_type` enum('student','parent','admin') NOT NULL DEFAULT 'student',
  `user_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `status` enum('pending','successful','failed','cancelled') NOT NULL DEFAULT 'pending',
  `receipt_number` varchar(255) DEFAULT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `proof_file_path` varchar(255) DEFAULT NULL,
  `payment_proof_notes` text DEFAULT NULL COMMENT 'Admin notes when reviewing manual bank transfer proof',
  `reference_no` varchar(255) DEFAULT NULL,
  `gateway_transaction_id` varchar(255) DEFAULT NULL COMMENT 'Transaction ID from online payment gateway (future use)',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_receipt_number_unique` (`receipt_number`),
  KEY `payments_user_id_foreign` (`user_id`),
  KEY `payments_course_id_foreign` (`course_id`),
  KEY `payments_invoice_id_foreign` (`invoice_id`),
  KEY `payments_student_id_foreign` (`student_id`),
  KEY `payments_payer_id_foreign` (`payer_id`),
  KEY `payments_verified_by_foreign` (`verified_by`),
  CONSTRAINT `payments_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_payer_id_foreign` FOREIGN KEY (`payer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (66,NULL,118,212,'student',212,NULL,'Manual Bank Transfer – INV-2026-00001',220.00,'successful','RCP-2026-00001',1,'bank_transfer','payment-proofs/o3TmsCbrS2sQOq9X4QofUdZgvvGhgz0Rqqnt3Pst.pdf','Verified by admin',NULL,NULL,'2026-09-17 01:40:02','2026-09-17 01:28:01','2026-09-17 01:40:02'),(68,NULL,120,214,'student',214,NULL,'Manual Bank Transfer – INV-2026-00003',60.00,'successful','RCP-2026-00003',1,'bank_transfer','payment-proofs/vbu2wHcvoeDH7aKsd8HsABKqjItJPdUwULQ8SGct.pdf',NULL,NULL,NULL,'2026-09-17 18:22:24','2026-09-17 18:21:34','2026-09-17 18:22:24');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `quiz_id` bigint(20) unsigned NOT NULL,
  `type` enum('text','image') NOT NULL DEFAULT 'text',
  `question_text` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct_option` char(1) NOT NULL,
  `explanation` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `questions_quiz_id_foreign` (`quiz_id`),
  CONSTRAINT `questions_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questions`
--

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quizzes`
--

DROP TABLE IF EXISTS `quizzes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quizzes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `total_questions` int(11) NOT NULL DEFAULT 10,
  `time_limit_minutes` int(11) NOT NULL DEFAULT 15,
  `passing_marks` int(11) NOT NULL DEFAULT 60,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quizzes_course_id_foreign` (`course_id`),
  CONSTRAINT `quizzes_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quizzes`
--

LOCK TABLES `quizzes` WRITE;
/*!40000 ALTER TABLE `quizzes` DISABLE KEYS */;
/*!40000 ALTER TABLE `quizzes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recorded_classes`
--

DROP TABLE IF EXISTS `recorded_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recorded_classes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `topic_name` varchar(255) NOT NULL,
  `video_url` varchar(255) NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `duration_minutes` int(11) NOT NULL DEFAULT 45,
  `views_count` int(11) NOT NULL DEFAULT 120,
  `summary` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recorded_classes_course_id_foreign` (`course_id`),
  KEY `recorded_classes_subject_id_foreign` (`subject_id`),
  CONSTRAINT `recorded_classes_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `recorded_classes_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recorded_classes`
--

LOCK TABLES `recorded_classes` WRITE;
/*!40000 ALTER TABLE `recorded_classes` DISABLE KEYS */;
/*!40000 ALTER TABLE `recorded_classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registrations`
--

DROP TABLE IF EXISTS `registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `register_as` varchar(255) NOT NULL DEFAULT 'student',
  `student_name` varchar(255) NOT NULL,
  `parent_name` varchar(255) DEFAULT NULL,
  `educator_name` varchar(255) DEFAULT NULL,
  `school_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `programme` varchar(255) NOT NULL,
  `level` varchar(255) DEFAULT NULL,
  `subjects` text DEFAULT NULL,
  `schedule` varchar(255) DEFAULT NULL,
  `promo_code` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `registrations_user_id_foreign` (`user_id`),
  CONSTRAINT `registrations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registrations`
--

LOCK TABLES `registrations` WRITE;
/*!40000 ALTER TABLE `registrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `registrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

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

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('1oU81FVi3gei8WnOhKLLAFFX18efJ8PW9JF8fb4p',NULL,'127.0.0.1','curl/8.13.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOVdudzFoekIzbXo5Q05EeTRLc3RiaFJEVDVvbWMxaFRVVWVIbzVBNyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hYm91dCI7czo1OiJyb3V0ZSI7czo1OiJhYm91dCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1790847587),('2mCBXBX93dHgPPoQQT650vzz5ZYp7M43ANL0dBAX',NULL,'127.0.0.1','curl/8.13.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOEFhdUZLeGl2bU5qNTFwbnlrVmc2NXp4eXRkZmJSeWg4cXQ5bXExaiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9wcm9ncmFtbWVzIjtzOjU6InJvdXRlIjtzOjEwOiJwcm9ncmFtbWVzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790847588),('2vWzn63aIteeveEa7uYWVTvuiqdKIILNY46q51k6',NULL,'127.0.0.1','curl/8.13.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiT1YxejBJUjVPRm1tb01reHhCc2lEMXM2eTdTVHBSZGpNVk44bGx2ayI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9wcmljaW5nIjtzOjU6InJvdXRlIjtzOjc6InByaWNpbmciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1790847588),('ggpwNFd2wBI2k4KqfDdrUMsDsDmEA9X3MT5d1Fni',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRzUxZ3R2cmFFbHA3bFcxOXNSWFVid0RINVhCcVgxcG5yQ2R0MXYxdiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9fQ==',1790848616),('ivkny3868L4mM4zdrN2Z3qvHpILfurly0ThcToqZ',NULL,'127.0.0.1','curl/8.13.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSnhDTUJmdVhSSDVXbHNmN1dHVjFod1k5aGI0OUhKR2x4dHVubjlzcCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1790847572),('Mu2SLLQVKfXIuU8wy6BD8pwKV3Iy1N65UqCE9HhD',NULL,'127.0.0.1','curl/8.13.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoicUNwbkt3eXNYTHU1blBTN0FzS3Buem9NSEFLZDNxVHhOZG4wN1pNTiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ibG9nIjtzOjU6InJvdXRlIjtzOjQ6ImJsb2ciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1790847588),('T9PUC6mvogmL1AetTzneRcjvdp3A8jepY47YvHgq',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVUhYN2t1aUlMOUtndG8zNXh5VHU4clFidE1wdFMySTZtdTlPSzJseCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9jaGF0L3VucmVhZC1jb3VudCI7czo1OiJyb3V0ZSI7czoxNzoiY2hhdC51bnJlYWQtY291bnQiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1790848628),('YBRu4afDk2ptTX7PhABxUnPWjT54XYnJQL6ssJLP',NULL,'127.0.0.1','curl/8.13.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRWU3NmYwQkhaU1psb3BSOGJDTklWMlFIWVpGVTYwUERKQkJPU21XOSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC90ZWFjaGVycyI7czo1OiJyb3V0ZSI7czo4OiJ0ZWFjaGVycyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1790847588);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `group` varchar(255) NOT NULL DEFAULT 'general',
  `label` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'platform_name','OlympiaScholar','general','Platform Name','2026-08-17 20:05:35','2026-08-17 20:05:35'),(2,'platform_tagline','Excel Beyond Limits','general','Platform Tagline','2026-08-17 20:05:35','2026-08-17 20:05:35'),(3,'support_email','support@olympiascholar.com','general','Support Email','2026-08-17 20:05:35','2026-08-17 20:05:35'),(4,'support_phone','+60 12-345 6789','general','Support Phone / WhatsApp','2026-08-17 20:05:35','2026-08-17 20:05:35'),(5,'support_whatsapp_link','https://wa.me/60123456789','general','WhatsApp Link','2026-08-17 20:05:35','2026-08-17 20:05:35'),(6,'contact_address','Kuala Lumpur, Malaysia','general','Business Address','2026-08-17 20:05:35','2026-08-17 20:05:35'),(7,'teacher_cut_pct','65','financial','Teacher Revenue Cut (%)','2026-08-17 20:05:35','2026-08-17 20:05:35'),(8,'currency_code','MYR','financial','Currency Code','2026-08-17 20:05:35','2026-08-17 20:05:35'),(9,'currency_symbol','RM','financial','Currency Symbol','2026-08-17 20:05:35','2026-08-17 20:05:35'),(10,'allow_registrations','1','features','Allow Public Registrations','2026-08-17 20:05:35','2026-08-17 20:05:35'),(11,'allow_gig_claims','1','features','Allow Teacher Gig Claiming','2026-08-17 20:05:35','2026-08-17 20:05:35'),(12,'maintenance_mode','0','features','Maintenance Mode','2026-08-17 20:05:35','2026-08-17 20:05:35'),(13,'free_trial_enabled','1','features','Free Trial Class Enabled','2026-08-17 20:05:35','2026-08-17 20:05:35'),(14,'default_temp_password','Temp@1234','security','Default Temporary Password','2026-08-17 20:05:35','2026-08-17 20:05:35'),(15,'plan_monthly_price','109','pricing','Monthly Plan — Display Price','2026-08-17 20:05:35','2026-09-28 04:06:07'),(16,'plan_monthly_per','/ 4 subjects','pricing','Monthly Plan — Billing Period','2026-08-17 20:05:35','2026-09-28 04:06:07'),(17,'plan_monthly_desc','Access to 1 subject with live classes 4x/month','pricing','Monthly Plan — Description','2026-08-17 20:05:35','2026-08-17 20:05:35'),(18,'plan_subject_price','250','pricing','Subject Package — Display Price','2026-08-17 20:05:35','2026-09-28 04:06:07'),(19,'plan_subject_per','/ 6 subjects','pricing','Subject Package — Billing Period','2026-08-17 20:05:35','2026-09-28 04:06:07'),(20,'plan_subject_desc','2-4 subjects bundled with priority support','pricing','Subject Package — Description','2026-08-17 20:05:35','2026-08-17 20:05:35'),(21,'plan_premium_price','530','pricing','Premium Plan — Display Price','2026-08-17 20:05:35','2026-09-28 04:06:07'),(22,'plan_premium_per','for all access','pricing','Premium Plan — Billing Period','2026-08-17 20:05:35','2026-09-28 04:06:07'),(23,'plan_premium_desc','Unlimited subjects, 1-on-1 tutoring and 24/7 WhatsApp support','pricing','Premium Plan — Description','2026-08-17 20:05:35','2026-08-17 20:05:35'),(24,'company_bank_name','Bank Islam','finance','Company Bank Name','2026-09-01 20:55:50','2026-09-01 22:53:25'),(25,'company_bank_account_name','Olympia Academy Sdn Bhd','finance','Company Account Name','2026-09-01 20:55:50','2026-09-01 22:53:25'),(26,'company_bank_account_number','5642 8901 2345','finance','Company Account Number','2026-09-01 20:55:51','2026-09-01 20:55:51'),(27,'company_bank_instructions','Please include your Student Name & Invoice No in the transfer reference.','finance','Transfer Instructions','2026-09-01 20:55:51','2026-09-01 20:55:51'),(28,'gateway_provider','mock_fpx','finance','Gateway Provider','2026-09-01 20:55:51','2026-09-01 22:53:25'),(29,'gateway_merchant_id','OSC_MERCHANT_8892','finance','Gateway Merchant ID','2026-09-01 20:55:51','2026-09-01 20:55:51'),(30,'gateway_sandbox_mode','0','finance','Gateway Sandbox Mode','2026-09-01 20:55:51','2026-09-01 22:53:25'),(31,'billing.invoice_due_days','7','billing','Invoice Due Days (after issue date)','2026-09-02 20:11:05','2026-09-02 20:11:05'),(32,'billing.email_sender_name','Olympia Scholars','billing','Email Sender Name','2026-09-02 20:11:05','2026-09-02 20:11:05'),(33,'billing.reminder_invoice_issued','1','billing','Send Invoice Issued Email','2026-09-02 20:11:05','2026-09-02 20:11:05'),(34,'billing.reminder_due_7_days','1','billing','Send 7-Day Before Due Reminder','2026-09-02 20:11:05','2026-09-02 20:11:05'),(35,'billing.reminder_due_3_days','1','billing','Send 3-Day Before Due Reminder','2026-09-02 20:11:05','2026-09-02 20:11:05'),(36,'billing.reminder_due_today','1','billing','Send Due Today Reminder','2026-09-02 20:11:05','2026-09-02 20:11:05'),(37,'billing.reminder_overdue_1_day','1','billing','Send 1-Day Overdue Reminder','2026-09-02 20:11:05','2026-09-02 20:11:05'),(38,'billing.reminder_overdue_3_days','1','billing','Send 3-Day Overdue Reminder','2026-09-02 20:11:05','2026-09-02 20:11:05'),(39,'billing.reminder_overdue_7_days','1','billing','Send 7-Day Overdue Reminder','2026-09-02 20:11:05','2026-09-02 20:11:05'),(40,'billing.bank_name','Maybank Islamic Berhad','billing','Bank Name','2026-09-02 20:11:05','2026-09-02 20:16:10'),(41,'billing.bank_account_number','5642 8901 2345','billing','Bank Account Number','2026-09-02 20:11:05','2026-09-02 20:16:10'),(42,'billing.bank_account_name','Olympia Scholars Academy Sdn Bhd','billing','Bank Account Name','2026-09-02 20:11:05','2026-09-02 20:16:10'),(43,'billing.payment_instructions','Please include your Student Name & Invoice No in the transfer reference, then upload proof of payment.','billing','Additional Payment Instructions','2026-09-02 20:11:05','2026-09-02 20:16:10'),(44,'parent_greeting_enabled','1','content','Parent Greeting Popup Enabled','2026-09-17 00:28:01','2026-09-17 18:25:22'),(45,'parent_greeting_image','images/welcome-parent-greeting.jpg','general','','2026-09-17 00:28:01','2026-09-17 00:28:01'),(46,'parent_greeting_title','Selamat Datang Ke Olympia Scholars','content','Parent Greeting Popup Title','2026-09-17 00:28:01','2026-09-17 18:25:22'),(47,'parent_greeting_body','We are thrilled to partner with you in nurturing your child\'s academic growth, curiosity, and confidence. Here is everything you need to stay connected with their learning journey.','content','Parent Greeting Popup Message','2026-09-17 00:28:01','2026-09-17 18:25:22');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_assignment_submissions`
--

DROP TABLE IF EXISTS `student_assignment_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_assignment_submissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_assignment_id` bigint(20) unsigned NOT NULL,
  `assignment_id` bigint(20) unsigned NOT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `status` enum('draft','submitted','graded','returned') NOT NULL DEFAULT 'draft',
  `current_page` int(10) unsigned NOT NULL DEFAULT 1,
  `zoom_level` decimal(4,2) NOT NULL DEFAULT 1.00,
  `total_pages` int(10) unsigned NOT NULL DEFAULT 1,
  `version` int(10) unsigned NOT NULL DEFAULT 1,
  `final_pdf_path` varchar(255) DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `last_saved_at` timestamp NULL DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_assignment_submissions_student_assignment_id_unique` (`student_assignment_id`),
  KEY `idx_sub_student_status` (`student_id`,`status`),
  KEY `idx_sub_assignment` (`assignment_id`),
  CONSTRAINT `student_assignment_submissions_assignment_id_foreign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_assignment_submissions_student_assignment_id_foreign` FOREIGN KEY (`student_assignment_id`) REFERENCES `student_assignments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_assignment_submissions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_assignment_submissions`
--

LOCK TABLES `student_assignment_submissions` WRITE;
/*!40000 ALTER TABLE `student_assignment_submissions` DISABLE KEYS */;
INSERT INTO `student_assignment_submissions` VALUES (1,29,19,116,'submitted',1,1.00,1,1,'submissions/29/final_submission_1790752370.pdf','2026-09-30 07:11:51',NULL,'2026-09-30 07:12:50','2026-09-30 07:11:51','2026-09-30 07:12:50'),(2,30,19,118,'submitted',1,1.00,44,1,'submissions/30/final_submission_1790752637.pdf','2026-09-30 07:16:30','2026-09-30 07:17:12','2026-09-30 07:17:17','2026-09-30 07:16:30','2026-09-30 07:17:17'),(3,28,19,120,'submitted',1,1.00,44,1,'submissions/28/final_submission_1790753096.pdf','2026-09-30 07:24:38','2026-09-30 07:24:52','2026-09-30 07:24:56','2026-09-30 07:24:38','2026-09-30 07:24:56'),(4,26,19,1,'submitted',1,1.00,44,1,'submissions/26/final_submission_1790761909.pdf','2026-09-30 09:28:26','2026-09-30 09:51:27','2026-09-30 09:51:50','2026-09-30 09:28:26','2026-09-30 09:51:50'),(5,36,21,116,'submitted',1,1.00,9,1,'submissions/36/final_submission_1790847940.pdf','2026-10-01 06:43:15','2026-10-01 08:55:49','2026-10-01 09:45:40','2026-10-01 06:43:15','2026-10-01 09:45:40');
/*!40000 ALTER TABLE `student_assignment_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_assignments`
--

DROP TABLE IF EXISTS `student_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `assignment_id` bigint(20) unsigned NOT NULL,
  `status` enum('pending','submitted','graded','late') NOT NULL DEFAULT 'pending',
  `score` int(11) DEFAULT NULL,
  `tahap_kepandaian` varchar(50) DEFAULT NULL,
  `time_taken_seconds` int(11) DEFAULT NULL,
  `submission_notes` text DEFAULT NULL,
  `answers_json` text DEFAULT NULL,
  `teacher_feedback` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_assignments_student_id_foreign` (`student_id`),
  KEY `student_assignments_assignment_id_foreign` (`assignment_id`),
  CONSTRAINT `student_assignments_assignment_id_foreign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_assignments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_assignments`
--

LOCK TABLES `student_assignments` WRITE;
/*!40000 ALTER TABLE `student_assignments` DISABLE KEYS */;
INSERT INTO `student_assignments` VALUES (21,116,18,'graded',100,'Cemerlang',96,NULL,'{\"1\":{\"section\":\"A\",\"type\":\"mcq\",\"question_number\":1,\"title\":\"Question 1\",\"student_choice\":\"B\",\"correct_option\":\"B\",\"is_correct\":true},\"2\":{\"section\":\"A\",\"type\":\"mcq\",\"question_number\":2,\"title\":\"Question 2\",\"student_choice\":\"A\",\"correct_option\":\"A\",\"is_correct\":true},\"3\":{\"section\":\"A\",\"type\":\"mcq\",\"question_number\":3,\"title\":\"Question 3\",\"student_choice\":\"B\",\"correct_option\":\"B\",\"is_correct\":true},\"4\":{\"section\":\"A\",\"type\":\"mcq\",\"question_number\":4,\"title\":\"Question 4\",\"student_choice\":\"B\",\"correct_option\":\"B\",\"is_correct\":true},\"5\":{\"section\":\"A\",\"type\":\"mcq\",\"question_number\":5,\"title\":\"Question 5\",\"student_choice\":\"C\",\"correct_option\":\"C\",\"is_correct\":true},\"6\":{\"section\":\"A\",\"type\":\"mcq\",\"question_number\":6,\"title\":\"Question 6\",\"student_choice\":\"B\",\"correct_option\":\"B\",\"is_correct\":true},\"7\":{\"section\":\"A\",\"type\":\"mcq\",\"question_number\":7,\"title\":\"Question 7\",\"student_choice\":\"C\",\"correct_option\":\"C\",\"is_correct\":true},\"8\":{\"section\":\"A\",\"type\":\"mcq\",\"question_number\":8,\"title\":\"Question 8\",\"student_choice\":\"B\",\"correct_option\":\"B\",\"is_correct\":true},\"9\":{\"section\":\"A\",\"type\":\"mcq\",\"question_number\":9,\"title\":\"Question 9\",\"student_choice\":\"B\",\"correct_option\":\"B\",\"is_correct\":true},\"10\":{\"section\":\"A\",\"type\":\"mcq\",\"question_number\":10,\"title\":\"Question 10\",\"student_choice\":\"B\",\"correct_option\":\"B\",\"is_correct\":true}}',NULL,NULL,NULL,'2026-09-27 18:35:44','2026-09-23 01:24:33','2026-09-27 18:35:44'),(22,118,18,'graded',100,'Cemerlang',19,NULL,'{\"1\":{\"section\":\"A\",\"type\":\"mcq\",\"question_number\":1,\"title\":\"Question 1\",\"student_choice\":\"B\",\"correct_option\":\"B\",\"is_correct\":true},\"2\":{\"section\":\"A\",\"type\":\"mcq\",\"question_number\":2,\"title\":\"Question 2\",\"student_choice\":\"A\",\"correct_option\":\"A\",\"is_correct\":true},\"3\":{\"section\":\"A\",\"type\":\"mcq\",\"question_number\":3,\"title\":\"Question 3\",\"student_choice\":\"B\",\"correct_option\":\"B\",\"is_correct\":true},\"4\":{\"section\":\"A\",\"type\":\"mcq\",\"question_number\":4,\"title\":\"Question 4\",\"student_choice\":\"B\",\"correct_option\":\"B\",\"is_correct\":true},\"5\":{\"section\":\"A\",\"type\":\"mcq\",\"question_number\":5,\"title\":\"Question 5\",\"student_choice\":\"C\",\"correct_option\":\"C\",\"is_correct\":true},\"6\":{\"section\":\"A\",\"type\":\"mcq\",\"question_number\":6,\"title\":\"Question 6\",\"student_choice\":\"B\",\"correct_option\":\"B\",\"is_correct\":true},\"7\":{\"section\":\"A\",\"type\":\"mcq\",\"question_number\":7,\"title\":\"Question 7\",\"student_choice\":\"C\",\"correct_option\":\"C\",\"is_correct\":true},\"8\":{\"section\":\"A\",\"type\":\"mcq\",\"question_number\":8,\"title\":\"Question 8\",\"student_choice\":\"B\",\"correct_option\":\"B\",\"is_correct\":true},\"9\":{\"section\":\"A\",\"type\":\"mcq\",\"question_number\":9,\"title\":\"Question 9\",\"student_choice\":\"B\",\"correct_option\":\"B\",\"is_correct\":true},\"10\":{\"section\":\"A\",\"type\":\"mcq\",\"question_number\":10,\"title\":\"Question 10\",\"student_choice\":\"B\",\"correct_option\":\"B\",\"is_correct\":true}}',NULL,NULL,NULL,'2026-09-23 01:25:16','2026-09-23 01:24:33','2026-09-23 01:25:16'),(24,120,18,'pending',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-23 01:24:33','2026-09-23 01:24:33'),(25,1,18,'pending',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-23 01:45:36','2026-09-23 01:45:36'),(26,1,19,'submitted',100,NULL,NULL,NULL,NULL,'Bagus awak ni','submissions/26/final_submission_1790761909.pdf','interactive_worksheet.pdf','2026-09-30 09:51:50','2026-09-30 07:10:53','2026-09-30 09:52:27'),(28,120,19,'graded',90,NULL,NULL,NULL,NULL,'BAGUS LEBIHKAN EFFORT!','submissions/28/final_submission_1790753096.pdf','interactive_worksheet.pdf','2026-09-30 07:24:56','2026-09-30 07:10:53','2026-09-30 07:25:43'),(29,116,19,'submitted',NULL,NULL,NULL,NULL,NULL,NULL,'submissions/29/final_submission_1790752370.pdf','interactive_worksheet.pdf','2026-09-30 07:12:50','2026-09-30 07:10:53','2026-09-30 07:12:50'),(30,118,19,'graded',100,NULL,NULL,NULL,NULL,'Bagus je weh tu.','submissions/30/final_submission_1790752637.pdf','interactive_worksheet.pdf','2026-09-30 07:17:17','2026-09-30 07:10:53','2026-09-30 07:18:24'),(35,1,21,'pending',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-01 06:42:35','2026-10-01 06:42:35'),(36,116,21,'submitted',NULL,NULL,NULL,NULL,NULL,NULL,'submissions/36/final_submission_1790847940.pdf','interactive_worksheet.pdf','2026-10-01 09:45:40','2026-10-01 06:42:35','2026-10-01 09:45:40'),(37,118,21,'pending',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-01 06:42:35','2026-10-01 06:42:35'),(38,120,21,'pending',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-01 06:42:35','2026-10-01 06:42:35');
/*!40000 ALTER TABLE `student_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_attendances`
--

DROP TABLE IF EXISTS `student_attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_attendances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `live_class_id` bigint(20) unsigned NOT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'pending_verification',
  `joined_at` timestamp NULL DEFAULT NULL,
  `left_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_attendances_student_id_live_class_id_unique` (`student_id`,`live_class_id`),
  KEY `student_attendances_live_class_id_foreign` (`live_class_id`),
  KEY `student_attendances_verified_by_foreign` (`verified_by`),
  CONSTRAINT `student_attendances_live_class_id_foreign` FOREIGN KEY (`live_class_id`) REFERENCES `live_classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_attendances_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_attendances_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_attendances`
--

LOCK TABLES `student_attendances` WRITE;
/*!40000 ALTER TABLE `student_attendances` DISABLE KEYS */;
INSERT INTO `student_attendances` VALUES (3,1,4,'attended','2026-08-17 11:02:00',NULL,NULL,NULL,NULL,'2026-08-18 23:30:40','2026-08-18 23:30:40'),(4,1,5,'attended','2026-08-14 11:02:00',NULL,NULL,NULL,NULL,'2026-08-18 23:30:40','2026-08-18 23:30:40'),(5,1,6,'attended','2026-08-15 11:02:00',NULL,NULL,NULL,NULL,'2026-08-18 23:30:40','2026-08-18 23:30:40');
/*!40000 ALTER TABLE `student_attendances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_quizzes`
--

DROP TABLE IF EXISTS `student_quizzes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_quizzes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `quiz_id` bigint(20) unsigned NOT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `total_questions` int(11) NOT NULL DEFAULT 10,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_quizzes_student_id_foreign` (`student_id`),
  KEY `student_quizzes_quiz_id_foreign` (`quiz_id`),
  CONSTRAINT `student_quizzes_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_quizzes_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_quizzes`
--

LOCK TABLES `student_quizzes` WRITE;
/*!40000 ALTER TABLE `student_quizzes` DISABLE KEYS */;
INSERT INTO `student_quizzes` VALUES (1,1,1,1,1,'2026-08-26 23:08:15','2026-08-18 19:03:32','2026-08-26 23:08:15'),(2,1,2,1,1,'2026-08-18 19:16:39','2026-08-18 19:16:39','2026-08-18 19:16:39');
/*!40000 ALTER TABLE `student_quizzes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_subtopic_progress`
--

DROP TABLE IF EXISTS `student_subtopic_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_subtopic_progress` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `subtopic_id` bigint(20) unsigned NOT NULL,
  `status` enum('Mastered','In Progress','Needs Practice','Not Started') NOT NULL DEFAULT 'Not Started',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_subtopic_progress_student_id_subtopic_id_unique` (`student_id`,`subtopic_id`),
  KEY `student_subtopic_progress_subtopic_id_foreign` (`subtopic_id`),
  CONSTRAINT `student_subtopic_progress_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_subtopic_progress_subtopic_id_foreign` FOREIGN KEY (`subtopic_id`) REFERENCES `subject_subtopics` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6901 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_subtopic_progress`
--

LOCK TABLES `student_subtopic_progress` WRITE;
/*!40000 ALTER TABLE `student_subtopic_progress` DISABLE KEYS */;
INSERT INTO `student_subtopic_progress` VALUES (3561,1,187,'Mastered','2026-08-21 00:49:47','2026-08-21 00:49:47'),(3581,1,188,'In Progress','2026-08-21 00:49:47','2026-08-21 00:49:47'),(3601,1,189,'In Progress','2026-08-21 00:49:47','2026-08-21 00:49:47'),(3621,1,190,'Mastered','2026-08-21 00:49:47','2026-08-21 00:49:47'),(3641,1,191,'In Progress','2026-08-21 00:49:47','2026-08-21 00:49:47'),(3681,1,193,'Needs Practice','2026-08-21 00:49:47','2026-08-21 00:49:47'),(3701,1,194,'Mastered','2026-08-21 00:49:47','2026-08-21 00:49:47'),(3721,1,195,'Mastered','2026-08-21 00:49:47','2026-08-21 00:49:47'),(3741,1,196,'In Progress','2026-08-21 00:49:47','2026-08-21 00:49:47'),(3761,1,197,'Mastered','2026-08-21 00:49:47','2026-08-21 00:49:47'),(3781,1,198,'In Progress','2026-08-21 00:49:47','2026-08-21 00:49:47'),(3801,1,199,'In Progress','2026-08-21 00:49:47','2026-08-21 00:49:47'),(3821,1,200,'In Progress','2026-08-21 00:49:47','2026-08-21 00:49:47'),(3841,1,201,'Mastered','2026-08-21 00:49:48','2026-08-21 00:49:48'),(3861,1,202,'In Progress','2026-08-21 00:49:48','2026-08-21 00:49:48'),(3881,1,203,'Mastered','2026-08-21 00:49:48','2026-08-21 00:49:48'),(3901,1,204,'In Progress','2026-08-21 00:49:48','2026-08-21 00:49:48'),(3921,1,205,'Mastered','2026-08-21 00:49:48','2026-08-21 00:49:48'),(3941,1,206,'Mastered','2026-08-21 00:49:48','2026-08-21 00:49:48'),(3961,1,207,'In Progress','2026-08-21 00:49:48','2026-08-21 00:49:48'),(3981,1,208,'Mastered','2026-08-21 00:49:48','2026-08-21 00:49:48'),(4001,1,209,'Mastered','2026-08-21 00:49:48','2026-08-21 00:49:48'),(4021,1,210,'In Progress','2026-08-21 00:49:48','2026-08-21 00:49:48'),(4041,1,211,'Mastered','2026-08-21 00:49:48','2026-08-21 00:49:48'),(4061,1,212,'In Progress','2026-08-21 00:49:48','2026-08-21 00:49:48'),(4081,1,213,'Needs Practice','2026-08-21 00:49:48','2026-08-21 00:49:48'),(4101,1,214,'Mastered','2026-08-21 00:49:48','2026-08-21 00:49:48'),(4121,1,215,'Mastered','2026-08-21 00:49:48','2026-08-21 00:49:48'),(4141,1,216,'In Progress','2026-08-21 00:49:48','2026-08-21 00:49:48'),(4161,1,217,'In Progress','2026-08-21 00:49:48','2026-08-21 00:49:48'),(4181,1,218,'In Progress','2026-08-21 00:49:48','2026-08-21 00:49:48'),(4201,1,219,'Needs Practice','2026-08-21 00:49:48','2026-08-21 00:49:48'),(4221,1,220,'Mastered','2026-08-21 00:49:48','2026-08-21 00:49:48'),(4241,1,221,'In Progress','2026-08-21 00:49:48','2026-08-21 00:49:48'),(4261,1,222,'In Progress','2026-08-21 00:49:48','2026-08-21 00:49:48'),(4281,1,223,'Mastered','2026-08-21 00:49:49','2026-08-21 00:49:49'),(4301,1,224,'In Progress','2026-08-21 00:49:49','2026-08-21 00:49:49'),(4321,1,225,'Mastered','2026-08-21 00:49:49','2026-08-21 00:49:49'),(4341,1,226,'Needs Practice','2026-08-21 00:49:49','2026-08-21 00:49:49'),(4361,1,227,'Mastered','2026-08-21 00:49:49','2026-08-21 00:49:49'),(4381,1,228,'In Progress','2026-08-21 00:49:49','2026-08-21 00:49:49'),(4401,1,229,'Needs Practice','2026-08-21 00:49:49','2026-08-21 00:49:49'),(4421,1,230,'Mastered','2026-08-21 00:49:49','2026-08-21 00:49:49'),(4441,1,231,'Mastered','2026-08-21 00:49:49','2026-08-21 00:49:49'),(4461,1,232,'In Progress','2026-08-21 00:49:49','2026-08-21 00:49:49'),(4481,1,233,'Mastered','2026-08-21 00:49:49','2026-08-21 00:49:49'),(4501,1,234,'Mastered','2026-08-21 00:49:49','2026-08-21 00:49:49'),(4521,1,235,'In Progress','2026-08-21 00:49:49','2026-08-21 00:49:49'),(4541,1,236,'Needs Practice','2026-08-21 00:49:50','2026-08-21 00:49:50'),(4561,1,237,'In Progress','2026-08-21 00:49:50','2026-08-21 00:49:50'),(4581,1,238,'Mastered','2026-08-21 00:49:50','2026-08-21 00:49:50'),(4601,1,239,'Mastered','2026-08-21 00:49:50','2026-08-21 00:49:50'),(4621,1,240,'Mastered','2026-08-21 00:49:50','2026-08-21 00:49:50'),(4641,1,241,'In Progress','2026-08-21 00:49:50','2026-08-21 00:49:50'),(4661,1,242,'Mastered','2026-08-21 00:49:50','2026-08-21 00:49:50'),(4681,1,243,'In Progress','2026-08-21 00:49:50','2026-08-21 00:49:50'),(4701,1,244,'In Progress','2026-08-21 00:49:50','2026-08-21 00:49:50'),(4721,1,245,'Mastered','2026-08-21 00:49:50','2026-08-21 00:49:50'),(4741,1,246,'In Progress','2026-08-21 00:49:50','2026-08-21 00:49:50'),(4761,1,247,'Needs Practice','2026-08-21 00:49:50','2026-08-21 00:49:50'),(4781,1,248,'Mastered','2026-08-21 00:49:50','2026-08-21 00:49:50'),(4801,1,249,'In Progress','2026-08-21 00:49:50','2026-08-21 00:49:50'),(4821,1,250,'Needs Practice','2026-08-21 00:49:50','2026-08-21 00:49:50'),(4841,1,251,'Mastered','2026-08-21 00:49:50','2026-08-21 00:49:50'),(4861,1,252,'In Progress','2026-08-21 00:49:51','2026-08-21 00:49:51'),(4881,1,253,'Needs Practice','2026-08-21 00:49:51','2026-08-21 00:49:51'),(4901,1,254,'Mastered','2026-08-21 00:49:51','2026-08-21 00:49:51'),(4921,1,255,'In Progress','2026-08-21 00:49:51','2026-08-21 00:49:51'),(4941,1,256,'Mastered','2026-08-21 00:49:51','2026-08-21 00:49:51'),(4961,1,257,'In Progress','2026-08-21 00:49:51','2026-08-21 00:49:51'),(4981,1,258,'In Progress','2026-08-21 00:49:51','2026-08-21 00:49:51'),(5001,1,259,'Mastered','2026-08-21 00:49:51','2026-08-21 00:49:51'),(5021,1,260,'In Progress','2026-08-21 00:49:51','2026-08-21 00:49:51'),(5041,1,261,'Needs Practice','2026-08-21 00:49:51','2026-08-21 00:49:51'),(5061,1,262,'In Progress','2026-08-21 00:49:51','2026-08-21 00:49:51'),(5081,1,263,'In Progress','2026-08-21 00:49:51','2026-08-21 00:49:51'),(5101,1,264,'Mastered','2026-08-21 00:49:51','2026-08-21 00:49:51'),(5121,1,265,'Mastered','2026-08-21 00:49:51','2026-08-21 00:49:51'),(5141,1,266,'In Progress','2026-08-21 00:49:51','2026-08-21 00:49:51'),(5161,1,267,'Mastered','2026-08-21 00:49:51','2026-08-21 00:49:51'),(5181,1,268,'Mastered','2026-08-21 00:49:51','2026-08-21 00:49:51'),(5201,1,269,'Mastered','2026-08-21 00:49:51','2026-08-21 00:49:51'),(5221,1,270,'Mastered','2026-08-21 00:49:51','2026-08-21 00:49:51'),(5241,1,271,'Mastered','2026-08-21 00:49:51','2026-08-21 00:49:51'),(5261,1,272,'In Progress','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5281,1,273,'Mastered','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5301,1,274,'In Progress','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5321,1,275,'In Progress','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5341,1,276,'Mastered','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5361,1,277,'In Progress','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5381,1,278,'In Progress','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5401,1,279,'In Progress','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5421,1,280,'Needs Practice','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5441,1,281,'Needs Practice','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5461,1,282,'Needs Practice','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5481,1,283,'In Progress','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5501,1,284,'In Progress','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5521,1,285,'Mastered','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5541,1,286,'Mastered','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5561,1,287,'In Progress','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5581,1,288,'Mastered','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5601,1,289,'In Progress','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5621,1,290,'In Progress','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5641,1,291,'Needs Practice','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5661,1,292,'In Progress','2026-08-21 00:49:52','2026-08-21 00:49:52'),(5681,1,293,'In Progress','2026-08-21 00:49:53','2026-08-21 00:49:53'),(5701,1,294,'Needs Practice','2026-08-21 00:49:53','2026-08-21 00:49:53'),(5721,1,295,'Needs Practice','2026-08-21 00:49:53','2026-08-21 00:49:53'),(5741,1,296,'In Progress','2026-08-21 00:49:53','2026-08-21 00:49:53'),(5761,1,297,'Needs Practice','2026-08-21 00:49:53','2026-08-21 00:49:53'),(5781,1,298,'In Progress','2026-08-21 00:49:53','2026-08-21 00:49:53'),(5801,1,299,'Mastered','2026-08-21 00:49:53','2026-08-21 00:49:53'),(5821,1,300,'Mastered','2026-08-21 00:49:53','2026-08-21 00:49:53'),(5841,1,301,'In Progress','2026-08-21 00:49:53','2026-08-21 00:49:53'),(5861,1,302,'In Progress','2026-08-21 00:49:53','2026-08-21 00:49:53'),(5881,1,303,'Mastered','2026-08-21 00:49:53','2026-08-21 00:49:53'),(5901,1,304,'In Progress','2026-08-21 00:49:53','2026-08-21 00:49:53'),(5921,1,305,'Needs Practice','2026-08-21 00:49:53','2026-08-21 00:49:53'),(5941,1,306,'In Progress','2026-08-21 00:49:53','2026-08-21 00:49:53'),(5961,1,307,'Needs Practice','2026-08-21 00:49:53','2026-08-21 00:49:53'),(5981,1,308,'Mastered','2026-08-21 00:49:53','2026-08-21 00:49:53'),(6001,1,309,'In Progress','2026-08-21 00:49:53','2026-08-21 00:49:53'),(6021,1,310,'Needs Practice','2026-08-21 00:49:53','2026-08-21 00:49:53'),(6041,1,311,'Needs Practice','2026-08-21 00:49:53','2026-08-21 00:49:53'),(6061,1,312,'Mastered','2026-08-21 00:49:53','2026-08-21 00:49:53'),(6081,1,313,'Mastered','2026-08-21 00:49:53','2026-08-21 00:49:53'),(6101,1,314,'In Progress','2026-08-21 00:49:54','2026-08-21 00:49:54'),(6121,1,315,'In Progress','2026-08-21 00:49:54','2026-08-21 00:49:54'),(6141,1,316,'Mastered','2026-08-21 00:49:54','2026-08-21 00:49:54'),(6161,1,317,'In Progress','2026-08-21 00:49:54','2026-08-21 00:49:54'),(6181,1,318,'In Progress','2026-08-21 00:49:54','2026-08-21 00:49:54'),(6201,1,319,'Needs Practice','2026-08-21 00:49:54','2026-08-21 00:49:54'),(6221,1,320,'In Progress','2026-08-21 00:49:54','2026-08-21 00:49:54'),(6241,1,321,'Needs Practice','2026-08-21 00:49:54','2026-08-21 00:49:54'),(6261,1,322,'In Progress','2026-08-21 00:49:54','2026-08-21 00:49:54'),(6281,1,323,'Needs Practice','2026-08-21 00:49:54','2026-08-21 00:49:54'),(6301,1,324,'Mastered','2026-08-21 00:49:54','2026-08-21 00:49:54'),(6321,1,325,'Mastered','2026-08-21 00:49:54','2026-08-21 00:49:54'),(6341,1,326,'In Progress','2026-08-21 00:49:54','2026-08-21 00:49:54'),(6361,1,327,'Mastered','2026-08-21 00:49:54','2026-08-21 00:49:54'),(6381,1,328,'In Progress','2026-08-21 00:49:54','2026-08-21 00:49:54'),(6401,1,329,'Needs Practice','2026-08-21 00:49:54','2026-08-21 00:49:54'),(6421,1,330,'In Progress','2026-08-21 00:49:54','2026-08-21 00:49:54'),(6441,1,331,'Needs Practice','2026-08-21 00:49:54','2026-08-21 00:49:54'),(6461,1,332,'In Progress','2026-08-21 00:49:54','2026-08-21 00:49:54'),(6481,1,333,'Mastered','2026-08-21 00:49:55','2026-08-21 00:49:55'),(6501,1,334,'Mastered','2026-08-21 00:49:55','2026-08-21 00:49:55'),(6521,1,335,'In Progress','2026-08-21 00:49:55','2026-08-21 00:49:55'),(6541,1,336,'In Progress','2026-08-21 00:49:55','2026-08-21 00:49:55'),(6561,1,337,'In Progress','2026-08-21 00:49:55','2026-08-21 00:49:55'),(6581,1,338,'Needs Practice','2026-08-21 00:49:55','2026-08-21 00:49:55'),(6601,1,339,'Mastered','2026-08-21 00:49:55','2026-08-21 00:49:55'),(6621,1,340,'Mastered','2026-08-21 00:49:55','2026-08-21 00:49:55'),(6641,1,341,'In Progress','2026-08-21 00:49:55','2026-08-21 00:49:55'),(6661,1,342,'In Progress','2026-08-21 00:49:55','2026-08-21 00:49:55'),(6681,1,343,'In Progress','2026-08-21 00:49:55','2026-08-21 00:49:55'),(6701,1,344,'Needs Practice','2026-08-21 00:49:55','2026-08-21 00:49:55'),(6721,1,345,'Mastered','2026-08-21 00:49:55','2026-08-21 00:49:55'),(6741,1,346,'In Progress','2026-08-21 00:49:55','2026-08-21 00:49:55'),(6761,1,347,'Needs Practice','2026-08-21 00:49:55','2026-08-21 00:49:55'),(6781,1,348,'Mastered','2026-08-21 00:49:55','2026-08-21 00:49:55'),(6801,1,349,'In Progress','2026-08-21 00:49:55','2026-08-21 00:49:55'),(6821,1,350,'Needs Practice','2026-08-21 00:49:55','2026-08-21 00:49:55'),(6841,1,351,'Mastered','2026-08-21 00:49:55','2026-08-21 00:49:55'),(6861,1,352,'In Progress','2026-08-21 00:49:56','2026-08-21 00:49:56'),(6881,1,353,'In Progress','2026-08-21 00:49:56','2026-08-21 00:49:56');
/*!40000 ALTER TABLE `student_subtopic_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_topic_masteries`
--

DROP TABLE IF EXISTS `student_topic_masteries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_topic_masteries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  `topic_name` varchar(255) NOT NULL,
  `mastery_percentage` int(11) NOT NULL DEFAULT 75,
  `quizzes_attempted` int(11) NOT NULL DEFAULT 5,
  `last_practiced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_topic_masteries_student_id_foreign` (`student_id`),
  KEY `student_topic_masteries_subject_id_foreign` (`subject_id`),
  CONSTRAINT `student_topic_masteries_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_topic_masteries_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_topic_masteries`
--

LOCK TABLES `student_topic_masteries` WRITE;
/*!40000 ALTER TABLE `student_topic_masteries` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_topic_masteries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `ic_number_hash` varchar(64) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `parent_user_id` bigint(20) unsigned DEFAULT NULL,
  `level` varchar(100) NOT NULL DEFAULT 'SPM',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `school_name` varchar(255) DEFAULT NULL,
  `programme` varchar(50) DEFAULT NULL,
  `total_points` int(11) NOT NULL DEFAULT 0,
  `parent_phone` varchar(30) DEFAULT NULL,
  `streak_days` int(11) NOT NULL DEFAULT 1,
  `last_active_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `students_ic_number_hash_unique` (`ic_number_hash`),
  KEY `students_user_id_foreign` (`user_id`),
  KEY `students_parent_user_id_foreign` (`parent_user_id`),
  CONSTRAINT `students_parent_user_id_foreign` FOREIGN KEY (`parent_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,10,NULL,NULL,NULL,NULL,NULL,NULL,'SPM','active',NULL,NULL,1782,NULL,1,'2026-09-30 09:56:33','2026-08-11 17:41:36','2026-09-30 09:56:33'),(116,210,'bb44087feb93e07141fcd057e09791d0b6a9009ec09795872a4746765595c0b8','Male','Malaysian','Selangor','2003-09-24',211,'Year 4','active','SAM Sungai Merab Luar',NULL,185,NULL,1,'2026-10-01 09:55:48','2026-09-01 19:49:35','2026-10-01 09:55:48'),(118,212,'ae3c7d87a29e48bc2575d66c957c6b623b726ffca3d5d3167d6373af73a469b9','Male','Malaysian','Selangor','2011-07-08',211,'Year 4','active','SAM Sungai Merab Luar',NULL,165,NULL,1,'2026-09-30 07:15:43','2026-09-03 01:12:09','2026-09-30 07:15:43'),(120,214,'228eb5efeeccf81384c19d645ed5a947916219525de1404a9569be4846e987c2','Male','Malaysian','Selangor','2003-09-24',215,'Year 4','active','SAM Sungai Merab Luar',NULL,165,NULL,1,'2026-09-30 07:24:24','2026-09-08 18:53:40','2026-09-30 07:24:24');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subject_subtopics`
--

DROP TABLE IF EXISTS `subject_subtopics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subject_subtopics` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `default_status` enum('Mastered','In Progress','Needs Practice','Not Started') NOT NULL DEFAULT 'Not Started',
  `order` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject_subtopics_topic_id_foreign` (`topic_id`),
  CONSTRAINT `subject_subtopics_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `subject_topics` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=194 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subject_subtopics`
--

LOCK TABLES `subject_subtopics` WRITE;
/*!40000 ALTER TABLE `subject_subtopics` DISABLE KEYS */;
INSERT INTO `subject_subtopics` VALUES (187,63,'Listening and Responding','In Progress',1,'2026-09-02 00:05:41','2026-09-02 00:05:41'),(188,64,'Listening','In Progress',1,'2026-09-02 00:06:45','2026-09-02 00:06:45'),(189,65,'Numbers and Counting','In Progress',1,'2026-09-02 00:07:26','2026-09-02 00:07:26'),(190,66,'Myself','In Progress',1,'2026-09-02 00:08:01','2026-09-02 00:08:01'),(191,67,'Aqidah','In Progress',1,'2026-09-02 00:08:27','2026-09-02 00:08:27'),(193,69,'belajar','In Progress',1,'2026-09-08 01:31:05','2026-09-08 01:31:05');
/*!40000 ALTER TABLE `subject_subtopics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subject_topics`
--

DROP TABLE IF EXISTS `subject_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subject_topics` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `chapter_number` int(11) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject_topics_subject_id_foreign` (`subject_id`),
  CONSTRAINT `subject_topics_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subject_topics`
--

LOCK TABLES `subject_topics` WRITE;
/*!40000 ALTER TABLE `subject_topics` DISABLE KEYS */;
INSERT INTO `subject_topics` VALUES (63,1,'Listening and Responding',1,'Listening and Responding','2026-09-02 00:05:04','2026-09-02 00:05:04'),(64,2,'Listening',1,'Listening','2026-09-02 00:06:35','2026-09-02 00:06:35'),(65,3,'Numbers and Counting',1,'Numbers and Counting','2026-09-02 00:07:17','2026-09-02 00:07:17'),(66,4,'Myself',1,'Myself','2026-09-02 00:07:53','2026-09-02 00:07:53'),(67,5,'Aqidah',1,'Aqidah','2026-09-02 00:08:22','2026-09-02 00:08:22'),(69,34,'belajar',1,'belajar','2026-09-08 01:30:55','2026-09-08 01:30:55');
/*!40000 ALTER TABLE `subject_topics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subjects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `education_type` varchar(30) NOT NULL DEFAULT 'secondary',
  `stage` varchar(50) NOT NULL DEFAULT 'Upper Secondary',
  `form_year` varchar(30) NOT NULL DEFAULT 'Form 4–5',
  `category` varchar(50) NOT NULL DEFAULT 'Core',
  `stream_package` varchar(50) DEFAULT NULL,
  `code` varchar(30) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `level` varchar(255) NOT NULL DEFAULT 'SPM',
  `color` varchar(20) NOT NULL DEFAULT '#1E40AF',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `monthly_fee` decimal(10,2) DEFAULT NULL COMMENT 'Admin-configurable monthly fee for this subject. Snapshotted into invoice_items at invoice generation.',
  `order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subjects_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subjects`
--

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;
INSERT INTO `subjects` VALUES (1,'Bahasa Melayu','bahasa-melayu','kindergarten','Preschool','Preschool','Early Development','General','KPM-6ISLVV','Develops early Malay language skills through listening, speaking, alphabet recognition, phonics, vocabulary, simple words, early reading, early writing, storytelling and communication.','languages','Kindergarten','#3b82f6',0,50.00,1,'2026-08-28 00:44:34','2026-09-02 20:16:10'),(2,'English','english','kindergarten','Preschool','Preschool','Early Development','General','KPM-HYN5WO','Develops early English communication skills through alphabet recognition, phonics, vocabulary, greetings, colours, numbers, shapes, simple sentences, listening, speaking, early reading and writing.','academic-cap','Kindergarten','#3b82f6',0,50.00,2,'2026-08-28 00:44:34','2026-09-02 20:16:10'),(3,'Early Mathematics','early-mathematics','kindergarten','Preschool','Preschool','Early Development','General','KPM-ZGVKPS','Develops early mathematical thinking through numbers, counting, quantity, matching, sorting, patterns, shapes, measurement, time, position, comparison and basic addition and subtraction.','academic-cap','Kindergarten','#3b82f6',0,50.00,3,'2026-08-28 00:44:34','2026-09-02 20:16:10'),(4,'Early Science','early-science','kindergarten','Preschool','Preschool','Early Development','General','KPM-ZBR0T5','Introduces children to scientific exploration through living and non-living things, the human body, senses, animals, plants, weather, water, materials, nature and safety.','academic-cap','Kindergarten','#3b82f6',0,50.00,4,'2026-08-28 00:44:34','2026-09-02 20:16:10'),(5,'Islamic Education','islamic-education','kindergarten','Preschool','Preschool','Early Development','General','KPM-IB2JTH','Introduces Islamic values and practices through basic Aqidah, Ibadah, Akhlak, daily prayers, short Surahs, Hijaiyah letters, stories of Prophets and Islamic manners.','academic-cap','Kindergarten','#3b82f6',0,50.00,5,'2026-08-28 00:44:34','2026-09-02 20:16:10'),(11,'Bahasa Melayu','bahasa-melayu-1','primary','Primary','Year 1–6','Core','General','KPM-XI2U2V','Develops Malay language proficiency through listening, speaking, reading, writing, grammar, vocabulary, comprehension, sentence construction, communication and literature.','academic-cap','KSSR','#3b82f6',0,60.00,6,'2026-08-28 01:00:24','2026-09-02 20:16:10'),(14,'English','english-1','primary','Primary','Year 1–6','Core','General','KPM-KW3SDL','Develops English language proficiency through listening, speaking, reading, writing, grammar, vocabulary, comprehension, sentence construction, communication and literature.','academic-cap','KSSR','#3b82f6',0,60.00,7,'2026-09-01 23:49:17','2026-09-02 20:16:10'),(15,'Mathematics','mathematics','primary','Primary','Year 1–6','Core','General','KPM-IYPIFE','Develops mathematical knowledge and problem-solving skills through numbers, operations, fractions, decimals, percentages, money, measurement, geometry, data, graphs, statistics and problem-solving.','academic-cap','KSSR','#3b82f6',0,60.00,8,'2026-09-01 23:50:55','2026-09-02 20:16:10'),(16,'Science','science','primary','Primary','Year 1–6','Core','General','KPM-FKEUNC','Develops scientific knowledge and inquiry skills through living things, humans, animals, plants, matter, materials, force, energy, electricity, light, sound, heat, Earth, the Solar System and the environment.','academic-cap','KSSR','#3b82f6',0,60.00,9,'2026-09-01 23:54:21','2026-09-02 20:16:10'),(17,'Sejarah','sejarah','primary','Primary','Year 1–6','Core','General','KPM-4H5MNZ','Introduces Malaysian history through early history, kingdoms, Malay Sultanates, the Malacca Sultanate, colonialism, nationalism, independence, formation of Malaysia, national identity and important historical figures.','academic-cap','KSSR','#3b82f6',0,60.00,10,'2026-09-01 23:54:21','2026-09-02 20:16:10'),(18,'Bahasa Melayu','bahasa-melayu-2','secondary','Lower Secondary','Form 1–3','Core','General','KPM-G5TDDI','Develops Malay language proficiency through grammar, reading, writing, comprehension, essay writing, literature, vocabulary, proverbs and communication.','academic-cap','SPM','#3b82f6',0,70.00,11,'2026-09-01 23:54:21','2026-09-02 20:16:10'),(19,'English','english-2','secondary','Lower Secondary','Form 1–3','Core','General','KPM-UEPIJG','Develops English proficiency through grammar, vocabulary, reading, writing, listening, speaking, comprehension, literature and communication.','academic-cap','SPM','#3b82f6',0,70.00,12,'2026-09-01 23:54:21','2026-09-02 20:16:10'),(20,'Mathematics','mathematics-1','secondary','Lower Secondary','Form 1–3','Core','General','KPM-WHJGQS','Develops mathematical reasoning and problem-solving through numbers, algebra, ratios, percentages, equations, inequalities, geometry, coordinates, graphs, statistics and probability.','academic-cap','SPM','#3b82f6',0,70.00,13,'2026-09-01 23:54:21','2026-09-02 20:16:10'),(21,'Science','science-1','secondary','Lower Secondary','Form 1–3','Core','General','KPM-M2WCAB','Develops scientific understanding through cells, matter, energy, force, electricity, light, sound, heat, human systems, plants, animals, Earth, the Solar System, technology and the environment.','academic-cap','SPM','#3b82f6',0,70.00,14,'2026-09-01 23:54:21','2026-09-02 20:16:10'),(22,'Sejarah','sejarah-1','secondary','Lower Secondary','Form 1–3','Core','General','KPM-GFUESV','Develops understanding of early civilizations, kingdoms, Malay history, the Malacca Sultanate, colonialism, nationalism, independence, formation of Malaysia and national identity.','academic-cap','SPM','#3b82f6',0,70.00,15,'2026-09-01 23:54:21','2026-09-02 20:16:10'),(23,'Bahasa Melayu','bahasa-melayu-3','secondary','Upper Secondary','Form 4–5','Core','General','KPM-BMP0OG','Develops advanced Malay language proficiency through grammar, reading, writing, comprehension, essay writing, literature, proverbs and effective communication.','academic-cap','SPM','#3b82f6',0,80.00,16,'2026-09-01 23:57:41','2026-09-02 20:16:10'),(24,'English','english-3','secondary','Upper Secondary','Form 4–5','Core','General','KPM-KBJZZJ','Develops advanced English language proficiency through listening, speaking, reading, writing, grammar, vocabulary, comprehension, essay writing, literature and communication.','academic-cap','SPM','#3b82f6',0,80.00,17,'2026-09-01 23:57:41','2026-09-02 20:16:10'),(25,'Mathematics','mathematics-2','secondary','Upper Secondary','Form 4–5','Core','General','KPM-39CJMD','Develops mathematical reasoning and problem-solving through algebra, functions, equations, inequalities, graphs, geometry, statistics, probability and financial mathematics.','academic-cap','SPM','#3b82f6',0,80.00,18,'2026-09-01 23:57:41','2026-09-02 20:16:10'),(26,'Sejarah','sejarah-2','secondary','Upper Secondary','Form 4–5','Core','General','KPM-RQPKMI','Develops understanding of Malaysian history, colonialism, nationalism, independence, formation of Malaysia, nation building and national identity.','academic-cap','SPM','#3b82f6',0,80.00,19,'2026-09-01 23:57:41','2026-09-02 20:16:10'),(27,'Physics','physics','secondary','Upper Secondary','Form 4–5','Core','STEM Pure Science','KPM-4HA8T0','Develops understanding of measurement, forces, motion, pressure, heat, waves, light, sound, electricity, electromagnetism, electronics and modern physics.','academic-cap','SPM','#3b82f6',0,90.00,20,'2026-09-01 23:58:13','2026-09-02 20:16:10'),(28,'Chemistry','chemistry','secondary','Upper Secondary','Form 4–5','Core','STEM Pure Science','KPM-OXDPE9','Develops understanding of matter, atomic structure, chemical bonding, chemical reactions, acids and bases, salts, rate of reaction, electrochemistry, organic chemistry and industrial chemistry.','academic-cap','SPM','#3b82f6',0,90.00,21,'2026-09-02 00:00:22','2026-09-02 20:16:10'),(29,'Biology','biology','secondary','Upper Secondary','Form 4–5','Core','STEM Pure Science','KPM-ZA8H4Y','Develops understanding of cells, enzymes, nutrition, respiration, transportation, reproduction, genetics, ecology, biodiversity and biotechnology.','academic-cap','SPM','#3b82f6',0,90.00,22,'2026-09-02 00:00:22','2026-09-02 20:16:10'),(30,'Additional Mathematics','additional-mathematics','secondary','Upper Secondary','Form 4–5','Core','Commerce & Accounting','KPM-8CVSFT','Develops advanced mathematical knowledge through functions, quadratic equations, indices, logarithms, sequences, coordinate geometry, linear programming, trigonometry, differentiation, integration, vectors and probability.','academic-cap','SPM','#3b82f6',0,90.00,23,'2026-09-02 00:00:22','2026-09-02 20:16:10'),(31,'Computer Science','computer-science','secondary','Upper Secondary','Form 4–5','Core','STEM Computing','KPM-OQ9UBY','Develops computational thinking, algorithms, programming, data structures, databases, computer systems, networks, cybersecurity and software development skills.','academic-cap','SPM','#3b82f6',0,90.00,24,'2026-09-02 00:00:22','2026-09-02 20:16:10'),(32,'Accounting','accounting','secondary','Upper Secondary','Form 4–5','Core','Commerce & Accounting','KPM-PLST3C','Develops accounting knowledge and skills through accounting concepts, accounting equations, double-entry bookkeeping, journals, ledgers, trial balance, financial statements, adjustments and financial analysis.','academic-cap','SPM','#3b82f6',0,80.00,25,'2026-09-02 00:00:22','2026-09-02 20:16:10'),(33,'Economics','economics','secondary','Upper Secondary','Form 4–5','Core','Commerce & Accounting','KPM-OAS79Y','Develops understanding of scarcity, choice, opportunity cost, demand, supply, market equilibrium, production, costs, market structures, money, banking, government policies and the Malaysian economy.','academic-cap','SPM','#3b82f6',0,80.00,26,'2026-09-02 00:00:22','2026-09-02 20:16:10'),(34,'Bahasa Melayu','bahasa-melayu-4','primary','Primary','Year 4','Core','General','KPM-CEWHQL','belajar bahasa melayu','academic-cap','KSSR','#3b82f6',1,60.00,27,'2026-09-08 01:30:28','2026-09-08 01:36:07'),(35,'English','english-year-4','primary','Primary','Year 4','Core','General','KPM-P4-ENG','English curriculum syllabus for Year 4 (KSSR Semakan).','academic-cap','Primary','#3b82f6',1,60.00,28,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(36,'Mathematics','mathematics-year-4','primary','Primary','Year 4','Core','General','KPM-P4-MAT','Mathematics curriculum syllabus for Year 4 (KSSR Semakan).','academic-cap','Primary','#3b82f6',1,60.00,29,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(37,'Science','science-year-4','primary','Primary','Year 4','Core','General','KPM-P4-SCI','Science curriculum syllabus for Year 4 (KSSR Semakan).','academic-cap','Primary','#3b82f6',1,60.00,30,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(38,'Sejarah','sejarah-year-4','primary','Primary','Year 4','Core','General','KPM-P4-SEJ','Sejarah curriculum syllabus for Year 4 (KSSR Semakan).','academic-cap','Primary','#3b82f6',1,60.00,31,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(39,'Bahasa Melayu','bahasa-melayu-year-5','primary','Primary','Year 5','Core','General','KPM-P5-BAH','Bahasa Melayu curriculum syllabus for Year 5 (KSSR Semakan).','academic-cap','Primary','#3b82f6',1,60.00,32,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(40,'English','english-year-5','primary','Primary','Year 5','Core','General','KPM-P5-ENG','English curriculum syllabus for Year 5 (KSSR Semakan).','academic-cap','Primary','#3b82f6',1,60.00,33,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(41,'Mathematics','mathematics-year-5','primary','Primary','Year 5','Core','General','KPM-P5-MAT','Mathematics curriculum syllabus for Year 5 (KSSR Semakan).','academic-cap','Primary','#3b82f6',1,60.00,34,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(42,'Science','science-year-5','primary','Primary','Year 5','Core','General','KPM-P5-SCI','Science curriculum syllabus for Year 5 (KSSR Semakan).','academic-cap','Primary','#3b82f6',1,60.00,35,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(43,'Sejarah','sejarah-year-5','primary','Primary','Year 5','Core','General','KPM-P5-SEJ','Sejarah curriculum syllabus for Year 5 (KSSR Semakan).','academic-cap','Primary','#3b82f6',1,60.00,36,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(44,'Bahasa Melayu','bahasa-melayu-year-6','primary','Primary','Year 6','Core','General','KPM-P6-BAH','Bahasa Melayu curriculum syllabus for Year 6 (KSSR Semakan).','academic-cap','Primary','#3b82f6',1,60.00,37,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(45,'English','english-year-6','primary','Primary','Year 6','Core','General','KPM-P6-ENG','English curriculum syllabus for Year 6 (KSSR Semakan).','academic-cap','Primary','#3b82f6',1,60.00,38,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(46,'Mathematics','mathematics-year-6','primary','Primary','Year 6','Core','General','KPM-P6-MAT','Mathematics curriculum syllabus for Year 6 (KSSR Semakan).','academic-cap','Primary','#3b82f6',1,60.00,39,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(47,'Science','science-year-6','primary','Primary','Year 6','Core','General','KPM-P6-SCI','Science curriculum syllabus for Year 6 (KSSR Semakan).','academic-cap','Primary','#3b82f6',1,60.00,40,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(48,'Sejarah','sejarah-year-6','primary','Primary','Year 6','Core','General','KPM-P6-SEJ','Sejarah curriculum syllabus for Year 6 (KSSR Semakan).','academic-cap','Primary','#3b82f6',1,60.00,41,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(49,'Bahasa Melayu','bahasa-melayu-form-1','secondary','Lower Secondary','Form 1','Core','General','KPM-F1-BAH','Bahasa Melayu curriculum syllabus for Form 1 (KSSM).','academic-cap','Lower Secondary','#3b82f6',1,70.00,42,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(50,'English','english-form-1','secondary','Lower Secondary','Form 1','Core','General','KPM-F1-ENG','English curriculum syllabus for Form 1 (KSSM).','academic-cap','Lower Secondary','#3b82f6',1,70.00,43,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(51,'Mathematics','mathematics-form-1','secondary','Lower Secondary','Form 1','Core','General','KPM-F1-MAT','Mathematics curriculum syllabus for Form 1 (KSSM).','academic-cap','Lower Secondary','#3b82f6',1,70.00,44,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(52,'Science','science-form-1','secondary','Lower Secondary','Form 1','Core','General','KPM-F1-SCI','Science curriculum syllabus for Form 1 (KSSM).','academic-cap','Lower Secondary','#3b82f6',1,70.00,45,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(53,'Sejarah','sejarah-form-1','secondary','Lower Secondary','Form 1','Core','General','KPM-F1-SEJ','Sejarah curriculum syllabus for Form 1 (KSSM).','academic-cap','Lower Secondary','#3b82f6',1,70.00,46,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(54,'Bahasa Melayu','bahasa-melayu-form-2','secondary','Lower Secondary','Form 2','Core','General','KPM-F2-BAH','Bahasa Melayu curriculum syllabus for Form 2 (KSSM).','academic-cap','Lower Secondary','#3b82f6',1,70.00,47,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(55,'English','english-form-2','secondary','Lower Secondary','Form 2','Core','General','KPM-F2-ENG','English curriculum syllabus for Form 2 (KSSM).','academic-cap','Lower Secondary','#3b82f6',1,70.00,48,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(56,'Mathematics','mathematics-form-2','secondary','Lower Secondary','Form 2','Core','General','KPM-F2-MAT','Mathematics curriculum syllabus for Form 2 (KSSM).','academic-cap','Lower Secondary','#3b82f6',1,70.00,49,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(57,'Science','science-form-2','secondary','Lower Secondary','Form 2','Core','General','KPM-F2-SCI','Science curriculum syllabus for Form 2 (KSSM).','academic-cap','Lower Secondary','#3b82f6',1,70.00,50,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(58,'Sejarah','sejarah-form-2','secondary','Lower Secondary','Form 2','Core','General','KPM-F2-SEJ','Sejarah curriculum syllabus for Form 2 (KSSM).','academic-cap','Lower Secondary','#3b82f6',1,70.00,51,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(59,'Bahasa Melayu','bahasa-melayu-form-3','secondary','Lower Secondary','Form 3','Core','General','KPM-F3-BAH','Bahasa Melayu curriculum syllabus for Form 3 (KSSM).','academic-cap','Lower Secondary','#3b82f6',1,70.00,52,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(60,'English','english-form-3','secondary','Lower Secondary','Form 3','Core','General','KPM-F3-ENG','English curriculum syllabus for Form 3 (KSSM).','academic-cap','Lower Secondary','#3b82f6',1,70.00,53,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(61,'Mathematics','mathematics-form-3','secondary','Lower Secondary','Form 3','Core','General','KPM-F3-MAT','Mathematics curriculum syllabus for Form 3 (KSSM).','academic-cap','Lower Secondary','#3b82f6',1,70.00,54,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(62,'Science','science-form-3','secondary','Lower Secondary','Form 3','Core','General','KPM-F3-SCI','Science curriculum syllabus for Form 3 (KSSM).','academic-cap','Lower Secondary','#3b82f6',1,70.00,55,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(63,'Sejarah','sejarah-form-3','secondary','Lower Secondary','Form 3','Core','General','KPM-F3-SEJ','Sejarah curriculum syllabus for Form 3 (KSSM).','academic-cap','Lower Secondary','#3b82f6',1,70.00,56,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(64,'Bahasa Melayu','bahasa-melayu-form-4','secondary','Upper Secondary','Form 4','Core','General','KPM-SPM4-BAH','Bahasa Melayu curriculum syllabus for Form 4 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,80.00,57,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(65,'English','english-form-4','secondary','Upper Secondary','Form 4','Core','General','KPM-SPM4-ENG','English curriculum syllabus for Form 4 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,80.00,58,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(66,'Mathematics','mathematics-form-4','secondary','Upper Secondary','Form 4','Core','General','KPM-SPM4-MAT','Mathematics curriculum syllabus for Form 4 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,80.00,59,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(67,'Sejarah','sejarah-form-4','secondary','Upper Secondary','Form 4','Core','General','KPM-SPM4-SEJ','Sejarah curriculum syllabus for Form 4 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,80.00,60,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(68,'Physics','physics-form-4','secondary','Upper Secondary','Form 4','Elective','STEM Pure Science','KPM-SPM4-PHY','Physics curriculum syllabus for Form 4 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,90.00,61,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(69,'Chemistry','chemistry-form-4','secondary','Upper Secondary','Form 4','Elective','STEM Pure Science','KPM-SPM4-CHE','Chemistry curriculum syllabus for Form 4 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,90.00,62,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(70,'Biology','biology-form-4','secondary','Upper Secondary','Form 4','Elective','STEM Pure Science','KPM-SPM4-BIO','Biology curriculum syllabus for Form 4 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,90.00,63,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(71,'Additional Mathematics','additional-mathematics-form-4','secondary','Upper Secondary','Form 4','Elective','STEM Pure Science','KPM-SPM4-ADD','Additional Mathematics curriculum syllabus for Form 4 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,90.00,64,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(72,'Computer Science','computer-science-form-4','secondary','Upper Secondary','Form 4','Elective','STEM Computing','KPM-SPM4-COM','Computer Science curriculum syllabus for Form 4 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,90.00,65,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(73,'Accounting','accounting-form-4','secondary','Upper Secondary','Form 4','Elective','Commerce & Accounting','KPM-SPM4-ACC','Accounting curriculum syllabus for Form 4 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,80.00,66,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(74,'Economics','economics-form-4','secondary','Upper Secondary','Form 4','Elective','Commerce & Accounting','KPM-SPM4-ECO','Economics curriculum syllabus for Form 4 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,80.00,67,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(75,'Bahasa Melayu','bahasa-melayu-form-5','secondary','Upper Secondary','Form 5','Core','General','KPM-SPM5-BAH','Bahasa Melayu curriculum syllabus for Form 5 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,80.00,68,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(76,'English','english-form-5','secondary','Upper Secondary','Form 5','Core','General','KPM-SPM5-ENG','English curriculum syllabus for Form 5 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,80.00,69,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(77,'Mathematics','mathematics-form-5','secondary','Upper Secondary','Form 5','Core','General','KPM-SPM5-MAT','Mathematics curriculum syllabus for Form 5 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,80.00,70,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(78,'Sejarah','sejarah-form-5','secondary','Upper Secondary','Form 5','Core','General','KPM-SPM5-SEJ','Sejarah curriculum syllabus for Form 5 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,80.00,71,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(79,'Physics','physics-form-5','secondary','Upper Secondary','Form 5','Elective','STEM Pure Science','KPM-SPM5-PHY','Physics curriculum syllabus for Form 5 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,90.00,72,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(80,'Chemistry','chemistry-form-5','secondary','Upper Secondary','Form 5','Elective','STEM Pure Science','KPM-SPM5-CHE','Chemistry curriculum syllabus for Form 5 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,90.00,73,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(81,'Biology','biology-form-5','secondary','Upper Secondary','Form 5','Elective','STEM Pure Science','KPM-SPM5-BIO','Biology curriculum syllabus for Form 5 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,90.00,74,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(82,'Additional Mathematics','additional-mathematics-form-5','secondary','Upper Secondary','Form 5','Elective','STEM Pure Science','KPM-SPM5-ADD','Additional Mathematics curriculum syllabus for Form 5 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,90.00,75,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(83,'Computer Science','computer-science-form-5','secondary','Upper Secondary','Form 5','Elective','STEM Computing','KPM-SPM5-COM','Computer Science curriculum syllabus for Form 5 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,90.00,76,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(84,'Accounting','accounting-form-5','secondary','Upper Secondary','Form 5','Elective','Commerce & Accounting','KPM-SPM5-ACC','Accounting curriculum syllabus for Form 5 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,80.00,77,'2026-09-10 22:51:48','2026-09-10 22:51:48'),(85,'Economics','economics-form-5','secondary','Upper Secondary','Form 5','Elective','Commerce & Accounting','KPM-SPM5-ECO','Economics curriculum syllabus for Form 5 SPM (KSSM).','academic-cap','SPM','#3b82f6',1,80.00,78,'2026-09-10 22:51:48','2026-09-10 22:51:48');
/*!40000 ALTER TABLE `subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `submission_pages`
--

DROP TABLE IF EXISTS `submission_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `submission_pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `submission_id` bigint(20) unsigned NOT NULL,
  `page_number` int(10) unsigned NOT NULL,
  `annotation_json` longtext DEFAULT NULL,
  `stroke_count` int(10) unsigned NOT NULL DEFAULT 0,
  `page_version` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sub_page` (`submission_id`,`page_number`),
  KEY `idx_sub_page_num` (`submission_id`,`page_number`),
  CONSTRAINT `submission_pages_submission_id_foreign` FOREIGN KEY (`submission_id`) REFERENCES `student_assignment_submissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `submission_pages`
--

LOCK TABLES `submission_pages` WRITE;
/*!40000 ALTER TABLE `submission_pages` DISABLE KEYS */;
INSERT INTO `submission_pages` VALUES (1,2,1,'{\"version\":1,\"strokes\":[{\"id\":\"st_1790752592763_86f45w\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.2674,0.35327,0.5],[0.27008,0.34566,0.5],[0.29025,0.33234,0.5],[0.30908,0.32378,0.5],[0.35748,0.29905,0.5],[0.41664,0.27241,0.5],[0.45563,0.25434,0.5],[0.48924,0.24483,0.5],[0.50941,0.23817,0.5],[0.51748,0.23722,0.5],[0.51479,0.24293,0.5],[0.50403,0.25244,0.5],[0.48387,0.27241,0.5],[0.46504,0.29905,0.5],[0.44487,0.31522,0.5],[0.4395,0.32283,0.5],[0.43815,0.32378,0.5],[0.4395,0.32378,0.5],[0.44891,0.31712,0.5],[0.48924,0.30381,0.5],[0.5121,0.3019,0.5],[0.52286,0.3019,0.5],[0.5242,0.3019,0.5],[0.52555,0.31046,0.5],[0.52689,0.32854,0.5],[0.52151,0.35232,0.5],[0.51882,0.37039,0.5],[0.51882,0.37895,0.5],[0.51882,0.3799,0.5],[0.52151,0.36088,0.5],[0.54034,0.3409,0.5],[0.56319,0.32663,0.5],[0.58471,0.31807,0.5],[0.59815,0.31712,0.5],[0.60487,0.31712,0.5],[0.61429,0.32759,0.5],[0.61697,0.34471,0.5],[0.61966,0.35898,0.5],[0.61429,0.36944,0.5],[0.59815,0.378,0.5],[0.58067,0.38181,0.5],[0.56588,0.37705,0.5],[0.5605,0.36659,0.5],[0.55513,0.35137,0.5],[0.55782,0.33424,0.5],[0.57261,0.31998,0.5],[0.60353,0.31141,0.5],[0.63176,0.30666,0.5],[0.64521,0.30476,0.5],[0.65597,0.30761,0.5],[0.67748,0.32188,0.5],[0.68824,0.34185,0.5],[0.69092,0.36468,0.5],[0.6842,0.39132,0.5],[0.67345,0.39893,0.5],[0.66941,0.39988,0.5],[0.66807,0.39988,0.5],[0.66672,0.39512,0.5],[0.66538,0.3799,0.5],[0.66538,0.36373,0.5],[0.68689,0.34661,0.5],[0.71647,0.32949,0.5],[0.74471,0.32188,0.5],[0.7716,0.31998,0.5],[0.78101,0.31998,0.5],[0.79849,0.33995,0.5],[0.8079,0.35993,0.5],[0.80924,0.38847,0.5],[0.80387,0.40844,0.5],[0.80252,0.42747,0.5],[0.79311,0.43508,0.5],[0.77429,0.43603,0.5],[0.75412,0.43603,0.5],[0.74202,0.43222,0.5],[0.73395,0.42652,0.5],[0.72723,0.41891,0.5],[0.72588,0.41225,0.5],[0.72857,0.41795,0.5],[0.72992,0.42652,0.5],[0.72992,0.44269,0.5],[0.71916,0.46266,0.5],[0.70034,0.48454,0.5],[0.66672,0.49786,0.5],[0.62639,0.50357,0.5],[0.59277,0.50357,0.5],[0.54975,0.4912,0.5],[0.50672,0.46076,0.5],[0.49462,0.44078,0.5],[0.49462,0.42176,0.5],[0.50403,0.41034,0.5],[0.50807,0.40939,0.5]]},{\"id\":\"st_1790752598267_z943ya\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.23647,0.07455,0.5],[0.23513,0.07455,0.5],[0.23378,0.07741,0.5],[0.2284,0.08216,0.5],[0.21765,0.09263,0.5],[0.21361,0.10404,0.5],[0.21361,0.11165,0.5],[0.2163,0.11641,0.5],[0.22168,0.12592,0.5],[0.23244,0.13734,0.5],[0.23782,0.14209,0.5],[0.23916,0.14209,0.5],[0.24588,0.13924,0.5],[0.25126,0.13639,0.5],[0.26202,0.12497,0.5],[0.27546,0.1107,0.5],[0.27681,0.10499,0.5],[0.27681,0.10404,0.5],[0.27681,0.10309,0.5],[0.27681,0.1088,0.5],[0.27681,0.11356,0.5],[0.27681,0.12307,0.5],[0.27815,0.13068,0.5],[0.27815,0.13829,0.5],[0.2795,0.14304,0.5],[0.28487,0.14495,0.5],[0.28622,0.14495,0.5],[0.28891,0.14495,0.5],[0.29832,0.14019,0.5],[0.30908,0.13258,0.5],[0.31983,0.12402,0.5],[0.32521,0.11736,0.5],[0.32655,0.11165,0.5],[0.32655,0.10499,0.5],[0.32655,0.10214,0.5],[0.32387,0.09738,0.5],[0.32387,0.09643,0.5],[0.32387,0.09548,0.5],[0.32252,0.09548,0.5],[0.32118,0.09548,0.5],[0.31983,0.09453,0.5],[0.31983,0.09168,0.5],[0.31983,0.09073,0.5],[0.32252,0.08312,0.5],[0.33059,0.0717,0.5],[0.33328,0.06314,0.5],[0.33328,0.05743,0.5],[0.33462,0.05458,0.5],[0.33462,0.05077,0.5],[0.33462,0.04507,0.5],[0.33462,0.04411,0.5],[0.33462,0.03936,0.5],[0.33462,0.03365,0.5],[0.33462,0.03175,0.5],[0.33462,0.0308,0.5],[0.33462,0.02985,0.5],[0.33328,0.02794,0.5],[0.33328,0.02699,0.5],[0.33193,0.02604,0.5],[0.3279,0.02604,0.5],[0.31849,0.02699,0.5],[0.31176,0.0308,0.5],[0.30639,0.0327,0.5],[0.30235,0.0346,0.5],[0.29966,0.03555,0.5],[0.29966,0.03841,0.5],[0.29697,0.04126,0.5],[0.29697,0.04316,0.5],[0.29563,0.04507,0.5],[0.29429,0.04602,0.5],[0.29429,0.04697,0.5],[0.29294,0.05077,0.5],[0.2916,0.05458,0.5],[0.2916,0.05933,0.5],[0.28084,0.06219,0.5],[0.27815,0.06314,0.5],[0.27681,0.06599,0.5],[0.27143,0.0679,0.5],[0.27008,0.06885,0.5],[0.26874,0.0698,0.5],[0.2674,0.07075,0.5],[0.26605,0.0717,0.5],[0.26471,0.07265,0.5],[0.26336,0.07265,0.5],[0.2674,0.07265,0.5]]},{\"id\":\"st_1790752600915_r4ibk4\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.29832,0.04792,0.5],[0.29966,0.04792,0.5],[0.30504,0.04792,0.5],[0.31714,0.04792,0.5],[0.32521,0.05172,0.5],[0.32655,0.05172,0.5],[0.33059,0.05363,0.5],[0.33059,0.05268,0.5]]},{\"id\":\"st_1790752601515_ied0z2\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.33193,0.02794,0.5],[0.33193,0.02699,0.5],[0.33193,0.02794,0.5],[0.32924,0.02985,0.5],[0.32924,0.0308,0.5],[0.3279,0.03175,0.5],[0.3279,0.0327,0.5],[0.3279,0.03365,0.5],[0.32521,0.04126,0.5],[0.31983,0.04411,0.5],[0.31983,0.04602,0.5]]},{\"id\":\"st_1790752614451_x0fxmy\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":1.5,\"points\":[[0.12622,0.42081,0.5],[0.12622,0.41605,0.5],[0.13832,0.40844,0.5],[0.16387,0.39608,0.5],[0.18807,0.38751,0.5],[0.20286,0.38181,0.5],[0.2042,0.38181,0.5],[0.20555,0.38181,0.5],[0.20689,0.38847,0.5],[0.20824,0.39893,0.5],[0.20689,0.41415,0.5],[0.20151,0.42937,0.5],[0.20151,0.44078,0.5],[0.20151,0.44744,0.5],[0.20555,0.4503,0.5],[0.20689,0.4503,0.5],[0.2163,0.43888,0.5],[0.2284,0.42366,0.5],[0.23782,0.4151,0.5],[0.23782,0.41986,0.5],[0.23378,0.43317,0.5],[0.22034,0.46171,0.5],[0.20958,0.48835,0.5],[0.20017,0.50071,0.5],[0.19882,0.50547,0.5],[0.19748,0.49215,0.5],[0.20958,0.47598,0.5],[0.22437,0.46076,0.5],[0.24319,0.44935,0.5],[0.26336,0.44459,0.5],[0.27008,0.44364,0.5],[0.27277,0.44364,0.5],[0.28084,0.45791,0.5],[0.28218,0.47693,0.5],[0.29025,0.49501,0.5],[0.28756,0.50452,0.5],[0.28756,0.50737,0.5],[0.28487,0.50737,0.5]]},{\"id\":\"st_1790752616939_6osdfp\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":5,\"points\":[[0.24454,0.56159,0.5],[0.25126,0.55493,0.5],[0.26202,0.55018,0.5],[0.27412,0.54447,0.5],[0.29429,0.53496,0.5],[0.31445,0.5302,0.5],[0.32118,0.52735,0.5],[0.32252,0.5264,0.5],[0.32387,0.5264,0.5],[0.32387,0.52735,0.5],[0.32387,0.53591,0.5],[0.31714,0.56159,0.5],[0.31042,0.58442,0.5],[0.30101,0.59489,0.5],[0.29966,0.59869,0.5],[0.30235,0.59774,0.5],[0.33059,0.57776,0.5],[0.35345,0.5635,0.5],[0.36958,0.55589,0.5],[0.36958,0.57206,0.5],[0.36286,0.59108,0.5],[0.3521,0.61106,0.5],[0.34538,0.61581,0.5],[0.34403,0.61867,0.5],[0.34807,0.59964,0.5],[0.36151,0.58252,0.5],[0.38168,0.5673,0.5],[0.39916,0.55969,0.5],[0.40319,0.55969,0.5],[0.40454,0.55969,0.5],[0.40588,0.57015,0.5],[0.40588,0.58918,0.5],[0.40454,0.6025,0.5],[0.40185,0.6063,0.5],[0.40588,0.59679,0.5],[0.41798,0.57967,0.5],[0.43008,0.55969,0.5],[0.44218,0.55303,0.5],[0.44891,0.55303,0.5],[0.4516,0.55303,0.5],[0.46235,0.56445,0.5],[0.46908,0.58442,0.5],[0.47042,0.6063,0.5],[0.47311,0.62438,0.5],[0.47311,0.62628,0.5],[0.47311,0.61962,0.5],[0.47983,0.60155,0.5],[0.49731,0.57681,0.5],[0.51613,0.55779,0.5],[0.5363,0.54637,0.5],[0.54706,0.54257,0.5],[0.5484,0.54257,0.5],[0.54975,0.54542,0.5]]},{\"id\":\"st_1790752620291_l5mivc\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":5,\"points\":[[0.66941,0.58918,0.5],[0.67076,0.58823,0.5],[0.68555,0.57206,0.5],[0.69899,0.55493,0.5],[0.72588,0.5302,0.5],[0.75143,0.51213,0.5],[0.76891,0.49501,0.5],[0.78101,0.4874,0.5],[0.78101,0.48835,0.5],[0.7595,0.51213,0.5],[0.73933,0.52925,0.5],[0.72723,0.54067,0.5],[0.71647,0.55113,0.5],[0.71513,0.55113,0.5],[0.72319,0.54828,0.5],[0.78235,0.52259,0.5],[0.81193,0.50927,0.5],[0.81731,0.50737,0.5],[0.81866,0.50737,0.5],[0.82,0.51593,0.5],[0.82134,0.55398,0.5],[0.82134,0.59489,0.5],[0.82134,0.62247,0.5],[0.82403,0.63199,0.5],[0.82672,0.62057,0.5],[0.83076,0.61201,0.5],[0.84824,0.5673,0.5],[0.85496,0.55398,0.5],[0.8563,0.54923,0.5],[0.8563,0.55018,0.5],[0.8563,0.55493,0.5],[0.8563,0.55874,0.5],[0.8563,0.55969,0.5],[0.8563,0.55874,0.5],[0.85765,0.55018,0.5],[0.85899,0.54162,0.5],[0.86437,0.52735,0.5],[0.86437,0.51879,0.5]]},{\"id\":\"st_1790752623196_sfk3js\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":5,\"points\":[[0.71244,0.13924,0.5],[0.71244,0.13829,0.5],[0.70706,0.14209,0.5],[0.69899,0.1478,0.5],[0.69361,0.15541,0.5],[0.68555,0.16112,0.5],[0.68286,0.16683,0.5],[0.68286,0.17253,0.5],[0.68286,0.17919,0.5],[0.6842,0.1849,0.5],[0.68689,0.1887,0.5],[0.68824,0.18966,0.5],[0.69227,0.19061,0.5],[0.69361,0.19061,0.5],[0.69765,0.19061,0.5],[0.70975,0.1868,0.5],[0.71513,0.18014,0.5],[0.7205,0.16968,0.5],[0.72319,0.16207,0.5],[0.72588,0.15922,0.5],[0.72588,0.15826,0.5],[0.72588,0.16112,0.5],[0.72185,0.16683,0.5],[0.71916,0.18395,0.5],[0.71782,0.19822,0.5],[0.72185,0.21344,0.5],[0.72454,0.22105,0.5],[0.72857,0.23151,0.5],[0.73261,0.23436,0.5],[0.73664,0.23627,0.5],[0.74471,0.23627,0.5],[0.75412,0.23627,0.5],[0.75815,0.23341,0.5],[0.76487,0.2239,0.5],[0.76622,0.21058,0.5],[0.76622,0.20107,0.5],[0.76487,0.19251,0.5],[0.76218,0.18205,0.5],[0.76084,0.17158,0.5],[0.75815,0.16873,0.5],[0.75681,0.16778,0.5],[0.75546,0.16683,0.5],[0.75412,0.16587,0.5],[0.75277,0.16492,0.5],[0.75277,0.16397,0.5],[0.75277,0.16302,0.5],[0.75681,0.16017,0.5],[0.76756,0.15256,0.5],[0.78908,0.14019,0.5],[0.80118,0.12878,0.5],[0.80655,0.12117,0.5],[0.81462,0.1126,0.5],[0.82538,0.10024,0.5],[0.83748,0.08882,0.5],[0.84689,0.08026,0.5],[0.85227,0.0736,0.5],[0.85361,0.07075,0.5],[0.8563,0.0679,0.5],[0.85765,0.06599,0.5],[0.85765,0.06409,0.5],[0.85765,0.06314,0.5],[0.85227,0.06314,0.5],[0.84824,0.06314,0.5],[0.83613,0.06694,0.5],[0.82807,0.06885,0.5],[0.81866,0.07075,0.5],[0.81193,0.07265,0.5],[0.80252,0.08216,0.5],[0.79983,0.09073,0.5],[0.78908,0.09929,0.5],[0.78101,0.10785,0.5],[0.77429,0.11641,0.5],[0.77294,0.12307,0.5],[0.77294,0.12878,0.5],[0.77025,0.13639,0.5],[0.76891,0.14495,0.5],[0.76622,0.15065,0.5],[0.76487,0.15161,0.5],[0.76218,0.15256,0.5],[0.76084,0.15351,0.5],[0.7595,0.15446,0.5],[0.75815,0.15351,0.5],[0.75681,0.15256,0.5]]},{\"id\":\"st_1790752625420_3xzglt\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":5,\"points\":[[0.70706,0.14209,0.5],[0.70571,0.14114,0.5],[0.70706,0.13734,0.5],[0.72185,0.12497,0.5],[0.72588,0.12117,0.5],[0.74336,0.11165,0.5],[0.75546,0.10785,0.5],[0.76353,0.10214,0.5],[0.7716,0.09834,0.5],[0.7837,0.09263,0.5],[0.7958,0.08597,0.5],[0.79983,0.08502,0.5]]},{\"id\":\"st_1790752625891_d054a2\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":5,\"points\":[[0.80252,0.08407,0.5],[0.80924,0.09453,0.5],[0.81731,0.1069,0.5],[0.82941,0.11736,0.5],[0.83479,0.12402,0.5],[0.83613,0.12687,0.5],[0.84017,0.12973,0.5],[0.84151,0.12973,0.5],[0.84151,0.12592,0.5],[0.84151,0.12307,0.5]]},{\"id\":\"st_1790752626428_or7gs0\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":5,\"points\":[[0.83748,0.07075,0.5],[0.8321,0.07265,0.5],[0.82134,0.07455,0.5],[0.81059,0.07741,0.5],[0.80118,0.08121,0.5]]},{\"id\":\"st_1790752627179_acxk3j\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":5,\"points\":[[0.73798,0.24578,0.5],[0.73798,0.2629,0.5],[0.73798,0.27527,0.5],[0.73798,0.28193,0.5],[0.73664,0.28763,0.5],[0.73664,0.28859,0.5],[0.73798,0.28859,0.5],[0.74336,0.28668,0.5]]},{\"id\":\"st_1790752627556_84prtw\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":5,\"points\":[[0.77697,0.23627,0.5],[0.77025,0.23817,0.5],[0.76218,0.24483,0.5],[0.75681,0.25054,0.5],[0.75143,0.25624,0.5],[0.74739,0.25719,0.5],[0.74739,0.2591,0.5],[0.76353,0.26005,0.5],[0.78773,0.2648,0.5],[0.80118,0.26576,0.5],[0.80655,0.26766,0.5],[0.8079,0.26671,0.5],[0.8079,0.2648,0.5],[0.80924,0.25719,0.5],[0.80924,0.25054,0.5],[0.80924,0.24388,0.5],[0.8079,0.24197,0.5],[0.80655,0.24197,0.5],[0.80118,0.24388,0.5],[0.79311,0.24863,0.5],[0.78773,0.25529,0.5],[0.78773,0.2648,0.5],[0.78908,0.27241,0.5],[0.79714,0.27812,0.5],[0.80521,0.27812,0.5],[0.81328,0.27717,0.5],[0.81866,0.26861,0.5],[0.82807,0.25149,0.5],[0.82807,0.24197,0.5],[0.82672,0.23436,0.5],[0.82403,0.23151,0.5],[0.82134,0.23056,0.5],[0.82134,0.22961,0.5],[0.82134,0.22866,0.5]]},{\"id\":\"st_1790752628364_rgw20v\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":5,\"points\":[[0.82672,0.22105,0.5],[0.82672,0.23341,0.5],[0.82672,0.25244,0.5],[0.8321,0.27146,0.5],[0.83479,0.28383,0.5],[0.83479,0.28763,0.5],[0.83479,0.28859,0.5],[0.83613,0.28954,0.5]]},{\"id\":\"st_1790752628731_c0cy3r\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":5,\"points\":[[0.82,0.24863,0.5],[0.82672,0.24578,0.5],[0.84151,0.24388,0.5],[0.85092,0.24007,0.5],[0.86034,0.23341,0.5],[0.86975,0.22961,0.5],[0.87244,0.22485,0.5],[0.87244,0.22295,0.5],[0.86706,0.22295,0.5],[0.86303,0.22295,0.5],[0.85899,0.22295,0.5],[0.85765,0.22295,0.5],[0.85361,0.22961,0.5],[0.85227,0.24102,0.5],[0.85227,0.25339,0.5],[0.86303,0.27622,0.5],[0.86975,0.28288,0.5],[0.87513,0.28383,0.5],[0.88319,0.28383,0.5],[0.89126,0.28288,0.5],[0.90202,0.27717,0.5],[0.90739,0.27146,0.5],[0.91143,0.26576,0.5],[0.91143,0.2629,0.5],[0.91143,0.261,0.5]]},{\"id\":\"st_1790752629523_gstncz\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":5,\"points\":[[0.79849,0.32949,0.5],[0.79849,0.33805,0.5],[0.80252,0.35707,0.5],[0.81193,0.37229,0.5],[0.82269,0.38942,0.5],[0.83076,0.39893,0.5],[0.8321,0.40178,0.5],[0.83345,0.40178,0.5],[0.84555,0.38466,0.5],[0.85361,0.36088,0.5],[0.86168,0.33329,0.5],[0.86437,0.31427,0.5],[0.86571,0.30666,0.5],[0.86571,0.30571,0.5]]},{\"id\":\"st_1790752629939_oyfock\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":5,\"points\":[[0.86571,0.30571,0.5],[0.86706,0.31712,0.5],[0.86975,0.3409,0.5],[0.86975,0.36183,0.5],[0.86975,0.38086,0.5],[0.86975,0.39037,0.5],[0.87109,0.39608,0.5]]},{\"id\":\"st_1790752630340_36tntc\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":5,\"points\":[[0.79445,0.37895,0.5],[0.79445,0.378,0.5],[0.8079,0.37325,0.5],[0.8321,0.36088,0.5],[0.85361,0.35137,0.5],[0.86706,0.34566,0.5],[0.87647,0.3409,0.5],[0.8805,0.33805,0.5],[0.88185,0.33805,0.5],[0.88319,0.3371,0.5]]},{\"id\":\"st_1790752630677_aptb6m\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":5,\"points\":[[0.88857,0.32759,0.5],[0.88454,0.32854,0.5],[0.87647,0.34566,0.5],[0.87647,0.35517,0.5],[0.87782,0.35803,0.5],[0.87916,0.35803,0.5],[0.89664,0.35137,0.5],[0.90336,0.33995,0.5],[0.90739,0.32949,0.5],[0.90739,0.32473,0.5],[0.90874,0.32378,0.5]]},{\"id\":\"st_1790752630883_3x7gek\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":5,\"points\":[[0.90874,0.32283,0.5],[0.91143,0.339,0.5],[0.91412,0.34851,0.5],[0.91681,0.35422,0.5],[0.91815,0.35517,0.5]]},{\"id\":\"st_1790752631227_g5dsi1\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":5,\"points\":[[0.94235,0.29715,0.5],[0.94235,0.30951,0.5],[0.94235,0.32283,0.5],[0.94235,0.32854,0.5],[0.94235,0.33044,0.5],[0.94101,0.33044,0.5],[0.94504,0.32283,0.5],[0.95176,0.31522,0.5],[0.95849,0.30381,0.5],[0.95983,0.3019,0.5]]},{\"id\":\"st_1790752631515_by74vh\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":5,\"points\":[[0.9679,0.2962,0.5],[0.97193,0.30095,0.5],[0.98941,0.31522,0.5],[1,0.32283,0.5],[1,0.32854,0.5],[1,0.32949,0.5]]}]}',22,6,'2026-09-30 07:16:35','2026-09-30 07:17:12'),(2,3,1,'{\"version\":1,\"strokes\":[{\"id\":\"st_1790753082891_b4z12z\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.15042,0.28478,0.5],[0.14908,0.28478,0.5],[0.14773,0.28478,0.5],[0.14639,0.28478,0.5],[0.1437,0.28478,0.5],[0.14101,0.28573,0.5],[0.13697,0.28668,0.5],[0.13429,0.28763,0.5],[0.13294,0.29049,0.5],[0.1316,0.29144,0.5],[0.13025,0.29239,0.5],[0.13025,0.29429,0.5],[0.13025,0.29524,0.5],[0.12891,0.29905,0.5],[0.12891,0.30095,0.5],[0.12891,0.30571,0.5],[0.12891,0.30856,0.5],[0.12891,0.31617,0.5],[0.13025,0.32093,0.5],[0.13025,0.32473,0.5],[0.13429,0.32854,0.5],[0.13563,0.33424,0.5],[0.13697,0.33424,0.5],[0.13832,0.33424,0.5],[0.14101,0.33615,0.5],[0.14235,0.33615,0.5],[0.1437,0.33615,0.5],[0.14504,0.33615,0.5],[0.14773,0.3352,0.5],[0.15714,0.32854,0.5],[0.15983,0.32283,0.5],[0.16387,0.31807,0.5],[0.16655,0.31617,0.5],[0.16655,0.31332,0.5],[0.16655,0.31046,0.5],[0.16655,0.30476,0.5],[0.16655,0.3019,0.5],[0.16655,0.30095,0.5],[0.16655,0.3,0.5],[0.16655,0.30095,0.5],[0.16655,0.30381,0.5],[0.16655,0.30666,0.5],[0.1679,0.31237,0.5],[0.1679,0.31902,0.5],[0.17059,0.32473,0.5],[0.17193,0.32854,0.5],[0.17462,0.33044,0.5],[0.17597,0.33234,0.5],[0.17866,0.3352,0.5],[0.18941,0.3409,0.5],[0.19076,0.3409,0.5],[0.1921,0.3409,0.5],[0.19748,0.34185,0.5],[0.19882,0.34185,0.5],[0.20151,0.34185,0.5],[0.20286,0.339,0.5],[0.20555,0.33424,0.5],[0.20958,0.32759,0.5],[0.21227,0.32473,0.5],[0.21227,0.31998,0.5],[0.21227,0.31427,0.5],[0.21092,0.30951,0.5],[0.20958,0.30476,0.5],[0.20824,0.30381,0.5],[0.20824,0.3019,0.5],[0.20824,0.30095,0.5],[0.20689,0.2981,0.5],[0.20689,0.29715,0.5],[0.20555,0.29524,0.5],[0.2042,0.29334,0.5],[0.20286,0.29334,0.5],[0.19882,0.29239,0.5],[0.19748,0.29239,0.5],[0.19613,0.29144,0.5],[0.19613,0.28954,0.5],[0.19479,0.28859,0.5],[0.19479,0.28763,0.5],[0.19479,0.28573,0.5],[0.19345,0.28193,0.5],[0.19345,0.28098,0.5],[0.19345,0.27717,0.5],[0.19345,0.27337,0.5],[0.19613,0.2648,0.5],[0.19748,0.25815,0.5],[0.19748,0.24863,0.5],[0.19748,0.24197,0.5],[0.19748,0.23436,0.5],[0.19748,0.22866,0.5],[0.19748,0.2239,0.5],[0.19748,0.21724,0.5],[0.19748,0.21439,0.5],[0.19748,0.20963,0.5],[0.19748,0.20297,0.5],[0.19882,0.19727,0.5],[0.19882,0.19061,0.5],[0.19882,0.183,0.5],[0.19882,0.18205,0.5],[0.19882,0.18014,0.5],[0.19882,0.17919,0.5],[0.19613,0.17919,0.5],[0.19479,0.17919,0.5],[0.19345,0.17919,0.5],[0.1921,0.18109,0.5],[0.19076,0.18205,0.5],[0.19076,0.18395,0.5],[0.18941,0.1849,0.5],[0.18941,0.18585,0.5],[0.18807,0.1887,0.5],[0.18672,0.19251,0.5],[0.18538,0.19727,0.5],[0.18538,0.19917,0.5],[0.18403,0.20583,0.5],[0.18134,0.20868,0.5],[0.18,0.21153,0.5],[0.18,0.21344,0.5],[0.18,0.21439,0.5],[0.18,0.21914,0.5],[0.18,0.22105,0.5],[0.18,0.22485,0.5],[0.17866,0.22771,0.5],[0.17731,0.23246,0.5],[0.17597,0.23817,0.5],[0.17462,0.23912,0.5],[0.17328,0.24578,0.5],[0.1679,0.24863,0.5],[0.16655,0.25529,0.5],[0.16387,0.26005,0.5],[0.16252,0.26385,0.5],[0.16118,0.26861,0.5],[0.1558,0.26956,0.5],[0.1558,0.27337,0.5],[0.15176,0.27622,0.5],[0.15042,0.27812,0.5],[0.14773,0.28002,0.5],[0.14639,0.28098,0.5],[0.14908,0.27622,0.5]]},{\"id\":\"st_1790753086306_x73885\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.16252,0.2201,0.5],[0.16521,0.21914,0.5],[0.17328,0.21914,0.5],[0.17731,0.21724,0.5],[0.17866,0.21724,0.5],[0.18,0.21724,0.5],[0.18403,0.21724,0.5],[0.18672,0.21629,0.5],[0.18807,0.21629,0.5],[0.18941,0.21629,0.5],[0.19076,0.21629,0.5],[0.1921,0.21534,0.5],[0.19345,0.21439,0.5],[0.19479,0.21344,0.5]]},{\"id\":\"st_1790753087051_ghs15z\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.19345,0.17729,0.5],[0.1921,0.19061,0.5],[0.1921,0.20297,0.5],[0.1921,0.20583,0.5]]},{\"id\":\"st_1790753087811_62ngdw\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.19345,0.31712,0.5],[0.19882,0.31807,0.5],[0.20017,0.31807,0.5],[0.20689,0.31902,0.5],[0.21092,0.31998,0.5],[0.21227,0.31998,0.5],[0.21361,0.31998,0.5],[0.21496,0.32093,0.5]]},{\"id\":\"st_1790753088346_tlwhxl\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.19882,0.32378,0.5],[0.19882,0.32473,0.5],[0.20017,0.33424,0.5],[0.20689,0.34851,0.5],[0.20824,0.35517,0.5],[0.20958,0.35898,0.5],[0.21092,0.36088,0.5],[0.21227,0.36183,0.5],[0.21227,0.36088,0.5]]},{\"id\":\"st_1790753088835_sf0y7g\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.18403,0.32283,0.5],[0.18403,0.32568,0.5],[0.18269,0.33615,0.5],[0.18269,0.34661,0.5],[0.18403,0.35327,0.5],[0.18403,0.35803,0.5],[0.18403,0.35993,0.5],[0.18403,0.35898,0.5],[0.18403,0.35803,0.5]]},{\"id\":\"st_1790753089571_l4cn5c\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.14235,0.30476,0.5],[0.14101,0.30476,0.5],[0.13563,0.30381,0.5],[0.13025,0.30285,0.5],[0.11277,0.2981,0.5],[0.08454,0.29334,0.5],[0.06303,0.28859,0.5],[0.03882,0.28668,0.5],[0.04151,0.28954,0.5],[0.06303,0.2962,0.5]]},{\"id\":\"st_1790753090171_fhcfz9\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.13697,0.31712,0.5],[0.13025,0.31902,0.5],[0.12084,0.32283,0.5],[0.11143,0.32473,0.5],[0.10202,0.32759,0.5],[0.09798,0.33139,0.5],[0.09261,0.3352,0.5],[0.08319,0.33805,0.5],[0.08857,0.3371,0.5],[0.09664,0.33329,0.5]]},{\"id\":\"st_1790753090667_8g4b9f\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.13429,0.32663,0.5],[0.13429,0.32949,0.5],[0.13429,0.33234,0.5],[0.13294,0.34471,0.5],[0.13832,0.35898,0.5],[0.13966,0.36659,0.5],[0.14504,0.36944,0.5]]}]}',9,1,'2026-09-30 07:24:52','2026-09-30 07:24:52'),(3,4,1,'{\"version\":1,\"strokes\":[{\"id\":\"st_1790761874962_icsvfo\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.35479,0.28288,0.5],[0.35076,0.28288,0.5],[0.34807,0.28288,0.5],[0.33866,0.28383,0.5],[0.32924,0.28573,0.5],[0.31983,0.29049,0.5],[0.30504,0.3019,0.5],[0.29563,0.31332,0.5],[0.28756,0.32759,0.5],[0.28622,0.33995,0.5],[0.28487,0.36564,0.5],[0.28487,0.3761,0.5],[0.29025,0.38276,0.5],[0.29832,0.38847,0.5],[0.32521,0.39608,0.5],[0.34807,0.39703,0.5],[0.36689,0.39703,0.5],[0.39378,0.38942,0.5],[0.40992,0.3799,0.5],[0.42471,0.36944,0.5],[0.43143,0.36278,0.5],[0.4395,0.35422,0.5],[0.44084,0.34946,0.5],[0.44218,0.34851,0.5],[0.44353,0.34946,0.5],[0.44891,0.35517,0.5],[0.45429,0.35993,0.5],[0.4637,0.36468,0.5],[0.47311,0.36944,0.5],[0.47849,0.37039,0.5],[0.48387,0.37039,0.5],[0.49328,0.36849,0.5],[0.50134,0.35803,0.5],[0.51076,0.339,0.5],[0.51882,0.31617,0.5],[0.52286,0.29524,0.5],[0.52017,0.27907,0.5],[0.51613,0.27051,0.5],[0.5121,0.26576,0.5],[0.50807,0.261,0.5],[0.50672,0.261,0.5],[0.50807,0.261,0.5],[0.5121,0.261,0.5],[0.51479,0.261,0.5],[0.51882,0.261,0.5],[0.52286,0.261,0.5],[0.52824,0.261,0.5],[0.5363,0.26195,0.5],[0.53765,0.2629,0.5],[0.54034,0.26385,0.5],[0.54303,0.26385,0.5],[0.54437,0.2648,0.5],[0.54437,0.26671,0.5],[0.54437,0.26861,0.5],[0.54303,0.27527,0.5],[0.53496,0.29049,0.5],[0.5242,0.30381,0.5],[0.52017,0.31046,0.5],[0.51882,0.31332,0.5],[0.51882,0.31427,0.5],[0.51613,0.31712,0.5],[0.51748,0.31712,0.5],[0.51882,0.31712,0.5],[0.52286,0.31712,0.5],[0.5242,0.31712,0.5],[0.52689,0.31712,0.5],[0.53092,0.31712,0.5],[0.53361,0.31712,0.5],[0.53765,0.31712,0.5],[0.53899,0.31712,0.5]]},{\"id\":\"st_1790761878088_gjkplx\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.58067,0.27146,0.5],[0.58067,0.27241,0.5],[0.58202,0.27907,0.5],[0.58471,0.28859,0.5],[0.58739,0.3,0.5],[0.59008,0.31237,0.5],[0.59008,0.31712,0.5],[0.59277,0.32473,0.5],[0.59412,0.33044,0.5],[0.59546,0.33995,0.5],[0.59681,0.34471,0.5],[0.59815,0.34851,0.5],[0.5995,0.35137,0.5],[0.60084,0.35422,0.5],[0.60084,0.35517,0.5]]},{\"id\":\"st_1790761879419_a1wgy9\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.62773,0.28573,0.5],[0.62908,0.28763,0.5],[0.62908,0.29334,0.5],[0.63176,0.30951,0.5],[0.6358,0.32473,0.5],[0.63714,0.33424,0.5],[0.63849,0.33995,0.5],[0.63983,0.34471,0.5]]},{\"id\":\"st_1790761880736_zlkdth\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.5995,0.33139,0.5],[0.60218,0.33044,0.5],[0.60353,0.33044,0.5],[0.60487,0.33044,0.5],[0.60622,0.33044,0.5],[0.60756,0.33044,0.5],[0.60891,0.33044,0.5],[0.61025,0.33044,0.5],[0.6116,0.33044,0.5],[0.61294,0.33044,0.5]]},{\"id\":\"st_1790761884245_ye06ve\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.82807,0.25719,0.5],[0.82941,0.25719,0.5],[0.82941,0.25624,0.5],[0.83345,0.25339,0.5],[0.83613,0.25149,0.5],[0.83748,0.24958,0.5],[0.84555,0.24673,0.5],[0.84958,0.24578,0.5],[0.8563,0.24483,0.5],[0.86034,0.24388,0.5],[0.86571,0.24388,0.5],[0.8684,0.24388,0.5],[0.87244,0.24388,0.5],[0.87647,0.24388,0.5],[0.87916,0.24388,0.5],[0.8805,0.24578,0.5],[0.88319,0.24863,0.5],[0.88588,0.25434,0.5],[0.88723,0.26385,0.5],[0.88723,0.26861,0.5],[0.88723,0.27337,0.5],[0.88723,0.27717,0.5],[0.88723,0.28288,0.5],[0.88185,0.28859,0.5],[0.87647,0.29334,0.5],[0.8684,0.3019,0.5],[0.86303,0.30476,0.5],[0.85899,0.30856,0.5],[0.85496,0.31046,0.5],[0.85092,0.31237,0.5],[0.84958,0.31332,0.5],[0.84824,0.31332,0.5],[0.84689,0.31427,0.5],[0.84286,0.31522,0.5],[0.83882,0.31617,0.5],[0.83613,0.31617,0.5],[0.83345,0.31617,0.5],[0.82672,0.31617,0.5],[0.82538,0.31617,0.5],[0.82403,0.31617,0.5],[0.82403,0.31522,0.5],[0.82403,0.31332,0.5],[0.82403,0.31237,0.5],[0.82403,0.31046,0.5],[0.82403,0.30951,0.5],[0.82269,0.30761,0.5],[0.82134,0.30476,0.5],[0.82134,0.30095,0.5],[0.82,0.2962,0.5],[0.81731,0.29049,0.5],[0.81597,0.28763,0.5],[0.81328,0.28383,0.5],[0.81193,0.28002,0.5],[0.80924,0.27812,0.5],[0.8079,0.27717,0.5],[0.80655,0.27527,0.5],[0.80387,0.27432,0.5],[0.79983,0.27146,0.5],[0.7958,0.26766,0.5],[0.79176,0.26195,0.5],[0.78639,0.25624,0.5],[0.78504,0.25244,0.5],[0.78504,0.24863,0.5],[0.78504,0.24768,0.5],[0.78639,0.24388,0.5],[0.79042,0.24197,0.5],[0.79311,0.24007,0.5],[0.79445,0.23912,0.5],[0.79983,0.23817,0.5],[0.80387,0.23817,0.5],[0.80924,0.23817,0.5],[0.81328,0.23817,0.5],[0.81731,0.23817,0.5],[0.82,0.23817,0.5],[0.82134,0.23817,0.5],[0.82538,0.24102,0.5],[0.82807,0.24293,0.5],[0.83076,0.24578,0.5],[0.8321,0.24863,0.5],[0.83479,0.25149,0.5],[0.83479,0.25339,0.5],[0.83479,0.25529,0.5],[0.83479,0.25719,0.5],[0.83479,0.2591,0.5],[0.83479,0.261,0.5]]}]}',5,21,'2026-09-30 09:28:36','2026-09-30 09:51:27'),(4,5,1,'{\"version\":1,\"strokes\":[{\"id\":\"txt_1790837992421_f0ntl\",\"tool\":\"text\",\"text\":\"semeneanjung\",\"x\":0.49866,\"y\":0.08668,\"color\":\"#111827\",\"fontSize\":17},{\"id\":\"txt_1790837998605_f5a4n\",\"tool\":\"text\",\"text\":\"seltan\",\"x\":0.38303,\"y\":0.4396,\"color\":\"#111827\",\"fontSize\":17},{\"id\":\"st_1790840883481_xx3moi\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.10471,0.41296,0.5],[0.10739,0.41296,0.5],[0.11143,0.42913,0.5],[0.11143,0.43864,0.5],[0.11143,0.45577,0.5],[0.11277,0.46338,0.5],[0.11412,0.46433,0.5],[0.11546,0.46433,0.5],[0.12084,0.46433,0.5],[0.13294,0.45862,0.5],[0.15311,0.45101,0.5],[0.16118,0.44721,0.5],[0.16252,0.44721,0.5],[0.16387,0.44625,0.5],[0.1679,0.44435,0.5],[0.17328,0.44055,0.5],[0.17328,0.43769,0.5]],\"bbox\":{\"minX\":0.10471,\"minY\":0.41296,\"maxX\":0.17328,\"maxY\":0.46433}},{\"id\":\"st_1790840883888_ncb535\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.17462,0.43103,0.5],[0.17328,0.43294,0.5],[0.17328,0.43864,0.5],[0.17328,0.4434,0.5],[0.17328,0.44625,0.5],[0.17731,0.44721,0.5],[0.17866,0.44245,0.5],[0.17866,0.4396,0.5],[0.17866,0.43674,0.5],[0.17866,0.43389,0.5],[0.18134,0.43579,0.5],[0.1921,0.44911,0.5],[0.20689,0.46052,0.5],[0.21227,0.46528,0.5],[0.2163,0.46813,0.5]],\"bbox\":{\"minX\":0.17328,\"minY\":0.43103,\"maxX\":0.2163,\"maxY\":0.46813}},{\"id\":\"st_1790840884553_ii2vzt\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.21092,0.38252,0.5],[0.2163,0.40916,0.5],[0.2163,0.42533,0.5],[0.21765,0.4434,0.5],[0.22034,0.45196,0.5],[0.22303,0.45577,0.5],[0.22437,0.45577,0.5],[0.22571,0.45577,0.5],[0.2284,0.45577,0.5]],\"bbox\":{\"minX\":0.21092,\"minY\":0.38252,\"maxX\":0.2284,\"maxY\":0.45577}},{\"id\":\"st_1790840884953_y4srf1\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.24992,0.42438,0.5],[0.24992,0.4396,0.5],[0.24992,0.45196,0.5],[0.25664,0.46813,0.5],[0.25933,0.47384,0.5],[0.26067,0.47384,0.5],[0.26336,0.47099,0.5],[0.27008,0.46052,0.5],[0.27277,0.44625,0.5]],\"bbox\":{\"minX\":0.24992,\"minY\":0.42438,\"maxX\":0.27277,\"maxY\":0.47384}},{\"id\":\"st_1790840885185_rizdbe\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.27546,0.42628,0.5],[0.28622,0.43864,0.5],[0.29697,0.45006,0.5],[0.31849,0.46528,0.5],[0.33866,0.47194,0.5],[0.35345,0.47669,0.5],[0.35748,0.47765,0.5],[0.36286,0.47574,0.5]],\"bbox\":{\"minX\":0.27546,\"minY\":0.42628,\"maxX\":0.36286,\"maxY\":0.47765}},{\"id\":\"st_1790840886113_3zvh74\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.58336,0.46623,0.5],[0.58739,0.46623,0.5],[0.59681,0.44911,0.5],[0.60891,0.42913,0.5],[0.62101,0.40916,0.5],[0.63176,0.38442,0.5],[0.63445,0.36064,0.5],[0.63445,0.35589,0.5],[0.63445,0.35303,0.5],[0.63311,0.3692,0.5],[0.63042,0.39298,0.5],[0.63176,0.41677,0.5],[0.6358,0.43484,0.5],[0.64252,0.44435,0.5],[0.64387,0.4453,0.5],[0.64521,0.4396,0.5],[0.64387,0.42628,0.5],[0.63445,0.40916,0.5],[0.62908,0.4025,0.5],[0.6237,0.39964,0.5],[0.6237,0.39869,0.5],[0.61966,0.39869,0.5],[0.61429,0.40345,0.5],[0.60622,0.41677,0.5],[0.60622,0.42723,0.5],[0.6116,0.43674,0.5],[0.62101,0.4434,0.5],[0.6479,0.44625,0.5],[0.66941,0.4396,0.5],[0.68017,0.42818,0.5],[0.68555,0.41391,0.5],[0.68555,0.40059,0.5],[0.68555,0.39394,0.5]],\"bbox\":{\"minX\":0.58336,\"minY\":0.35303,\"maxX\":0.68555,\"maxY\":0.46623}},{\"id\":\"st_1790840886801_6fi1qc\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.68555,0.39013,0.5]],\"bbox\":{\"minX\":0.68555,\"minY\":0.39013,\"maxX\":0.68555,\"maxY\":0.39013}},{\"id\":\"txt_1790840889920_lnk8s\",\"tool\":\"text\",\"text\":\"sqsq\",\"x\":0.28891,\"y\":0.52235,\"color\":\"#111827\",\"fontSize\":17},{\"id\":\"st_1790843605837_ew484d\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.56857,0.49287,0.5],[0.56992,0.49287,0.5],[0.57933,0.49287,0.5],[0.59681,0.49287,0.5],[0.63042,0.49287,0.5],[0.69496,0.49287,0.5],[0.75681,0.49287,0.5],[0.78504,0.49287,0.5],[0.81597,0.49667,0.5],[0.82538,0.49857,0.5]],\"bbox\":{\"minX\":0.56857,\"minY\":0.49287,\"maxX\":0.82538,\"maxY\":0.49857}},{\"id\":\"st_1790844082581_btijf7\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.22168,0.52711,0.5],[0.22975,0.52711,0.5],[0.23647,0.52616,0.5],[0.25798,0.52331,0.5],[0.29966,0.51855,0.5],[0.34941,0.50999,0.5],[0.37765,0.50428,0.5],[0.38975,0.50428,0.5],[0.39109,0.50428,0.5]],\"bbox\":{\"minX\":0.22168,\"minY\":0.50428,\"maxX\":0.39109,\"maxY\":0.52711}},{\"id\":\"st_1790844909835_gqhq4p\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.48252,0.05624,0.5],[0.48387,0.0591,0.5],[0.49193,0.07907,0.5],[0.49462,0.09524,0.5],[0.49597,0.11427,0.5],[0.49866,0.13995,0.5],[0.49866,0.16183,0.5],[0.5,0.17229,0.5],[0.5,0.1742,0.5],[0.5,0.17229,0.5],[0.50269,0.16183,0.5]]},{\"id\":\"st_1790844910427_0am1id\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.49328,0.0629,0.5],[0.50269,0.0648,0.5],[0.58336,0.07432,0.5],[0.71916,0.08573,0.5],[0.85227,0.08763,0.5],[0.94908,0.08954,0.5],[0.98672,0.08954,0.5],[0.9921,0.08954,0.5],[0.9921,0.08859,0.5],[0.98941,0.08668,0.5],[0.98807,0.08573,0.5],[0.98403,0.08383,0.5],[0.98,0.08383,0.5],[0.97462,0.08193,0.5],[0.96252,0.08002,0.5],[0.9437,0.07812,0.5],[0.90336,0.07241,0.5],[0.8805,0.07146,0.5],[0.86303,0.07146,0.5],[0.8442,0.07812,0.5],[0.82807,0.09144,0.5],[0.81597,0.11427,0.5],[0.8079,0.13424,0.5],[0.80655,0.14566,0.5],[0.8079,0.14946,0.5],[0.79983,0.14566,0.5],[0.76084,0.13805,0.5],[0.73126,0.1371,0.5],[0.70437,0.1371,0.5],[0.67479,0.1371,0.5],[0.65597,0.13995,0.5],[0.64655,0.14376,0.5],[0.63445,0.14661,0.5],[0.62101,0.14946,0.5],[0.60756,0.15042,0.5],[0.60622,0.15137,0.5],[0.60487,0.15137,0.5]]},{\"id\":\"st_1790844920755_sf3omx\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.73395,0.20654,0.5],[0.73664,0.21415,0.5],[0.74336,0.22747,0.5],[0.74336,0.23508,0.5],[0.75008,0.25981,0.5],[0.75143,0.27883,0.5],[0.75143,0.28264,0.5]]},{\"id\":\"st_1790844921147_rvaa0f\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.73126,0.20844,0.5],[0.75143,0.20844,0.5],[0.78101,0.20749,0.5],[0.79445,0.20559,0.5],[0.79983,0.20559,0.5]]},{\"id\":\"st_1790844921427_9uq51n\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.73798,0.24649,0.5],[0.7595,0.23888,0.5],[0.78908,0.22556,0.5],[0.80118,0.21891,0.5],[0.80252,0.21795,0.5]]},{\"id\":\"st_1790844921819_nmzn9m\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.77563,0.23032,0.5],[0.77697,0.23032,0.5],[0.78235,0.24459,0.5],[0.78504,0.25505,0.5],[0.78773,0.26457,0.5],[0.78773,0.26552,0.5],[0.78908,0.26266,0.5],[0.79176,0.25125,0.5],[0.79311,0.23508,0.5],[0.79311,0.22556,0.5],[0.79311,0.22271,0.5],[0.79311,0.22556,0.5],[0.79311,0.22842,0.5],[0.79311,0.23127,0.5],[0.79445,0.23317,0.5],[0.7958,0.23317,0.5],[0.80118,0.23032,0.5],[0.80924,0.21891,0.5],[0.81462,0.2113,0.5],[0.81731,0.20844,0.5],[0.81731,0.21415,0.5],[0.81731,0.23127,0.5],[0.81731,0.24554,0.5],[0.82,0.25315,0.5],[0.82269,0.25315,0.5],[0.83613,0.24364,0.5],[0.84555,0.23222,0.5],[0.85227,0.21891,0.5]]},{\"id\":\"st_1790844922451_14v5ht\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.85361,0.21891,0.5],[0.85361,0.22556,0.5],[0.85361,0.23603,0.5],[0.85361,0.23888,0.5],[0.85361,0.23983,0.5],[0.8563,0.23983,0.5],[0.86034,0.22937,0.5],[0.86706,0.21891,0.5]]},{\"id\":\"st_1790844922739_mdgw8i\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.86706,0.19322,0.5],[0.86571,0.20749,0.5],[0.86571,0.23413,0.5],[0.87244,0.25696,0.5],[0.88454,0.27503,0.5],[0.89798,0.28169,0.5],[0.89933,0.28169,0.5],[0.90605,0.27598,0.5],[0.91815,0.26076,0.5]]},{\"id\":\"st_1790844923019_jax9fe\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.92487,0.21225,0.5],[0.92084,0.21891,0.5],[0.91681,0.24174,0.5],[0.91681,0.25886,0.5],[0.91681,0.26837,0.5],[0.93294,0.26837,0.5],[0.97462,0.24935,0.5],[1,0.23222,0.5]]},{\"id\":\"st_1790844942155_ofjfc2\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.2405,0.55945,0.5],[0.23916,0.5585,0.5],[0.23782,0.5566,0.5],[0.23782,0.55755,0.5],[0.23647,0.5585,0.5],[0.23513,0.56516,0.5],[0.23647,0.57087,0.5],[0.2405,0.57467,0.5],[0.24588,0.57562,0.5],[0.24857,0.57562,0.5],[0.25933,0.56992,0.5],[0.26471,0.56231,0.5],[0.26874,0.55184,0.5],[0.27008,0.54423,0.5],[0.27008,0.54043,0.5],[0.27008,0.53948,0.5],[0.27008,0.53853,0.5],[0.26874,0.53853,0.5],[0.2674,0.53853,0.5],[0.26202,0.54043,0.5],[0.25933,0.54614,0.5],[0.25933,0.55565,0.5],[0.26471,0.56516,0.5],[0.27008,0.57182,0.5],[0.27815,0.57372,0.5],[0.28487,0.57372,0.5],[0.28891,0.57277,0.5],[0.29294,0.56611,0.5],[0.29429,0.55945,0.5],[0.29563,0.55184,0.5],[0.29563,0.54328,0.5],[0.29563,0.54233,0.5],[0.29429,0.54233,0.5],[0.29025,0.54233,0.5],[0.28084,0.54614,0.5],[0.27815,0.55375,0.5],[0.28218,0.5585,0.5],[0.28487,0.56421,0.5],[0.28891,0.56706,0.5],[0.29294,0.56706,0.5],[0.29563,0.56706,0.5],[0.29966,0.56421,0.5],[0.29966,0.55755,0.5],[0.29966,0.5547,0.5],[0.29832,0.55375,0.5],[0.29966,0.55375,0.5],[0.29966,0.55565,0.5],[0.31042,0.56611,0.5],[0.32118,0.57658,0.5],[0.33193,0.58419,0.5],[0.33597,0.58514,0.5],[0.33731,0.58514,0.5],[0.33866,0.58133,0.5],[0.33866,0.57182,0.5],[0.33328,0.5604,0.5],[0.33193,0.5585,0.5],[0.33059,0.55755,0.5],[0.33193,0.55755,0.5],[0.33193,0.5585,0.5],[0.33462,0.55945,0.5],[0.33597,0.55945,0.5],[0.33731,0.55945,0.5],[0.34134,0.5566,0.5],[0.34672,0.55184,0.5],[0.34403,0.54518,0.5],[0.34269,0.54328,0.5]]},{\"id\":\"st_1790844944443_bvyem2\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.33328,0.61748,0.5],[0.3279,0.61748,0.5],[0.32387,0.61938,0.5],[0.32252,0.62889,0.5],[0.32387,0.6365,0.5],[0.33059,0.64126,0.5],[0.33597,0.64411,0.5],[0.33731,0.64411,0.5],[0.34269,0.64411,0.5],[0.35613,0.6308,0.5],[0.36151,0.61938,0.5],[0.36286,0.61272,0.5],[0.36286,0.60511,0.5],[0.35882,0.59941,0.5],[0.35479,0.5956,0.5],[0.35479,0.59465,0.5],[0.3521,0.58799,0.5],[0.35076,0.58419,0.5],[0.35076,0.58894,0.5],[0.35882,0.60036,0.5],[0.36555,0.61843,0.5],[0.36824,0.63175,0.5],[0.3763,0.63841,0.5],[0.37765,0.64221,0.5],[0.37899,0.64221,0.5],[0.39109,0.6365,0.5],[0.39916,0.62509,0.5],[0.40319,0.61463,0.5],[0.41261,0.60797,0.5],[0.40857,0.60226,0.5],[0.40723,0.60131,0.5],[0.39916,0.60131,0.5],[0.39513,0.60797,0.5],[0.39109,0.61463,0.5],[0.39244,0.62794,0.5],[0.39513,0.6365,0.5],[0.39782,0.63841,0.5],[0.4005,0.6365,0.5],[0.40454,0.6346,0.5],[0.40588,0.6308,0.5],[0.40723,0.62128,0.5],[0.40857,0.61082,0.5],[0.40992,0.60606,0.5],[0.40992,0.60321,0.5],[0.41126,0.60606,0.5],[0.41664,0.61558,0.5],[0.42202,0.62033,0.5],[0.42874,0.62224,0.5],[0.43008,0.62224,0.5],[0.43143,0.62224,0.5],[0.43143,0.61367,0.5],[0.43008,0.60321,0.5],[0.42739,0.58704,0.5],[0.42605,0.58038,0.5],[0.42605,0.57658,0.5],[0.42605,0.57087,0.5],[0.42336,0.56897,0.5],[0.42336,0.56992,0.5],[0.42739,0.57658,0.5],[0.43277,0.59845,0.5],[0.44487,0.62224,0.5],[0.45294,0.6308,0.5],[0.45563,0.63175,0.5],[0.45429,0.63175,0.5],[0.4516,0.62794,0.5],[0.44622,0.62128,0.5],[0.43008,0.61272,0.5],[0.42874,0.61082,0.5],[0.42874,0.60987,0.5],[0.42739,0.60892,0.5],[0.42605,0.60702,0.5],[0.42471,0.60511,0.5],[0.42471,0.60416,0.5],[0.42336,0.60321,0.5],[0.42336,0.60226,0.5],[0.42471,0.60226,0.5],[0.43277,0.60226,0.5],[0.44084,0.60226,0.5],[0.4516,0.60226,0.5],[0.46101,0.5975,0.5],[0.47042,0.5918,0.5],[0.47176,0.58799,0.5],[0.47176,0.58704,0.5],[0.47042,0.58704,0.5],[0.47042,0.58799,0.5],[0.47042,0.59845,0.5],[0.46908,0.61653,0.5],[0.46235,0.6308,0.5],[0.46504,0.64031,0.5],[0.47445,0.64221,0.5],[0.4758,0.64221,0.5],[0.48252,0.6346,0.5],[0.49731,0.62224,0.5],[0.51613,0.61177,0.5],[0.52017,0.59845,0.5],[0.51479,0.58514,0.5],[0.50538,0.57943,0.5],[0.50403,0.57848,0.5],[0.50269,0.57848,0.5],[0.49731,0.57943,0.5],[0.49597,0.57943,0.5],[0.49462,0.58133,0.5],[0.49328,0.58133,0.5],[0.49328,0.58228,0.5],[0.49462,0.58228,0.5],[0.50672,0.58323,0.5],[0.5121,0.58038,0.5],[0.51748,0.57848,0.5],[0.5242,0.57562,0.5],[0.54975,0.57372,0.5],[0.58471,0.56516,0.5],[0.62908,0.5604,0.5],[0.64521,0.55945,0.5]]},{\"id\":\"st_1790844947155_u4jf00\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.61966,0.5604,0.5],[0.63311,0.57943,0.5],[0.64252,0.5937,0.5],[0.64924,0.60416,0.5],[0.65059,0.60702,0.5],[0.64924,0.60226,0.5],[0.64118,0.58419,0.5],[0.63714,0.56706,0.5],[0.63445,0.55375,0.5],[0.63445,0.54423,0.5],[0.63983,0.53472,0.5],[0.64521,0.53092,0.5],[0.65462,0.52901,0.5],[0.66134,0.52996,0.5],[0.66941,0.53377,0.5],[0.67882,0.54709,0.5],[0.69092,0.56136,0.5],[0.69361,0.57087,0.5],[0.69361,0.57658,0.5],[0.69361,0.57753,0.5],[0.69361,0.57658,0.5],[0.69227,0.56516,0.5],[0.69227,0.5566,0.5]]},{\"id\":\"st_1790844947746_sjztq1\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.69092,0.54233,0.5],[0.69092,0.54233,0.5],[0.6963,0.54994,0.5],[0.71647,0.56992,0.5],[0.73261,0.58038,0.5],[0.73395,0.58133,0.5],[0.73395,0.58038,0.5],[0.72319,0.56992,0.5],[0.71647,0.55945,0.5]]},{\"id\":\"st_1790844948035_fltfxy\",\"tool\":\"pen\",\"color\":\"#111827\",\"width\":2.5,\"points\":[[0.69361,0.51094,0.5],[0.69765,0.50904,0.5],[0.70034,0.50904,0.5],[0.70168,0.50904,0.5]]}]}',26,54,'2026-10-01 06:48:03','2026-10-01 08:55:49');
/*!40000 ALTER TABLE `submission_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_applications`
--

DROP TABLE IF EXISTS `teacher_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `qualification` varchar(255) NOT NULL,
  `experience_years` int(11) NOT NULL DEFAULT 3,
  `preferred_subjects` varchar(255) NOT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teacher_applications_user_id_foreign` (`user_id`),
  CONSTRAINT `teacher_applications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_applications`
--

LOCK TABLES `teacher_applications` WRITE;
/*!40000 ALTER TABLE `teacher_applications` DISABLE KEYS */;
/*!40000 ALTER TABLE `teacher_applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_earnings`
--

DROP TABLE IF EXISTS `teacher_earnings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_earnings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` bigint(20) unsigned NOT NULL,
  `teaching_gig_id` bigint(20) unsigned DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `base_amount` decimal(8,2) NOT NULL,
  `bonus_amount` decimal(8,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(8,2) NOT NULL,
  `status` enum('pending','approved','paid') NOT NULL DEFAULT 'approved',
  `payout_batch_id` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teacher_earnings_teacher_id_foreign` (`teacher_id`),
  KEY `teacher_earnings_teaching_gig_id_foreign` (`teaching_gig_id`),
  CONSTRAINT `teacher_earnings_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_earnings_teaching_gig_id_foreign` FOREIGN KEY (`teaching_gig_id`) REFERENCES `teaching_gigs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_earnings`
--

LOCK TABLES `teacher_earnings` WRITE;
/*!40000 ALTER TABLE `teacher_earnings` DISABLE KEYS */;
/*!40000 ALTER TABLE `teacher_earnings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_subject`
--

DROP TABLE IF EXISTS `teacher_subject`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_subject` (
  `teacher_id` bigint(20) unsigned NOT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`teacher_id`,`subject_id`),
  KEY `teacher_subject_subject_id_foreign` (`subject_id`),
  CONSTRAINT `teacher_subject_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_subject_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_subject`
--

LOCK TABLES `teacher_subject` WRITE;
/*!40000 ALTER TABLE `teacher_subject` DISABLE KEYS */;
INSERT INTO `teacher_subject` VALUES (6,34),(6,35),(6,36),(7,2),(7,14),(7,19),(7,24),(7,28),(8,3),(8,4),(8,15),(8,16),(8,20),(8,21),(8,25),(8,29),(8,30),(8,31);
/*!40000 ALTER TABLE `teacher_subject` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teachers`
--

DROP TABLE IF EXISTS `teachers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teachers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `application_status` varchar(20) NOT NULL DEFAULT 'active',
  `user_id` bigint(20) unsigned NOT NULL,
  `aster_employee_id` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 1,
  `is_online` tinyint(1) NOT NULL DEFAULT 1,
  `hourly_rate` decimal(8,2) NOT NULL DEFAULT 50.00,
  `tier` varchar(255) NOT NULL DEFAULT 'senior',
  `wallet_balance` decimal(10,2) NOT NULL DEFAULT 1450.00,
  `acceptance_rate` int(11) NOT NULL DEFAULT 96,
  `completion_rate` int(11) NOT NULL DEFAULT 98,
  `total_gigs_completed` int(11) NOT NULL DEFAULT 42,
  `qualification` varchar(255) NOT NULL,
  `experience_years` int(11) NOT NULL DEFAULT 0,
  `bio` text DEFAULT NULL,
  `teaching_style` varchar(255) DEFAULT NULL,
  `rating` decimal(3,1) NOT NULL DEFAULT 5.0,
  `total_students` int(11) NOT NULL DEFAULT 0,
  `photo` varchar(255) DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teachers_user_id_foreign` (`user_id`),
  CONSTRAINT `teachers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teachers`
--

LOCK TABLES `teachers` WRITE;
/*!40000 ALTER TABLE `teachers` DISABLE KEYS */;
INSERT INTO `teachers` VALUES (6,'active',34,NULL,1,1,50.00,'senior',1450.00,96,98,42,'B.Ed Early Childhood Education, UM',7,'Dedicated specialist educator with 7 years of teaching experience, specialising in Preschool Literacy & Early Childhood Development.','Montessori & Play-Based Interactive Learning',5.0,180,'images/teacher-aisyah.png','Preschool Literacy & Early Childhood Development','2026-08-21 01:04:39','2026-09-10 20:17:29'),(7,'active',35,NULL,1,1,50.00,'senior',1450.00,96,98,42,'Dip. Early Childhood & Phonics, OUM',6,'Dedicated specialist educator with 6 years of teaching experience, specialising in Early Numeracy & Phonics English/BM.','Multisensory & Storytelling Method',4.9,165,'images/teacher-melissa.png','Early Numeracy & Phonics English/BM','2026-08-21 01:04:40','2026-08-21 01:04:40'),(8,'active',36,NULL,1,1,50.00,'senior',1450.00,96,98,42,'B.Ed Primary Education (KSSR), UPSI',10,'Dedicated specialist educator with 10 years of teaching experience, specialising in Primary Mathematics & Science KSSR UASA.','Inquiry-Based & Problem Solving',4.9,240,'images/teacher-daniel.png','Primary Mathematics & Science KSSR UASA','2026-08-21 01:04:40','2026-08-21 01:04:40');
/*!40000 ALTER TABLE `teachers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teaching_gigs`
--

DROP TABLE IF EXISTS `teaching_gigs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teaching_gigs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `gig_type` enum('substitute_sos','scheduled_class','private_tutoring','exam_workshop') NOT NULL DEFAULT 'scheduled_class',
  `scheduled_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `duration_minutes` int(11) NOT NULL DEFAULT 60,
  `payout_amount` decimal(8,2) NOT NULL DEFAULT 50.00,
  `surge_bonus` decimal(8,2) NOT NULL DEFAULT 0.00,
  `status` enum('open','claimed','checked_in','completed','cancelled') NOT NULL DEFAULT 'open',
  `assigned_teacher_id` bigint(20) unsigned DEFAULT NULL,
  `claimed_at` timestamp NULL DEFAULT NULL,
  `checked_in_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `meeting_url` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teaching_gigs_subject_id_foreign` (`subject_id`),
  KEY `teaching_gigs_assigned_teacher_id_foreign` (`assigned_teacher_id`),
  CONSTRAINT `teaching_gigs_assigned_teacher_id_foreign` FOREIGN KEY (`assigned_teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `teaching_gigs_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teaching_gigs`
--

LOCK TABLES `teaching_gigs` WRITE;
/*!40000 ALTER TABLE `teaching_gigs` DISABLE KEYS */;
/*!40000 ALTER TABLE `teaching_gigs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testimonials`
--

DROP TABLE IF EXISTS `testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testimonials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'Student',
  `subject` varchar(255) DEFAULT NULL,
  `quote` text NOT NULL,
  `rating` decimal(2,1) NOT NULL DEFAULT 5.0,
  `photo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testimonials`
--

LOCK TABLES `testimonials` WRITE;
/*!40000 ALTER TABLE `testimonials` DISABLE KEYS */;
INSERT INTO `testimonials` VALUES (1,'Muhammad Hariz','SPM Student',NULL,'I went from a C to an A+ in Mathematics after just 3 months with Olympian Scholars. The teachers are patient, thorough, and truly care about your progress.',5.0,NULL,1,'2026-08-11 17:41:41','2026-08-11 17:41:41'),(2,'Puan Rosmah','Parent',NULL,'As a parent, I can see the real improvement in my daughter\'s confidence and grades. The portal makes it easy for me to track her progress weekly.',5.0,NULL,1,'2026-08-11 17:41:41','2026-08-11 17:41:41'),(3,'Priya Nair','IGCSE Student',NULL,'The interactive lessons and live Q&A sessions made learning Chemistry so much less intimidating. I\'m now consistently scoring above 90% in my practice papers.',4.8,NULL,1,'2026-08-11 17:41:41','2026-08-11 17:41:41');
/*!40000 ALTER TABLE `testimonials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_notifications`
--

DROP TABLE IF EXISTS `user_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'info',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `user_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=255 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_notifications`
--

LOCK TABLES `user_notifications` WRITE;
/*!40000 ALTER TABLE `user_notifications` DISABLE KEYS */;
INSERT INTO `user_notifications` VALUES (1,10,'quiz','NEW DRILL QUIZ ADDED','A new practice drill \'Quiz 2 — Chapter 2\' has been added to your Mathematics workspace. [Quiz ID: 2]','2026-08-18 19:16:25','2026-08-18 19:15:56','2026-08-18 19:16:25'),(13,10,'task','New Assignment Assigned','Your teacher assigned \'tese\' for Mathematics. Due on 27 Aug 2026.','2026-08-19 23:00:46','2026-08-19 19:37:21','2026-08-19 23:00:46'),(25,10,'task','New Assignment Assigned','Your teacher assigned \'Try\' for Mathematics. Due on 27 Aug 2026.','2026-08-19 23:00:46','2026-08-19 19:37:21','2026-08-19 23:00:46'),(37,10,'task','New Assignment Assigned','Your teacher assigned \'Chapter Review 1\' for Mathematics. Due on 15 Aug 2026.','2026-08-19 23:00:46','2026-08-19 19:37:21','2026-08-19 23:00:46'),(49,10,'task','New Assignment Assigned','Your teacher assigned \'Past Year Practice 2\' for Mathematics. Due on 06 Aug 2026.','2026-08-19 23:00:46','2026-08-19 19:37:21','2026-08-19 23:00:46'),(61,10,'task','New Assignment Assigned','Your teacher assigned \'Formula Sheet Exercise 3\' for Mathematics. Due on 20 Aug 2026.','2026-08-19 23:00:46','2026-08-19 19:37:22','2026-08-19 23:00:46'),(73,10,'task','New Assignment Assigned','Your teacher assigned \'Essay Writing 4\' for Mathematics. Due on 05 Aug 2026.','2026-08-19 23:00:46','2026-08-19 19:37:22','2026-08-19 23:00:46'),(116,10,'grade','Assignment Graded & Reviewed','Your teacher graded your submission for \'tese\' with a score of 100/100. Feedback: \"BAGUS\"','2026-08-19 23:00:46','2026-08-19 19:37:22','2026-08-19 23:00:46'),(135,10,'task','Official Exam Scheduled','Your teacher assigned Exam \'try\' for Mathematics. Due on 05 Sep 2026.',NULL,'2026-08-26 01:12:19','2026-08-26 01:12:19'),(150,212,'invoice','NEW INVOICE ISSUED','Invoice #INV-2026-00001 (Tuition Fee September 2026 MUHAMMAD ADAM DANISH) for RM 220.00 has been issued. Due: 10 Sep 2026.',NULL,'2026-09-03 01:12:10','2026-09-03 01:12:10'),(151,212,'invoice','PAYMENT DUE DATE UPDATED','The payment due date for invoice #INV-2026-00001 (Tuition Fee September 2026 MUHAMMAD ADAM DANISH) has been updated to 09 Sep 2026. Note: Extension granted.',NULL,'2026-09-07 22:56:10','2026-09-07 22:56:10'),(152,211,'invoice','PAYMENT DUE DATE UPDATED','The payment due date for MUHAMMAD ADAM DANISH\'s invoice #INV-2026-00001 (Tuition Fee September 2026 MUHAMMAD ADAM DANISH) has been updated to 09 Sep 2026. Note: Extension granted.',NULL,'2026-09-07 22:56:10','2026-09-07 22:56:10'),(153,212,'invoice','INVOICE DETAILS UPDATED','Invoice #INV-2026-00001 has been updated. Amount due: RM 220.00. Due date: 09 Sep 2026.',NULL,'2026-09-07 22:56:10','2026-09-07 22:56:10'),(154,211,'invoice','INVOICE DETAILS UPDATED','Invoice #INV-2026-00001 for MUHAMMAD ADAM DANISH has been updated. Amount due: RM 220.00. Due date: 09 Sep 2026.',NULL,'2026-09-07 22:56:10','2026-09-07 22:56:10'),(155,212,'invoice','INVOICE STATUS UPDATED','The status of invoice #INV-2026-00001 (Tuition Fee September 2026 MUHAMMAD ADAM DANISH) is now ISSUED.',NULL,'2026-09-07 22:56:10','2026-09-07 22:56:10'),(156,211,'invoice','INVOICE STATUS UPDATED','The status of MUHAMMAD ADAM DANISH\'s invoice #INV-2026-00001 (Tuition Fee September 2026 MUHAMMAD ADAM DANISH) is now ISSUED.',NULL,'2026-09-07 22:56:10','2026-09-07 22:56:10'),(157,34,'course','NEW COURSE ASSIGNED','The course \'bahasa melayu (Primary School (KSSR))\' (Primary) has been assigned to your educator profile.','2026-09-08 19:17:31','2026-09-08 01:36:07','2026-09-08 19:17:31'),(158,34,'live_class','LIVE CLASS SCHEDULED','A live session \'Ch. 01: belajar — belajar\' for bahasa melayu (Primary School (KSSR)) has been scheduled for 12 Sep 2026, 06:38 PM.','2026-09-08 19:17:31','2026-09-08 01:39:17','2026-09-08 19:17:31'),(160,34,'material','Material Uploaded','You successfully uploaded Notes \'test\' for bahasa melayu (Primary School (KSSR)).','2026-09-08 19:17:31','2026-09-08 01:48:50','2026-09-08 19:17:31'),(161,34,'task','Assignment Published & Assigned','You successfully assigned Assignment \'test\' to bahasa melayu (Primary School (KSSR)) (0 students).','2026-09-08 19:17:31','2026-09-08 01:49:21','2026-09-08 19:17:31'),(162,214,'invoice','NEW INVOICE ISSUED','Invoice #INV-2026-00003 (Tuition Fee September 2026 SYAZRIL IZHAN BIN SHUHAIMI) for RM 60.00 has been issued. Due: 16 Sep 2026.',NULL,'2026-09-08 18:53:40','2026-09-08 18:53:40'),(163,34,'account','ACCOUNT STATUS UPDATED','Your educator account status has been updated to Inactive.','2026-09-29 09:57:10','2026-09-08 19:28:03','2026-09-29 09:57:10'),(165,34,'account','ACCOUNT STATUS UPDATED','Your educator account status has been updated to Active.','2026-09-29 09:57:10','2026-09-10 20:17:29','2026-09-29 09:57:10'),(166,34,'subject','TEACHING SUBJECTS UPDATED','Your assigned teaching subjects have been updated.','2026-09-29 09:57:10','2026-09-10 23:28:23','2026-09-29 09:57:10'),(167,34,'class_reminder','New Class Session Scheduled by Administration','A new live class session \'Admin Special Assessment Prep\' has been scheduled for 25 Sep 2026, 02:00 PM.','2026-09-29 09:57:10','2026-09-16 18:56:59','2026-09-29 09:57:10'),(168,10,'class_reminder','Class Rescheduled: Admin Special Assessment Prep','Teacher CIKGU AISYAH SOFIA has rescheduled Admin Special Assessment Prep from Fri, 25 Sep 02:00 PM to Sat, 26 Sep 03:30 PM. Reason: \"Administrative schedule adjustment\".',NULL,'2026-09-16 18:56:59','2026-09-16 18:56:59'),(170,214,'class_reminder','Class Rescheduled: Admin Special Assessment Prep','Teacher CIKGU AISYAH SOFIA has rescheduled Admin Special Assessment Prep from Fri, 25 Sep 02:00 PM to Sat, 26 Sep 03:30 PM. Reason: \"Administrative schedule adjustment\".',NULL,'2026-09-16 18:56:59','2026-09-16 18:56:59'),(171,215,'class_reminder','Child\'s Class Rescheduled (SYAZRIL IZHAN BIN SHUHAIMI)','Admin Special Assessment Prep for SYAZRIL IZHAN BIN SHUHAIMI has been moved to Sat, 26 Sep 03:30 PM by CIKGU AISYAH SOFIA. Reason: \"Administrative schedule adjustment\".',NULL,'2026-09-16 18:56:59','2026-09-16 18:56:59'),(172,34,'class_reminder','Class Session Rescheduled by Administrator','Session \'Admin Special Assessment Prep\' has been moved to 26 Sep 2026, 03:30 PM by administration. Reason: \"Administrative schedule adjustment\".','2026-09-29 09:57:10','2026-09-16 18:56:59','2026-09-29 09:57:10'),(173,34,'class_reminder','New Class Session Scheduled by Administration','A new live class session \'Admin Special Assessment Prep\' has been scheduled for 25 Sep 2026, 02:00 PM.','2026-09-29 09:57:10','2026-09-16 18:57:30','2026-09-29 09:57:10'),(174,10,'class_reminder','Class Rescheduled: Admin Special Assessment Prep','Teacher CIKGU AISYAH SOFIA has rescheduled Admin Special Assessment Prep from Fri, 25 Sep 02:00 PM to Sat, 26 Sep 03:30 PM. Reason: \"Administrative schedule adjustment\".',NULL,'2026-09-16 18:57:30','2026-09-16 18:57:30'),(176,214,'class_reminder','Class Rescheduled: Admin Special Assessment Prep','Teacher CIKGU AISYAH SOFIA has rescheduled Admin Special Assessment Prep from Fri, 25 Sep 02:00 PM to Sat, 26 Sep 03:30 PM. Reason: \"Administrative schedule adjustment\".',NULL,'2026-09-16 18:57:30','2026-09-16 18:57:30'),(177,215,'class_reminder','Child\'s Class Rescheduled (SYAZRIL IZHAN BIN SHUHAIMI)','Admin Special Assessment Prep for SYAZRIL IZHAN BIN SHUHAIMI has been moved to Sat, 26 Sep 03:30 PM by CIKGU AISYAH SOFIA. Reason: \"Administrative schedule adjustment\".',NULL,'2026-09-16 18:57:30','2026-09-16 18:57:30'),(178,34,'class_reminder','Class Session Rescheduled by Administrator','Session \'Admin Special Assessment Prep\' has been moved to 26 Sep 2026, 03:30 PM by administration. Reason: \"Administrative schedule adjustment\".','2026-09-29 09:57:10','2026-09-16 18:57:30','2026-09-29 09:57:10'),(179,212,'payment','PAYMENT SLIP SUBMITTED','Bank transfer slip for invoice #INV-2026-00001 (RM 220.00) has been received and is pending verification.',NULL,'2026-09-17 01:28:08','2026-09-17 01:28:08'),(180,1,'payment','NEW PAYMENT SLIP PENDING','New bank transfer slip submitted by MUHAMMAD ADAM DANISH for invoice #INV-2026-00001 (RM 220.00).',NULL,'2026-09-17 01:28:08','2026-09-17 01:28:08'),(181,212,'payment','PAYMENT VERIFIED','Your bank transfer payment of RM 220.00 for invoice # has been verified and approved. Receipt: RCP-2026-00001.',NULL,'2026-09-17 01:40:02','2026-09-17 01:40:02'),(183,1,'payment','NEW PAYMENT SLIP PENDING','New bank transfer slip submitted by TRY JE for invoice #INV-2026-00002 (RM 0.00).',NULL,'2026-09-17 17:28:29','2026-09-17 17:28:29'),(185,214,'payment','PAYMENT SLIP SUBMITTED','Bank transfer slip for invoice #INV-2026-00003 (RM 60.00) has been received and is pending verification.',NULL,'2026-09-17 18:21:49','2026-09-17 18:21:49'),(186,1,'payment','NEW PAYMENT SLIP PENDING','New bank transfer slip submitted by SYAZRIL IZHAN BIN SHUHAIMI for invoice #INV-2026-00003 (RM 60.00).',NULL,'2026-09-17 18:21:49','2026-09-17 18:21:49'),(187,214,'payment','PAYMENT VERIFIED','Your bank transfer payment of RM 60.00 for invoice # has been verified and approved. Receipt: RCP-2026-00003.',NULL,'2026-09-17 18:22:24','2026-09-17 18:22:24'),(190,35,'class_reminder','New Class Session Scheduled by Administration','A new live class session \'Kira Kira apa ya\' has been scheduled for 29 Sep 2026, 10:00 AM.','2026-09-29 07:53:25','2026-09-28 06:38:47','2026-09-29 07:53:25'),(191,35,'class_reminder','New Class Session Scheduled by Administration','A new live class session \'Test Alignment Session\' has been scheduled for 05 Oct 2026, 02:00 PM.','2026-09-29 07:53:25','2026-09-28 07:22:59','2026-09-29 07:53:25'),(193,211,'class_reminder','New Class Scheduled for MUHAMMAD AIDILL AQASHA BIN YUNUS','A new live class session \'Test Alignment Session\' (English) has been scheduled for MUHAMMAD AIDILL AQASHA BIN YUNUS on Mon, 05 Oct 2026, 02:00 PM.',NULL,'2026-09-28 07:22:59','2026-09-28 07:22:59'),(195,211,'class_reminder','New Class Scheduled for MUHAMMAD ADAM DANISH','A new live class session \'Test Alignment Session\' (English) has been scheduled for MUHAMMAD ADAM DANISH on Mon, 05 Oct 2026, 02:00 PM.',NULL,'2026-09-28 07:22:59','2026-09-28 07:22:59'),(198,215,'class_reminder','New Class Scheduled for SYAZRIL IZHAN BIN SHUHAIMI','A new live class session \'Test Alignment Session\' (English) has been scheduled for SYAZRIL IZHAN BIN SHUHAIMI on Mon, 05 Oct 2026, 02:00 PM.',NULL,'2026-09-28 07:22:59','2026-09-28 07:22:59'),(199,210,'class_live','Class Live Now 🔴','Your live class \'Kira Kira apa ya\' with CIKGU MELISSA TAN is in progress now! Click to join. [LiveClass ID: 9] [Status: LIVE]','2026-09-29 03:33:48','2026-09-29 02:52:41','2026-09-29 03:33:48'),(200,210,'class_reminder','New Class Scheduled: English class','A new English live class \'English class\' with CIKGU MELISSA TAN has been scheduled for Wed, 30 Sep 2026, 10:00 AM.','2026-09-29 09:31:27','2026-09-29 07:50:46','2026-09-29 09:31:27'),(201,211,'class_reminder','New Class Scheduled for MUHAMMAD AIDILL AQASHA BIN YUNUS','A new live class session \'English class\' (English) has been scheduled for MUHAMMAD AIDILL AQASHA BIN YUNUS on Wed, 30 Sep 2026, 10:00 AM.',NULL,'2026-09-29 07:50:46','2026-09-29 07:50:46'),(202,212,'class_reminder','New Class Scheduled: English class','A new English live class \'English class\' with CIKGU MELISSA TAN has been scheduled for Wed, 30 Sep 2026, 10:00 AM.',NULL,'2026-09-29 07:50:46','2026-09-29 07:50:46'),(203,211,'class_reminder','New Class Scheduled for MUHAMMAD ADAM DANISH','A new live class session \'English class\' (English) has been scheduled for MUHAMMAD ADAM DANISH on Wed, 30 Sep 2026, 10:00 AM.',NULL,'2026-09-29 07:50:46','2026-09-29 07:50:46'),(205,214,'class_reminder','New Class Scheduled: English class','A new English live class \'English class\' with CIKGU MELISSA TAN has been scheduled for Wed, 30 Sep 2026, 10:00 AM.',NULL,'2026-09-29 07:50:46','2026-09-29 07:50:46'),(206,215,'class_reminder','New Class Scheduled for SYAZRIL IZHAN BIN SHUHAIMI','A new live class session \'English class\' (English) has been scheduled for SYAZRIL IZHAN BIN SHUHAIMI on Wed, 30 Sep 2026, 10:00 AM.',NULL,'2026-09-29 07:50:46','2026-09-29 07:50:46'),(207,34,'class_reminder','New Class Session Scheduled by Administration','A new live class session \'Class BM\' has been scheduled for 29 Sep 2026, 04:00 PM.','2026-09-29 09:57:10','2026-09-29 07:58:25','2026-09-29 09:57:10'),(208,10,'class_reminder','New Class Scheduled: Class BM','A new Bahasa Melayu live class \'Class BM\' with CIKGU AISYAH SOFIA has been scheduled for Tue, 29 Sep 2026, 04:00 PM.',NULL,'2026-09-29 07:58:25','2026-09-29 07:58:25'),(210,214,'class_reminder','New Class Scheduled: Class BM','A new Bahasa Melayu live class \'Class BM\' with CIKGU AISYAH SOFIA has been scheduled for Tue, 29 Sep 2026, 04:00 PM.',NULL,'2026-09-29 07:58:25','2026-09-29 07:58:25'),(211,215,'class_reminder','New Class Scheduled for SYAZRIL IZHAN BIN SHUHAIMI','A new live class session \'Class BM\' (Bahasa Melayu) has been scheduled for SYAZRIL IZHAN BIN SHUHAIMI on Tue, 29 Sep 2026, 04:00 PM.',NULL,'2026-09-29 07:58:25','2026-09-29 07:58:25'),(212,210,'class_reminder','New Class Scheduled: Class BM','A new Bahasa Melayu live class \'Class BM\' with CIKGU AISYAH SOFIA has been scheduled for Tue, 29 Sep 2026, 04:00 PM.','2026-09-29 09:31:27','2026-09-29 07:58:25','2026-09-29 09:31:27'),(213,211,'class_reminder','New Class Scheduled for MUHAMMAD AIDILL AQASHA BIN YUNUS','A new live class session \'Class BM\' (Bahasa Melayu) has been scheduled for MUHAMMAD AIDILL AQASHA BIN YUNUS on Tue, 29 Sep 2026, 04:00 PM.',NULL,'2026-09-29 07:58:25','2026-09-29 07:58:25'),(214,212,'class_reminder','New Class Scheduled: Class BM','A new Bahasa Melayu live class \'Class BM\' with CIKGU AISYAH SOFIA has been scheduled for Tue, 29 Sep 2026, 04:00 PM.',NULL,'2026-09-29 07:58:25','2026-09-29 07:58:25'),(215,211,'class_reminder','New Class Scheduled for MUHAMMAD ADAM DANISH','A new live class session \'Class BM\' (Bahasa Melayu) has been scheduled for MUHAMMAD ADAM DANISH on Tue, 29 Sep 2026, 04:00 PM.',NULL,'2026-09-29 07:58:25','2026-09-29 07:58:25'),(216,34,'class_reminder','New Class Session Scheduled by Administration','A new live class session \'Class BM\' has been scheduled for 29 Sep 2026, 04:41 PM.','2026-09-29 09:57:10','2026-09-29 08:39:30','2026-09-29 09:57:10'),(217,10,'class_reminder','New Class Scheduled: Class BM','A new Bahasa Melayu live class \'Class BM\' with CIKGU AISYAH SOFIA has been scheduled for Tue, 29 Sep 2026, 04:41 PM.',NULL,'2026-09-29 08:39:30','2026-09-29 08:39:30'),(219,214,'class_reminder','New Class Scheduled: Class BM','A new Bahasa Melayu live class \'Class BM\' with CIKGU AISYAH SOFIA has been scheduled for Tue, 29 Sep 2026, 04:41 PM.',NULL,'2026-09-29 08:39:30','2026-09-29 08:39:30'),(220,215,'class_reminder','New Class Scheduled for SYAZRIL IZHAN BIN SHUHAIMI','A new live class session \'Class BM\' (Bahasa Melayu) has been scheduled for SYAZRIL IZHAN BIN SHUHAIMI on Tue, 29 Sep 2026, 04:41 PM.',NULL,'2026-09-29 08:39:30','2026-09-29 08:39:30'),(221,210,'class_reminder','New Class Scheduled: Class BM','A new Bahasa Melayu live class \'Class BM\' with CIKGU AISYAH SOFIA has been scheduled for Tue, 29 Sep 2026, 04:41 PM.','2026-09-29 09:31:27','2026-09-29 08:39:30','2026-09-29 09:31:27'),(222,211,'class_reminder','New Class Scheduled for MUHAMMAD AIDILL AQASHA BIN YUNUS','A new live class session \'Class BM\' (Bahasa Melayu) has been scheduled for MUHAMMAD AIDILL AQASHA BIN YUNUS on Tue, 29 Sep 2026, 04:41 PM.',NULL,'2026-09-29 08:39:30','2026-09-29 08:39:30'),(223,212,'class_reminder','New Class Scheduled: Class BM','A new Bahasa Melayu live class \'Class BM\' with CIKGU AISYAH SOFIA has been scheduled for Tue, 29 Sep 2026, 04:41 PM.',NULL,'2026-09-29 08:39:30','2026-09-29 08:39:30'),(224,211,'class_reminder','New Class Scheduled for MUHAMMAD ADAM DANISH','A new live class session \'Class BM\' (Bahasa Melayu) has been scheduled for MUHAMMAD ADAM DANISH on Tue, 29 Sep 2026, 04:41 PM.',NULL,'2026-09-29 08:39:30','2026-09-29 08:39:30'),(225,10,'task','New Assignment Assigned','Your teacher assigned Assignment \'Test je ni\' for Bahasa Melayu. Due on 07 Oct 2026.',NULL,'2026-09-30 07:10:53','2026-09-30 07:10:53'),(227,214,'task','New Assignment Assigned','Your teacher assigned Assignment \'Test je ni\' for Bahasa Melayu. Due on 07 Oct 2026.',NULL,'2026-09-30 07:10:53','2026-09-30 07:10:53'),(228,210,'task','New Assignment Assigned','Your teacher assigned Assignment \'Test je ni\' for Bahasa Melayu. Due on 07 Oct 2026.',NULL,'2026-09-30 07:10:53','2026-09-30 07:10:53'),(229,212,'task','New Assignment Assigned','Your teacher assigned Assignment \'Test je ni\' for Bahasa Melayu. Due on 07 Oct 2026.',NULL,'2026-09-30 07:10:53','2026-09-30 07:10:53'),(230,34,'task','Assignment Published & Assigned','You successfully assigned Assignment \'Test je ni\' to Bahasa Melayu (KSSR) (5 students).',NULL,'2026-09-30 07:10:53','2026-09-30 07:10:53'),(231,34,'submission','Interactive Assignment Submitted','MUHAMMAD AIDILL AQASHA BIN YUNUS submitted handwritten answers for \'Test je ni\'.',NULL,'2026-09-30 07:12:50','2026-09-30 07:12:50'),(232,210,'task','Assignment Submitted','You successfully submitted answers for \'Test je ni\'.',NULL,'2026-09-30 07:12:50','2026-09-30 07:12:50'),(233,34,'submission','Interactive Assignment Submitted','MUHAMMAD ADAM DANISH submitted handwritten answers for \'Test je ni\'.',NULL,'2026-09-30 07:17:17','2026-09-30 07:17:17'),(234,212,'task','Assignment Submitted','You successfully submitted answers for \'Test je ni\'.',NULL,'2026-09-30 07:17:17','2026-09-30 07:17:17'),(235,212,'grade','Assignment Graded & Reviewed','Your teacher graded your submission for \'Test je ni\' with a score of 100/100. Feedback: \"Bagus je weh tu.\"',NULL,'2026-09-30 07:18:24','2026-09-30 07:18:24'),(236,34,'grade','Submission Graded','You reviewed and graded MUHAMMAD ADAM DANISH\'s submission for \'Test je ni\' (100/100).',NULL,'2026-09-30 07:18:24','2026-09-30 07:18:24'),(237,34,'submission','Interactive Assignment Submitted','SYAZRIL IZHAN BIN SHUHAIMI submitted handwritten answers for \'Test je ni\'.',NULL,'2026-09-30 07:24:56','2026-09-30 07:24:56'),(238,214,'task','Assignment Submitted','You successfully submitted answers for \'Test je ni\'.',NULL,'2026-09-30 07:24:56','2026-09-30 07:24:56'),(239,214,'grade','Assignment Graded & Reviewed','Your teacher graded your submission for \'Test je ni\' with a score of 90/100. Feedback: \"BAGUS LEBIHKAN EFFORT!\"',NULL,'2026-09-30 07:25:43','2026-09-30 07:25:43'),(240,34,'grade','Submission Graded','You reviewed and graded SYAZRIL IZHAN BIN SHUHAIMI\'s submission for \'Test je ni\' (90/100).',NULL,'2026-09-30 07:25:43','2026-09-30 07:25:43'),(241,34,'submission','Interactive Assignment Submitted','MUHAMMAD HARIZ submitted handwritten answers for \'Test je ni\'.',NULL,'2026-09-30 09:51:50','2026-09-30 09:51:50'),(242,10,'task','Assignment Submitted','You successfully submitted answers for \'Test je ni\'.',NULL,'2026-09-30 09:51:50','2026-09-30 09:51:50'),(243,10,'grade','Assignment Graded & Reviewed','Your teacher graded your submission for \'Test je ni\' with a score of 100/100. Feedback: \"Bagus awak ni\"',NULL,'2026-09-30 09:52:27','2026-09-30 09:52:27'),(244,34,'grade','Submission Graded','You reviewed and graded MUHAMMAD HARIZ\'s submission for \'Test je ni\' (100/100).',NULL,'2026-09-30 09:52:27','2026-09-30 09:52:27'),(245,34,'class_reminder','New Class Session Scheduled by Administration','A new live class session \'Class\' has been scheduled for 01 Oct 2026, 10:00 AM.',NULL,'2026-09-30 09:58:21','2026-09-30 09:58:21'),(246,10,'class_reminder','New Class Scheduled: Class','A new Bahasa Melayu live class \'Class\' with CIKGU AISYAH SOFIA has been scheduled for Thu, 01 Oct 2026, 10:00 AM.',NULL,'2026-09-30 09:58:21','2026-09-30 09:58:21'),(248,214,'class_reminder','New Class Scheduled: Class','A new Bahasa Melayu live class \'Class\' with CIKGU AISYAH SOFIA has been scheduled for Thu, 01 Oct 2026, 10:00 AM.',NULL,'2026-09-30 09:58:21','2026-09-30 09:58:21'),(249,215,'class_reminder','New Class Scheduled for SYAZRIL IZHAN BIN SHUHAIMI','A new live class session \'Class\' (Bahasa Melayu) has been scheduled for SYAZRIL IZHAN BIN SHUHAIMI on Thu, 01 Oct 2026, 10:00 AM.',NULL,'2026-09-30 09:58:21','2026-09-30 09:58:21'),(250,210,'class_reminder','New Class Scheduled: Class','A new Bahasa Melayu live class \'Class\' with CIKGU AISYAH SOFIA has been scheduled for Thu, 01 Oct 2026, 10:00 AM.',NULL,'2026-09-30 09:58:21','2026-09-30 09:58:21'),(251,211,'class_reminder','New Class Scheduled for MUHAMMAD AIDILL AQASHA BIN YUNUS','A new live class session \'Class\' (Bahasa Melayu) has been scheduled for MUHAMMAD AIDILL AQASHA BIN YUNUS on Thu, 01 Oct 2026, 10:00 AM.',NULL,'2026-09-30 09:58:21','2026-09-30 09:58:21'),(252,212,'class_reminder','New Class Scheduled: Class','A new Bahasa Melayu live class \'Class\' with CIKGU AISYAH SOFIA has been scheduled for Thu, 01 Oct 2026, 10:00 AM.',NULL,'2026-09-30 09:58:21','2026-09-30 09:58:21'),(253,211,'class_reminder','New Class Scheduled for MUHAMMAD ADAM DANISH','A new live class session \'Class\' (Bahasa Melayu) has been scheduled for MUHAMMAD ADAM DANISH on Thu, 01 Oct 2026, 10:00 AM.',NULL,'2026-09-30 09:58:21','2026-09-30 09:58:21'),(254,210,'task','Assignment Submitted','You successfully submitted answers for \'MANUAL QA TEST FLOW vertical\'.',NULL,'2026-10-01 09:45:40','2026-10-01 09:45:40');
/*!40000 ALTER TABLE `user_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','parent','teacher','admin') NOT NULL DEFAULT 'student',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `phone` varchar(20) DEFAULT NULL,
  `nationality_type` varchar(255) NOT NULL DEFAULT 'local',
  `passport_number` varchar(255) DEFAULT NULL,
  `address_line_1` varchar(255) DEFAULT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `postcode` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'Malaysia',
  `ic_number_hash` varchar(64) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_ic_number_hash_unique` (`ic_number_hash`),
  KEY `users_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=216 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'ADMIN OLYMPIAN','admin@olympianscholars.my',NULL,'$2y$12$4MYzP2zEBgBB/XbFPFcYAeunr5CLAxg0/C2js3rd0.HQDO3XcO2cS','admin',1,NULL,'local',NULL,NULL,NULL,NULL,NULL,NULL,'Malaysia',NULL,NULL,NULL,'2026-08-11 17:41:34','2026-09-01 22:51:35'),(10,'MUHAMMAD HARIZ','student1@olympianscholars.my',NULL,'$2y$12$6mKyJczK3ny21Nq/J0TYJ.fvN.W2VccV5aOEb0q/Lk1N4.adGOp8i','student',1,NULL,'local',NULL,NULL,NULL,NULL,NULL,NULL,'Malaysia',NULL,NULL,NULL,'2026-08-11 17:41:36','2026-09-20 23:22:13'),(34,'CIKGU AISYAH SOFIA','aisyah.sofia@olympianscholars.my',NULL,'$2y$12$NGdg5yKIz2iCEczKECIugud1gJIAHtDzIOW5TvHw8C.T/CZC6B6H.','teacher',1,NULL,'local',NULL,NULL,NULL,NULL,NULL,NULL,'Malaysia',NULL,NULL,NULL,'2026-08-21 01:04:39','2026-08-21 01:04:39'),(35,'CIKGU MELISSA TAN','melissa.tan@olympianscholars.my',NULL,'$2y$12$ri4us.ooHeDZbFdMjO.SmeNXv8u1VvBqaeOHYLjcJQR5e63gQi4Lm','teacher',1,NULL,'local',NULL,NULL,NULL,NULL,NULL,NULL,'Malaysia',NULL,NULL,NULL,'2026-08-21 01:04:40','2026-08-21 01:04:40'),(36,'CIKGU HAFIZ RAZAK','hafiz.razak@olympianscholars.my',NULL,'$2y$12$ZMc91IGJoQ5307yMkcM41.JxiEJQpLKi1vUQNy90J6fINkAqLOynW','teacher',1,NULL,'local',NULL,NULL,NULL,NULL,NULL,NULL,'Malaysia',NULL,NULL,NULL,'2026-08-21 01:04:40','2026-08-21 01:04:40'),(210,'MUHAMMAD AIDILL AQASHA BIN YUNUS','aqashayunus03@gmail.com',NULL,'$2y$12$1F3kV/9DbKQ/zGYtFEKmuuR.ynN6YpShumYNP6rP3cjuI1qaJXT7u','student',1,'0135831410','local',NULL,NULL,NULL,NULL,NULL,NULL,'Malaysia',NULL,NULL,NULL,'2026-09-01 19:49:35','2026-09-01 19:49:35'),(211,'YUNUS BIN DERAMAN','ttokwangs@gmail.com',NULL,'$2y$12$pJ9NHAz0imfzREd19ePyLOeGecj2DZ3neA/UVcug3Z0A.8TpAZVPO','parent',1,'0192062661','local',NULL,NULL,NULL,NULL,NULL,NULL,'Malaysia','af0ba3e217c030aed5d51e0da850e5ea371bc586f3a3fb34dc696e20a5091bd5',NULL,NULL,'2026-09-01 19:57:55','2026-09-16 23:42:48'),(212,'MUHAMMAD ADAM DANISH','aqashayunus09@gmail.com',NULL,'$2y$12$vol8w9SFQPue9HglmIAfCexK8vAwvF17U/wK.NX3YW32a.PerLn5q','student',1,'0123456789','local',NULL,NULL,NULL,NULL,NULL,NULL,'Malaysia',NULL,NULL,NULL,'2026-09-03 01:12:09','2026-09-03 01:12:09'),(214,'SYAZRIL IZHAN BIN SHUHAIMI','aqashayunus05@gmail.com',NULL,'$2y$12$9hyJ1Iloo/CAsWegHX4Z3.t2/Yc0imRFlgKkcZp9mUaBD8JJzm1M2','student',1,'0135831410','local',NULL,NULL,NULL,NULL,NULL,NULL,'Malaysia',NULL,NULL,NULL,'2026-09-08 18:53:40','2026-09-08 18:53:40'),(215,'MAK SYAZRIL','maksyazril@gmail.com',NULL,'$2y$12$vesQoMDuc8paKhIJhLomWOIlvI5w4ZMuGT9Zps2EFxdGYJzikQTBm','parent',1,'01234558901','local',NULL,'12,jalan contoh',NULL,'43500','Semenyih','Selangor','Malaysia','1872bf1f7426518ac3146d6e5f30d863e6227d02e617c4e4256f0c841dd816b0',NULL,NULL,'2026-09-08 19:00:25','2026-09-08 19:00:25');
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

-- Dump completed on 2026-10-01 18:15:57
