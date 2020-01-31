<?php

declare(strict_types=1);

namespace xenialdan\UserManager\commands\friend;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\SubCommandCollision;
use InvalidArgumentException;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use xenialdan\UserManager\API;

class FriendCommand extends BaseCommand
{

    /**
     * This is where all the arguments, permissions, sub-commands, etc would be registered
     * @throws SubCommandCollision
     */
    protected function prepare(): void
    {
        $this->setPermission("usermanager.friend");
        $this->registerSubCommand(new FriendListCommand("list", "Show your friend list. Add false to display as chat message"));
        $this->registerSubCommand(new FriendAddCommand("add", "Send a friend request to a player"));
        $this->registerSubCommand(new FriendRemoveCommand("remove", "Remove a friend"));
        $this->registerSubCommand(new FriendAcceptCommand("accept", "Accept a friend request"));
        $this->registerSubCommand(new FriendDenyCommand("deny", "Decline a friend request"));
        $this->registerSubCommand(new FriendRequestsCommand("requests", "View friend requests"));
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
        if (empty($args))
            API::openFriendsUI($sender);
    }
}
