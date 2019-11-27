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

class PartyCreateCommand extends BaseSubCommand
{

    /**
     * This is where all the arguments, permissions, sub-commands, etc would be registered
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("usermanager.party.create");
        $this->registerArgument(0, new RawStringArgument("Name", true));
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
        if (Party::getParty($user)) {
            $user->getPlayer()->sendMessage(TextFormat::RED . "You are already in a party. Leave first.");
            return;
        }
        $party = new Party($user);
        if (!empty(TextFormat::clean($name = trim($args["Name"] ?? "")))) $party->setName($name);
        Party::addParty($party);
        $user->getPlayer()->sendMessage('The party "' . $party . '" has been created!');
    }
}
