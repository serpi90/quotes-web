DELETE FROM `Settings` WHERE `key` = 'password_hash';
INSERT INTO `Settings` (`key`, `value`) VALUES ( 'password_hash', MD5( 'mypassword' ) );

