<?php

namespace xenialdan\UserManager\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use xenialdan\UserManager\API;
use xenialdan\UserManager\event\UserLoginEvent;

class ChatListener implements Listener
{

    public function onLogin(UserLoginEvent $event): void
    {
        $user = $event->getUser();
        API::sendJoinMessages($user->getPlayer());
    }

    public function onChat(PlayerChatEvent $event): void
    {
        return;//TODO
        /*if (($user = UserStore::getUser($player = $event->getPlayer())) instanceof User) {
            $rec = $event->getRecipients();
            $rec = array_filter($rec, function (Player $player): bool {
                return true;//TODO return false if blocked or muted
            });
            $event->setRecipients($rec);
        }*/
    }
}