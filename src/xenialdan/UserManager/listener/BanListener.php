<?php

declare(strict_types=1);

namespace xenialdan\UserManager\listener;

use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use xenialdan\UserManager\BanStore;
use xenialdan\UserManager\event\UserBanEvent;
use xenialdan\UserManager\event\UserJoinEvent;
use xenialdan\UserManager\Loader;
use xenialdan\UserManager\models\Ban;

class BanListener implements Listener
{

    /**
     * @priority HIGHEST
     * @param UserJoinEvent $event
     */
    public function onUserLoginEvent(UserJoinEvent $event): void//TODO CHANGE AGAIN to loginevent?
    {
        $user = $event->getUser();
        $player = $user->getPlayer();
        /* TODO HANDLE BAN & WARN CHECKS HERE */
        $ban = BanStore::getBanById($user->getId());
        if ($ban instanceof Ban) {
            if ($ban->hasExpired()) {
                Loader::$queries->deleteBan($ban, function (int $affectedRows) use ($ban): void {
                    Loader::getInstance()->getLogger()->notice("Removed ban " . $ban . " ($affectedRows)");
                    unset($ban);
                });
                return;
            }
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
                #$player->kick($msg, false);
                #$event->setKickMessage($msg);
                #$event->setCancelled();
                $event->getUser()->getPlayer()->kick($ban->getReason(), false, $ban->getReason());
            }
            return;
        }
    }

    public function onUserBanEvent(UserBanEvent $event): void
    {
        if (!$event->isCancelled()) {
            if ($event->getUser()->isOnline()) {
                $event->getUser()->getPlayer()->kick($event->getBan()->getReason(), false, $event->getBan()->getReason());
            }
        }
    }

}