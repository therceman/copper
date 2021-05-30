<?php


namespace Copper\Component\Auth;

use Copper\Entity\AbstractEntity;
use Copper\Handler\VarHandler;

class AbstractUserEntity extends AbstractEntity
{
    const ROLE_GUEST = 'guest';
    const ROLE_USER = 'user';
    const ROLE_MODERATOR = 'moderator';
    const ROLE_ADMIN = 'admin';
    const ROLE_SUPER_ADMIN = 'super_admin';

    /** @var string */
    public $login;
    /** @var string */
    public $password;
    /** @var string */
    public $role;

    /**
     * @param array|string $role
     *
     * @return bool
     */
    public function hasRole($role)
    {
        if (VarHandler::isArray($role))
            return in_array($this->role, $role);
        else
            return $this->role === $role;
    }

    public function isGuest()
    {
        return $this->hasRole(self::ROLE_GUEST);
    }
}