<?php

declare(strict_types=1);

namespace xenialdan\UserManager;

class Queries
{
    //Create tables
    const INIT_TABLES_USERS = "usermanager.init.users";
    const INIT_TABLES_BANS = "usermanager.init.bans";
    const INIT_TABLES_WARNS = "usermanager.init.warns";
    const INIT_TABLES_AUTHCODE = "usermanager.init.authcode";
    const INIT_TABLES_RELATIONSHIP = "usermanager.init.relationship";
    const INIT_TABLES_MESSAGES = "usermanager.init.messages";
    //ban TODO
    const GET_BAN = "usermanager.ban.get";
    const ADD_BAN = "usermanager.ban.add";
    const UPDATE_BAN = "usermanager.ban.update";
    const DELETE_BAN = "usermanager.ban.delete";
    //warn TODO
    const GET_WARN = "usermanager.warn.get";
    const ADD_WARN = "usermanager.warn.add";
    const UPDATE_WARN = "usermanager.warn.update";
    const DELETE_WARN = "usermanager.warn.delete";
    //user get
    const GET_USER_ID_BY_NAME = "usermanager.user.get.idbyname";
    const GET_USER_DATA_BY_ID = "usermanager.user.data.get.byid";
    const GET_USER_DATA_BY_NAME = "usermanager.user.data.get.byname";
    const GET_EVERY_USER_DATA = "usermanager.user.data.get.all";
    //user add / update
    const ADD_USER = "usermanager.user.add.new";
    const ADD_USER_UPDATE_IP = "usermanager.user.add.ip";
    //authcode
    const UPDATE_AUTH_CODE = "usermanager.authcode.update";
    const CHECK_AUTH_CODE = "usermanager.authcode.check";
    //relation
    const SET_USER_RELATION = "usermanager.relationship.set";
    const HAS_USER_RELATION = "usermanager.relationship.get";
    const CHECK_RELATION_STATE = "usermanager.relationship.check";
    const GET_FRIEND_LIST = "usermanager.relationship.friend.list";
    const SHOW_FRIEND_REQUESTS = "usermanager.relationship.friend.pending";

    /**
     * Queries constructor.
     */
    public function __construct()
    {
        Loader::getDataProvider()->executeGeneric(self::INIT_TABLES_USERS);
        Loader::getDataProvider()->executeGeneric(self::INIT_TABLES_BANS);
        Loader::getDataProvider()->executeGeneric(self::INIT_TABLES_WARNS);
        Loader::getDataProvider()->executeGeneric(self::INIT_TABLES_AUTHCODE);
        Loader::getDataProvider()->executeGeneric(self::INIT_TABLES_RELATIONSHIP);
        Loader::getDataProvider()->executeGeneric(self::INIT_TABLES_MESSAGES);
    }

    public function getUserList(callable $function): void
    {
        //TODO use function
        Loader::getDataProvider()->executeSelect(self::GET_EVERY_USER_DATA, [], function (array $rows) {
            var_dump($rows);
        });
    }

    /**
     * @param $playername
     */
    public function getUserIdByName(string $playername, callable $function): void
    {
        Loader::getDataProvider()->executeSelect(self::GET_USER_ID_BY_NAME, [
            "username" => $playername,
        ], function (array $rows) {
            var_dump($rows);
        });
    }

    /**
     * @param string $playername
     */
    public function getUser(string $playername, callable $function): void
    {
        Loader::getDataProvider()->executeSelect(self::GET_USER_DATA_BY_NAME, [
            "username" => $playername,
        ], function (array $rows) {
            var_dump($rows);
        });
        #$user = new User($val["user_id"], $val["username"], $val["lastip"], []);//TODO IP -> use latest
    }

    /**
     * @param int $id
     */
    public function getUserById(int $id, callable $function): void
    {
        Loader::getDataProvider()->executeSelect(self::GET_USER_DATA_BY_ID, [
            "user_id" => $id,
        ], function (array $rows) {
            var_dump($rows);
        });
        #$user = new User($val["user_id"], $val["username"], $val["lastip"], []);//TODO IP -> use latest
    }

    public function addUser(User $user, callable $function): void
    {
        Loader::getDataProvider()->executeInsert(self::ADD_USER, [
            "username" => $user->getUsername(),
            "lastuuid" => "",//TODO add
            "lastip" => $user->getIP(),
        ], function (int $insertId, int $affectedRows) {
            var_dump($insertId, $affectedRows);
        });
    }

