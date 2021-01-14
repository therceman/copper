<?php


use Copper\Component\CP\CPConfigurator;

return function (CPConfigurator $cp) {

    $cp->session_key = 'copper_control_panel';
    $cp->password = 'this_is_the_copper_1337';
    $cp->password_field = 'password';
    $cp->enabled = true;

};