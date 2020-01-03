<?php
/**
 * Created by PhpStorm.
 * User: lu052788
 * Date: 16/03/2018
 * Time: 16:06
 */

namespace SSO\ValueObject\User;


use Doctrine\Common\Collections\ArrayCollection;

class User
{

    private $id;

    private $username;

    private $enabled;

    private $totp;

    private $emailVerified;

    private $firstName;

    private $lastName;

    private $email;

    private $federationLink;

    /**
     * @var UserAttributes
     */
    private $attributes;

    private $credentials;

    private $disableableCredentialTypes;

    private $requiredActions;

    private $socialLinks;

    private $realmRoles;

    private $applicationRoles;

    public function __construct()
    {
        $this->disableableCredentialTypes = new ArrayCollection();
        $this->requiredActions            = new ArrayCollection();

    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotp()
    {
        return $this->totp;
    }

    /**
     * @param mixed $totp
     * @return User
     */
    public function setTotp($totp)
    {
        $this->totp = $totp;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmailVerified()
    {
        return $this->emailVerified;
    }

    /**
     * @param mixed $emailVerified
     * @return User
     */
    public function setEmailVerified($emailVerified)
    {
        $this->emailVerified = $emailVerified;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFederationLink()
    {
        return $this->federationLink;
    }

    /**
     * @param mixed $federationLink
     * @return User
     */
    public function setFederationLink($federationLink)
    {
        $this->federationLink = $federationLink;

        return $this;
    }

    /**
     * @return UserAttributes
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param UserAttributes $attributes
     * @return User
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @param mixed $credentials
     * @return User
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequiredActions()
    {
        return $this->requiredActions;
    }

    /**
     * @param mixed $requiredActions
     * @return User
     */
    public function setRequiredActions($requiredActions)
    {
        $this->requiredActions = $requiredActions;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSocialLinks()
    {
        return $this->socialLinks;
    }

    /**
     * @param mixed $socialLinks
     * @return User
     */
    public function setSocialLinks($socialLinks)
    {
        $this->socialLinks = $socialLinks;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRealmRoles()
    {
        return $this->realmRoles;
    }

    /**
     * @param mixed $realmRoles
     * @return User
     */
    public function setRealmRoles($realmRoles)
    {
        $this->realmRoles = $realmRoles;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getApplicationRoles()
    {
        return $this->applicationRoles;
    }

    /**
     * @param mixed $applicationRoles
     * @return User
     */
    public function setApplicationRoles($applicationRoles)
    {
        $this->applicationRoles = $applicationRoles;

        return $this;
    }

    public function possuiSenhaDefinida()
    {
        if (!empty($this->federationLink)) {
            $atributos = $this->attributes;
            if ($atributos instanceof UserAttributes) {
                if (!empty($atributos->getLdapEntryDn())) {
                    return TRUE;
                }
            }
        } else if (!$this->disableableCredentialTypes->isEmpty()) {
            $chave = 'password';

            return $this->disableableCredentialTypes->contains($chave);
        }

        return FALSE;
    }

    public function isSenhaTemporaria()
    {
        if (empty($this->federationLink)) {
            if (!$this->disableableCredentialTypes->isEmpty() && $this->disableableCredentialTypes->contains('password')) {
                if (!$this->requiredActions->isEmpty() && $this->requiredActions->contains('UPDATE_PASSWORD')) {
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    public static function setUserFromOpenId($user)
    {
        $ssoUser = new User();

        $ssoUser->setId($user->sub);
        $ssoUser->setUsername($user->preferred_username);
        $ssoUser->setFirstName($user->given_name);
        $ssoUser->setLastName($user->family_name);
        $ssoUser->setEmail($user->email);

        return $ssoUser;
    }

    public static function setUserFromObject(\stdClass $user)
    {
        $ssoUser = new User();

        $ssoUser->id       = $user->id;
        $ssoUser->username = $user->username;
        $ssoUser->enabled  = $user->enabled;

        if (property_exists($user, 'firstName')) {
            $ssoUser->firstName = $user->firstName;
        }

        if (property_exists($user, 'lastName')) {
            $ssoUser->lastName = $user->lastName;
        }

        if (property_exists($user, 'email')) {
            $ssoUser->email = $user->email;
        }

        $ssoUser->totp          = $user->totp;
        $ssoUser->emailVerified = $user->emailVerified;

        if (property_exists($user, 'federationLink') && $user->federationLink != NULL) {
            $ssoUser->federationLink = $user->federationLink;
        }

        if (property_exists($user, 'attributes') && $user->attributes != NULL) {
            $ssoUser->attributes = UserAttributes::setUserAttributesFromObject($user->attributes);
        }

        if (property_exists($user, 'credentials')) {
            $ssoUser->credentials = $user->credentials;
        }

        if (property_exists($user, 'disableableCredentialTypes')) {
            $ssoUser->disableableCredentialTypes = new ArrayCollection($user->disableableCredentialTypes);
        }

        if (property_exists($user, 'requiredActions')) {
            $ssoUser->requiredActions = new ArrayCollection($user->requiredActions);
        }

        if (property_exists($user, 'socialLinks')) {
            $ssoUser->socialLinks = $user->socialLinks;
        }

        if (property_exists($user, 'realmRoles')) {
            $ssoUser->realmRoles = $user->realmRoles;
        }

        if (property_exists($user, 'applicationRoles')) {
            $ssoUser->applicationRoles = $user->applicationRoles;
        }

        return $ssoUser;
    }
}