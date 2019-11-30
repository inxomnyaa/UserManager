<?php

namespace xenialdan\UserManager\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\utils\TextFormat;
use ReflectionException;
use RuntimeException;
use xenialdan\UserManager\BanStore;
use xenialdan\UserManager\event\UserDisconnectEvent;
use xenialdan\UserManager\event\UserLoginEvent;
use xenialdan\UserManager\Loader;
use xenialdan\UserManager\models\Ban;
use xenialdan\UserManager\User;
use xenialdan\UserManager\UserStore;

class GenericEventListener implements Listener
{
    private static $clientData = [];

    /**
     * @priority HIGHEST
     * @param DataPacketReceiveEvent $event
     */
    public function onPacket(DataPacketReceiveEvent $event)
    {
        if ($event->getPacket()->pid() === LoginPacket::NETWORK_ID) {
            $this->onLoginPacket($event);
        }
    }

    /**
     * @param DataPacketReceiveEvent $event
     */
    private function onLoginPacket(DataPacketReceiveEvent $event)
    {
        /** @var LoginPacket $pk */
        $pk = $event->getPacket();
        self::$clientData[$pk->clientUUID] = $pk->clientData;
    }

    /**
     * @priority HIGHEST
     * @param PlayerPreLoginEvent $event
     */
    public function onConnect(PlayerPreLoginEvent $event)
    {
        $player = $event->getPlayer();
        if (!($user = UserStore::getUser($player)) instanceof User) {
            Loader::$queries->getUser($player->getName(), function (array $rows) use ($player): void {
                if (empty($rows)) {
                    UserStore::createNewUser($player->getName(), $player->getAddress(), []);
                } else {
                    UserStore::createUser($rows[0]["user_id"], $rows[0]["username"], $player->getAddress());
                }
            });
        } else {
            /* TODO HANDLE BAN & WARN CHECKS HERE */
            $ban = BanStore::getBanById($user->getId());
            if ($ban instanceof Ban) {
                $msg = TextFormat::DARK_RED . TextFormat::BOLD . "You are banned!" . TextFormat::EOL . $ban->reason;
                $debug = "Banned user tried to log in:" . TextFormat::EOL . $ban;
                $kick = false;
                if ($ban->isTypeBanned(Ban::TYPE_IP) && $user->getIP() === $player->getAddress()) {
                    $kick = true;
                }
                if ($ban->isTypeBanned(Ban::TYPE_NAME) && strtolower($user->getUsername()) === $player->getLowerCaseName()) {
                    $kick = true;
                }
                //TODO UUID, XUID
                if ($kick) {
                    //TODO check why kick message does not appear + stuck in loading resources
                    Loader::getInstance()->getLogger()->debug($debug);
                    $event->setKickMessage($msg);
                    $event->setCancelled();
                    $player->kick($msg, false);
                }
                return;
            }
            $user->setClientData(self::$clientData[$event->getPlayer()->getUniqueId()->toString()] ?? null);
            $user->setDisplayName($user->getDisplayName());
        }
    }

    /**
     * @priority HIGHEST
     * @param PlayerJoinEvent $event
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function onJoin(PlayerJoinEvent $event): void
    {
        if (($user = UserStore::getUser($event->getPlayer())) instanceof User) {
            $ev = new UserLoginEvent($user);
            $ev->call();
        }
    }

    /**
     * @priority HIGHEST
     * @param PlayerQuitEvent $event
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function onLeave(PlayerQuitEvent $event): void
    {
        if (($user = UserStore::getUser($event->getPlayer())) instanceof User) {
            $ev = new UserDisconnectEvent($user, $event->getQuitMessage(), $event->getQuitReason());
            $ev->call();
        }
    }
}