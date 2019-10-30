<?php

declare(strict_types=1);

namespace xenialdan\UserManager;

use pocketmine\plugin\PluginBase;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use xenialdan\UserManager\commands\UserManagerCommand;

class Loader extends PluginBase
{
    /** @var Loader */
    private static $instance = null;
    /** @var DataConnector */
    private $database;
    /**
     * @var Queries
     */
    private $queries;

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
            new UserManagerCommand("usermanager", "UserManager help"),
        ]);
    }

    public function onEnable()
    {
        $this->saveDefaultConfig();
        $this->database = libasynql::create($this, $this->getConfig()->get("database"), [
            "sqlite" => "sqlite.sql",
            "mysql" => "mysql.sql"
        ]);
        $this->queries = new Queries();
        #$this->database->executeGeneric(Queries::INITIALIZE_TABLES_PLAYER);
    }

    public function onDisable()
    {
        if (isset($this->database)) $this->database->close();
    }
}