-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.7.31-log - MySQL Community Server (GPL)
-- Server OS:                    Win32
-- HeidiSQL Version:             11.0.0.5919
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for system_db
DROP DATABASE IF EXISTS `system_db`;
CREATE DATABASE IF NOT EXISTS `system_db` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_bin */;
USE `system_db`;

-- Dumping structure for table system_db.account
DROP TABLE IF EXISTS `account`;
CREATE TABLE IF NOT EXISTS `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8_bin NOT NULL,
  `name` varchar(50) COLLATE utf8_bin NOT NULL,
  `s_name` varchar(50) COLLATE utf8_bin NOT NULL,
  `device_id` varchar(50) COLLATE utf8_bin NOT NULL,
  `rank` int(11) NOT NULL DEFAULT '0',
  `msg_not` int(11) NOT NULL DEFAULT '0',
  `req_not` int(11) NOT NULL DEFAULT '0',
  `active` int(11) NOT NULL DEFAULT '0',
  `sklad` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Dumping data for table system_db.account: ~2 rows (approximately)
DELETE FROM `account`;
/*!40000 ALTER TABLE `account` DISABLE KEYS */;
INSERT INTO `account` (`id`, `username`, `name`, `s_name`, `device_id`, `rank`, `msg_not`, `req_not`, `active`, `sklad`) VALUES
	(1, 'brannik', 'georgi', 'golemshinski', 'ffffffff-9333-3174-ffff-ffffef05ac4a', 2, 1, 1, 1, 1),
	(9, 'Mitaka', 'Mitaka ', ' Dimitrov', 'ffffffff-c665-08d3-ffff-ffffef05ac4a', 1, 1, 1, 1, 1);
/*!40000 ALTER TABLE `account` ENABLE KEYS */;

-- Dumping structure for table system_db.dates
DROP TABLE IF EXISTS `dates`;
CREATE TABLE IF NOT EXISTS `dates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ovner_id` int(11) NOT NULL,
  `sklad` int(11) NOT NULL DEFAULT '0',
  `type` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Dumping data for table system_db.dates: ~2 rows (approximately)
DELETE FROM `dates`;
/*!40000 ALTER TABLE `dates` DISABLE KEYS */;
INSERT INTO `dates` (`id`, `ovner_id`, `sklad`, `type`, `date`) VALUES
	(1, 1, 1, 1, '2020-12-03'),
	(2, 1, 1, 1, '2021-12-03');
/*!40000 ALTER TABLE `dates` ENABLE KEYS */;

-- Dumping structure for table system_db.documents
DROP TABLE IF EXISTS `documents`;
CREATE TABLE IF NOT EXISTS `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_number` int(11) NOT NULL DEFAULT '0',
  `owner_id` int(11) NOT NULL DEFAULT '0',
  `sklad` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Dumping data for table system_db.documents: ~4 rows (approximately)
DELETE FROM `documents`;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
INSERT INTO `documents` (`id`, `doc_number`, `owner_id`, `sklad`, `status`, `date`) VALUES
	(41, 2440896, 1, 1, 0, '2021-03-11'),
	(42, 2440897, 1, 1, 0, '2021-03-11'),
	(43, 2440899, 1, 1, 0, '2021-03-11'),
	(44, 2240993, 1, 1, 0, '2021-03-11');
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;

-- Dumping structure for table system_db.notifycations
DROP TABLE IF EXISTS `notifycations`;
CREATE TABLE IF NOT EXISTS `notifycations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `reciever_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `text` text COLLATE utf8_bin NOT NULL,
  `send_to_app` int(11) NOT NULL DEFAULT '0',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Dumping data for table system_db.notifycations: ~4 rows (approximately)
DELETE FROM `notifycations`;
/*!40000 ALTER TABLE `notifycations` DISABLE KEYS */;
INSERT INTO `notifycations` (`id`, `sender_id`, `reciever_id`, `type`, `status`, `text`, `send_to_app`, `date_created`) VALUES
	(1, 9, 1, 1, 0, '1', 1, '2021-03-11 09:04:12'),
	(2, 9, 0, 0, 0, 'This is system message and is not send to push notifications !!!! type=3 (status=0 = permanent / status=1 is 1 not send 1 week after date of creation / status=2 not seen after 1 day) ', 1, '2021-03-11 09:04:15'),
	(4, 9, 1, 2, 0, '2', 1, '2021-03-11 09:04:16'),
	(5, -1, 1, 3, 0, 'User $USER_NAME declined/accepted your date request', 1, '2021-03-07 17:30:48');
/*!40000 ALTER TABLE `notifycations` ENABLE KEYS */;

-- Dumping structure for table system_db.updates
DROP TABLE IF EXISTS `updates`;
CREATE TABLE IF NOT EXISTS `updates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` int(11) NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Dumping data for table system_db.updates: ~1 rows (approximately)
DELETE FROM `updates`;
/*!40000 ALTER TABLE `updates` DISABLE KEYS */;
INSERT INTO `updates` (`id`, `version`, `date`) VALUES
	(1, 1, '2021-03-11 09:58:39');
/*!40000 ALTER TABLE `updates` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
