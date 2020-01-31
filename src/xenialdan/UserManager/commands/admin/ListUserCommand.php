<?php

declare(strict_types=1);

namespace xenialdan\UserManager\commands\admin;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\args\BooleanArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use InvalidArgumentException;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use xenialdan\customui\elements\Button;
use xenialdan\customui\windows\SimpleForm;
use xenialdan\UserManager\API;
use xenialdan\UserManager\User;
use xenialdan\UserManager\UserStore;

class ListUserCommand extends BaseSubCommand
{

    /**
     * This is where all the arguments, permissions, sub-commands, etc would be registered
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("usermanager.listuser");
        $this->registerArgument(0, new BooleanArgument("ui", true));
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
        $users = UserStore::getUsers();
        if (!($args["ui"] ?? false)) {
            $form = new SimpleForm("Registered users");
            foreach ($users as $user) {
                $form->addButton(new Button($user->getUsername()));//TODO head image
            }
            $form->setCallable(function (Player $player, string $data) use ($form): void {
                var_dump($data);
                API::openUserUI($player, UserStore::getUserByName($data), $form);
            });
            $sender->sendForm($form);
            return;
        }
        $sender->sendMessage(implode(", ", array_map(function (User $user): string {
            return $user->getUsername();
        }, $users)));
    }
}
