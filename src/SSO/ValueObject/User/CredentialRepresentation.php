<?php
/**
 * Created by PhpStorm.
 * User: lu052788
 * Date: 19/03/2018
 * Time: 10:12
 */

namespace SSO\ValueObject\User;


use JsonSerializable;

class CredentialRepresentation implements JsonSerializable
{

    const CLIENT_CERT = 'cert';
    const HOTP = 'hotp';
    const KERBEROS = 'kerberos';
    const PASSWORD = 'password';
    const PASSWORD_TOKEN = 'password-token';
    const SECRET = 'secret';
    const TOTP = 'totp';

    /**
     * @var integer
     */
    private $counter;

    /**
     * @var string
     */
    private $device;

    /**
     * @var string
     */
    private $hashedSaltedValue;

    /**
     * @var integer
     */
    private $hashIterations;

    /**
     * @var string
     */
    private $salt;

    /**
     * @var boolean
     */
    private $temporary;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $value;

    /**
     * @return int
     */
    public function getCounter()
    {
        return $this->counter;
    }

    /**
     * @param int $counter
     * @return CredentialRepresentation
     */
    public function setCounter($counter)
    {
        $this->counter = $counter;
        return $this;
    }

    /**
     * @return string
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * @param string $device
     * @return CredentialRepresentation
     */
    public function setDevice($device)
    {
        $this->device = $device;
        return $this;
    }

    /**
     * @return string
     */
    public function getHashedSaltedValue()
    {
        return $this->hashedSaltedValue;
    }

    /**
     * @param string $hashedSaltedValue
     * @return CredentialRepresentation
     */
    public function setHashedSaltedValue($hashedSaltedValue)
    {
        $this->hashedSaltedValue = $hashedSaltedValue;
        return $this;
    }

    /**
     * @return int
     */
    public function getHashIterations()
    {
        return $this->hashIterations;
    }

    /**
     * @param int $hashIterations
     * @return CredentialRepresentation
     */
    public function setHashIterations($hashIterations)
    {
        $this->hashIterations = $hashIterations;
        return $this;
    }

    /**
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @param string $salt
     * @return CredentialRepresentation
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTemporary()
    {
        return $this->temporary;
    }

    /**
     * @param bool $temporary
     * @return CredentialRepresentation
     */
    public function setTemporary($temporary)
    {
        $this->temporary = $temporary;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return CredentialRepresentation
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return CredentialRepresentation
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'counter' => $this->getCounter(),
            'device' => $this->getDevice(),
            'hashedSaltedValue' => $this->getHashedSaltedValue(),
            'hashIterations' => $this->getHashIterations(),
            'salt' => $this->getSalt(),
            'temporary' => $this->isTemporary(),
            'type' => $this->getType(),
            'value' => $this->getValue(),
        ];
    }
}