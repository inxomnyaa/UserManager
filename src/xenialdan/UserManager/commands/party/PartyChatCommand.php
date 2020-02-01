<?php

declare(strict_types=1);

namespace xenialdan\UserManager\commands\party;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\args\TextArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use InvalidArgumentException;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\UserManager\models\Party;
use xenialdan\UserManager\UserStore;

class PartyChatCommand extends BaseSubCommand
{

    /**
     * This is where all the arguments, permissions, sub-commands, etc would be registered
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("usermanager.party.chat");
        $this->registerArgument(0, new TextArgument("message", true));
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

        if (empty($args["message"] ?? "")) {
            $state = $party->toggleChat($user);
            if ($state) $user->getPlayer()->sendMessage(TextFormat::GREEN . "You joined the party chat");
            else $user->getPlayer()->sendMessage(TextFormat::RED . "You left the party chat");
        } else {
            $user->getPlayer()->chat("!p " . $args["message"]);
            $user->getPlayer()->sendWhisper("Party Chat", "You can put !p, !party or !pc in front of a message to send it in the party chat!");
        }
    }
}
