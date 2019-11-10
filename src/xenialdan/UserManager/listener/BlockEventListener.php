<?php

namespace xenialdan\UserManager\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;
use xenialdan\UserManager\Loader;
use xenialdan\UserManager\User;

class BlockEventListener implements Listener
{

    public function onChat(PlayerChatEvent $event): void
    {
        if (($user = Loader::$userstore::getUser($player = $event->getPlayer())) instanceof User) {
            $rec = $event->getRecipients();
            $rec = array_filter($rec, function (Player $player): bool {
                return true;//TODO return false if blocked or muted
            });
            $event->setRecipients($rec);
        }
    }
}