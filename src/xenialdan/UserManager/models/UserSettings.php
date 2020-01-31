<?php

declare(strict_types=1);

namespace xenialdan\UserManager\models;

use JsonSerializable;
use ReflectionClass;
use ReflectionProperty;

class UserSettings implements JsonSerializable
{
    const PREFIX_STRING = "u";
    const PREFIX_BOOL = "t";

    /** @var string */
    public $u_language = "en_US";
    /** @var string */
    public $u_nickname = "";
    /** @var string */
    public $u_profile_message = "";
    /** @var bool */
    public $t_allow_user_find = true;
    /** @var bool */
    public $t_allow_friend_request = true;
    /** @var bool */
    public $t_allow_message = true;
    /** @var bool */
    public $t_allow_online_status = true;

    public function __construct(array $data = [])
    {
        if (empty($data)) return;
        /** @var ReflectionClass<UserSettings> $reflectionClass */
        $reflectionClass = new ReflectionClass(UserSettings::class);
        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $key => $property) {
            if (isset($data[$key]) || isset($data[$property->getName()])) {
                $var = $data[$key] ?? $data[$property->getName()];
                if (is_bool($this->{$property->getName()})) $var = boolval($var);
                $this->{$property->getName()} = $var;
            }
        }
    }

    public function compare(UserSettings $settings): array
    {
        return array_diff_assoc($this->jsonSerialize(), $settings->jsonSerialize());
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return (array)$this;
    }
}