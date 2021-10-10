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

    $db->default_varchar_length = 255;
    $db->default_decimal_length = [9, 2];

    $db->trim_varchar = true;
    $db->trim_text = true;
    $db->trim_enum = true;

    $db->boolean_not_strict = true;

    $db->throwErrorWhenNothingToUpdate = false;

    $db->ifNullDefaultValue = 0;
};