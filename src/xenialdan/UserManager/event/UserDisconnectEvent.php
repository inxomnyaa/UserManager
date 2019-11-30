<?php

declare(strict_types=1);

namespace xenialdan\UserManager\event;

use pocketmine\lang\TranslationContainer;
use xenialdan\UserManager\User;

class UserDisconnectEvent extends UserEvent
{
    /** @var TranslationContainer|string */
    protected $quitMessage;
    /** @var string */
    protected $quitReason;

    /**
     * UserDisconnectEvent constructor.
     * @param User $user
     * @param TranslationContainer|string $quitMessage
     * @param string $quitReason
     */
    public function __construct(User $user, $quitMessage, string $quitReason)
    {
        $this->quitMessage = $quitMessage;
        $this->quitReason = $quitReason;
        parent::__construct($user);
    }

    /**
     * @return TranslationContainer|string
     */
    public function getQuitMessage()
    {
        return $this->quitMessage;
    }

    /**
     * @return string
     */
    public function getQuitReason(): string
    {
        return $this->quitReason;
    }

}