<?php

declare(strict_types=1);

namespace xenialdan\UserManager\event;

use pocketmine\event\Cancellable;
use xenialdan\UserManager\models\UserSettings;
use xenialdan\UserManager\User;

class UserSettingsChangeEvent extends UserEvent implements Cancellable
{
    /**
     * @var UserSettings
     */
    private $new;
    /**
     * @var UserSettings
     */
    private $old;

    /**
     * UserSettingsChangeEvent constructor.
     * @param User $user
     * @param UserSettings $new
     */
    public function __construct(User $user, UserSettings $new)
    {
        parent::__construct($user);
        $this->new = $new;
        $this->old = $user->getSettings();//TODO $user->getSettings()
    }

    /**
     * @return UserSettings
     */
    public function getNew(): UserSettings
    {
        return $this->new;
    }

    /**
     * @return UserSettings
     */
    public function getOld(): UserSettings
    {
        return $this->old;
    }

    /**
     * @return array
     */
    public function getChanged(): array
    {
        return $this->new->compare($this->old);
    }

}