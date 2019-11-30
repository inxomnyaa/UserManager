<?php

declare(strict_types=1);

namespace xenialdan\UserManager\event;

use pocketmine\event\Cancellable;
use xenialdan\UserManager\models\Party;
use xenialdan\UserManager\User;

class PartyRenameEvent extends PartyEvent implements Cancellable
{
    /** @var string */
    private $name;

    public function __construct(Party $party, User $user, string $name)
    {
        $this->name = $name;
        parent::__construct($party, $user);
    }

    /**
     * @return string
     */
    public function getNewName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setNewName(string $name = "Party"): void
    {
        $this->name = $name;
    }

    public function isValid(): bool
    {
        return User::isValidUserName($this->getNewName());
    }
}