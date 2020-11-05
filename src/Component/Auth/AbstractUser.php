<?php


namespace Copper\Component\Auth;

use Copper\Entity\AbstractEntity;

class AbstractUser extends AbstractEntity
{
    const ROLE_ADMIN = 'admin';
    const ROLE_GUEST = 'guest';

    /** @var int */
    public $id;
    /** @var string */
    public $login;
    /** @var string */
    public $role;

    /**
     * @param array|string $role
     */
    public function hasRole($role)
    {
        if (is_array($role))
            return in_array($this->role, $role);
        else
            return $this->role === $role;
    }
}