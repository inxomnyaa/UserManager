<?php

declare(strict_types=1);

namespace xenialdan\UserManager;

use Ds\Map;
use pocketmine\Player;
use xenialdan\UserManager\event\UserBanEvent;
use xenialdan\UserManager\models\Ban;

class BanStore
{
    /**
     * userId => Ban
     * @var Map
     */
    private static $bans;

    public static function init(): void
    {
        self::$bans = new Map();
        Loader::$queries->getBanList(function (array $rows): void {
            foreach ($rows as $banData) {
                $ban = new Ban($banData["user_id"], $banData["since"], $banData["until"], $banData["expires"] === 1, $banData["reason"], $banData["types"]);
                if (!$ban->hasExpired())
                    self::addBan($ban);
                else {//TODO Remove/cleanup this hack
                    Loader::$queries->deleteBan($ban, function (int $a): void {
                        echo $a;
                    });
                }
            }
            Loader::getInstance()->getLogger()->info(self::$bans->count() . " ban entries loaded from database");
        });
    }

    /**
     * @return Ban[]
     */
    public static function getBans(): array
    {
        return self::$bans->toArray();
    }

    private static function addBan(Ban $ban): void
    {
        self::$bans->put($ban->getUserId(), $ban);
        Loader::getInstance()->getLogger()->debug("Added ban $ban");
    }

    public static function createBan(Ban $ban): void
    {
        $ev = new UserBanEvent(UserStore::getUserById($ban->getUserId()), $ban);
        $ev->call();
        if ($ev->isCancelled()) return;
        Loader::$queries->addBan($ban, function (int $insertId, int $affectedRows) use ($ban): void {
            self::addBan($ban);
        });
    }

    public static function getBan(?Player $player): ?Ban
    {
        if ($player === null) return null;
        return self::getBanByName($player->getLowerCaseName());
    }

    public static function getBanByName(string $playername): ?Ban
    {
        $user = UserStore::getUserByName($playername);
        if ($user instanceof User) return self::getBanById($user->getId());
        return null;
    }

    public static function getBanById(int $id): ?Ban
    {
        if (self::$bans->isEmpty()) return null;
        if (self::$bans->hasKey($id)) return self::$bans->get($id);
        return null;
    }
}