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
        if ($party->getOwnerId() !== $user->getId()) {
            $user->getPlayer()->sendMessage(TextFormat::RED . "You are not the owner of this party");
            return;
        }

        $form = new SimpleForm("Set Party owner", "Change the owner of the party. Select a party member");
        foreach ($party->getMembers() as $member) {
            if ($member->getId() !== $party->getOwnerId()) $form->addButton(new Button($member->getUsername()));//TODO head image
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
        if ($party->getOwnerId() === $user->getId()) {
            $party->getOwner()->getPlayer()->sendMessage(TextFormat::RED . $user->getDisplayName() . " already is the owner of the party!");
            return;
        }
        $oldOwner = $party->getOwner();
        $party->setOwnerId($user->getId());
        $oldOwner->getPlayer()->sendMessage(TextFormat::GOLD . "The new owner of the party now is " . $user->getDisplayName() . "!");
        $user->getPlayer()->sendMessage(TextFormat::GOLD . "You are now the owner of the party \"" . $party->getName() . "\"!");
        foreach ($party->getMembers() as $member) {
            $member->getPlayer()->sendMessage(TextFormat::GOLD . $oldOwner->getDisplayName() . " changed the party owner. " . $user->getDisplayName() . " now is the owner of the party!");
        }
    }
}
