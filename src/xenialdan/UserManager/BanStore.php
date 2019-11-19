<?php

declare(strict_types=1);

namespace xenialdan\UserManager;

use Ds\Map;
use pocketmine\Player;
use xenialdan\UserManager\models\Ban;

class BanStore
{
    /**
     * userId => Ban
     * @var Map
     */
    private static $bans;

    public function __construct()
    {
        self::$bans = new Map();
        Loader::$queries->getBanList(function (array $rows): void {
            foreach ($rows as $banData) {
                self::addBan(new Ban($banData["user_id"], $banData["since"], $banData["until"], $banData["expires"] === 1, $banData["reason"], $banData["types"]));
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

    public static function addBan(Ban $ban): void
    {
        self::$bans->put($ban->getUserId(), $ban);
        Loader::getInstance()->getLogger()->debug("Added ban $ban");
    }

    public static function getBan(?Player $player): ?Ban
    {
        if ($player === null) return null;
        return self::getBanByName($player->getLowerCaseName());
    }

    public static function getBanByName(string $playername): ?Ban
    {
        $user = Loader::$userstore::getUserByName($playername);
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