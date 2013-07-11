
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table accounts
# ------------------------------------------------------------

CREATE TABLE `accounts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fireflyuser_id` int(11) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `balance` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fireflyuser_id` (`fireflyuser_id`),
  CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`fireflyuser_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table beneficiaries
# ------------------------------------------------------------

CREATE TABLE `beneficiaries` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fireflyuser_id` int(11) unsigned NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `name` varchar(750) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `fireflyuser_id_2` (`fireflyuser_id`),
  CONSTRAINT `beneficiaries_ibfk_1` FOREIGN KEY (`fireflyuser_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table budgets
# ------------------------------------------------------------

CREATE TABLE `budgets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `fireflyuser_id` int(11) unsigned NOT NULL,
  `name` varchar(500) NOT NULL DEFAULT '',
  `amount` decimal(10,2) unsigned NOT NULL,
  `date` date NOT NULL DEFAULT '1900-01-02',
  PRIMARY KEY (`id`),
  KEY `user_id` (`fireflyuser_id`),
  CONSTRAINT `budgets_ibfk_1` FOREIGN KEY (`fireflyuser_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table cache
# ------------------------------------------------------------

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL DEFAULT '',
  `value` longtext NOT NULL,
  `expiration` int(10) unsigned NOT NULL,
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table categories
# ------------------------------------------------------------

CREATE TABLE `categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `icon_id` int(11) unsigned DEFAULT '2',
  `fireflyuser_id` int(11) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `showtrend` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `fireflyuser_id` (`fireflyuser_id`,`name`),
  KEY `fireflyuser_id_2` (`fireflyuser_id`),
  KEY `icon_id` (`icon_id`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`fireflyuser_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `categories_ibfk_2` FOREIGN KEY (`icon_id`) REFERENCES `icons` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table icons
# ------------------------------------------------------------

CREATE TABLE `icons` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `file` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sessions
# ------------------------------------------------------------

CREATE TABLE `sessions` (
  `id` varchar(40) COLLATE utf8_bin NOT NULL,
  `last_activity` int(10) NOT NULL,
  `payload` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



# Dump of table settings
# ------------------------------------------------------------

CREATE TABLE `settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fireflyuser_id` int(11) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `name` varchar(500) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fireflyuser_id` (`fireflyuser_id`),
  CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`fireflyuser_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table targets
# ------------------------------------------------------------

CREATE TABLE `targets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `fireflyuser_id` int(10) unsigned NOT NULL,
  `account_id` int(11) unsigned NOT NULL,
  `description` varchar(900) NOT NULL DEFAULT '',
  `amount` decimal(10,2) NOT NULL,
  `duedate` date DEFAULT NULL,
  `startdate` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fireflyuser_id` (`fireflyuser_id`),
  KEY `account_id` (`account_id`),
  CONSTRAINT `targets_ibfk_1` FOREIGN KEY (`fireflyuser_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `targets_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table transactions
# ------------------------------------------------------------

CREATE TABLE `transactions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fireflyuser_id` int(11) unsigned NOT NULL,
  `account_id` int(11) unsigned NOT NULL,
  `budget_id` int(11) unsigned DEFAULT NULL,
  `category_id` int(11) unsigned DEFAULT NULL,
  `beneficiary_id` int(11) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `description` varchar(500) NOT NULL DEFAULT '',
  `amount` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `onetime` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fireflyuser_id` (`fireflyuser_id`),
  KEY `beneficiary_id` (`beneficiary_id`),
  KEY `category_id` (`category_id`),
  KEY `budget_id` (`budget_id`),
  KEY `account_id` (`account_id`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`fireflyuser_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `transactions_ibfk_4` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `transactions_ibfk_5` FOREIGN KEY (`beneficiary_id`) REFERENCES `beneficiaries` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table transfers
# ------------------------------------------------------------

CREATE TABLE `transfers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `fireflyuser_id` int(11) unsigned NOT NULL,
  `account_from` int(11) unsigned NOT NULL,
  `account_to` int(11) unsigned NOT NULL,
  `category_id` int(11) unsigned DEFAULT NULL,
  `budget_id` int(10) unsigned DEFAULT NULL,
  `target_id` int(11) unsigned DEFAULT NULL,
  `description` varchar(500) NOT NULL,
  `amount` decimal(10,2) unsigned NOT NULL,
  `date` date NOT NULL,
  `ignoreprediction` tinyint(1) NOT NULL DEFAULT '0',
  `countasexpense` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `account_from` (`account_from`),
  KEY `account_to` (`account_to`),
  KEY `fireflyuser_id` (`fireflyuser_id`),
  KEY `category_id` (`category_id`),
  KEY `budget_id` (`budget_id`),
  KEY `target_id` (`target_id`),
  CONSTRAINT `transfers_ibfk_1` FOREIGN KEY (`account_from`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `transfers_ibfk_2` FOREIGN KEY (`account_to`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `transfers_ibfk_3` FOREIGN KEY (`fireflyuser_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `transfers_ibfk_4` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `transfers_ibfk_5` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `transfers_ibfk_7` FOREIGN KEY (`target_id`) REFERENCES `targets` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table users
# ------------------------------------------------------------

CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `email` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
