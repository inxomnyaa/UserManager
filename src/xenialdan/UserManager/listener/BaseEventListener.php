<?php

namespace xenialdan\UserManager\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use ReflectionException;
use RuntimeException;
use xenialdan\UserManager\event\UserLoginEvent;
use xenialdan\UserManager\Loader;
use xenialdan\UserManager\User;

class BaseEventListener implements Listener
{

    /**
     * TODO handle ban
     * @param PlayerPreLoginEvent $event
     */
    public function onConnect(PlayerPreLoginEvent $event)
    {
        if (!($user = Loader::$userstore::getUser($event->getPlayer())) instanceof User) {
            Loader::$queries->getUser(($player = $event->getPlayer())->getLowerCaseName(), function (array $rows) use ($player): void {
                if (empty($rows)) {
                    Loader::$userstore::createNewUser($player->getLowerCaseName(), $player->getAddress(), []);
                } else {
                    Loader::$userstore::createUser($rows[0]["user_id"], $rows[0]["username"], $player->getAddress());
                }
            });
        }
    }

    /**
     * @param PlayerJoinEvent $event
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function onJoin(PlayerJoinEvent $event): void
    {
        var_dump(__METHOD__);
        if (($user = Loader::$userstore::getUser($event->getPlayer())) instanceof User) {
            var_dump($user);
            $ev = new UserLoginEvent($user);
            $ev->call();
        }
    }
}