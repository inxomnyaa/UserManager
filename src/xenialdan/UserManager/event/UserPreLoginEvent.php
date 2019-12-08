<?php

declare(strict_types=1);

namespace xenialdan\UserManager\event;

use pocketmine\event\Cancellable;

/**
 * Class UserPreLoginEvent
 * Called when a player connects to the server
 *
 * @package xenialdan\UserManager\event
 */
class UserPreLoginEvent extends UserEvent implements Cancellable
{
}