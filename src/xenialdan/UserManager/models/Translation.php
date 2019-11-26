<?php

declare(strict_types=1);

namespace xenialdan\UserManager\models;

interface Translation
{
    public const YES = "g.yes";
    public const NO = "g.no";
    public const SETTINGS_TITLE = "settings.title";
    public const SETTINGS_LANGUAGE = "settings.language";
    public const SETTINGS_NICKNAME = "settings.nickname";
    public const SETTINGS_PROFILE_MESSAGE = "settings.profile_message";
    public const SETTINGS_ALLOW_USER_FIND = "settings.allow_user_find";
    public const SETTINGS_ALLOW_FRIEND_REQUEST = "settings.allow_friend_request";
    public const SETTINGS_ALLOW_MESSAGE = "settings.allow_message";
    public const SETTINGS_ALLOW_ONLINE_STATUS = "settings.allow_online_status";
    public const SETTINGS_CHANGED = "settings.changed";

}