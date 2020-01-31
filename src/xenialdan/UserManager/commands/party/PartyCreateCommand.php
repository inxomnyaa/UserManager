<?php

declare(strict_types=1);

namespace xenialdan\UserManager\commands\party;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\UserManager\event\PartyCreateEvent;
use xenialdan\UserManager\models\Party;
use xenialdan\UserManager\User;
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
        if (Party::getParty($user)) {
            $user->getPlayer()->sendMessage(TextFormat::RED . "You are already in a party. Leave first.");
            return;
        }
        $party = new Party($user);
        if (User::isValidUserName($name = strval($args["Name"] ?? ""))) {//Yes, i honestly abuse this method here. Party names are just like player names
            $party->setName(User::cleanUserName($name));
        } else
            $user->getPlayer()->sendMessage("Invalid name given, using default. The party name must consist out of 1 - 16 of the following symbols: A-Z a-z 0-9 _ and space");
        try {
            ($ev = new PartyCreateEvent($party, $user))->call();
            if (!$ev->isCancelled()) {
                Party::addParty($ev->getParty());
                $user->getPlayer()->sendMessage('The party "' . $ev->getParty()->getName() . '" has been created!');
            } else {
                $user->getPlayer()->sendMessage('The party "' . $ev->getParty()->getName() . '" could not be created!');
            }
        } catch (\Exception $e) {
        }
    }
}
