<?php

declare(strict_types=1);

namespace xenialdan\UserManager;

class User
{

    private $id, $username, $ip, $flags = [];

    public function __construct($id = -1, string $username, string $ip, /*PermissionFlags*/
                                $flags)
    {
        $this->id = $id;
        $this->username = $username;
        $this->ip = $ip;
        $this->flags = $flags;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->getId();
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    public function getIP()
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp(string $ip)
    {
        $this->ip = $ip;
    }

    public function __toString(): string
    {
        $result = var_export(get_object_vars($this), true);
        return $result;
    }
}