<?php


namespace Copper\Component\Auth;

use Closure;

class AuthConfigurator
{
    /**
     * Default access role for all routes.
     * <hr>
     * <code>
     * Example: 'admin', ['admin', 'moderator'], false
     * </code>
     * <br>
     * Default: false
     *
     * @var string|string[]|false
     */
    public $defaultAccessRole;

    /** @var Closure */
    public $userHandlerClosure;
    /** @var Closure */
    public $validateHandlerClosure;

    /** @var string */
    public $loginRoute;
    /** @var string */
    public $returnToRouteParam;
    /** @var string */
    public $forbiddenTemplate;

    /** @var bool */
    public $log;
    /** @var string */
    public $log_filepath;
    /** @var string */
    public $log_format;

    // TODO this functions should be changed to Class::method string or ['Class','method'] array
    // for future config cache to work correctly

    /**
     * @param Closure $login_password_closure
     */
    public function registerValidateHandlerClosure(Closure $login_password_closure)
    {
        $this->validateHandlerClosure = $login_password_closure;
    }

    /**
     * @param Closure $id_closure
     */
    public function registerUserHandlerClosure(Closure $id_closure)
    {
        $this->userHandlerClosure = $id_closure;
    }
}