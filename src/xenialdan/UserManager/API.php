<?php

declare(strict_types=1);

namespace xenialdan\UserManager;

use InvalidArgumentException;
use pocketmine\form\Form;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\customui\elements\Button;
use xenialdan\customui\elements\Dropdown;
use xenialdan\customui\elements\Input;
use xenialdan\customui\elements\Toggle;
use xenialdan\customui\windows\CustomForm;
use xenialdan\customui\windows\ModalForm;
use xenialdan\customui\windows\SimpleForm;
use xenialdan\UserManager\models\Ban;

class API
{
    public const FRIEND_PENDING = 0;
    public const FRIEND_ACCEPTED = 1;
    public const FRIEND_DECLINED = 2;
    public const FRIEND_BLOCKED = 3;

    public const STATE_MESSAGE_UNREAD = 0;
    public const STATE_MESSAGE_EDITED = 1;
    public const STATE_MESSAGE_DELETED = 2;

    /**
     * TODO
     * @param Player $player
     * @param null|User $user
     * @param Form|null $previousForm
     * @throws InvalidArgumentException
     */
    public static function openUserUI(Player $player, ?User $user, ?Form $previousForm = null): void
    {
        if ($user === null) {
            $player->sendMessage("DEBUG: null");
            return;
        }
        $form = new SimpleForm($user->getUsername() . " Information");
        $form->addButton(new Button("Name: " . $user->getRealUsername()));//TODO image
        $form->addButton(new Button("Nick: " . $user->getDisplayName()));
        $form->addButton(new Button("Online: " . ($user->isOnline() ? "Yes" : "No")));
        $form->addButton(new Button("Manage friendship"));
        $form->addButton(new Button("Back"));
        $form->setCallable(function (Player $player, string $data) use ($form, $previousForm, $user): void {
            if ($data === "Back") {
                if ($previousForm) $player->sendForm($previousForm);
            } else if ($data === "Manage friendship") {
                API::openManageUI($player, $user, $form);
            } else $player->sendForm($form);
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @param null|User $user
     * @param Form|null $previousForm
     * @throws InvalidArgumentException
     */
    public static function openRequestUserUI(Player $player, ?User $user, ?Form $previousForm = null): void
    {
        if ($user === null) {
            $player->sendMessage("DEBUG: null");
            return;
        }
        $form = new SimpleForm($user->getUsername() . " Information");
        $form->addButton(new Button("Name: " . $user->getRealUsername()));//TODO image
        $form->addButton(new Button("Nick: " . $user->getDisplayName()));
        $form->addButton(new Button("Accept"));
        $form->addButton(new Button("Reject"));
        $form->addButton(new Button("Block"));
        $form->addButton(new Button("Back"));
        $form->setCallable(function (Player $player, string $data) use ($form, $previousForm, $user): void {
            switch ($data) {
                case "Back":
                {
                    if ($previousForm) $player->sendForm($previousForm);
                    break;
                }
                case "Accept":
                {
                    API::acceptFriendRequest($player, $user);
                    break;
                }
                case "Reject":
                {
                    API::rejectFriendRequest($player, $user);
                    break;
                }
                case "Block":
                {
                    API::openBlockUserUI($player, $user);
                    break;
                }
                default:
                    $player->sendForm($form);
            }
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @param null|User $user
     * @param Form|null $previousForm
     * @throws InvalidArgumentException
     */
    public static function openManageUI(Player $player, ?User $user, ?Form $previousForm = null): void
    {
        if ($user === null) {
            $player->sendMessage("DEBUG: null");
            return;
        }
        $form = new SimpleForm($user->getUsername() . " Friendship");
        $form->addButton(new Button("Name: " . $user->getRealUsername()));//TODO image
        $form->addButton(new Button("Add friend"));//TODO remove if already friend
        $form->addButton(new Button("Remove friend"));//TODO remove if not friend
        $form->addButton(new Button("Block user"));//TODO remove if blocked
        $form->addButton(new Button("Unblock user"));//TODO remove if not blocked
        $form->addButton(new Button("Back"));
        $form->setCallable(function (Player $player, string $data) use ($form, $previousForm, $user): void {
            switch ($data) {
                case "Back":
                {
                    if ($previousForm) $player->sendForm($previousForm);
                    break;
                }
                case "Add friend":
                {
                    API::openFriendConfirmUI($player, $user, $form);
                    break;
                }
                case "Remove friend":
                {
                    API::openFriendRemoveConfirmUI($player, $user, $form);
                    break;
                }
                case "Block user":
                {
                    API::openBlockUserUI($player, $user, $form);
                    break;
                }
                case "Unblock user":
                {
                    API::openUnblockUserUI($player, $user, $form);
                    break;
                }
                default:
                    $player->sendForm($form);
            }
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @param Form|null $previousForm
     * @throws InvalidArgumentException
     */
    public static function openFriendsUI(Player $player, ?Form $previousForm = null): void
    {
        $user = UserStore::getUser($player);
        if ($user === null) {
            $player->sendMessage("DEBUG: null");
            return;
        }
        $form = new SimpleForm("Friend Manager");
        $form->addButton(new Button("Add"));//TODO image
        $form->addButton(new Button("List"));//TODO image
        $form->addButton(new Button("Friend requests"));//TODO image
        $form->addButton(new Button("Search user"));//TODO image
        $form->addButton(new Button("Blocked users"));//TODO image
        #$form->addButton(new Button("Messages"));//TODO image
        $form->addButton(new Button("Back"));
        $form->setCallable(function (Player $player, string $data) use ($form, $previousForm): void {
            if ($data === "Back") {
                if ($previousForm) $player->sendForm($previousForm);
                return;
            }
            switch ($data) {
                case "Add":
                {
                    API::openFriendAddUI($player);
                    break;
                }
                case "List":
                {
                    API::openFriendListUI($player, $form);
                    break;
                }
                case "Friend requests":
                {
                    API::openFriendRequestUI($player, $form);
                    break;
                }
                case "Search user":
                {
                    API::openUserSearchUI(
                        $player,
                        "Friend Manager - Search",
                        function ($player, $user, $form): void {
                            API::openUserUI($player, $user, $form);
                        }
                    );
                    break;
                }
                case "Blocked users":
                {
                    API::openBlockedListUI($player, $form);
                    break;
                }
                default:
                    $player->sendMessage("TODO: $data");
            }
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @throws InvalidArgumentException
     */
    public static function openFriendAddUI(Player $player): void
    {
        //TODO use openUserSearchUI
        $form = new CustomForm("Friend Manager - Add");
        $form->addElement(new Input("Search user", "Username"));
        $options = array_values(array_map(function (User $user): string {
            return $user->getRealUsername();
        }, array_filter(UserStore::getUsers(), function (User $user) use ($player): bool {
            return $user->getUsername() !== $player->getLowerCaseName();
        })));
        $form->addElement(new Dropdown("Select user", $options));
        $form->setCallable(function (Player $player, array $data) use ($form): void {
            if (empty(($name = $data[0]))) $name = $data[1];
            if (($user = (UserStore::getUserByName($name))) instanceof User && $user->getUsername() !== $player->getLowerCaseName()) {
                API::openFriendConfirmUI($player, $user, $form);
            } else {
                API::openUserNotFoundUI($player, $name, $form);
            }
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @param string $title
     * @param callable $continueAt
     * @throws InvalidArgumentException
     */
    public static function openUserSearchUI(Player $player, string $title, callable $continueAt): void
    {
        $form = new CustomForm($title);
        $form->addElement(new Input("Search user", "Username"));
        $form->setCallable(function (Player $player, array $data) use ($form, $continueAt): void {
            if (($user = (UserStore::getUserByName($name = $data[0]))) instanceof User && $user->getUsername() !== $player->getLowerCaseName()) {
                $continueAt($player, $user, $form);
            } else {
                API::openUserNotFoundUI($player, $name, $form);
            }
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @param User $friend
     * @param Form|null $previousForm
     * @throws InvalidArgumentException
     */
    public static function openFriendConfirmUI(Player $player, User $friend, ?Form $previousForm = null): void
    {
        $form = new SimpleForm("Friend Manager - Add", "Add " . $friend->getRealUsername() . " as friend?");
        $form->addButton(new Button($friend->getDisplayName() . "'s profile"));#TODO image
        $form->addButton(new Button("Yes"));#TODO image
        $form->addButton(new Button("No"));#TODO image
        $form->setCallable(function (Player $player, string $data) use ($form, $friend, $previousForm): void {
            if ($data === "Yes") {
                API::sendFriendRequest($player, $friend);
            } else if ($data === "No") {
                if ($previousForm) $player->sendForm($previousForm);
            } else {
                API::openUserUI($player, $friend, $form);
            }
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @param User $friend
     * @param Form|null $previousForm
     * @throws InvalidArgumentException
     */
    public static function openFriendRemoveConfirmUI(Player $player, User $friend, ?Form $previousForm = null): void
    {
        $form = new SimpleForm("Friend Manager - Remove", "Remove " . $friend->getRealUsername() . " from friends?");
        $form->addButton(new Button($friend->getDisplayName() . "'s profile"));#TODO image
        $form->addButton(new Button("Yes"));#TODO image
        $form->addButton(new Button("No"));#TODO image
        $form->setCallable(function (Player $player, string $data) use ($form, $friend, $previousForm): void {
            if ($data === "Yes") {
                API::removeFriend($player, $friend);
            } else if ($data === "No") {
                if ($previousForm) $player->sendForm($previousForm);
            } else {
                API::openUserUI($player, $friend, $form);
            }
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @param User $friend
     * @param Form|null $previousForm
     * @throws InvalidArgumentException
     */
    public static function openBlockUserUI(Player $player, User $friend, ?Form $previousForm = null): void
    {
        $form = new SimpleForm("Friend Manager - Block", "Block " . $friend->getRealUsername() . "? This will hide chat messages and block conversation with this user.");
        $form->addButton(new Button($friend->getDisplayName() . "'s profile"));#TODO image
        $form->addButton(new Button("Yes"));#TODO image
        $form->addButton(new Button("No"));#TODO image
        $form->setCallable(function (Player $player, string $data) use ($form, $friend, $previousForm): void {
            if ($data === "Yes") {
                API::blockUser($player, $friend);
            } else if ($data === "No") {
                if ($previousForm) $player->sendForm($previousForm);
            } else {
                API::openUserUI($player, $friend, $form);
            }
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @param User $friend
     * @param Form|null $previousForm
     * @throws InvalidArgumentException
     */
    public static function openUnblockUserUI(Player $player, User $friend, ?Form $previousForm = null): void
    {
        $form = new SimpleForm("Friend Manager - Unblock", "Unblock " . $friend->getRealUsername() . "? This will show chat messages and allow conversation with this user.");
        $form->addButton(new Button($friend->getDisplayName() . "'s profile"));#TODO image
        $form->addButton(new Button("Yes"));#TODO image
        $form->addButton(new Button("No"));#TODO image
        $form->setCallable(function (Player $player, string $data) use ($form, $friend, $previousForm): void {
            if ($data === "Yes") {
                API::removeFriend($player, $friend);
            } else if ($data === "No") {
                if ($previousForm) $player->sendForm($previousForm);
            } else {
                API::openUserUI($player, $friend, $form);
            }
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @param string $name
     * @param Form|null $previousForm
     * @throws InvalidArgumentException
     */
    public static function openUserNotFoundUI(Player $player, string $name, ?Form $previousForm = null): void
    {
        $form = new ModalForm("Friend Manager - Error", "User " . $name . " not found!", "Back", "Cancel");
        $form->setCallable(function (Player $player, bool $data) use ($previousForm): void {
            if ($data) {
                if ($previousForm) $player->sendForm($previousForm);
            }
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @param Form|null $previousForm
     */
    public static function openFriendListUI(Player $player, ?Form $previousForm = null): void
    {
        $user = UserStore::getUser($player);
        if ($user === null) {
            $player->sendMessage("DEBUG: null");
            return;
        }
        Loader::$queries->getFriends($user->getId(), function (array $rows) use ($user, $player, $previousForm): void {
            $form = new SimpleForm("Friend Manager - List");
            foreach ($user->getUsersFromRelationship($rows, $user->getId()) as $friend) {
                $form->addButton(new Button(($friend->isOnline() ? TextFormat::DARK_GREEN : TextFormat::DARK_RED) . $friend->getRealUsername()));//TODO image
            }
            $form->addButton(new Button("Back"));
            $form->setCallable(function (Player $player, string $data) use ($form, $previousForm): void {
                if ($data === "Back") {
                    if ($previousForm) $player->sendForm($previousForm);
                } else API::openUserUI($player, UserStore::getUserByName($data), $form);
            });
            $player->sendForm($form);
        });
    }

    /**
     * TODO
     * @param Player $player
     * @param Form|null $previousForm
     */
    public static function openBlockedListUI(Player $player, ?Form $previousForm = null): void
    {
        $user = UserStore::getUser($player);
        if ($user === null) {
            $player->sendMessage("DEBUG: null");
            return;
        }
        Loader::$queries->getBlocked($user->getId(), function (array $rows) use ($user, $player, $previousForm): void {
            $form = new SimpleForm("Friend Manager - Blocked users");
            foreach ($user->getUsersFromRelationship($rows, $user->getId()) as $friend) {
                $form->addButton(new Button(TextFormat::DARK_RED . $friend->getRealUsername()));//TODO image
            }
            $form->addButton(new Button("Back"));
            $form->setCallable(function (Player $player, string $data) use ($form, $previousForm): void {
                if ($data === "Back") {
                    if ($previousForm) $player->sendForm($previousForm);
                } else API::openUserUI($player, UserStore::getUserByName($data), $form);
            });
            $player->sendForm($form);
        });
    }

    /**
     * TODO
     * @param Player $player
     * @param Form|null $previousForm
     */
    public static function openFriendRequestUI(Player $player, ?Form $previousForm = null): void
    {
        $user = UserStore::getUser($player);
        if ($user === null) {
            $player->sendMessage("DEBUG: null");
            return;
        }
        Loader::$queries->getFriendRequests($user->getId(), function (array $rows) use ($user, $player, $previousForm): void {
            $form = new SimpleForm("Friend Manager - Requests");
            foreach ($user->getUsersFromRelationship($rows, $user->getId()) as $friend) {
                $form->addButton(new Button(($friend->isOnline() ? TextFormat::DARK_GREEN : TextFormat::DARK_RED) . $friend->getRealUsername()));//TODO image
            }
            $form->addButton(new Button("Back"));
            $form->setCallable(function (Player $player, string $data) use ($form, $previousForm): void {
                if ($data === "Back") {
                    if ($previousForm) $player->sendForm($previousForm);
                } else API::openRequestUserUI($player, UserStore::getUserByName($data), $form);
            });
            $player->sendForm($form);
        });
    }

    public static function sendFriendRequest(Player $player, User $friend): void
    {
        if (($user = UserStore::getUser($player)) instanceof User) {
            Loader::$queries->setUserRelation($user->getId(), $friend->getId(), API::FRIEND_PENDING, function (int $insertId, int $affectedRows) use ($player, $user, $friend) {
                if ($affectedRows > 0) {
                    $player->sendMessage("Friend request sent to " . $friend->getDisplayName());
                    if ($friend->isOnline()) $friend->getPlayer()->sendMessage("Got friend request by " . $user->getDisplayName());
                }
            });
        }
    }

    public static function removeFriend(Player $player, User $friend): void
    {
        if (($user = UserStore::getUser($player)) instanceof User) {
            Loader::$queries->removeUserRelation($user->getId(), $friend->getId(), function (int $affectedRows) use ($player, $user, $friend) {
                if ($affectedRows > 0) {
                    $player->sendMessage("Friend " . $friend->getDisplayName() . " removed");
                    if ($friend->isOnline()) $friend->getPlayer()->sendMessage($user->getDisplayName() . " removed you from their friends");
                }
            });
        }
    }

    public static function acceptFriendRequest(Player $player, User $friend): void
    {
        if (($user = UserStore::getUser($player)) instanceof User) {
            Loader::$queries->setUserRelation($user->getId(), $friend->getId(), API::FRIEND_ACCEPTED, function (int $insertId, int $affectedRows) use ($player, $user, $friend) {
                if ($affectedRows > 0) {
                    $player->sendMessage("Accepted friend request by " . $friend->getDisplayName());
                    if ($friend->isOnline()) $friend->getPlayer()->sendMessage($user->getDisplayName() . " accepted your friend request!");
                }
            });
        }
    }

    public static function rejectFriendRequest(Player $player, User $friend): void
    {
        if (($user = UserStore::getUser($player)) instanceof User) {
            Loader::$queries->setUserRelation($user->getId(), $friend->getId(), API::FRIEND_DECLINED, function (int $insertId, int $affectedRows) use ($player, $user, $friend) {
                if ($affectedRows > 0) {
                    $player->sendMessage("Rejected friend request by " . $friend->getDisplayName());
                    if ($friend->isOnline()) $friend->getPlayer()->sendMessage($user->getDisplayName() . " did not accept your friend request.");
                }
            });
        }
    }

    public static function blockUser(Player $player, User $friend): void
    {
        if (($user = UserStore::getUser($player)) instanceof User) {
            Loader::$queries->setUserRelation($user->getId(), $friend->getId(), API::FRIEND_BLOCKED, function (int $insertId, int $affectedRows) use ($player, $user, $friend) {
                if ($affectedRows > 0) {
                    $player->sendMessage("Blocked user " . $friend->getDisplayName());
                    if ($friend->isOnline()) $friend->getPlayer()->sendMessage($user->getDisplayName() . " blocked you.");
                }
            });
        }
    }

    public static function sendJoinMessages(Player $player): void
    {
        if (($user = UserStore::getUser($player)) instanceof User) {
            //Op messages
            if ($player->isOp()) {
                $player->sendMessage(count(array_filter(UserStore::getUsers(), function (User $user): bool {
                        return $user->isOnline();
                    })) . "/" . count(UserStore::getUsers()) . " registered users online right now");
            }
            //Friend messages
            Loader::$queries->getFriends($user->getId(), function (array $rows) use ($player, $user): void {
                $friends = $user->getUsersFromRelationship($rows, $user->getId());
                $onlineFriends = array_filter($friends, function (User $friend): bool {
                    return $friend->isOnline();
                });
                $player->sendMessage(count($onlineFriends) . "/" . count($friends) . " Friends online:");
                $player->sendMessage(implode(", ", array_map(function (User $friend) {
                    return $friend->getDisplayName();
                }, $onlineFriends)));
            });
            Loader::$queries->getFriendRequests($user->getId(), function (array $rows) use ($player, $user): void {
                $friends = $user->getUsersFromRelationship($rows, $user->getId());
                $player->sendMessage("You got " . count($friends) . " open friend requests");
                $player->sendMessage(implode(", ", array_map(function (User $friend) {
                    return $friend->getDisplayName();
                }, $friends)));
            });
            //Messages
        }
    }

    /**
     * TODO
     * @param Player $player
     * @param Form|null $previousForm
     */
    public static function openBannedListUI(Player $player, ?Form $previousForm = null): void
    {
        $user = UserStore::getUser($player);
        if ($user === null) {
            $player->sendMessage("DEBUG: null");
            return;
        }
        $form = new SimpleForm("Ban Manager - Banned users");
        foreach (BanStore::getBans() as $ban) {
            $form->addButton(new Button(TextFormat::DARK_RED . UserStore::getUserById($ban->getUserId())->getRealUsername()));//TODO image
        }
        $form->addButton(new Button("Back"));
        $form->setCallable(function (Player $player, string $data) use ($form, $previousForm): void {
            if ($data === "Back") {
                if ($previousForm) $player->sendForm($previousForm);
            } else API::openBanEntryUI($player, BanStore::getBanByName($data), $form);
        });
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param Ban $ban
     * @param Form|null $previousForm
     * @throws InvalidArgumentException
     */
    public static function openBanEntryUI(Player $player, Ban $ban, ?Form $previousForm = null): void
    {
        if (($user = UserStore::getUserById($ban->getUserId())) === null) {
            $player->sendMessage("Could not find user for ban entry $ban");
            return;
        }
        $content = "";
        $content .= "Username: " . $user->getRealUsername();
        if ($user->getRealUsername() !== $user->getDisplayName()) $content .= TextFormat::EOL . TextFormat::RESET . "Nickname: " . $user->getDisplayName();
        $content .= TextFormat::EOL . TextFormat::RESET . "Reason: " . $ban->getReason();
        $content .= TextFormat::EOL . TextFormat::RESET . "Since: " . strftime("%c", $ban->getSince());
        $content .= TextFormat::EOL . TextFormat::RESET . "Until: " . strftime("%c", $ban->getUntil());
        $content .= TextFormat::EOL . TextFormat::RESET . "Expires: " . ($ban->expires ? TextFormat::DARK_GREEN . "Yes" : TextFormat::RED . "No");
        if ($ban->expires) $content .= TextFormat::EOL . TextFormat::RESET . "Has Expired: " . ($ban->hasExpired() ? TextFormat::DARK_GREEN . "Yes" : TextFormat::RED . "No");
        $content .= TextFormat::EOL . TextFormat::RESET . "Name ban: " . ($ban->isTypeBanned(Ban::TYPE_NAME) ? TextFormat::DARK_GREEN . "Yes" : TextFormat::RED . "No");
        $content .= TextFormat::EOL . TextFormat::RESET . "IP ban: " . ($ban->isTypeBanned(Ban::TYPE_IP) ? TextFormat::DARK_GREEN . "Yes" : TextFormat::RED . "No");
        $content .= TextFormat::EOL . TextFormat::RESET . "UUID ban: " . ($ban->isTypeBanned(Ban::TYPE_UUID) ? TextFormat::DARK_GREEN . "Yes" : TextFormat::RED . "No");
        $content .= TextFormat::EOL . TextFormat::RESET . "XUID ban: " . ($ban->isTypeBanned(Ban::TYPE_XUID) ? TextFormat::DARK_GREEN . "Yes" : TextFormat::RED . "No");
        $form = new SimpleForm($user->getUsername() . " Ban Information", $content);
        $form->addButton(new Button("Modify Ban"));
        $form->addButton(new Button("Delete Ban"));
        $form->addButton(new Button("Back"));
        $form->setCallable(function (Player $player, string $data) use ($form, $previousForm, $user): void {
            if ($data === "Back") {
                if ($previousForm) $player->sendForm($previousForm);
            } else if ($data === "Modify Ban") {
                //TODO API::openManageBanUI($player, $user, $form);
            } else $player->sendForm($form);
        });
        $player->sendForm($form);
    }

    /**
     * TODO
     * @param Player $player
     * @param User $user
     * @param Form|null $previousForm
     * @throws InvalidArgumentException
     */
    public static function openBanCreateUI(Player $player, User $user, ?Form $previousForm = null): void
    {
        $form = new CustomForm("Ban " . $user->getRealUsername());
        $form->addElement(new Input("Reason", "Reason", "§l§cYou have been banned!"));
        $form->addElement(new Toggle("Expires", true));
        $form->addElement(new Input("Until", "Example: 1 day 2 hours 5 minutes", "1 day"));
        $form->addElement(new Toggle("Name ban", true));
        $form->addElement(new Toggle("IP ban", true));
        $form->addElement(new Toggle("UUID ban", true));
        $form->addElement(new Toggle("XUID ban", true));
        $form->setCallable(function (Player $player, array $data) use ($form, $previousForm, $user): void {
            [$reason, $expires, $until, $type_name, $type_ip, $type_uuid, $type_xuid] = $data;
            $untilTime = time();
            if ($expires) {
                //TODO better time check here
                $untilTime = strtotime($until);
                if ($untilTime === false) {
                    $player->sendMessage('"' . $until . '" could not be converted to time');//TODO show form with error
                    return;
                }
            }
            $types = "";
            if ($type_name) $types .= Ban::TYPE_NAME;
            if ($type_ip) $types .= Ban::TYPE_IP;
            if ($type_uuid) $types .= Ban::TYPE_UUID;
            if ($type_xuid) $types .= Ban::TYPE_XUID;
            $ban = new Ban($user->getId(), time(), $untilTime, $expires, $reason, $types);
            API::openBanEntryUI($player, $ban, $form);
        });
        $form->setCallableClose(function (Player $player) use ($previousForm): void {
            if ($previousForm) $player->sendForm($previousForm);
        });
        $player->sendForm($form);
    }
}