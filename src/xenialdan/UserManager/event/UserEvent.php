<?php

declare(strict_types=1);

namespace xenialdan\UserManager\event;

use pocketmine\event\Event;
use xenialdan\UserManager\User;

abstract class UserEvent extends Event
{
    /** @var User */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

}