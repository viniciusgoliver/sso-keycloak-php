<?php
/**
 * Created by PhpStorm.
 * User: lu052788
 * Date: 19/03/2018
 * Time: 10:16
 */

namespace SSO\ValueObject\User;


class UserSessionRepresentation
{

    /**
     * @var array
     */
    private $clients = [];

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $ipAddress;

    /**
     * @var int
     */
    private $lastAccess;

    /**
     * @var int
     */
    private $start;

    /**
     * @var string
     */
    private $userId;

    /**
     * @var string
     */
    private $username;

    /**
     * @return array
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * @param array $clients
     * @return UserSessionRepresentation
     */
    public function setClients($clients)
    {
        $this->clients = $clients;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return UserSessionRepresentation
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     * @return UserSessionRepresentation
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    /**
     * @return int
     */
    public function getLastAccess()
    {
        return $this->lastAccess;
    }

    /**
     * @param int $lastAccess
     * @return UserSessionRepresentation
     */
    public function setLastAccess($lastAccess)
    {
        $this->lastAccess = $lastAccess;
        return $this;
    }

    /**
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param int $start
     * @return UserSessionRepresentation
     */
    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     * @return UserSessionRepresentation
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return UserSessionRepresentation
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

}