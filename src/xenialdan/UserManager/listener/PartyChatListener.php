<?php

namespace xenialdan\UserManager\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\utils\TextFormat;
use xenialdan\UserManager\models\Party;
use xenialdan\UserManager\models\Translations;
use xenialdan\UserManager\User;
use xenialdan\UserManager\UserStore;

class PartyChatListener implements Listener
{

    public function onChat(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        if (($user = UserStore::getUser($player)) instanceof User) {
            $party = Party::getParty($user);
            $ltrim = ltrim(TextFormat::clean($event->getMessage()));
            $containsBang = stripos($ltrim, "!p ") === 0 || stripos($ltrim, "!party ") === 0 || stripos($ltrim, "!pc ") === 0;
            if ($party instanceof Party) {
                if ($party->hasChatEnabled($user) || $containsBang) {
                    $event->setFormat(TextFormat::BOLD . TextFormat::AQUA . "[Party] " . TextFormat::RESET . "{%0}: {%1}");
                    $event->setMessage(str_ireplace(["!p ", "!pc ", "!party "], "", $event->getMessage()));
                    $recipients = [];
                    foreach ($party->getMembers() as $member) {
                        if ($member->isOnline()) {
                            $recipients[] = $member->getPlayer();
                        }
                    }
                    $event->setRecipients($recipients);
                }
            } else if ($containsBang) {
                $user->getPlayer()->sendMessage(Translations::translate("party.chat.noparty", [], $user));//TODO translation
            }
        }
    }
}