<?php

declare(strict_types=1);

namespace xenialdan\UserManager\event;

use pocketmine\event\Cancellable;

class PartyDeleteEvent extends PartyEvent implements Cancellable
{
}