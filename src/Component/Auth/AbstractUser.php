<?php


namespace Copper\Component\Auth;


class AbstractUser
{
    const ROLE_ADMIN = 'admin';
    const ROLE_GUEST = 'guest';

    /** @var int */
    public $id;
    /** @var string */
    public $login;
    /** @var string */
    public $role;

    /** @var string */
    public $email;

    /**
     * AbstractUser constructor.
     * @param int $id
     * @param string $login
     * @param string $role
     * @param string $email
     */
    public function __construct(int $id, string $login, string $role, string $email = "")
    {
        $this->id = $id;
        $this->login = $login;
        $this->role = $role;

        $this->email = $email;
    }

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