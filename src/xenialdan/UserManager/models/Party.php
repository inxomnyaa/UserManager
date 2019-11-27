<?php

declare(strict_types=1);

namespace xenialdan\UserManager\models;

use Ds\Map;
use xenialdan\UserManager\Loader;
use xenialdan\UserManager\User;
use xenialdan\UserManager\UserStore;

/**
 * Can be used to create parties
 * Holds current parties
 */
class Party
{
    /** @var Party[] */
    private static $parties = [];

    public static function init(): void
    {

    }

    public static function getParty(User $user): ?Party
    {
        foreach (self::$parties as $party) {
            if ($party->isMember($user)) return $party;
        }
        return null;
    }

    public static function addParty(Party $party): void
    {
        self::$parties[$party->getOwnerId()] = $party;
        Loader::getInstance()->getLogger()->debug("Added party $party");
    }

    public static function removeParty(Party $party): void
    {
        unset(self::$parties[$party->getOwnerId()]);
        Loader::getInstance()->getLogger()->debug("Removed party $party");
    }

    /**
     * userId => User
     * @var Map
     */
    private $members;
    /** @var int */
    private $ownerId;
    /** @var string */
    public $name = "Party";

    public function __construct(User $owner, User ...$members)
    {
        $this->members = new Map();
        $this->ownerId = $owner->getId();
        $this->addMember($owner);
        foreach ($members as $member) {
            $this->addMember($member);
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Party
     */
    public function setName(string $name): Party
    {
        $this->name = $name;
        return $this;
    }

    public function addMember(User $user): void
    {
        $this->members->put($user->getId(), $user);
    }

    public function getMember(User $user): void
    {
        $this->members->put($user->getId(), $user);
    }

    /**
     * @return User[]
     */
    public function getMembers(): array
    {
        return $this->members->toArray();
    }

    public function isMember(User $user): bool
    {
        return $this->members->hasKey($user->getId());
    }

    /**
     * @return int
     */
    public function getOwnerId(): int
    {
        return $this->ownerId;
    }

    /**
     * @param int $ownerId
     * @return Party
     */
    public function setOwnerId(int $ownerId): Party
    {
        $this->ownerId = $ownerId;
        return $this;
    }

    /**
     * @return null|User
     */
    public function getOwner(): ?User
    {
        return UserStore::getUserById($this->ownerId);
    }

    public function __toString()
    {
        return "Party Owner: " . ($this->getOwner() ? $this->getOwner()->getRealUsername() : $this->getOwnerId()) . " Members [" . count($this->getMembers()) . "]: " . join(", ", array_map(function (User $user): string {
                return $user->getRealUsername();
            }, $this->getMembers()));
    }

}