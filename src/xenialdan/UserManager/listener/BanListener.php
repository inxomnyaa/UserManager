<?php

declare(strict_types=1);

namespace xenialdan\UserManager\listener;

use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use xenialdan\UserManager\BanStore;
use xenialdan\UserManager\event\UserLoginEvent;
use xenialdan\UserManager\Loader;
use xenialdan\UserManager\models\Ban;

class BanListener implements Listener
{

    public function onUserLoginEvent(UserLoginEvent $event): void
    {
        $user = $event->getUser();
        $player = $user->getPlayer();
        /* TODO HANDLE BAN & WARN CHECKS HERE */
        $ban = BanStore::getBanById($user->getId());
        if ($ban instanceof Ban) {
            $msg = TextFormat::DARK_RED . TextFormat::BOLD . "You are banned!" . TextFormat::EOL . $ban->reason;
            $debug = "Banned user tried to log in:" . TextFormat::EOL . $ban;
            $kick = false;
            if ($ban->isTypeBanned(Ban::TYPE_IP) && $user->getIP() === $player->getAddress()) {
                $kick = true;
            }
            if ($ban->isTypeBanned(Ban::TYPE_NAME) && $user->getIUsername() === $player->getLowerCaseName()) {
                $kick = true;
            }
            //TODO UUID, XUID
            if ($kick) {
                //TODO check why kick message does not appear + stuck in loading resources
                Loader::getInstance()->getLogger()->debug($debug);
                $event->setCancelled();
                $player->kick($msg, false);
            }
            return;
        }
    }

}