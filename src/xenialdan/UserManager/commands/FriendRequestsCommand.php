<?php

declare(strict_types=1);

namespace xenialdan\UserManager\commands;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\args\BooleanArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use xenialdan\UserManager\API;
use xenialdan\UserManager\Loader;
use xenialdan\UserManager\User;

class FriendRequestsCommand extends BaseSubCommand
{

    /**
     * This is where all the arguments, permissions, sub-commands, etc would be registered
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("usermanager.friend.requests");
        $this->registerArgument(0, new BooleanArgument("ui", true));
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param BaseArgument[] $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        /** @var Player $sender */
        if (!($args["ui"] ?? false)) {
            API::openFriendRequestUI($sender);
            return;
        }
        $user = Loader::$userstore::getUser($sender);
        if ($user === null) {
            $sender->sendMessage("DEBUG: null");
            return;
        }
        Loader::$queries->getFriendRequests($user->getId(), function (array $rows) use ($user, $sender): void {
            $names = array_map(function (User $user): string {
                return $user->getUsername();
            }, $user->getUsersFromRelationship($rows, $user->getId()));
            if (count($names) > 0) {
                $sender->sendMessage("Friend requests (" . count($names) . "):");
                $sender->sendMessage(implode(", ", $names));
            } else {
                $sender->sendMessage("You got no friend requests");
            }
        });
    }
}
