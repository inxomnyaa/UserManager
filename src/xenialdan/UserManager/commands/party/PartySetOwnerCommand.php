<?php

declare(strict_types=1);

namespace xenialdan\UserManager\commands\party;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\BaseSubCommand;
use InvalidArgumentException;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\customui\elements\Button;
use xenialdan\customui\windows\ModalForm;
use xenialdan\customui\windows\SimpleForm;
use xenialdan\UserManager\event\PartySetOwnerEvent;
use xenialdan\UserManager\models\Party;
use xenialdan\UserManager\User;
use xenialdan\UserManager\UserStore;

class PartySetOwnerCommand extends BaseSubCommand
{

    /**
     * This is where all the arguments, permissions, sub-commands, etc would be registered
     */
    protected function prepare(): void
    {
        $this->setPermission("usermanager.party.setowner");
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param BaseArgument[] $args
     * @throws InvalidArgumentException
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $user = UserStore::getUser($sender);
        if ($user === null) {
            $sender->sendMessage("DEBUG: null");
            return;
        }
        $party = Party::getParty($user);
        if (!$party instanceof Party) {
            $user->getPlayer()->sendMessage(TextFormat::RED . "You are in no party");
            return;
        }
        if (!$party->isOwner($user)) {
            $user->getPlayer()->sendMessage(TextFormat::RED . "You are not the owner of this party");
            return;
        }

        $form = new SimpleForm("Set Party owner", "Select a party member to change the owner of the party");
        foreach ($party->getMembers() as $member) {
            if (!$party->isOwner($member)) $form->addButton(new Button($member->getRealUsername()));//TODO head image
        }
        $form->setCallable(function (Player $player, string $data) use ($party): void {
            $newOwner = UserStore::getUserByName($data);
            $form = new ModalForm("Change party owner?", "Transfer the ownership to $data?", "Yes", "No");
            $form->setCallable(function (Player $player, bool $confirm) use ($party, $newOwner): void {
                if ($confirm) {
                    self::setOwner($party, $newOwner);
                }
            });
            $player->sendForm($form);
        });
        $sender->sendForm($form);
    }

    /**
     * @param Party $party
     * @param User $user
     */
    private static function setOwner(Party $party, User $user): void
    {
        if (!$party->isMember($user)) {
            $party->getOwner()->getPlayer()->sendMessage(TextFormat::RED . "Is not a member of the party!");
            return;
        }
        if ($party->isOwner($user)) {
            $party->getOwner()->getPlayer()->sendMessage(TextFormat::RED . $user->getDisplayName() . " already is the owner of the party!");
            return;
        }

        try {
            ($ev = new PartySetOwnerEvent($party, $user))->call();
            if (!$ev->isCancelled()) {
                $party->getOwner()->getPlayer()->sendMessage(TextFormat::GOLD . "The new owner of the party now is " . $ev->getNewOwner()->getDisplayName() . "!");
                $ev->getNewOwner()->getPlayer()->sendMessage(TextFormat::GOLD . "You are now the owner of the party \"" . $party->getName() . "\"!");
                foreach ($party->getMembers() as $member) {
                    $member->getPlayer()->sendMessage(TextFormat::GOLD . $ev->getOwner()->getDisplayName() . " changed the party owner. " . $ev->getNewOwner()->getDisplayName() . " now is the owner of the party!");
                }
                $party->setOwnerId($ev->getNewOwner()->getId());
            } else {
                $user->getPlayer()->sendMessage(TextFormat::AQUA . "The new owner of the party was not set");
            }
        } catch (\Exception $e) {
        }
    }
}
