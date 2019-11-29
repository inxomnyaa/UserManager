<?php

declare(strict_types=1);

namespace xenialdan\UserManager\commands\admin;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\BaseCommand;
use InvalidArgumentException;
use pocketmine\command\CommandSender;
use pocketmine\form\Form;
use pocketmine\Player;
use xenialdan\UserManager\API;
use xenialdan\UserManager\User;
use xenialdan\UserManager\UserStore;

class BanCommand extends BaseCommand
{

    /**
     * This is where all the arguments, permissions, sub-commands, etc would be registered
     */
    protected function prepare(): void
    {
        $this->setPermission("usermanager.ban");
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
        API::openUserSearchUI(
            $sender,
            "Ban Manager - Search",
            function (Player $player, User $user, Form $form): void {
                API::openBanCreateUI($player, $user, $form);
            }
        );
    }
}
