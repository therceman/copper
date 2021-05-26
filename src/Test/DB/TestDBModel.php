<?php

namespace Copper\Test\DB;

use Copper\Component\DB\DBModel;
use Copper\Component\DB\DBModelField;

class TestDBModel extends DBModel
{
    const ID = 'id';
    const LOGIN = 'login';
    const NAME = 'name';
    const IS = 'is';
    const PASSWORD = 'password';
    const ROLE = 'role';
    const ROLE__GUEST = 'guest';
    const ROLE__USER = 'user';
    const ROLE__MODERATOR = 'moderator';
    const ROLE__ADMIN = 'admin';
    const ROLE__SUPER_ADMIN = 'super_admin';
    const EMAIL = 'email';
    const SALARY = 'salary';
    const ENUM = 'enum';
    const DEC_DEF = 'dec_def';
    const INT = 'int';

    public function getTableName()
    {
        return 'db_test';
    }

    public function getEntityClassName()
    {
        return TestDBEntity::class;
    }

    public function setFields()
    {
        $this->addField(self::ID, DBModelField::SMALLINT)->primary()->unsigned();
        $this->addField(self::NAME, DBModelField::VARCHAR)->null();
        $this->addField(self::IS, DBModelField::VARCHAR)->null();
        $this->addField(self::LOGIN, DBModelField::VARCHAR, 25)->unique();
        $this->addField(self::PASSWORD, DBModelField::VARCHAR, '32');
        $this->addField(self::ROLE, DBModelField::ENUM, [
            self::ROLE__GUEST,
            self::ROLE__USER,
            self::ROLE__MODERATOR,
            self::ROLE__ADMIN,
            self::ROLE__SUPER_ADMIN
        ])->default(self::ROLE__GUEST);
        $this->addField(self::EMAIL, DBModelField::VARCHAR, 50)->unique();
        $this->addField(self::SALARY, DBModelField::DECIMAL, ['6', 2])->default(123.57);
        $this->addField(self::ENUM, DBModelField::ENUM, ['apple','banana'])->default('banana');
        $this->addField(self::DEC_DEF, DBModelField::DECIMAL)->default(0);
        $this->addField(self::INT, DBModelField::INT)->default(0);

        // ------ State Fields ------
        $this->addStateFields();
    }

}