<?php

namespace Copper\Component\Auth;

use Copper\Component\DB\DBHandler;
use Copper\Entity\AbstractEntity;
use Copper\Handler\DateHandler;
use Copper\Handler\FileHandler;
use Copper\Handler\StringHandler;
use Copper\Kernel;
use Copper\Traits\ComponentHandlerTrait;
use Symfony\Component\HttpFoundation\Session\Session;

class AuthHandler
{
    use ComponentHandlerTrait;

    const SESSION_KEY = 'auth_id';

    const LOG_ACTION__AUTHORIZE = 'Authorize';
    const LOG_ACTION__LOGOUT = 'Logout';

    const SERVICE_VALIDATE_METHOD = 'validate';
    const SERVICE_AUTHORIZE_METHOD = 'authorize';

    /** @var Session */
    public $session;
    /** @var AuthConfigurator */
    public $config;
    /** @var DBHandler */
    public $db;
    /** @var AbstractUserEntity */
    private $user;

    /**
     * AuthHandler constructor.
     *
     * @param string $configFilename
     * @param AuthConfigurator|null $config
     */
    public function __construct(string $configFilename, AuthConfigurator $config = null)
    {
        $this->session = new Session();
        $this->session->start();

        $this->db = Kernel::getDb();
        $this->user = null;

        $this->config = $config ?? $this->configure(AuthConfigurator::class, $configFilename);
    }

    private function log($action, $userId)
    {
        if ($this->config->log === false)
            return false;

        $log_data = StringHandler::sprintf($this->config->log_format, [
            DateHandler::dateTime(),
            $action,
            $this->sessionId(),
            $userId
        ]);

        if (FileHandler::fileExists(Kernel::getAppLogPath()) === false)
            FileHandler::createFolder(Kernel::getAppLogPath());

        return FileHandler::appendContent($this->config->log_filepath, $log_data . "\n");
    }

    /**
     * Remove authorization access
     */
    public function logout()
    {
        $this->log(self::LOG_ACTION__LOGOUT, $this->session->get(self::SESSION_KEY));

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

        $this->log(self::LOG_ACTION__AUTHORIZE, $id);
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
     * @return string
     */
    public function sessionId()
    {
        return $this->session->getId();
    }

    /**
     * Check if user has active logged-in session
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
     * @return AbstractUserEntity|AbstractEntity|null
     */
    public function user(string $entityClass = AbstractUserEntity::class)
    {
        /** @var AbstractEntity $entityClass */
        $guestUser = $entityClass::fromArray([
            "login" => $this->session->getId(),
            "role" => AbstractUserEntity::ROLE_GUEST
        ]);

        if ($this->check() === false)
            return $guestUser;

        if ($this->user !== null)
            return $this->user;

        /** @var AuthServiceInterface $authService */
        $authService = $this->config->serviceClassName;

        $user = null;
        if (method_exists($authService, self::SERVICE_AUTHORIZE_METHOD))
            $user = $authService::authorize($this->id());

        $this->user = ($user === null) ? $guestUser : $user;

        return $this->user;
    }

    /**
     * Finds a user by credentials (login & password)
     *
     * @param string $login
     * @param string $password
     * @return AbstractUserEntity|null
     */
    public function validate(string $login, string $password)
    {
        /** @var AuthServiceInterface $authService */
        $authService = $this->config->serviceClassName;

        if (method_exists($authService, self::SERVICE_VALIDATE_METHOD))
            return $authService::validate($login, $password);

        return null;
    }

}