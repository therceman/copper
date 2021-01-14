<?php


use Copper\Component\DB\DBConfigurator;

return function (DBConfigurator $db) {

    $db->host = 'localhost';
    $db->dbname = '';
    $db->user = 'root';
    $db->password = 'pass';

    $db->hashSalt = 'this_is_secret_salt__change_me';
};