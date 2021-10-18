<?php


namespace Copper\Component\CP;

class CPConfigurator
{
    /**
     * Allowed IP's. Set it to null to disable
     * @var array
     */
    public $ip_whitelist;

    /** @var string */
    public $session_key;
    /** @var string */
    public $password;
    /** @var string */
    public $password_field;
    /** @var bool */
    public $enabled;
    /** @var string */
    public $route_name;
    /** @var string */
    public $route_path;
    /** @var string */
    public $action_route_name;
}