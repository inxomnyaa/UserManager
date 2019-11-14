<?php

declare(strict_types=1);

namespace xenialdan\UserManager\listener;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\form\Form;
use pocketmine\form\FormValidationException;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\ServerSettingsRequestPacket;
use pocketmine\network\mcpe\protocol\ServerSettingsResponsePacket;
use pocketmine\Player;
use xenialdan\customui\elements\Dropdown;
use xenialdan\customui\elements\Input;
use xenialdan\customui\elements\Toggle;
use xenialdan\customui\windows\ServerForm;
use xenialdan\UserManager\event\UserLoginEvent;
use xenialdan\UserManager\event\UserSettingsChangeEvent;
use xenialdan\UserManager\Loader;
use xenialdan\UserManager\models\UserSettings;
use xenialdan\UserManager\User;
use xenialdan\UserManager\UserStore;

class SettingsListener implements Listener
{
    /** @var Form[] */
    private $forms = [];
    /** @var int */
    private $formId = -1;

    /**
     * @param DataPacketReceiveEvent $event
     */
    public function onPacket(DataPacketReceiveEvent $event)
    {
        if ($event->getPacket()->pid() === ServerSettingsRequestPacket::NETWORK_ID) {
            $event->setCancelled(!$this->onSettingsRequest($event));
        } else if ($event->getPacket()->pid() === ServerSettingsResponsePacket::NETWORK_ID) {
            $event->setCancelled(!$this->onSettingsResponse($event));
        } else if ($event->getPacket()->pid() === ModalFormResponsePacket::NETWORK_ID) {
            $event->setCancelled(!$this->onSettingsModalResponse($event));
        }
    }

    public function onLogin(UserLoginEvent $event): void
    {
        $user = $event->getUser();
        var_dump($user);
        Loader::$queries->createUserSettings($user->getId(), function (int $insertId, int $affectedRows) use ($user): void {
            if ($affectedRows > 0) {
                Loader::getInstance()->getLogger()->debug("Created entry $insertId in user_settings for user " . $user->getRealUsername());
            }
            Loader::$queries->getUserSettings($user->getId(), function (array $rows, array $columns) use ($user): void {
                //TODO debug
                var_dump("CREATE SETTINGS");
                $user->setSettings(new UserSettings($rows[0]));
            });
        });
    }

    private function onSettingsRequest(DataPacketReceiveEvent $event): bool
    {
        $player = $event->getPlayer();
        if (($user = UserStore::getUser($player)) instanceof User) {
            //TODO debug
            $settings = $user->getSettings();
            if ($settings instanceof UserSettings) {
                var_dump($settings);
                $form = new ServerForm("Settings");
                foreach ($settings->jsonSerialize() as $row => $value) {
                    [$type, $entry] = explode("_", $row, 2);
                    switch ($type) {
                        case UserSettings::PREFIX_STRING:
                        {
                            if (strpos($entry, "language") !== false) {
                                //hack TODO
                                $form->addElement(new Dropdown($entry, [$value]));
                                break;
                            }
                            $form->addElement(new Input($row, /*translation*/ $entry, $value));
                            break;
                        }
                        case UserSettings::PREFIX_BOOL:
                        {
                            $form->addElement(new Toggle( /*translation*/ $entry, $value == 1));
                            break;
                        }
                    }
                }
                $form->setCallable(function (Player $player, array $data) use ($user): void {
                    $ev = new UserSettingsChangeEvent($user, new UserSettings($data));
                    $ev->call();
                    if (!$ev->isCancelled()) {
                        $user->setSettings($ev->getNew());
                        $user->getPlayer()->sendMessage("Changed settings: ");
                        foreach ($ev->getChanged() as $key => $value) {
                            $user->getPlayer()->sendMessage($key . " > " . $value);
                        }
                    }
                });

                $this->formId++;
                $this->forms[$this->formId] = $form;
                $packet = new ServerSettingsResponsePacket();
                $packet->formData = json_encode($form);
                $packet->formId = $this->formId;
                $player->dataPacket($packet);

                #$player->sendForm($form);//TODO must be ServerSettingsResponsePacket, sendForm uses Modal :/

                return true;
            } else return false;
        }
        return false;
    }

    private function onSettingsModalResponse(DataPacketReceiveEvent $event)
    {
        /** @var ModalFormResponsePacket $pk */
        $pk = $event->getPacket();
        $player = $event->getPlayer();
        if (($user = UserStore::getUser($player)) instanceof User) {
            $formId = $pk->formId;
            if (isset($this->forms[$formId])) {
                try {
                    $this->forms[$formId]->handleResponse($player, json_decode($pk->formData, true));
                } catch (FormValidationException $e) {
                    $event->getPlayer()->getServer()->getLogger()->critical("Failed to validate form " . get_class($this->forms[$formId]) . ": " . $e->getMessage());
                    $event->getPlayer()->getServer()->getLogger()->logException($e);
                } finally {
                    unset($this->forms[$formId]);
                }
            }
        }
    }

    private function onSettingsResponse(DataPacketReceiveEvent $event)
    {
        var_dump($event->getPacket());
    }
}