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

    /** @var string */
    public $serviceClassName;
}