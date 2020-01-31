<?php

declare(strict_types=1);

namespace xenialdan\UserManager\commands\party;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use InvalidArgumentException;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\customui\windows\ModalForm;
use xenialdan\UserManager\models\Party;
use xenialdan\UserManager\User;
use xenialdan\UserManager\UserStore;

class PartyJoinCommand extends BaseSubCommand
{

    /**
     * This is where all the arguments, permissions, sub-commands, etc would be registered
     */
    protected function prepare(): void
    {
        $this->setPermission("usermanager.party.join");
        $this->registerArgument(0, new RawStringArgument("Player", true));
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
        $name = strval($args["Player"]);
        if (!User::isValidUserName($name)) {
            $sender->sendMessage("Invalid name given");
            return;
        }
        $name = User::cleanUserName(strval($name));
        $find = UserStore::getUserByName($name);
        if (!$find instanceof User) {
            $sender->sendMessage("No user with the name $name found");
            return;
        }
        $party = Party::getParty($find);
        if (!$party instanceof Party) {
            $user->getPlayer()->sendMessage(TextFormat::RED . "This user is in no party");
            return;
        }
        $form = new ModalForm("Join Party?", "Do you want to join the party \"{$party->getName()}\" by {$party->getOwner()->getDisplayName()}?", "Yes", "No");
        $form->setCallable(function (Player $player, bool $data) use ($party, $user): void {
            if ($data) {
                self::join($party, $user);
            }
        });
        $sender->sendForm($form);
    }

    /**
     * @param Party $party
     * @param User $user
     */
    private static function join(Party $party, User $user): void
    {
        if ($party->isRequested($user)) {
            $user->getPlayer()->sendMessage(TextFormat::RED . "You have already asked to join the party!");
            return;
        }
        if ($party->isMember($user)) {
            $user->getPlayer()->sendMessage(TextFormat::RED . "You are already member of the party!");
            return;
        }
        $party->requestMember($user);
        $user->getPlayer()->sendMessage(TextFormat::RED . "You have requested to join the party \"" . $party->getName() . "\"");
        $party->getOwner()->getPlayer()->sendMessage(TextFormat::GOLD . $user->getDisplayName() . " requested to join the party");
    }
}
