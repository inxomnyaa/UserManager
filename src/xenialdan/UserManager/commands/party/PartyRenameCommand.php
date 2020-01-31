<?php

declare(strict_types=1);

namespace xenialdan\UserManager\commands\party;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use InvalidArgumentException;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\UserManager\event\PartyRenameEvent;
use xenialdan\UserManager\models\Party;
use xenialdan\UserManager\User;
use xenialdan\UserManager\UserStore;

class PartyRenameCommand extends BaseSubCommand
{

    /**
     * This is where all the arguments, permissions, sub-commands, etc would be registered
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("usermanager.party.rename");
        $this->registerArgument(0, new RawStringArgument("Name"));
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
        if (!$party->isOwner($user)) {
            $user->getPlayer()->sendMessage(TextFormat::RED . "You are not the owner of this party");
            return;
        }

        if (User::isValidUserName($name = strval($args["Name"] ?? ""))) {//Yes, i honestly abuse this method here. Party names are just like player names
            try {
                ($ev = new PartyRenameEvent($party, $user, User::cleanUserName($name)))->call();
                if (!$ev->isCancelled()) {
                    $party->setName($ev->getNewName());
                    $user->getPlayer()->sendMessage(TextFormat::AQUA . "Party name successfully set to " . $party->getName());
                } else {
                    $user->getPlayer()->sendMessage(TextFormat::AQUA . "The party name was not set");
                }
            } catch (\Exception $e) {
            }
        } else {
            $user->getPlayer()->sendMessage("Invalid name given. The party name must consist out of 1 - 16 of the following symbols: A-Z a-z 0-9 _ and space");
            return;
        }
    }
}
