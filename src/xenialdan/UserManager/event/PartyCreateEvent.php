<?php

declare(strict_types=1);

namespace xenialdan\UserManager\event;

use pocketmine\event\Cancellable;

class PartyCreateEvent extends PartyEvent implements Cancellable
{
}