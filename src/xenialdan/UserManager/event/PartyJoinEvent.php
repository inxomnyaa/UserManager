<?php

declare(strict_types=1);

namespace xenialdan\UserManager\event;

use pocketmine\event\Cancellable;
use xenialdan\UserManager\models\Party;
use xenialdan\UserManager\User;

class PartyJoinEvent extends PartyEvent implements Cancellable
{
    const REASON_API = 0;
    const REASON_REQUEST_ACCEPTED = 1;
    const REASON_INVITE_ACCEPTED = 2;

    /** @var int */
    private $reason;

    public function __construct(Party $party, User $user, int $reason = self::REASON_API)
    {
        $this->reason = $reason;
        parent::__construct($party, $user);
    }

    /**
     * @return int
     */
    public function getReason(): int
    {
        return $this->reason;
    }

    /**
     * @param int $reason
     */
    public function setReason(int $reason = self::REASON_API): void
    {
        $this->reason = $reason;
    }
}