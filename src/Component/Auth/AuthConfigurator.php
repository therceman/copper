<?php


namespace Copper\Component\Auth;

use Closure;

class AuthConfigurator
{
    /** @var Closure */
    public $userHandlerClosure;
    /** @var Closure */
    public $validateHandlerClosure;

    /** @var string */
    public $loginRoute = 'get_auth_login';
    /** @var string */
    public $returnToRouteParam = 'return_to_route';
    /** @var string */
    public $forbiddenTemplate = 'forbidden';

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