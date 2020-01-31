<?php

declare(strict_types=1);

namespace xenialdan\UserManager\event;

use pocketmine\event\Cancellable;
use xenialdan\UserManager\models\Ban;
use xenialdan\UserManager\User;

class UserBanEvent extends UserEvent implements Cancellable
{
    /**
     * @var Ban
     */
    private $ban;

    /**
     * UserSettingsChangeEvent constructor.
     * @param User $user
     * @param Ban $ban
     */
    public function __construct(User $user, Ban $ban)
    {
        parent::__construct($user);
        $this->ban = $ban;
    }

    /**
     * @return Ban
     */
    public function getBan(): Ban
    {
        return $this->ban;
    }

}