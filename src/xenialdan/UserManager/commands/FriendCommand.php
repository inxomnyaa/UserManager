<?php

declare(strict_types=1);

namespace xenialdan\UserManager\commands;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use xenialdan\UserManager\API;

class FriendCommand extends BaseCommand
{

    /**
     * This is where all the arguments, permissions, sub-commands, etc would be registered
     */
    protected function prepare(): void
    {
        $this->setPermission("usermanager.friend");
        $this->registerSubCommand(new FriendListCommand("list", "Show your friend list. Add false to display as chat message"));
        $this->registerSubCommand(new FriendAddCommand("add", "Send a friend request to a player"));
        $this->registerSubCommand(new FriendRemoveCommand("remove", "Remove a friend"));
        $this->registerSubCommand(new FriendAcceptCommand("accept", "Accept a friend request"));
        $this->registerSubCommand(new FriendDenyCommand("deny", "Decline a friend request"));
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param BaseArgument[] $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        /** @var Player $sender */
        if (empty($args))
            API::openFriendsUI($sender);
    }
}
