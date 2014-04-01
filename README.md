Installation
============

- Import sql/tables.sql into your desired mysql database.
- Configure php/config.php with database and mail parameters, using config.default.php as template.
- Run the following SQL script to set your desired password.
-- INSERT INTO `Settings` (`key`, `value`) VALUES ( 'password\_hash', MD5( 'mypassword' ) )
TODO
====

- Add a graphs of most quoted users and things like that.

