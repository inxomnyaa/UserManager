<?php

declare(strict_types=1);

namespace xenialdan\UserManager\commands\party;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\BaseSubCommand;
use InvalidArgumentException;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\customui\elements\Button;
use xenialdan\customui\windows\SimpleForm;
use xenialdan\UserManager\API;
use xenialdan\UserManager\models\Party;
use xenialdan\UserManager\UserStore;

class PartyMembersCommand extends BaseSubCommand
{

    /**
     * This is where all the arguments, permissions, sub-commands, etc would be registered
     */
    protected function prepare(): void
    {
        $this->setPermission("usermanager.party.members");
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
        $form = new SimpleForm("Party Members");
        foreach ($party->getMembers() as $member) {
            $form->addButton(new Button($member->getUsername()));//TODO head image
        }
        $form->setCallable(function (Player $player, string $data) use ($form): void {
            API::openUserUI($player, UserStore::getUserByName($data), $form);
        });
        $sender->sendForm($form);
    }
}
