<?php


use Copper\Component\CP\CPConfigurator;

return function (CPConfigurator $cp) {

    $cp->session_key = 'copper_control_panel';
    $cp->password = 'this_is_the_copper_1337';
    $cp->password_field = 'password';
    $cp->enabled = true;
    $cp->route_name = 'get_copper_cp';
    $cp->route_path = '/copper_cp';
    $cp->action_route_name = 'copper_cp_action';

};