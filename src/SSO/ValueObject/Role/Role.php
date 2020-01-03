<?php
/**
 * Created by PhpStorm.
 * User: lu052788
 * Date: 19/03/2018
 * Time: 10:17
 */

namespace SSO\ValueObject\Role;


class Role
{

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var boolean
     */
    private $composite;

    private $composites;

    public function __construct(){}

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Role
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Role
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Role
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return bool
     */
    public function isComposite()
    {
        return $this->composite;
    }

    /**
     * @param bool $composite
     * @return Role
     */
    public function setComposite($composite)
    {
        $this->composite = $composite;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getComposites()
    {
        return $this->composites;
    }

    /**
     * @param mixed $composites
     * @return Role
     */
    public function setComposites($composites)
    {
        $this->composites = $composites;
        return $this;
    }

    public static function setRoleFromStdClass(\stdClass $roleSSO)
    {
        $role = new Role();
        $role->id = $roleSSO->id;
        $role->name = $roleSSO->name;
        $role->description = isset($roleSSO->description) ? $roleSSO->description : null;
        $role->composite = isset($roleSSO->composite) ? $roleSSO->composite : null;
        $role->composites = isset($roleSSO->composites) ? $roleSSO->composites : null;

        return $role;
    }
}