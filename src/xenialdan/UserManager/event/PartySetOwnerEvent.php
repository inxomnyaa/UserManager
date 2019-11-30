<?php

declare(strict_types=1);

namespace xenialdan\UserManager\event;

use pocketmine\event\Cancellable;
use xenialdan\UserManager\User;

class PartySetOwnerEvent extends PartyEvent implements Cancellable
{

    /**
     * @return User
     */
    public function getOwner(): User
    {
        return $this->getParty()->getOwner();
    }

    /**
     * @return User
     */
    public function getNewOwner(): User
    {
        return $this->getUser();
    }

    /**
     * @param User $newOwner
     */
    public function setNewOwner(User $newOwner): void
    {
        $this->setUser($newOwner);
    }

    /**
     * Note: we don't check if the owner is online because
     * he might have caused the event by disconnecting
     * @return bool
     */
    public function isValid(): bool
    {
        return !$this->getOwner()->equals($this->getNewOwner()) && $this->getNewOwner()->isOnline();
    }
}