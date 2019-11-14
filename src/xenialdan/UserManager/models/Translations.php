<?php

declare(strict_types=1);

namespace xenialdan\UserManager\models;

use pocketmine\lang\BaseLang;
use pocketmine\lang\TranslationContainer;
use xenialdan\UserManager\exceptions\LanguageException;
use xenialdan\UserManager\Loader;
use xenialdan\UserManager\User;

class Translations
{

    /** @var BaseLang[] */
    protected static $languages;

    public static function init(): void
    {
        foreach (Loader::getInstance()->getLanguageList() as $k => $item) {
            self::registerLanguage(new BaseLang($k, Loader::getInstance()->getLanguageFolder()));
        }
    }

    public static function registerLanguage(BaseLang $baseLang): void
    {
        self::$languages[$baseLang->getLang()] = $baseLang;
        Loader::getInstance()->getLogger()->debug("Loaded language {$baseLang->getName()}");
    }

    /**
     * @param string|null $shortName 3 letter format conform with iso639-2
     * @return BaseLang
     * @throws LanguageException
     * @see https://www.loc.gov/standards/iso639-2/php/English_list.php
     */
    public static function getLanguage(?string $shortName = null): BaseLang
    {
        $lang = self::$languages[strtolower($shortName ?? Loader::getInstance()->getPluginLanguage())] ?? null;
        if ($lang instanceof BaseLang) return $lang;
        throw new LanguageException("Language $shortName not found");
    }

    /**
     * @param string $entry
     * @param array $params
     * @param User|null $user
     * @return string
     * @throws LanguageException
     */
    public static function translate(string $entry, array $params = [], ?User $user = null): string
    {
        $language = self::getLanguage(Loader::getInstance()->getPluginLanguage());
        if ($user instanceof User && ($settings = $user->getSettings()) instanceof UserSettings) {
            $language = self::getLanguage($settings->u_language);
        }
        return $language->translate(new TranslationContainer($entry, $params));
    }

}