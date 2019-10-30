<?php

declare(strict_types=1);

namespace xenialdan\UserManager;

use pocketmine\plugin\PluginBase;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use xenialdan\UserManager\commands\BlockCommand;
use xenialdan\UserManager\commands\FriendCommand;
use xenialdan\UserManager\commands\UnblockCommand;
use xenialdan\UserManager\commands\UserManagerCommand;
use xenialdan\UserManager\listener\BaseEventListener;

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
        #$this->database->executeGeneric(Queries::INITIALIZE_TABLES_PLAYER);
    }

    public function onDisable()
    {
        if (isset($this->database)) $this->database->close();
    }
}