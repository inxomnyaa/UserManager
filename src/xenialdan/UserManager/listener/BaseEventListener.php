<?php

namespace xenialdan\UserManager\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use xenialdan\UserManager\API;
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

    public function onJoin(PlayerJoinEvent $event): void
    {
        API::sendJoinMessages($event->getPlayer());
    }
}