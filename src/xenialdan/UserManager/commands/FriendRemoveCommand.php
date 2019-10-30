<?php

declare(strict_types=1);

namespace xenialdan\UserManager\commands;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use xenialdan\UserManager\API;
use xenialdan\UserManager\Loader;
use xenialdan\UserManager\User;

class FriendRemoveCommand extends BaseSubCommand
{

    /**
     * This is where all the arguments, permissions, sub-commands, etc would be registered
     */
    protected function prepare(): void
    {
        $this->setPermission("usermanager.friend.remove");
        $this->registerArgument(0, new RawStringArgument("Player"));
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param BaseArgument[] $args
     * @throws \InvalidArgumentException
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $user = Loader::$userstore::getUser($sender);
        if ($user === null) {
            $sender->sendMessage("DEBUG: null");
            return;
        }
        $name = trim($args["Player"] ?? "");
        if (empty($name)) {
            $sender->sendMessage("Invalid name given");
            return;
        }
        if (($friend = (Loader::$userstore::getUserByName($name))) instanceof User && $friend->getUsername() !== $sender->getLowerCaseName()) {
            API::openFriendRemoveConfirmUI($sender, $friend);
        } else {
            API::openUserNotFoundUI($sender, $name);
        }
    }
}
