<?php

namespace PHPSTORM_META {

    expectedArguments(
        \Copper\Component\HTML\HTML::meta(),
        0,
        'application-name',
        'author',
        'description',
        'generator',
        'keywords',
        'viewport',
    );

    /** @var \Copper\Component\Templating\ViewHandler $view */
    $view = null;

    override(\Copper\Component\Auth\AuthHandler::user(0), map([
        '' => '@',
    ]));

    override(\Copper\FunctionResponse::getResult(0), map([
        '' => '@',
    ]));
}