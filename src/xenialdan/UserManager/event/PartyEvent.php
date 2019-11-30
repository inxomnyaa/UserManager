<?php

declare(strict_types=1);

namespace xenialdan\UserManager\event;

use pocketmine\event\Event;
use xenialdan\UserManager\models\Party;
use xenialdan\UserManager\User;

abstract class PartyEvent extends Event
{
    /** @var Party */
    private $party;
    /** @var User */
    private $user;

    public function __construct(Party $party, User $user)
    {
        $this->party = $party;
        $this->user = $user;
    }

    /**
     * @return Party
     */
    public function getParty(): Party
    {
        return $this->party;
    }

    /**
     * @param Party $party
     */
    public function setParty(Party $party): void
    {
        $this->party = $party;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getOwner(): User
    {
        return $this->getParty()->getOwner();
    }

    /**
     * @return User[]
     */
    public function getMembers(): array
    {
        return $this->getParty()->getMembers();
    }

    /**
     * @return string
     */
    public function getNewName(): string
    {
        return $this->getParty()->getName();
    }

}