<?php

namespace Copper\Component\Auth;

use Copper\Component\DB\DBHandler;
use Copper\Kernel;
use Symfony\Component\HttpFoundation\Session\Session;

class AuthHandler
{
    const SESSION_KEY = 'auth_id';

    /** @var Session */
    public $session;
    /** @var AuthConfigurator */
    public $config;
    /** @var DBHandler */
    public $db;
    /** @var AbstractUser */
    public $user;

    /**
     * AuthHandler constructor.
     *
     * @param AuthConfigurator $packageAuthConfig
     * @param AuthConfigurator $projectAuthConfig
     */
    public function __construct(AuthConfigurator $packageAuthConfig, AuthConfigurator $projectAuthConfig = null)
    {
        $this->session = new Session();
        $this->session->start();

        $this->db = Kernel::getDb();
        $this->user = null;

        $this->config = $this->mergeConfig($packageAuthConfig, $projectAuthConfig);
    }

    private function mergeConfig(AuthConfigurator $packageAuthConfig, AuthConfigurator $projectAuthConfig = null)
    {
        if ($projectAuthConfig === null)
            return $packageAuthConfig;

        $vars = get_object_vars($projectAuthConfig);

        foreach ($vars as $key => $value) {
            if ($value !== null || trim($value) !== "")
                $packageAuthConfig->$key = $value;
        }

        return $packageAuthConfig;
    }

    /**
     * Remove authorization access
     */
    public function logout()
    {
        $this->session->invalidate();
    }

    /**
     * Authorize user by id
     *
     * @param int $id
     */
    public function authorize(int $id)
    {
        $this->session->set(self::SESSION_KEY, intval($id));
    }

    /**
     * Get authorized user id
     *
     * @return int
     */
    public function id()
    {
        return $this->session->get(self::SESSION_KEY, null);
    }

    /**
     * Check if user has active session
     *
     * @return bool
     */
    public function check()
    {
        return $this->session->has(self::SESSION_KEY);
    }

    /**
     * Get currently authorized user
     *
     * @param string $entityClass
     *
     * @return AbstractUser|mixed|null
     */
    public function user($entityClass = '')
    {
        $guestUser = AbstractUser::fromArray(["login" => $this->session->getId(), "role" => AbstractUser::ROLE_GUEST]);

        if ($this->check() === false)
            return $guestUser;

        if ($this->user !== null)
            return $this->user;

        $user = call_user_func_array($this->config->userHandlerClosure, [$this->id(), $this->db]);

        $this->user = ($user === null) ? $guestUser : $user;

        return $this->user;
    }

    /**
     * Finds a user by credentials (login & password)
     *
     * @param string $login
     * @param string $password
     * @return AbstractUser|null
     */
    public function validate(string $login, string $password)
    {
        return call_user_func_array($this->config->validateHandlerClosure, [$login, $password, $this->db]);
    }

}