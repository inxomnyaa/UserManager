<?php

declare(strict_types=1);

namespace xenialdan\UserManager\models;

use Ds\Map;
use pocketmine\utils\TextFormat;
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
    /**
     * timestamp => User
     * @var Map
     */
    private $invites;
    /**
     * timestamp => User
     * @var Map
     */
    private $requests;
    /** @var int */
    private $ownerId;
    /** @var string */
    public $name = "Party";

    public function __construct(User $owner, User ...$members)
    {
        $this->members = new Map();
        $this->invites = new Map();
        $this->requests = new Map();
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

    public function removeMember(User $user): void
    {
        if ($this->members->hasKey($user->getId())) $this->members->remove($user->getId());
        if ($this->members->isEmpty()) {
            Party::removeParty($this);
            return;
        }

        if ($this->isOwner($user)) {//Set new owner
            $this->setOwnerId((int)$this->members->keys()->first());
            $this->getOwner()->getPlayer()->sendMessage(TextFormat::AQUA . "The party leader left. You are now the party leader!");
            foreach ($this->getMembers() as $member) {
                $member->getPlayer()->sendMessage(TextFormat::GOLD . "The party leader left. " . $this->getOwner()->getDisplayName() . " is now the party leader!");
            }
        }
    }

    public function getMemberById(int $id): ?User
    {
        return $this->members->get($id, null);
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

    /**
     * @param User $user
     * @return bool
     */
    public function isOwner(User $user): bool
    {
        return $user->getId() === $this->getOwnerId();
    }

    public function inviteMember(User $user): void
    {
        $this->invites->put(time(), $user);
        $this->cleanupExpired();
    }

    public function isInvited(User $user): bool
    {
        $this->cleanupExpired();
        $filter = $this->invites->filter(function ($key, User $userInvited) use ($user): bool {
            return $userInvited->getId() === $user->getId();
        });
        return !$filter->isEmpty();
    }

    public function acceptInvite(User $user): void
    {
        $filter = $this->invites->filter(function ($key, User $userInvited) use ($user): bool {
            return $userInvited->getId() === $user->getId();
        });
        if ($filter->isEmpty()) return;
        $this->invites->remove($filter->keys()->first());
        $this->addMember($filter->values()->first());
        $this->cleanupExpired();
    }

    public function denyInvite(User $user): void
    {
        $filter = $this->invites->filter(function ($key, User $userInvited) use ($user): bool {
            return $userInvited->getId() === $user->getId();
        });
        if ($filter->isEmpty()) return;
        $this->invites->remove($filter->keys()->first());
        $this->cleanupExpired();
    }

    /**
     * @param User $user
     * @return Party[]
     */
    public static function getInvitedParties(User $user): array
    {
        $parties = [];
        foreach (self::$parties as $party) {
            if ($party->isInvited($user)) $parties[] = $party;
        }
        return $parties;
    }

    /**
     * @param User $user
     * @return Party[]
     */
    public static function getRequestedParties(User $user): array
    {
        $parties = [];
        foreach (self::$parties as $party) {
            if ($party->isRequested($user)) $parties[] = $party;
        }
        return $parties;
    }

    /**
     * @return User[]
     */
    public function getInvites(): array
    {
        return $this->invites->toArray();
    }

    /**
     * @return User[]
     */
    public function getRequests(): array
    {
        return $this->requests->toArray();
    }

    public function requestMember(User $user): void
    {
        $this->requests->put(time(), $user);
        $this->cleanupExpired();
    }

    public function isRequested(User $user): bool
    {
        $this->cleanupExpired();
        $filter = $this->requests->filter(function ($key, User $userRequestd) use ($user): bool {
            return $userRequestd->getId() === $user->getId();
        });
        return !$filter->isEmpty();
    }

    public function acceptRequest(User $user): void
    {
        $filter = $this->requests->filter(function ($key, User $userRequestd) use ($user): bool {
            return $userRequestd->getId() === $user->getId();
        });
        if ($filter->isEmpty()) return;
        $this->requests->remove($filter->keys()->first());
        $this->addMember($filter->values()->first());
        $this->cleanupExpired();
    }

    public function denyRequest(User $user): void
    {
        $filter = $this->requests->filter(function ($key, User $userRequestd) use ($user): bool {
            return $userRequestd->getId() === $user->getId();
        });
        if ($filter->isEmpty()) return;
        $this->requests->remove($filter->keys()->first());
        $this->cleanupExpired();
    }

    private function cleanupExpired(): void
    {
        foreach ($this->invites->keys() as $key) {
            if ($key > strtotime("1 minute"))//TODO config for expiration time
                $this->invites->remove($key);
        }
        foreach ($this->requests->keys() as $key) {
            if ($key > strtotime("1 minute"))//TODO config for expiration time
                $this->requests->remove($key);
        }
    }

    public function __toString()
    {
        return "Party Owner: " . ($this->getOwner() ? $this->getOwner()->getRealUsername() : $this->getOwnerId()) . " Members [" . count($this->getMembers()) . "]: " . join(", ", array_map(function (User $user): string {
                return $user->getRealUsername();
            }, $this->getMembers()));
    }

}