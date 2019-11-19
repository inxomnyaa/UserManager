<?php

declare(strict_types=1);

namespace xenialdan\UserManager\models;

use JsonSerializable;

class Ban implements JsonSerializable
{
    const TYPE_NAME = "n";
    const TYPE_UUID = "u";
    const TYPE_IP = "i";
    const TYPE_XUID = "x";

    /** @var int */
    protected $user_id;
    /** @var int */
    public $since;
    /** @var int */
    public $until;
    /** @var bool */
    public $expires;
    /** @var string */
    public $reason = "";
    /** @var string */
    public $types;

    /**
     * Ban constructor.
     * @param int $user_id
     * @param int $since
     * @param int $until
     * @param bool $expires
     * @param string $reason
     * @param string $types
     */
    public function __construct(int $user_id, int $since, int $until, bool $expires, string $reason, string $types)
    {
        $this->user_id = $user_id;
        $this->since = $since;
        $this->until = $until;
        $this->expires = $expires;
        $this->reason = $reason;
        $this->types = $types;
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

    public function isTypeBanned(string $type): bool
    {
        //TODO validate type
        return strpos($this->types, $type) !== false;
    }

    public function hasExpired(): bool
    {
        return $this->expires && time() >= $this->until;
    }

    /**
     * @return int
     */
    public function getSince(): int
    {
        return $this->since;
    }

    /**
     * @return int
     */
    public function getUntil(): int
    {
        return $this->until;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function __toString(): string
    {
        $result = var_export(get_object_vars($this), true);
        return $result;
    }
}