CREATE TABLE IF NOT EXISTS Quote (
  idQuote int(15) unsigned NOT NULL AUTO_INCREMENT,
  number int(15) unsigned NOT NULL,
  `year` year(4) NOT NULL,
  quote text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (idQuote),
  UNIQUE KEY `number` (number),
  KEY `year` (`year`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS Quoted (
  idQuoted int(15) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(64) COLLATE utf8_bin NOT NULL,
  display tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (idQuoted)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS QuotedAlias (
  idQuoted int(15) unsigned NOT NULL,
  alias char(64) COLLATE utf8_bin NOT NULL,
  UNIQUE KEY alias (alias),
  KEY idQuoted (idQuoted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS R_Quoted_Quote (
  idQuote int(15) unsigned NOT NULL,
  idQuoted int(15) unsigned NOT NULL,
  KEY idQuoted (idQuoted),
  KEY idQuote (idQuote)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


ALTER TABLE `QuotedAlias`
  ADD CONSTRAINT QuotedAlias_ibfk_1 FOREIGN KEY (idQuoted) REFERENCES Quoted (idQuoted);

ALTER TABLE `R_Quoted_Quote`
  ADD CONSTRAINT R_Quoted_Quote_ibfk_1 FOREIGN KEY (idQuote) REFERENCES Quote (idQuote),
  ADD CONSTRAINT R_Quoted_Quote_ibfk_2 FOREIGN KEY (idQuoted) REFERENCES Quoted (idQuoted);

