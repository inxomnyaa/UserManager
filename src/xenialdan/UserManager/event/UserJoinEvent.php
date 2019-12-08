<?php

declare(strict_types=1);

namespace xenialdan\UserManager\event;

use pocketmine\event\Cancellable;

/**
 * Class UserJoinEvent
 * Called when the player spawns in the world after logging in
 *
 * @package xenialdan\UserManager\event
 */
class UserJoinEvent extends UserEvent implements Cancellable
{
}