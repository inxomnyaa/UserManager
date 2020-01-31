<?php

declare(strict_types=1);

namespace xenialdan\UserManager\commands\party;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\BaseSubCommand;
use InvalidArgumentException;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\customui\windows\ModalForm;
use xenialdan\UserManager\models\Party;
use xenialdan\UserManager\User;
use xenialdan\UserManager\UserStore;

class PartyLeaveCommand extends BaseSubCommand
{

    /**
     * This is where all the arguments, permissions, sub-commands, etc would be registered
     */
    protected function prepare(): void
    {
        $this->setPermission("usermanager.party.leave");
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param BaseArgument[] $args
     * @throws InvalidArgumentException
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command is for players only.");
            return;
        }
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
        $form = new ModalForm("Leave Party?", "Do you want to leave the party \"{$party->getName()}\"?", "Yes", "No");
        $form->setCallable(function (Player $player, bool $data) use ($party, $user): void {
            if ($data) {
                self::leave($party, $user);
            }
        });
        $sender->sendForm($form);
    }

    /**
     * @param Party $party
     * @param User $user
     */
    private static function leave(Party $party, User $user): void
    {
        $party->removeMember($user);
        $user->getPlayer()->sendMessage(TextFormat::RED . "You have left the party \"" . $party->getName() . "\"");
        foreach ($party->getMembers() as $member)
            $member->getPlayer()->sendMessage(TextFormat::GOLD . $user->getDisplayName() . " has left the party");
    }
}
