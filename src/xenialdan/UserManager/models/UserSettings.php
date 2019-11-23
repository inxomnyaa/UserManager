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
        /** @var ReflectionClass $reflectionClass */
        $reflectionClass = new ReflectionClass(UserSettings::class);
        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $key => $property) {
            $this->{$property->getName()} = $data[$key] ?? $data[$property->getName()];
        }
    }

    public function compare(UserSettings $settings): array
    {
        $other = $settings->jsonSerialize();
        return array_filter($this->jsonSerialize(), function ($value, $key) use ($other): bool {
            #var_dump($value,$key,$other[$key]??null);
            if ($value === null || $other[$key] ?? null === null) return false;
            return $other[$key] !== $value;
        }, ARRAY_FILTER_USE_BOTH);
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