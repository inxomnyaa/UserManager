<?php

declare(strict_types=1);

namespace xenialdan\UserManager;

class API
{
    public const FRIEND_PENDING = 0;
    public const FRIEND_ACCEPTED = 1;
    public const FRIEND_DECLINED = 2;
    public const FRIEND_BLOCKED = 3;

    public const STATE_ONLINE = 1;
    public const STATE_OFFLINE = 2;
    public const STATE_UNKNOWN = 3;

    /**
     * @param $player
     * @return User|null
     */
    public static function getUser($player)
    {
        if (empty(self::getUserList()) || !self::userExists($player)) $user = self::getDataProvider()->getUser(strtolower($player->getName()));
        else if (self::userExists($player)) {
            $id = array_search(strtolower($player->getName()), array_map(function (User $user) {
                return $user->getIUserName();
            }, self::getUserList()));
            return self::getUserList()[$id];
        } else $user = self::addUser($player);
        return $user;
    }

    /**
     * @return User[]
     */
    public static function getUserList()
    {
        if (empty(self::$userlist)) self::$userlist = self::getDataProvider()->getUserList();
        return self::$userlist;
    }

    /**
     * @param int $id
     * @return null|User
     */
    public static function getUserByID(int $id)
    {
        foreach (self::getUserList() as $user) {
            if ($user->getUserId() === $id) return $user;
        }
        return self::getDataProvider()->getUserById($id);
    }
}