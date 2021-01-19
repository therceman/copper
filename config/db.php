<?php


use Copper\Component\DB\DBConfigurator;

return function (DBConfigurator $db) {

    // 	Table Collation: utf8mb4_unicode_ci

    // Enable / Disable DB support
    $db->enabled = false;

    $db->host = 'localhost';
    $db->dbname = '';
    $db->user = 'root';
    $db->password = 'pass';

    $db->engine = 'InnoDB';

    $db->hashSalt = 'this_is_secret_salt__change_me';

    $db->timezone = 'Europe/Riga';
};