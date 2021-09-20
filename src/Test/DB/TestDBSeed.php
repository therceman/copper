<?php

namespace Copper\Test\DB;

use Copper\Component\DB\DBHandler;
use Copper\Component\DB\DBSeed;
use Copper\Handler\DateHandler;

class TestDBSeed extends DBSeed
{
    const HASH_SALT = '123123123';

    public function getModelClassName()
    {
        return TestDBModel::class;
    }

    public function setSeeds()
    {
        // ----------- Super Admin 1 -----------

        $user = new TestDBEntity();

        $user->login = 'super_admin';
        $user->password = DBHandler::hashWithSalt('super_admin_pass', self::HASH_SALT);
        $user->role = TestDBEntity::ROLE_SUPER_ADMIN;
        $user->email = 'super_admin@arkadia_trade.com';
        $user->enabled = true;
        $user->salary = 57;

        $this->seed($user);

        // ----------- Admin 2 -----------

        $user = new TestDBEntity();

        $user->login = 'admin';
        $user->password = DBHandler::hashWithSalt('admin_pass', self::HASH_SALT);
        // $user->role = TestDBEntity::ROLE_ADMIN; should be set to 2 by default
        $user->name = "Admin Сделанный lietotāj's";
        $user->email = 'admin@arkadia_trade.com';
        $user->enabled = true;
        $user->salary = 150;

        $this->seed($user);

        // ----------- User 3 -----------

        $user = new TestDBEntity();

        $user->login = 'user';
        $user->password = DBHandler::hashWithSalt('user_pass', self::HASH_SALT);
        $user->role = TestDBEntity::ROLE_USER;
        $user->email = 'user@arkadia_trade.com';
        $user->is = "admimin";
        $user->enabled = true;
        $user->salary = 100;

        $this->seed($user);

        // ----------- Disabled User 4 -----------

        $user = new TestDBEntity();

        $user->login = "disabled_user";
        $user->password = DBHandler::hashWithSalt('user_pass', self::HASH_SALT);
        $user->role = TestDBEntity::ROLE_USER;
        $user->email = 'user_disabled@arkadia_trade.com';
        $user->enabled = false;
        $user->salary = 100;
        $user->enum = 'apple';

        $this->seed($user);

        // ----------- Archived User 5 -----------

        $user = new TestDBEntity();

        $user->login = "removed_user";
        $user->password = DBHandler::hashWithSalt('user_pass', self::HASH_SALT);
        $user->role = TestDBEntity::ROLE_USER;
        $user->email = 'archived_user@arkadia_trade.com';
        $user->archived_at = DateHandler::dateTime();
        $user->enabled = false;
        $user->salary = 151;

        $this->seed($user);
    }
}