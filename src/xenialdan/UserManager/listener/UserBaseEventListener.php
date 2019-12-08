<?php

namespace xenialdan\UserManager\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use ReflectionException;
use RuntimeException;
use xenialdan\UserManager\event\UserDisconnectEvent;
use xenialdan\UserManager\event\UserJoinEvent;
use xenialdan\UserManager\event\UserLoginEvent;
use xenialdan\UserManager\event\UserPreLoginEvent;
use xenialdan\UserManager\Loader;
use xenialdan\UserManager\User;
use xenialdan\UserManager\UserStore;

class UserBaseEventListener implements Listener
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
     * @priority HIGHEST
     * @param DataPacketReceiveEvent $event
     */
    private function onLoginPacket(DataPacketReceiveEvent $event)
    {
        /** @var LoginPacket $pk */
        $pk = $event->getPacket();
        self::$clientData[$pk->clientUUID] = $pk->clientData;
    }

    /**
     * @priority HIGH
     * @param PlayerPreLoginEvent $event
     * @throws RuntimeException
     */
    public function onConnect(PlayerPreLoginEvent $event)
    {
        var_dump(date("r"), $event->getEventName());
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
            $user->setClientData(self::$clientData[$event->getPlayer()->getUniqueId()->toString()] ?? null);
            $user->setDisplayName(
                $user->getDisplayName());
            $ev = new UserPreLoginEvent($user);
            $ev->call();
        }
    }

    /**
     * TODO user settings (Nickname!) should already be accessible here!
     * @priority HIGHEST
     * @param PlayerLoginEvent $event
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function onLogin(PlayerLoginEvent $event): void
    {
        var_dump(date("r"), $event->getEventName());
        if (($user = UserStore::getUser($event->getPlayer())) instanceof User) {
            $user->setDisplayName(
                $user->getDisplayName());
            $ev = new UserLoginEvent($user);
            $ev->call();
            #var_dump($ev, $event->getPlayer()->isOnline() ? "true" : "false");
        }
    }

    /**
     * TODO user settings (Nickname!) should already be accessible here!
     * @priority HIGHEST
     * @param PlayerJoinEvent $event
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function onJoin(PlayerJoinEvent $event): void
    {
        var_dump(date("r"), $event->getEventName());
        if (($user = UserStore::getUser($event->getPlayer())) instanceof User) {
            $user->setDisplayName(
                $user->getDisplayName());
            $ev = new UserJoinEvent($user);
            $ev->call();
            #var_dump($ev, $event->getPlayer()->isOnline() ? "true" : "false");
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