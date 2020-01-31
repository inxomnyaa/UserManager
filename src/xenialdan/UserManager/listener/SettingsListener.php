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
use xenialdan\customui\elements\Input;
use xenialdan\customui\elements\Toggle;
use xenialdan\customui\windows\ServerForm;
use xenialdan\UserManager\event\UserJoinEvent;
use xenialdan\UserManager\event\UserSettingsChangeEvent;
use xenialdan\UserManager\exceptions\LanguageException;
use xenialdan\UserManager\Loader;
use xenialdan\UserManager\models\Translations;
use xenialdan\UserManager\models\UserSettings;
use xenialdan\UserManager\User;
use xenialdan\UserManager\UserStore;

class SettingsListener implements Listener
{
    /** @var Form[] */
    private $forms = [];
    /** @var int */
    private $formId = 1000;

    /**
     * @param DataPacketReceiveEvent $event
     * @throws LanguageException
     */
    public function onPacket(DataPacketReceiveEvent $event): void
    {
        if ($event->getPacket()->pid() === ServerSettingsRequestPacket::NETWORK_ID) {
            $event->setCancelled(!$this->onSettingsRequest($event));
        } else if ($event->getPacket()->pid() === ServerSettingsResponsePacket::NETWORK_ID) {
            $event->setCancelled(!$this->onSettingsResponse($event));
        } else if ($event->getPacket()->pid() === ModalFormResponsePacket::NETWORK_ID) {
            $event->setCancelled(!$this->onSettingsModalResponse($event));
        }
    }

    public function onJoin(UserJoinEvent $event): void
    {
        $user = $event->getUser();
        Loader::$queries->changeUserSettingsLanguage($user->getId(), $user->getPlayer()->getLocale(), function (int $affectedRows) use ($user): void {
            Loader::$queries->createUserSettings($user->getId(), $user->getPlayer()->getLocale(), function (int $insertId, int $affectedRows) use ($user): void {
                if ($affectedRows > 0) {
                    Loader::getInstance()->getLogger()->debug("Created entry $insertId in user_settings for user " . $user->getRealUsername());
                }
                Loader::$queries->getUserSettings($user->getId(), function (array $rows, array $columns) use ($user): void {
                    //TODO debug
                    $settings = new UserSettings($rows[0]);
                    var_dump("CREATE SETTINGS", $settings);
                    $user->setSettings($settings, false);
                });
            });
        });
    }

    /**
     * @param DataPacketReceiveEvent $event
     * @return bool
     * @throws LanguageException
     */
    private function onSettingsRequest(DataPacketReceiveEvent $event): bool
    {
        var_dump("Settings request for player " . $event->getPlayer()->getName());
        $player = $event->getPlayer();
        if (($user = UserStore::getUser($player)) instanceof User) {
            var_dump("Settings request for user " . $user);
            //TODO debug
            $settings = $user->getSettings();
            if ($settings instanceof UserSettings) {
                var_dump("Settings found: ", $settings);
                $form = new ServerForm(Translations::translate(Translations::SETTINGS_TITLE, [], $user));
                foreach ($settings->jsonSerialize() as $row => $value) {
                    [$type, $entry] = explode("_", $row, 2);
                    switch ($type) {
                        case UserSettings::PREFIX_STRING:
                        {
                            if (strpos($entry, "language") !== false) {
                                //hack TODO
                                break;
                            }
                            $form->addElement(new Input(Translations::translate("settings.$entry", [], $user), Translations::translate("settings.$entry", [], $user), $value));
                            break;
                        }
                        case UserSettings::PREFIX_BOOL:
                        {
                            $form->addElement(new Toggle(Translations::translate("settings.$entry", [], $user), $value == 1));
                            break;
                        }
                    }
                }
                $form->setCallable(function (Player $player, array $data) use ($user): void {
                    //TODO HACK push language in front. This can be removed when form rewrite adds indexes
                    array_unshift($data, $user->getSettings()->u_language);

                    $ev = new UserSettingsChangeEvent($user, new UserSettings($data));
                    $ev->call();
                    var_dump($ev->getOld(), $ev->getNew(), $ev->getChanged());
                    if (!$ev->isCancelled()) {
                        $user->setSettings($ev->getNew());
                        if (count($ev->getChanged()) > 0) {
                            $user->getPlayer()->sendMessage(Translations::translate("settings.changed", [], $user));
                            foreach ($ev->getChanged() as $key => $value) {
                                [$type, $entry] = explode("_", $key, 2);
                                $user->getPlayer()->sendMessage(Translations::translate("settings.$entry", [], $user) . " = " . (is_bool($value) ? ($value ? Translations::translate(Translations::YES, [], $user) : Translations::translate(Translations::NO, [], $user)) : $value));
                            }
                        }
                    }
                });

                //TODO hack
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

    /**
     * Clone of \pocketmine\Player::sendForm()
     * TODO HACK. remove when pmmp supports sending of ServerSettingsForm
     * @param DataPacketReceiveEvent $event
     * @return bool
     */
    private function onSettingsModalResponse(DataPacketReceiveEvent $event): bool
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
        return true;
    }

    private function onSettingsResponse(DataPacketReceiveEvent $event): bool
    {
        Loader::getInstance()->getLogger()->error($event->getPacket()->pid() . " " . $event->getPacket()->getName() . " received. This should not happen (sadly, because #blamemojang for using ModalForm as response). This error can be ignored.");
        var_dump($event->getPacket());
        return true;
    }
}