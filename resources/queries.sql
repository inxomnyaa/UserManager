#CREATE TABLE IF NOT EXISTS bans (user_id INT PRIMARY KEY, since TIMESTAMP, until TIMESTAMP, expires BOOL, reason TEXT, types TEXT);
#CREATE TABLE IF NOT EXISTS warns (user_id INT PRIMARY KEY, since TIMESTAMP, reason TEXT);
#CREATE TABLE IF NOT EXISTS users (user_id INT PRIMARY KEY AUTO_INCREMENT, username VARCHAR(16) UNIQUE, lastuuid VARCHAR(256), lastip VARCHAR(15));
#CREATE TABLE IF NOT EXISTS authcode (user_id INT PRIMARY KEY, authcode VARCHAR(15));
#CREATE TABLE IF NOT EXISTS `relationship` (
#				  `user_one_id` INT(50) UNSIGNED NOT NULL,
#				  `user_two_id` INT(50) UNSIGNED NOT NULL,
#				  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
#				  `action_user_id` INT(50) UNSIGNED NOT NULL
#				);
#ALTER TABLE `relationship` ADD UNIQUE KEY `unique_users_id` (`user_one_id`,`user_two_id`);
#CREATE TABLE IF NOT EXISTS messages (sender_id INT, receiver_id INT, status TINYINT, action_user_id INT);
SELECT * FROM bans WHERE `user_id` = ?;
INSERT OR REPLACE INTO bans (`user_id`, `since`, `until`, `expires`, `reason`, `types`) VALUES(:user_id, :since, :until, :expires, :reason, :types);
UPDATE bans SET `user_id` = :user_id, `since` = :since, `until` = :until, `expires` = :expires, `reason` = :reason, `types` = :types WHERE `user_id` = :user_id;
DELETE FROM bans WHERE `user_id` = ?;

SELECT * FROM warns WHERE `user_id` = ?;
INSERT OR REPLACE INTO warns (`user_id`, `since`, `reason`) VALUES(?,?,?);
UPDATE warns SET `user_id` = ?, `since` = ?, `reason` = ? WHERE `user_id` = VALUES(user_id);
DELETE FROM warns WHERE `user_id` = ?;

SELECT `user_id` FROM users WHERE `username` = ?;
SELECT * FROM users WHERE `user_id` = ?;
SELECT * FROM users WHERE `username` = ?;
SELECT * FROM users;

INSERT OR REPLACE INTO `users` (`username`, `lastuuid`, `lastip`) VALUES (?,?,?);
INSERT OR REPLACE INTO `users` (`user_id`, `lastip`) VALUES (?,?);

INSERT OR REPLACE INTO `authcode` (`user_id`, `authcode`) VALUES (?,?);
SELECT * FROM `authcode` WHERE `user_id` = ? AND `authcode` = ?;

INSERT OR REPLACE INTO `relationship` (`user_one_id`, `user_two_id`, `status`, `action_user_id`) VALUES (?,?,?,?);
UPDATE `relationship` SET `status` = 1, `action_user_id` = ? WHERE `user_one_id` = ? AND `user_two_id` = ?;#TODO
SELECT * FROM `relationship` WHERE `user_one_id` = ? AND `user_two_id` = ?;
SELECT * FROM `relationship` WHERE `user_one_id` = ? AND `user_two_id` = ? AND `status` = ?;
SELECT * FROM `relationship` WHERE (`user_one_id` = ? OR `user_two_id` = VALUES(`user_one_id`)) AND `status` = 1;
SELECT * FROM `relationship` WHERE (`user_one_id` = ? OR `user_two_id` = VALUES(`user_one_id`)) AND `status` = 0 AND `action_user_id` != VALUES(`user_one_id`)
SELECT * FROM `relationship` WHERE (`user_one_id` = ? OR `user_two_id` = ?) AND `status` = " . API::FRIEND_ACCEPTED . ";
SELECT * FROM `relationship` WHERE (`user_one_id` = ? OR `user_two_id` = ?) AND `status` = " . API::FRIEND_PENDING . " AND `action_user_id` != ?;