<?php

declare(strict_types=1);

namespace xenialdan\UserManager;

use poggit\libasynql\SqlError;

class Queries
{
    //Create tables
    const INIT_TABLES_USERS = "usermanager.init.users";
    const INIT_TABLES_BANS = "usermanager.init.bans";
    const INIT_TABLES_WARNS = "usermanager.init.warns";
    const INIT_TABLES_AUTHCODE = "usermanager.init.authcode";
    const INIT_TABLES_RELATIONSHIP = "usermanager.init.relationship";
    const INIT_TABLES_MESSAGES = "usermanager.init.messages";
    const INIT_TABLES_USER_SETTINGS = "usermanager.init.user_settings";
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
    //report TODO
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
    const REMOVE_USER_RELATION = "usermanager.relationship.remove";
    const HAS_USER_RELATION = "usermanager.relationship.get";
    const CHECK_RELATION_STATE = "usermanager.relationship.check";
    const GET_FRIEND_LIST = "usermanager.relationship.friend.list";
    const GET_BLOCKED_LIST = "usermanager.relationship.blocked.list";
    const SHOW_FRIEND_REQUESTS = "usermanager.relationship.friend.pending";
    //messages
    const SEND_MESSAGE = "usermanager.messages.send";
    const SET_MESSAGE_STATUS = "usermanager.messages.status.message";
    const SET_MESSAGE_STATUS_SENT = "usermanager.messages.status.sender";
    const SET_MESSAGE_STATUS_RECEIVED = "usermanager.messages.status.receiver";
    const EDIT_MESSAGE = "usermanager.messages.edit";
    const DELETE_MESSAGE = "usermanager.messages.delete.message";
    const DELETE_MESSAGE_SENDER = "usermanager.messages.delete.sender";
    //user settings
    const CREATE_USER_SETTINGS = "usermanager.user_settings.create";
    const GET_USER_SETTINGS = "usermanager.user_settings.get";
    const SET_USER_SETTINGS = "usermanager.user_settings.set";
    //TODO exp, coins

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
        Loader::getDataProvider()->executeGeneric(self::INIT_TABLES_USER_SETTINGS);
    }

    /* GENERIC */

    /**
     * @param callable $function
     */
    public function getUserList(callable $function): void
    {
        Loader::getDataProvider()->executeSelect(self::GET_EVERY_USER_DATA, [], $function);
    }

    /**
     * @param string $playername
     * @param callable $function
     */
    public function getUserIdByName(string $playername, callable $function): void
    {
        print "getuserlist";
        Loader::getDataProvider()->executeSelect(self::GET_USER_ID_BY_NAME, [
            "username" => $playername,
        ], $function);
    }

    /**
     * @param string $playername
     * @param callable $function
     */
    public function getUser(string $playername, callable $function): void
    {
        print "getuser";
        Loader::getDataProvider()->executeSelect(self::GET_USER_DATA_BY_NAME, [
            "username" => $playername,
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
        #$user = new User($val["user_id"], $val["username"], $val["lastip"], []);//TODO IP -> use latest
    }

    /**
     * @param int $id
     * @param callable $function
     */
    public function getUserById(int $id, callable $function): void
    {
        print "getuserbyid";
        Loader::getDataProvider()->executeSelect(self::GET_USER_DATA_BY_ID, [
            "user_id" => $id,
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
        #$user = new User($val["user_id"], $val["username"], $val["lastip"], []);//TODO IP -> use latest
    }

    /**
     * @param User $user
     * @param callable $function
     */
    public function addUser(User $user, callable $function): void
    {
        print "adduser";
        print $user;
        Loader::getDataProvider()->executeInsert(self::ADD_USER, [//TODO check if insert
            "username" => $user->getUsername(),
            "lastuuid" => "",//TODO add
            "lastip" => $user->getIP(),
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }

    /**
     * @param User $user
     * @param callable $function
     */
    public function updateUserIP(User $user, callable $function): void
    {
        Loader::getDataProvider()->executeInsert(self::ADD_USER_UPDATE_IP, [
            "user_id" => $user->getId(),
            "lastip" => $user->getIP(),
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }

    /* AUTHORISATION */

    /**
     * @param User $user
     * @param callable $function
     */
    public function updateAuthCode(User $user, callable $function): void
    {
        $characters = "ABCDEFGHJKLMNPQRSTUVWXYZ123456789";
        $auth = strtoupper(substr(str_shuffle($characters), 5, 5));
        Loader::getDataProvider()->executeInsert(self::UPDATE_AUTH_CODE, [
            "user_id" => $user->getId(),
            "authcode" => $auth,
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }

    /**
     * @param User $user
     * @param string $auth
     * @param callable $function
     */
    public function checkAuthCode(User $user, string $auth, callable $function): void
    {
        Loader::getDataProvider()->executeSelect(self::CHECK_AUTH_CODE, [
            "user_id" => $user->getId(),
            "authcode" => $auth,
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }

    /* SOCIAL */

    /**
     * @param int $id_issuer
     * @param int $id_target
     * @param int $status
     * @param callable $function
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
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }

    /**
     * @param int $id_issuer
     * @param int $id_target
     * @param callable $function
     */
    public function removeUserRelation(int $id_issuer, int $id_target, callable $function): void
    {
        $user1 = $id_issuer < $id_target ? $id_issuer : $id_target;
        $user2 = $id_issuer < $id_target ? $id_target : $id_issuer;
        Loader::getDataProvider()->executeChange(self::REMOVE_USER_RELATION, [
            "user_one_id" => $user1,
            "user_two_id" => $user2,
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }

    /**
     * @param int $id_issuer
     * @param int $id_target
     * @param callable $function
     */
    public function hasUserRelation(int $id_issuer, int $id_target, callable $function): void
    {
        $user1 = $id_issuer < $id_target ? $id_issuer : $id_target;
        $user2 = $id_issuer < $id_target ? $id_target : $id_issuer;
        Loader::getDataProvider()->executeSelect(self::HAS_USER_RELATION, [
            "user_one_id" => $user1,
            "user_two_id" => $user2,
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }

    /**
     * @param int $id_issuer
     * @param int $id_target
     * @param int $status
     * @param callable $function
     */
    public function checkRelationState(int $id_issuer, int $id_target, int $status, callable $function): void
    {
        $user1 = $id_issuer < $id_target ? $id_issuer : $id_target;
        $user2 = $id_issuer < $id_target ? $id_target : $id_issuer;
        Loader::getDataProvider()->executeSelect(self::CHECK_RELATION_STATE, [
            "user_one_id" => $user1,
            "user_two_id" => $user2,
            "status" => $status,
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }

    /**
     * @param int $id_issuer
     * @param callable $function
     */
    public function getFriends(int $id_issuer, callable $function): void
    {
        Loader::getDataProvider()->executeSelect(self::GET_FRIEND_LIST, [
            "user_one_id" => $id_issuer,
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }

    /**
     * @param int $id_issuer
     * @param callable $function
     */
    public function getFriendRequests(int $id_issuer, callable $function): void
    {
        Loader::getDataProvider()->executeSelect(self::SHOW_FRIEND_REQUESTS, [
            "user_one_id" => $id_issuer,
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }

    /**
     * @param int $id_issuer
     * @param callable $function
     */
    public function getBlocked(int $id_issuer, callable $function): void
    {
        Loader::getDataProvider()->executeSelect(self::GET_BLOCKED_LIST, [
            "user_one_id" => $id_issuer,
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }

    /* MESSAGES */

    /**
     * @param int $id_issuer
     * @param int $id_target
     * @param string $message
     * @param callable $function
     */
    public function sendMessage(int $id_issuer, int $id_target, string $message, callable $function): void
    {
        $user1 = $id_issuer < $id_target ? $id_issuer : $id_target;
        $user2 = $id_issuer < $id_target ? $id_target : $id_issuer;
        Loader::getDataProvider()->executeInsert(self::SEND_MESSAGE, [
            "user_one_id" => $user1,
            "user_two_id" => $user2,
            "message" => $message,
            "sender_id" => $id_issuer,
            "created" => time(),
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }

    /**
     * @param int $message_id
     * @param int $status
     * @param callable $function
     */
    public function setMessageStatus(int $message_id, int $status, callable $function): void
    {
        Loader::getDataProvider()->executeChange(self::SET_MESSAGE_STATUS, [
            "status" => $status,
            "id" => $message_id,
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }

    /**
     * @param int $id_issuer
     * @param int $id_target
     * @param int $status
     * @param callable $function
     */
    public function setSenderMessageStatus(int $id_issuer, int $id_target, int $status, callable $function): void
    {
        $user1 = $id_issuer < $id_target ? $id_issuer : $id_target;
        Loader::getDataProvider()->executeChange(self::SET_MESSAGE_STATUS_SENT, [
            "status" => $status,
            "user_one_id" => $user1,
            "sender_id" => $id_issuer,
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }

    /**
     * @param int $id_issuer
     * @param int $id_target
     * @param int $status
     * @param callable $function
     */
    public function setReceiverMessageStatus(int $id_issuer, int $id_target, int $status, callable $function): void
    {
        $user1 = $id_issuer < $id_target ? $id_issuer : $id_target;
        Loader::getDataProvider()->executeChange(self::SET_MESSAGE_STATUS_RECEIVED, [
            "status" => $status,
            "user_one_id" => $user1,
            "sender_id" => $id_issuer,
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }

    /**
     * @param int $message_id
     * @param string $message
     * @param callable $function
     */
    public function editMessage(int $message_id, string $message, callable $function): void
    {
        Loader::getDataProvider()->executeChange(self::EDIT_MESSAGE, [
            "id" => $message_id,
            "message" => $message,
            "edited" => time(),
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }

    /**
     * @param int $message_id
     * @param callable $function
     */
    public function deleteMessage(int $message_id, callable $function): void
    {
        Loader::getDataProvider()->executeChange(self::DELETE_MESSAGE, [
            "id" => $message_id,
            "edited" => time(),
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }

    /**
     * @param int $id_issuer
     * @param int $id_target
     * @param callable $function
     */
    public function deleteSenderMessages(int $id_issuer, int $id_target, callable $function): void
    {
        $user1 = $id_issuer < $id_target ? $id_issuer : $id_target;
        Loader::getDataProvider()->executeChange(self::EDIT_MESSAGE, [
            "user_one_id" => $user1,
            "sender_id" => $id_issuer,
            "edited" => time(),
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }

    /* USER SETTINGS */

    /**
     * @param int $user_id
     * @param callable $function
     */
    public function createUserSettings(int $user_id, callable $function): void
    {
        Loader::getDataProvider()->executeInsert(self::CREATE_USER_SETTINGS, [
            "user_id" => $user_id
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }

    /**
     * @param int $user_id
     * @param callable $function
     */
    public function getUserSettings(int $user_id, callable $function): void
    {
        Loader::getDataProvider()->executeSelect(self::GET_USER_SETTINGS, [
            "user_id" => $user_id
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }

    /**
     * @param int $user_id
     * @param string $u_language
     * @param string $u_nickname
     * @param string $u_profile_message
     * @param bool $t_allow_user_find
     * @param bool $t_allow_friend_request
     * @param bool $t_allow_message
     * @param bool $t_allow_online_status
     * @param callable $function
     */
    public function changeUserSettings(int $user_id, string $u_language, string $u_nickname, string $u_profile_message,
                                       bool $t_allow_user_find, bool $t_allow_friend_request, bool $t_allow_message,
                                       bool $t_allow_online_status, callable $function): void
    {
        Loader::getDataProvider()->executeChange(self::SET_USER_SETTINGS, [
            "user_id" => $user_id,
            "u_language" => $u_language,
            "u_nickname" => $u_nickname,
            "u_profile_message" => $u_profile_message,
            "t_allow_user_find" => $t_allow_user_find,
            "t_allow_friend_request" => $t_allow_friend_request,
            "t_allow_message" => $t_allow_message,
            "t_allow_online_status" => $t_allow_online_status
        ], $function, function (SqlError $error) {
            var_dump($error);
        });
    }
}