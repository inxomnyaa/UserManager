<?php

declare(strict_types=1);

namespace xenialdan\UserManager;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\UserManager\models\UserSettings;

class User
{

    private $id, $username, $ip, $flags = [];
    /** @var null|UserSettings */
    private $settings = null;
    private $clientData = null;

    public function __construct($id = -1, string $username, string $ip, array/*PermissionFlags*/
    $flags = [])
    {
        $this->id = $id;
        $this->username = $username;
        $this->ip = $ip;
        $this->flags = $flags;
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
    public function getIUsername(): string
    {
        return strtolower($this->username);
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
     * @return string
     */
    public function getDisplayName(): string
    {
        if ($this->settings instanceof UserSettings && self::isValidUserName($this->settings->u_nickname))
            return $this->settings->u_nickname;
        if ($this->isOnline())
            return $this->getPlayer()->getDisplayName();
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
            if (($friend = UserStore::getUserByName($userData["username"])) instanceof User) {
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
    public function getUsersFromRelationship(array $rows, int $userId): array
    {
        $friends = [];
        foreach ($rows as $userData) {
            $friendId = (int)$userData["user_one_id"];
            if ($friendId === $userId) $friendId = (int)$userData["user_two_id"];
            if (($friend = UserStore::getUserById($friendId)) instanceof User) {
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
     * @param bool $query
     */
    public function setSettings(UserSettings $settings, bool $query = true): void
    {
        $this->settings = $settings;
        if ($query) Loader::$queries->changeUserSettings($this->getId(), $settings, function (int $affectedRows): void {
            var_dump(__METHOD__, "Changed $affectedRows rows");
        });
        $name = $settings->u_nickname;
        //TODO cleanup
        if (self::isValidUserName($name)) {
            $this->setDisplayName($name);
        } else {
            $this->setDisplayName($this->getRealUsername());
        }
    }

    /**
     * @param string $name
     */
    public function setDisplayName(string $name): void
    {
        $this->getPlayer()->setDisplayName($name);
        $this->getPlayer()->setNameTag($name);
    }

    public function __toString(): string
    {
        $result = var_export(get_object_vars($this), true);
        return $result;
    }

    /**
     * @return array|null
     */
    public function getClientData(): ?array
    {
        return $this->clientData;
    }

    /**
     * @param array|null $clientData
     */
    public function setClientData(?array $clientData): void
    {
        $this->clientData = $clientData;
    }

    /**
     * Validates the given username.
     * @param null|string $name
     * @return bool
     */
    public static function isValidUserName(?string $name): bool
    {
        if ($name === null) {
            return false;
        }
        return Player::isValidUserName(self::cleanUserName($name));
    }

    /**
     * Cleans the given username.
     * @param string $name
     * @return string
     */
    public static function cleanUserName(string $name): string
    {
        return TextFormat::clean(trim($name));
    }

    /**
     * Check if this user matches another user
     * @param User $user
     * @return bool
     */
    public function equals(User $user): bool
    {
        return $this->getUserId() === $user->getUserId();
    }

    /**
     * Check if this user's id matches another user's id
     * @param int $userId
     * @return bool
     */
    public function equalsId(int $userId): bool
    {
        return $this->getUserId() === $userId;
    }
}