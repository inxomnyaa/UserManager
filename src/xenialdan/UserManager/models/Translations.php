<?php

declare(strict_types=1);

namespace xenialdan\UserManager\models;

use pocketmine\lang\BaseLang;
use pocketmine\lang\TranslationContainer;
use ReflectionProperty;
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
            try {
                self::registerLanguage(new BaseLang($k, Loader::getInstance()->getLanguageFolder(), Loader::getInstance()->getPluginLanguage()));
            } catch (LanguageException $e) {
                Loader::getInstance()->getLogger()->logException($e);
                continue;
            }
        }
    }

    /**
     * @param BaseLang $baseLang
     * @throws LanguageException
     */
    public static function registerLanguage(BaseLang $baseLang): void
    {
        if (isset(self::$languages[$baseLang->getLang()])) throw new LanguageException("Language " . $baseLang->getLang() . " is already registered");
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
     * Checks against the english translation and dumps missing translation keys
     * @param string|null $shortName 3 letter format conform with iso639-2
     * @return bool Returns true when there are missing entries
     */
    public static function languageNeedsUpdate(string $shortName): bool
    {
        try {
            $shortName = strtolower($shortName);
            if ($shortName === strtolower(BaseLang::FALLBACK_LANGUAGE)) return false;
            $class = new BaseLang($shortName, Loader::getInstance()->getLanguageFolder());
            $fallback = new ReflectionProperty($class, "fallbackLang");
            $lang = new ReflectionProperty($class, "lang");
            $fallback->setAccessible(true);
            $lang->setAccessible(true);
            $diff = array_diff_key($fallback->getValue($class), $lang->getValue($class));
            if (count($diff) > 0) {
                Loader::getInstance()->getLogger()->notice("Language " . $class->getName() . " (" . $class->getLang() . ".ini) is missing following entries: '" . implode("','", $diff) . "'");
                if (!empty(Loader::getInstance()->getDescription()->getWebsite())) Loader::getInstance()->getLogger()->notice("If you'd like to complete the translation, feel free to submit changes at " . Loader::getInstance()->getDescription()->getWebsite());
                return true;
            }
        } catch (\Exception $exception) {
            Loader::getInstance()->getLogger()->logException($exception);
        }
        return false;
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
            $shortName = Loader::$localeMapping[$settings->u_language][0] ?? null;
            $language = self::getLanguage($shortName);
        }
        return $language->translate(new TranslationContainer($entry, $params));
    }

}