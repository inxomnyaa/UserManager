<?php

declare(strict_types=1);

namespace xenialdan\UserManager\commands;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use xenialdan\UserManager\API;
use xenialdan\UserManager\Loader;
use xenialdan\UserManager\User;

class BlockCommand extends BaseCommand
{

    /**
     * This is where all the arguments, permissions, sub-commands, etc would be registered
     */
    protected function prepare(): void
    {
        $this->setPermission("usermanager.block");
        $this->registerArgument(0, new RawStringArgument("Player", true));
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param BaseArgument[] $args
     * @throws \InvalidArgumentException
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        /** @var Player $sender */
        if (empty($args))
            API::openBlockedListUI($sender);//TODO
        else {
            $name = trim($args["Player"] ?? "");
            if (empty($name)) {
                $sender->sendMessage("Invalid name given");
                return;
            }
            if (($friend = (Loader::$userstore::getUserByName($name))) instanceof User && $friend->getUsername() !== $sender->getLowerCaseName()) {
                API::openBlockUserUI($sender, $friend);
            } else {
                API::openUserNotFoundUI($sender, $name);
            }
        }
    }
}
