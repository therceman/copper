<?php

namespace PHPSTORM_META {

    /** @var \Copper\Component\Templating\ViewHandler $view */
    $view = null;

    override(\Copper\Component\Auth\AuthHandler::user(0), map([
        '' => '@',
    ]));
}