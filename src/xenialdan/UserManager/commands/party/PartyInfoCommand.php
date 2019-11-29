<?php

declare(strict_types=1);

namespace xenialdan\UserManager\commands\party;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use InvalidArgumentException;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\customui\elements\Button;
use xenialdan\customui\windows\SimpleForm;
use xenialdan\UserManager\API;
use xenialdan\UserManager\models\Party;
use xenialdan\UserManager\User;
use xenialdan\UserManager\UserStore;

class PartyInfoCommand extends BaseSubCommand
{

    /**
     * This is where all the arguments, permissions, sub-commands, etc would be registered
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("usermanager.party.info");
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
        $user = UserStore::getUser($sender);
        if ($user === null) {
            $sender->sendMessage("DEBUG: null");
            return;
        }
        if (empty($args["Player"] ?? null)) {
            $party = Party::getParty($user);
            if (!$party instanceof Party) {
                $user->getPlayer()->sendMessage(TextFormat::RED . "You are in no party");
                return;
            }
        } else {
            $name = trim($args["Player"] ?? "");
            if (empty($name)) {
                $sender->sendMessage("Invalid name given");
                return;
            }
            if (($friend = (UserStore::getUserByName($name))) instanceof User) {
                $party = Party::getParty($friend);
                if (!$party instanceof Party) {
                    $user->getPlayer()->sendMessage(TextFormat::RED . "This player is in no party");
                    return;
                }//else continue
            } else {
                API::openUserNotFoundUI($sender, $name);
                return;
            }
        }
        $content = "Name: " . $party->getName();
        $content .= TextFormat::EOL . "Owner: " . $party->getOwner()->getRealUsername();
        $content .= TextFormat::EOL . "Members [" . count($party->getMembers()) . "]: " . join(", ", array_map(function (User $member): string {
                return $member->getDisplayName();
            }, $party->getMembers()));
        $form = new SimpleForm("Party info", $content);
        $form->addButton(new Button("Show member info"));
        $form->addButton(new Button("Back"));
        $form->setCallable(function (Player $player, string $data) use ($form, $party): void {
            if ($data === "Back") return;//TODO
            if ($data === "Show member info") {
                $player->getServer()->dispatchCommand($player, "party members");
                return;
            }
        });
        $sender->sendForm($form);
    }
}
