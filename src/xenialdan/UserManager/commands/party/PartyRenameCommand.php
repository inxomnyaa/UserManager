<?php

declare(strict_types=1);

namespace xenialdan\UserManager\commands\party;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use InvalidArgumentException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use xenialdan\UserManager\models\Party;
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

        $name = trim(TextFormat::clean($args["Name"] ?? ""));
        if (empty($name)) {
            $sender->sendMessage("Invalid name given");
            return;
        }
        $party->setName($name);
        $user->getPlayer()->sendMessage(TextFormat::AQUA . "Party name successfully set to " . $party->getName());
    }
}