    public function updateUserIP(User $user, callable $function): void
    {
        Loader::getDataProvider()->executeInsert(self::ADD_USER_UPDATE_IP, [
            "user_id" => $user->getId(),
            "lastip" => $user->getIP(),
        ], function (int $insertId, int $affectedRows) {
            var_dump($insertId, $affectedRows);
        });
    }

    /* AUTHORISATION */

    public function updateAuthCode(User $user, callable $function): void
    {
        $characters = "ABCDEFGHJKLMNPQRSTUVWXYZ123456789";
        $auth = strtoupper(substr(str_shuffle($characters), 5, 5));
        Loader::getDataProvider()->executeInsert(self::UPDATE_AUTH_CODE, [
            "user_id" => $user->getId(),
            "authcode" => $auth,
        ], function (int $insertId, int $affectedRows) {
            var_dump($insertId, $affectedRows);
        });
    }

    public function checkAuthCode(User $user, string $auth, callable $function): void
    {
        Loader::getDataProvider()->executeSelect(self::CHECK_AUTH_CODE, [
            "user_id" => $user->getId(),
            "authcode" => $auth,
        ], function (array $rows) {
            var_dump($rows);
        });
    }

    /* SOCIAL */
    /**
     * @param $id_issuer
     * @param $id_target
     * @param int $status
     */
    public function setUserRelation(int $id_issuer, int $id_target, $status = API::FRIEND_PENDING, callable $function): void
    {
        $user1 = $id_issuer < $id_target ? $id_issuer : $id_target;
        $user2 = $id_issuer < $id_target ? $id_target : $id_issuer;
        Loader::getDataProvider()->executeInsert(self::SET_USER_RELATION, [
            "user_one_id" => $user1,
            "user_two_id" => $user2,
            "status" => $status,
            "action_user_id" => $id_issuer,
        ], function (int $insertId, int $affectedRows) {
            var_dump($insertId, $affectedRows);
        });
    }

    /**
     * @param $id_issuer
     * @param $id_target
     */
    public function hasUserRelation(int $id_issuer, int $id_target, callable $function): void
    {
        $user1 = $id_issuer < $id_target ? $id_issuer : $id_target;
        $user2 = $id_issuer < $id_target ? $id_target : $id_issuer;
        Loader::getDataProvider()->executeSelect(self::HAS_USER_RELATION, [
            "user_one_id" => $user1,
            "user_two_id" => $user2,
        ], function (array $rows) {
            var_dump($rows);
        });
    }

    /**
     * @param int $id_issuer
     * @param int $id_target
     * @param int $status
     */
    public function checkRelationState(int $id_issuer, int $id_target, int $status, callable $function): void
    {
        $user1 = $id_issuer < $id_target ? $id_issuer : $id_target;
        $user2 = $id_issuer < $id_target ? $id_target : $id_issuer;
        Loader::getDataProvider()->executeSelect(self::CHECK_RELATION_STATE, [
            "user_one_id" => $user1,
            "user_two_id" => $user2,
            "status" => $status,
        ], function (array $rows) {
            var_dump($rows);
        });
    }

    public function getFriends(int $id_issuer, callable $function): void
    {
        Loader::getDataProvider()->executeSelect(self::GET_FRIEND_LIST, [
            "user_one_id" => $id_issuer,
        ], function (array $rows) {
            var_dump($rows);
        });
        /*
            if ($val['user_one_id'] !== $id_issuer)
                $friends[] = API::getUserByID($val['user_one_id']);
            if ($val['user_two_id'] !== $id_issuer)
                $friends[] = API::getUserByID($val['user_two_id']);
            #$friends[] = new User($val["user_id"], Server::getInstance()->getPlayer($val["username"])??Server::getInstance()->getOfflinePlayer($val["username"]), $val["lastuuid"], $val["lastip"]);
        }*/
    }

    public function getFriendRequests(int $id_issuer, callable $function): void
    {
        Loader::getDataProvider()->executeSelect(self::SHOW_FRIEND_REQUESTS, [
            "user_one_id" => $id_issuer,
        ], function (array $rows) {
            var_dump($rows);
        });

        #$friends[] = API::getUserByID($val['action_user_id']);
    }
}