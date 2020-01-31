<?php

namespace xenialdan\UserManager\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use xenialdan\UserManager\API;
use xenialdan\UserManager\event\UserJoinEvent;

class ChatListener implements Listener
{

    public function onJoin(UserJoinEvent $event): void
    {
        $user = $event->getUser();
        API::sendJoinMessages($user->getPlayer());
    }

    public function onChat(PlayerChatEvent $event): void
    {
        return;//TODO
        //TODO if global mute -> cancel, send "u r muted"
        //TODO if local mute -> filter recipients if they muted
        /*if (($user = UserStore::getUser($player = $event->getPlayer())) instanceof User) {
            $rec = $event->getRecipients();
            $rec = array_filter($rec, function (Player $player): bool {
                return true;//TODO return false if blocked or muted
            });
            $event->setRecipients($rec);
        }*/
    }
}