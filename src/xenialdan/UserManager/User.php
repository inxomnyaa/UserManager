<?php

declare(strict_types=1);

namespace xenialdan\UserManager;

use pocketmine\Player;
use xenialdan\UserManager\models\UserSettings;

class User
{

    private $id, $username, $ip, $flags = [];
    private $banned = false;
    /** @var null|UserSettings */
    private $settings = null;

    public function __construct($id = -1, string $username, string $ip, array/*PermissionFlags*/
    $flags = [])
    {
        $this->id = $id;
        $this->username = $username;
        $this->ip = $ip;
        $this->flags = $flags;
        $this->settings = new UserSettings();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->getId();
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getRealUsername(): string
    {
        return $this->isOnline() ? $this->getPlayer()->getName() : $this->username;
    }

    public function getPlayer(): ?Player
    {
        return Loader::getInstance()->getServer()->getOfflinePlayer($this->username)->getPlayer();
    }

    /**
     * @return bool
     */
    public function isOnline(): bool
    {
        return $this->getPlayer() instanceof Player && $this->getPlayer()->isOnline();
    }

    /**
     * TODO
     * @return bool
     */
    public function isBanned(): bool
    {
        return $this->banned;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        $player = $this->getPlayer();
        if ($player instanceof Player)
            return $player->getDisplayName();
        return $this->username;
    }

    public function getIP()
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp(string $ip)
    {
        $this->ip = $ip;
    }

    /**
     * Returns an array containing Users and takes the results of Queries from users table as parameter
     * @param array $rows
     * @return User[]
     */
    public function getFriendFromUsers(array $rows = []): array
    {
        $friends = [];
        foreach ($rows as $userData) {
            if (($friend = Loader::$userstore::getUserByName($userData["username"])) instanceof User) {
                $friends[] = $friend;
            }
        }
        return $friends;
    }

    /**
     * Returns an array containing Users and takes the results of Queries from the relationship table as parameter
     * @param array $rows
     * @param int $userId
     * @return User[]
     */
    public function getUsersFromRelationship(array $rows = [], int $userId): array
    {
        $friends = [];
        foreach ($rows as $userData) {
            $friendId = (int)$userData["user_one_id"];
            if ($friendId === $userId) $friendId = (int)$userData["user_two_id"];
            if (($friend = Loader::$userstore::getUserById($friendId)) instanceof User) {
                $friends[] = $friend;
            }
        }
        return $friends;
    }

    /**
     * @return null|UserSettings
     */
    public function getSettings(): ?UserSettings
    {
        return $this->settings;
    }

    /**
     * @param UserSettings $settings
     */
    public function setSettings(UserSettings $settings): void
    {
        $this->settings = $settings;
        Loader::$queries->changeUserSettings($this->getId(), $settings, function (int $affectedRows): void {
            var_dump(__METHOD__, "Changed $affectedRows rows");
        });
    }

    public function __toString(): string
    {
        $result = var_export(get_object_vars($this), true);
        return $result;
    }
}