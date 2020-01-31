<?php

declare(strict_types=1);

namespace xenialdan\UserManager\commands\admin;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use InvalidArgumentException;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use xenialdan\UserManager\API;
use xenialdan\UserManager\BanStore;
use xenialdan\UserManager\models\Ban;

class BanlistCommand extends BaseCommand
{

    /**
     * This is where all the arguments, permissions, sub-commands, etc would be registered
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("usermanager.banlist");
        $this->registerArgument(0, new RawStringArgument("Player", true));
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
            API::openBannedListUI($sender);//TODO
        else {
            $name = trim(strval($args["Player"] ?? ""));
            if (empty($name)) {
                $sender->sendMessage("Invalid name given");
                return;
            }
            if (($ban = BanStore::getBanByName($name)) instanceof Ban) {
                API::openBanEntryUI($sender, $ban);
            } else {
                API::openUserNotFoundUI($sender, $name);
            }
        }
    }
}
