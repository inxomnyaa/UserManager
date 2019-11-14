<?php

declare(strict_types=1);

namespace xenialdan\UserManager\commands;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\SubCommandCollision;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;

class UserManagerCommand extends BaseCommand
{

    /**
     * This is where all the arguments, permissions, sub-commands, etc would be registered
     * @throws SubCommandCollision
     */
    protected function prepare(): void
    {
        $this->setPermission("usermanager");
        $this->registerSubCommand(new VersionCommand("version", "UserManager version"));
        $this->registerSubCommand(new ListUserCommand("listuser", "List all users"));
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param BaseArgument[] $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (empty($args)) $sender->sendMessage(TF::GREEN . $this->getUsage());
    }
}
