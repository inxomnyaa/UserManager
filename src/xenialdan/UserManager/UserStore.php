<?php

declare(strict_types=1);

namespace xenialdan\UserManager;

use Ds\Map;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class UserStore
{
    /**
     * lowercasename => User
     * @var Map
     */
    private static $users;

    public static function init(): void
    {
        self::$users = new Map();
        Loader::$queries->getUserList(function (array $rows): void {
            foreach ($rows as $userData) {
                self::createUser($userData["user_id"], $userData["username"], $userData["lastip"]);
            }
            Loader::getInstance()->getLogger()->info(self::$users->count() . " users loaded from database");
        });
    }

    /**
     * @return User[]
     */
    public static function getUsers(): array
    {
        return self::$users->toArray();
    }

    public static function createNewUser(string $username, string $address, array $flags = []): void
    {
        $user = new User(-1, $username, $address, $flags);
        Loader::$queries->addUser($user, function (int $insertId, int $affectedRows) use ($user): void {
            $user->setId($insertId);
            self::addUser($user);
        });
    }

    public static function createUser(int $user_id, string $username, string $address, array $flags = [], bool $add = true): User
    {
        $user = new User($user_id, $username, $address, $flags);
        if ($add) self::addUser($user);
        return $user;
    }

    public static function addUser(User $user): void
    {
        self::$users->put($user->getIUsername(), $user);
        Loader::getInstance()->getLogger()->debug("Added user $user");
    }

    public static function getUser(?Player $player): ?User
    {
        if ($player === null) return null;
        return self::getUserByName($player->getLowerCaseName());
    }

    public static function getUserByName(string $playername): ?User
    {
        $playername = strtolower(TextFormat::clean($playername));
        if (self::$users->isEmpty()) return null;
        if (self::$users->hasKey($playername)) return self::$users->get($playername);
        else {
            //soft check name
            $player = Loader::getInstance()->getServer()->getPlayer($playername);
            if ($player !== null && self::$users->hasKey($player->getLowerCaseName())) return self::$users->get($player->getLowerCaseName());
        }
        return null;
    }

    public static function getUserById(int $id): ?User
    {
        if (self::$users->isEmpty()) return null;
        $filter = self::$users->filter(function ($key, User $user) use ($id): bool {
            return $user->getId() === $id;
        });
        if ($filter->isEmpty()) return null;
        return $filter->values()->first();
    }
}