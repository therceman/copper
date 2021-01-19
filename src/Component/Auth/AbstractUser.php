<?php


namespace Copper\Component\Auth;

use Copper\Entity\AbstractEntity;

class AbstractUser extends AbstractEntity
{
    const ROLE_USER = 3;
    const ROLE_ADMIN = 2;
    const ROLE_SUPER_ADMIN = 1;
    const ROLE_GUEST = 0;

    /** @var string */
    public $login;
    /** @var string */
    public $password;
    /** @var int */
    public $role;

    /**
     * @param array|int $role
     *
     * @return bool
     */
    public function hasRole($role)
    {
        if (is_array($role))
            return in_array($this->role, $role);
        else
            return $this->role === $role;
    }
}