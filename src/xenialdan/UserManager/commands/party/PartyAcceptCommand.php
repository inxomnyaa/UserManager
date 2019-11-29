<?php

declare(strict_types=1);

namespace xenialdan\UserManager\commands\party;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use InvalidArgumentException;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\customui\elements\Button;
use xenialdan\customui\windows\SimpleForm;
use xenialdan\UserManager\models\Party;
use xenialdan\UserManager\User;
use xenialdan\UserManager\UserStore;

class PartyAcceptCommand extends BaseSubCommand
{

    /**
     * This is where all the arguments, permissions, sub-commands, etc would be registered
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("usermanager.party.accept");
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
            $form = new SimpleForm("Party Requests", "Parties that want you to join");
            foreach (Party::getInvitedParties($user) as $party) {
                $form->addButton(new Button($party->getOwner()->getRealUsername()));
            }
            $form->addButton(new Button("Back"));
            $form->setCallable(function (Player $player, string $data) use ($form, $user): void {
                if ($data === "Back") return;
                if (($party = (Party::getParty(UserStore::getUserByName($data)))) instanceof Party) {
                    self::acceptParty($party, $user);
                }
            });
            $sender->sendForm($form);
            return;
        } else {
            if ($party->getOwnerId() !== $user->getId()) {
                $user->getPlayer()->sendMessage(TextFormat::RED . "You are not the owner of this party");
                return;
            }
            $form = new SimpleForm("Party Requests", "Players that want to join the party");
            foreach ($party->getRequests() as $member) {
                if ($member->getId() !== $party->getOwnerId()) $form->addButton(new Button($member->getRealUsername()));
            }
            $form->addButton(new Button("Back"));
            $form->setCallable(function (Player $player, string $data) use ($form, $party): void {
                if ($data === "Back") return;
                if (($userByName = (UserStore::getUserByName($data))) instanceof User) {
                    self::acceptPlayer($party, $userByName);
                }
            });
            $sender->sendForm($form);
            return;
        }
    }

    /**
     * @param Party $party
     * @param User $user
     */
    private static function acceptPlayer(Party $party, User $user): void
    {
        if (!$party->isRequested($user)) {
            $party->getOwner()->getPlayer()->sendMessage(TextFormat::RED . $user->getDisplayName() . " has not requested to join to the party!");
            return;
        }
        $party->acceptRequest($user);
        $user->getPlayer()->sendMessage(TextFormat::GOLD . "Your request to join the party \"{$party->getName()}\" by " . $party->getOwner()->getDisplayName() . " was accepted");
        $party->getOwner()->getPlayer()->sendMessage(TextFormat::GREEN . $user->getDisplayName() . " has been accepted to join the party");
    }

    /**
     * @param Party $party
     * @param User $user
     */
    private static function acceptParty(Party $party, User $user): void
    {
        if (!$party->isInvited($user)) {
            $party->getOwner()->getPlayer()->sendMessage(TextFormat::RED . $user->getDisplayName() . " has not been invited to the party!");
            return;
        }
        $party->acceptInvite($user);
        $user->getPlayer()->sendMessage(TextFormat::GOLD . "You accepted to join the party \"{$party->getName()}\" by " . $party->getOwner()->getDisplayName());
        $party->getOwner()->getPlayer()->sendMessage(TextFormat::GREEN . $user->getDisplayName() . " has accepted to join the party");
    }
}
