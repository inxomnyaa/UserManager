<?php

declare(strict_types=1);

namespace xenialdan\UserManager;

use InvalidStateException;
use pocketmine\lang\BaseLang;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use poggit\libasynql\SqlError;
use xenialdan\UserManager\commands\BlockCommand;
use xenialdan\UserManager\commands\FriendCommand;
use xenialdan\UserManager\commands\UnblockCommand;
use xenialdan\UserManager\commands\UserManagerCommand;
use xenialdan\UserManager\exceptions\LanguageException;
use xenialdan\UserManager\listener\BaseEventListener;
use xenialdan\UserManager\listener\ChatEventListener;
use xenialdan\UserManager\listener\SettingsListener;
use xenialdan\UserManager\models\Translations;

class Loader extends PluginBase
{
    /** @var Loader */
    private static $instance = null;
    /** @var DataConnector */
    private $database;
    /** @var Queries */
    public static $queries;
    /** @var UserStore */
    public static $userstore;
    /** @var string 3 letter */
    protected static $pluginLang = BaseLang::FALLBACK_LANGUAGE;

    /**
     * Returns an instance of the plugin
     * @return Loader
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    public static function getDataProvider(): DataConnector
    {
        return Loader::getInstance()->database;
    }

    public function onLoad()
    {
        self::$instance = $this;
        $this->saveDefaultConfig();
        $this->getServer()->getCommandMap()->registerAll("UserManager", [
            new UserManagerCommand("usermanager", "UserManager help", ["um"]),
            new FriendCommand("friend", "Open friend list or manage friends"),
            new BlockCommand("block", "Block users"),
            new UnblockCommand("unblock", "Unblock users"),
        ]);
    }

    /**
     * @throws LanguageException
     * @throws InvalidStateException
     * @throws PluginException
     * @throws SqlError
     */
    public function onEnable()
    {
        $this->saveDefaultConfig();
        $this->database = libasynql::create($this, $this->getConfig()->get("database"), [
            "sqlite" => "sqlite.sql",
            "mysql" => "mysql.sql"
        ]);
        //create tables
        self::$queries = new Queries();
        //User store
        self::$userstore = new UserStore();
        //events
        $this->getServer()->getPluginManager()->registerEvents(new BaseEventListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new SettingsListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new ChatEventListener(), $this);
        //translations
        Translations::init();
        $lang = (string)$this->getConfig()->get("language", BaseLang::FALLBACK_LANGUAGE);
        try {
            if (strlen($lang) !== 3) {
                throw new LanguageException("Selected plugin language {$lang} is invalid, the string must be 3 letters long.");
            }
            try {
                Translations::getLanguage($lang);
            } catch (LanguageException $e) {
                throw new LanguageException("Selected plugin language {$lang} is invalid: " . $e->getMessage());
            }
        } catch (LanguageException $exception) {
            $lang = BaseLang::FALLBACK_LANGUAGE;
            $this->getLogger()->warning($exception->getMessage());
            $this->getLogger()->warning("Resetting to default ($lang)");
            $this->getConfig()->set("language", $lang);
            $this->getConfig()->save();
        } finally {
            self::$pluginLang = $lang;
            $this->getLogger()->debug("Plugin language set to ".Translations::getLanguage()->getName());
            Translations::languageNeedsUpdate($lang);
        }
    }

    public function onDisable()
    {
        if (isset($this->database)) $this->database->close();
    }

    /**
     * @return string 3 letter
     */
    public function getPluginLanguage(): string
    {
        return self::$pluginLang;
    }

    /**
     * Returns the path to the language files folder.
     *
     * @return string
     */
    public function getLanguageFolder(): string
    {
        return $this->getFile() . "resources" . DIRECTORY_SEPARATOR . "lang" . DIRECTORY_SEPARATOR;
    }

    /**
     * Get a list of available languages
     * @return array
     */
    public function getLanguageList(): array
    {
        return BaseLang::getLanguageList($this->getLanguageFolder());
    }
}