<?php

declare(strict_types=1);

namespace xenialdan\UserManager;

use pocketmine\lang\BaseLang;

class Translations
{

    /**
     * @var BaseLang
     */
    public static $pluginLang;

    public function __construct(string $pluginLang)
    {
        self::$pluginLang = new BaseLang($pluginLang);//TODO
    }

}