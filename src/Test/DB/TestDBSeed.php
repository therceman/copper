<?php

namespace Copper\Test\DB;

use Copper\Component\DB\DBHandler;
use Copper\Component\DB\DBModelField;
use Copper\Component\DB\DBSeed;

class TestDBSeed extends DBSeed
{
    const HASH_SALT = '123123123';

    public function getModelClassName()
    {
        return TestDBModel::class;
    }

    public function setSeeds()
    {
        // ----------- Super Admin -----------

        $user = new TestDBEntity();

        $user->login = 'super_admin';
        $user->password = DBHandler::hashWithSalt('super_admin_pass', self::HASH_SALT);
        $user->role = TestDBEntity::ROLE_SUPER_ADMIN;
        $user->email = 'super_admin@arkadia_trade.com';
        $user->enabled = true;

        $this->seed($user);

        // ----------- Admin -----------

        $user = new TestDBEntity();

        $user->login = 'admin';
        $user->password = DBHandler::hashWithSalt('admin_pass', self::HASH_SALT);
        // $user->role = TestDBEntity::ROLE_ADMIN; should be set to 2 by default
        $user->name = "Admin Сделанный lietotāj's";
        $user->email = 'admin@arkadia_trade.com';
        $user->enabled = true;

        $this->seed($user);

        // ----------- User -----------

        $user = new TestDBEntity();

        $user->login = 'user';
        $user->password = DBHandler::hashWithSalt('user_pass', self::HASH_SALT);
        $user->role = TestDBEntity::ROLE_USER;
        $user->email = 'user@arkadia_trade.com';
        $user->enabled = true;

        $this->seed($user);

        // ----------- Disabled User -----------

        $user = new TestDBEntity();

        $user->login = "disabled_user";
        $user->password = DBHandler::hashWithSalt('user_pass', self::HASH_SALT);
        $user->role = TestDBEntity::ROLE_USER;
        $user->email = 'user_disabled@arkadia_trade.com';
        $user->enabled = false;

        $this->seed($user);

        // ----------- Removed User -----------

        $user = new TestDBEntity();

        $user->login = "removed_user";
        $user->password = DBHandler::hashWithSalt('user_pass', self::HASH_SALT);
        $user->role = TestDBEntity::ROLE_USER;
        $user->email = 'removed_user@arkadia_trade.com';
        $user->removed_at = DBHandler::datetime();
        $user->enabled = false;

        $this->seed($user);
    }
}