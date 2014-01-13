CREATE TABLE users (
user_id INT(11) NOT NULL AUTO_INCREMENT,
user_name VARCHAR(64) NOT NULL,
user_password_hash VARCHAR(255) NOT NULL,
user_email VARCHAR(64) NOT NULL,
user_active TINYINT(1) NOT NULL DEFAULT '0',
user_activation_hash VARCHAR(40) DEFAULT NULL,
user_password_reset_hash char(40) DEFAULT NULL,
user_password_reset_timestamp BIGINT(20) DEFAULT NULL,
user_rememberme_token VARCHAR(64) DEFAULT NULL,
user_failed_logins TINYINT(1) NOT NULL DEFAULT '0',
user_last_failed_login INT(1) DEFAULT NULL,
user_registration_datetime DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
user_registration_ip VARCHAR(39) NOT NULL DEFAULT '0.0.0.0',
PRIMARY KEY (user_id),
UNIQUE KEY user_name (user_name),
UNIQUE KEY user_email (user_email)
)