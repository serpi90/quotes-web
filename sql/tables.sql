SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `PersonAlias`;
DROP TABLE IF EXISTS `Quote`;
DROP TABLE IF EXISTS `QuoteDraft`;
DROP TABLE IF EXISTS `Person`;
DROP TABLE IF EXISTS `Settings`;

CREATE TABLE `Person` (
	`idPerson` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`name` char(64) COLLATE utf8_bin NOT NULL,
	`active` tinyint(1) NOT NULL,
	PRIMARY KEY (`idPerson`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `PersonAlias` (
	`idPerson` bigint(20) unsigned NOT NULL,
	`alias` char(64) COLLATE utf8_bin NOT NULL,
	KEY `idPerson` (`idPerson`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `PersonAlias` ADD CONSTRAINT `R_PersonAlias_Person` FOREIGN KEY (`idPerson`) REFERENCES `Person` (`idPerson`);

CREATE TABLE `Quote` (
	`idQuote` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`number` bigint(20) unsigned NOT NULL,
	`phrase` text COLLATE utf8_bin NOT NULL,
	`registered` datetime NOT NULL,
	`idPerson` bigint(20) unsigned NOT NULL,
	PRIMARY KEY (`idQuote`),
	UNIQUE (`number`),
	KEY `idPerson` (`idPerson`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `Quote` ADD CONSTRAINT `R_Quote_Person` FOREIGN KEY (`idPerson`) REFERENCES `Person` (`idPerson`);

CREATE TABLE `QuoteDraft` (
	`idQuoteDraft` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`phrase` text COLLATE utf8_bin NOT NULL,
	`idPerson` bigint(20) unsigned NOT NULL,
	`registered` datetime NOT NULL,
	PRIMARY KEY (`idQuoteDraft`),
	KEY `idPerson` (`idPerson`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `QuoteDraft` ADD CONSTRAINT `R_QuoteDraft_Person` FOREIGN KEY (`idPerson`) REFERENCES `Person` (`idPerson`);

CREATE TABLE `Settings` (
	`key` varchar(32) COLLATE utf8_bin NOT NULL,
	`value` varchar(32) COLLATE utf8_bin NOT NULL,
	PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

