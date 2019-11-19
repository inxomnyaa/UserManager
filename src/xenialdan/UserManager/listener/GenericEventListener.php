<?php

namespace xenialdan\UserManager\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\utils\TextFormat;
use ReflectionException;
use RuntimeException;
use xenialdan\UserManager\event\UserLoginEvent;
use xenialdan\UserManager\Loader;
use xenialdan\UserManager\models\Ban;
use xenialdan\UserManager\User;

class GenericEventListener implements Listener
{

    /**
     * @param DataPacketReceiveEvent $event
     */
    public function onPacket(DataPacketReceiveEvent $event)
    {
        if ($event->getPacket()->pid() === LoginPacket::NETWORK_ID) {
            $this->onLoginPacket($event);
        }
    }

    /**
     * TODO handle ban
     * @param DataPacketReceiveEvent $event
     */
    private function onLoginPacket(DataPacketReceiveEvent $event)
    {
        /** @var LoginPacket $pk */
        $pk = $event->getPacket();
        var_dump($pk->clientData, $pk->locale, $pk->username, $pk->protocol, $pk->clientUUID, $pk->clientId, $pk->xuid, $pk->identityPublicKey, $pk->serverAddress);
    }

    /**
     * TODO handle ban
     * @param PlayerPreLoginEvent $event
     */
    public function onConnect(PlayerPreLoginEvent $event)
    {
        $player = $event->getPlayer();
        if (!($user = Loader::$userstore::getUser($player)) instanceof User) {
            Loader::$queries->getUser($player->getLowerCaseName(), function (array $rows) use ($player): void {
                if (empty($rows)) {
                    Loader::$userstore::createNewUser($player->getLowerCaseName(), $player->getAddress(), []);
                } else {
                    Loader::$userstore::createUser($rows[0]["user_id"], $rows[0]["username"], $player->getAddress());
                }
            });
        } else {
            $name = $user->getSettings()->u_nickname;
            if (!empty(trim(TextFormat::clean($name)))) $player->setDisplayName($name);//TODO CRITICAL: check if settings already properly init
            /* TODO HANDLE BAN & WARN CHECKS HERE */
            var_dump($player->getLocale());
            var_dump($player->getUniqueId());
            var_dump($player->getName());
            var_dump($player->getDisplayName());
            var_dump($player->getAddress());
            $ban = Loader::$banstore::getBanById($user->getId());
            if ($ban instanceof Ban) {
                $msg = TextFormat::DARK_RED . TextFormat::BOLD . "You are banned!" . TextFormat::EOL . $ban->reason;
                $debug = "Banned user tried to log in:" . TextFormat::EOL . $ban;
                $kick = false;
                if ($ban->isTypeBanned(Ban::TYPE_IP) && $user->getIP() === $player->getAddress()) {
                    $kick = true;
                }
                if ($ban->isTypeBanned(Ban::TYPE_NAME) && strtolower($user->getUsername()) === $player->getLowerCaseName()) {
                    $kick = true;
                }
                //TODO UUID, XUID
                /*if($ban->isTypeBanned(Ban::TYPE_UUID) && $player->getUniqueId() === $ban->){
                    $kick = true;
                }*/
                if ($kick) {
                    //TODO check why kick message does not appear + stuck in loading resources
                    Loader::getInstance()->getLogger()->debug($debug);
                    $event->setKickMessage($msg);
                    $event->setCancelled();
                    $player->kick($msg, false);
                }
            }
        }
    }

    /**
     * @param PlayerJoinEvent $event
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function onJoin(PlayerJoinEvent $event): void
    {
        var_dump(__METHOD__);
        if (($user = Loader::$userstore::getUser($event->getPlayer())) instanceof User) {
            var_dump($user);
            $ev = new UserLoginEvent($user);
            $ev->call();
        }
    }
}