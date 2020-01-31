<?php

declare(strict_types=1);

namespace xenialdan\UserManager\event;

use pocketmine\event\Cancellable;
use pocketmine\Player;
use xenialdan\UserManager\User;

/**
 * Class UserLoginEvent
 * Called after the player has successfully authenticated, before it spawns
 *
 * @package xenialdan\UserManager\event
 */
class UserLoginEvent extends UserEvent implements Cancellable
{
    private $player;
    /** @var string */
    protected $kickMessage = "";

    public function __construct(User $user, Player $player)
    {
        $this->player = $player;
        parent::__construct($user);
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @param string $kickMessage
     */
    public function setKickMessage(string $kickMessage): void
    {
        $this->kickMessage = $kickMessage;
    }

    /**
     * @return string
     */
    public function getKickMessage(): string
    {
        return $this->kickMessage;
    }
}