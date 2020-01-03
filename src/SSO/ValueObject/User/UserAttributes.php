<?php
/**
 * Created by PhpStorm.
 * User: lu052788
 * Date: 19/03/2018
 * Time: 07:47
 */

namespace SSO\ValueObject\User;


class UserAttributes
{

    /**
     * @var string
     */
    private $ldapID;

    /**
     * @var string
     */
    private $ldapEntryDn;

    /**
     * @var string
     */
    private $locale;

    private $createTimestamp;

    private $modifyTimestamp;

    public function __construct(){}

    /**
     * @return string
     */
    public function getLdapID()
    {
        return $this->ldapID;
    }

    /**
     * @param string $ldapID
     * @return UserAttributes
     */
    public function setLdapID($ldapID)
    {
        $this->ldapID = $ldapID;
        return $this;
    }

    /**
     * @return string
     */
    public function getLdapEntryDn()
    {
        return $this->ldapEntryDn;
    }

    /**
     * @param string $ldapEntryDn
     * @return UserAttributes
     */
    public function setLdapEntryDn($ldapEntryDn)
    {
        $this->ldapEntryDn = $ldapEntryDn;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return UserAttributes
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreateTimestamp()
    {
        return $this->createTimestamp;
    }

    /**
     * @param mixed $createTimestamp
     * @return UserAttributes
     */
    public function setCreateTimestamp($createTimestamp)
    {
        $this->createTimestamp = $createTimestamp;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getModifyTimestamp()
    {
        return $this->modifyTimestamp;
    }

    /**
     * @param mixed $modifyTimestamp
     * @return UserAttributes
     */
    public function setModifyTimestamp($modifyTimestamp)
    {
        $this->modifyTimestamp = $modifyTimestamp;
        return $this;
    }

    public static function setUserAttributesFromObject(\stdClass $attributes)
    {
        $userAttributes = new UserAttributes();

        if ($attributes == null ) {
            return $userAttributes;
        }

        if(property_exists($attributes, 'createTimestamp')){
            $userAttributes->createTimestamp = $attributes->createTimestamp;
        }

        if(property_exists($attributes, 'LDAP_ENTRY_DN')){
            $userAttributes->ldapEntryDn = $attributes->LDAP_ENTRY_DN;
        }

        if(property_exists($attributes, 'locale')){
            $userAttributes->locale = $attributes->locale;
        }

        if(property_exists($attributes, 'LDAP_ID')){
            $userAttributes->ldapID = $attributes->LDAP_ID;
        }

        if(property_exists($attributes, 'modifyTimestamp')){
            $userAttributes->modifyTimestamp = $attributes->modifyTimestamp;
        }

        return $userAttributes;
    }
}