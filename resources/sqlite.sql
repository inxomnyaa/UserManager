-- #! mysql
-- #{usermanager
-- #  {init
-- #    {users
CREATE TABLE IF NOT EXISTS `users` (
	`user_id`	INTEGER PRIMARY KEY AUTOINCREMENT,
	`username`	TEXT UNIQUE,
	`lastuuid`	TEXT,
	`lastip`	TEXT
);
-- #    }
-- #    {authcode
CREATE TABLE IF NOT EXISTS `authcode` (
`user_id` INTEGER,
`authcode` TEXT,
PRIMARY KEY(`user_id`)
);
-- #    }
-- #    {bans
CREATE TABLE IF NOT EXISTS `bans` (
`user_id` INTEGER,
`since` TEXT,
`until` TEXT,
`expires` INTEGER,
`reason` TEXT,
`types` TEXT,
PRIMARY KEY(`user_id`)
);
-- #    }
-- #    {messages
CREATE TABLE IF NOT EXISTS `messages` (
`sender_id` INTEGER,
`receiver_id` INTEGER,
`status` INTEGER,
`action_user_id` INTEGER
);
-- #    }
-- #    {relationship
CREATE TABLE IF NOT EXISTS `relationship` (
`user_one_id` INTEGER UNIQUE,
`user_two_id` INTEGER UNIQUE,
`status` INTEGER NOT NULL DEFAULT 0,
`action_user_id` INTEGER NOT NULL
);
-- #    }
-- #    {warns
CREATE TABLE `warns` (
`user_id` INTEGER,
`since` TEXT,
`reason` TEXT,
PRIMARY KEY(`user_id`)
);
-- #    }
-- #  }
-- #  {ban
-- #    {get
SELECT * FROM bans WHERE `user_id` = :user_id;
-- #    }
-- #    {add
INSERT OR REPLACE INTO bans (`user_id`, `since`, `until`, `expires`, `reason`, `types`) VALUES(:user_id, :since, :until, :expires, :reason, :types);
-- #    }
-- #    {update
UPDATE bans SET `user_id` = :user_id, `since` = :since, `until` = :until, `expires` = :expires, `reason` = :reason, `types` = :types WHERE `user_id` = :user_id;
-- #    }
-- #    {delete
DELETE FROM bans WHERE `user_id` = :user_id;
-- #    }
-- #  }
-- #  {warn
-- #    {get
SELECT * FROM warns WHERE `user_id` = :user_id;
-- #    }
-- #    {add
INSERT OR REPLACE INTO warns (`user_id`, `since`, `reason`) VALUES(:user_id, :since, :reason);
-- #    }
-- #    {update
UPDATE warns SET `user_id` = :user_id, `since` = :since, `reason` = :reason WHERE `user_id` = :user_id;
-- #    }
-- #    {delete
DELETE FROM warns WHERE `user_id` = :user_id;
-- #    }
-- #  }
-- #  {user
-- #    {get
-- #      {idbyname
SELECT `user_id` FROM users WHERE `username` = :username;
-- #      }
-- #    }
-- #    {data
-- #      {get
-- #        {byid
SELECT * FROM users WHERE `user_id` = :user_id;
-- #        }
-- #        {byname
SELECT * FROM users WHERE `username` = :username;
-- #        }
-- #        {all
SELECT * FROM users;
-- #        }
-- #      }
-- #    }
-- #    {add
-- #      {new
INSERT OR REPLACE INTO `users` (`username`, `lastuuid`, `lastip`) VALUES (:username,:lastuuid,:lastip);
-- #      }
-- #      {ip
INSERT OR REPLACE INTO `users` (`user_id`, `lastip`) VALUES (:user_id,:lastip);
-- #      }
-- #    }
-- #  }
-- #  {authcode
-- #    {update
INSERT OR REPLACE INTO `authcode` (`user_id`, `authcode`) VALUES (:user_id,:authcode);
-- #    }
-- #    {check
SELECT * FROM `authcode` WHERE `user_id` = :user_id AND `authcode` = :authcode;
-- #    }
-- #  }
-- #  {relationship
-- #    {set
INSERT OR REPLACE INTO `relationship` (`user_one_id`, `user_two_id`, `status`, `action_user_id`) VALUES (:user_one_id,:user_two_id,:status,:action_user_id);
-- #    }
-- #    {get
SELECT * FROM `relationship` WHERE `user_one_id` = :user_one_id AND `user_two_id` = :user_two_id;
-- #    }
-- #    {check
SELECT * FROM `relationship` WHERE `user_one_id` = :user_one_id AND `user_two_id` = :user_two_id AND `status` = :status;
-- #    }
-- #    {friend
-- #      {list
SELECT * FROM `relationship` WHERE (`user_one_id` = :user_one_id OR `user_two_id` = :user_one_id) AND `status` = 1;
-- #      }
-- #      {pending
SELECT * FROM `relationship` WHERE (`user_one_id` = :user_one_id OR `user_two_id` = :user_one_id) AND `status` = 0 AND `action_user_id` != :user_one_id;
-- #      }
-- #    }
-- #  }
-- #}