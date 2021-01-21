<?php

namespace Copper\Test\DB;

use Copper\Component\DB\DBModel;
use Copper\Component\DB\DBModelField;

class TestDBModel extends DBModel
{
    const ID = 'id';
    const LOGIN = 'login';
    const NAME = 'name';
    const PASSWORD = 'password';
    const ROLE = 'role';
    const EMAIL = 'email';
    const SALARY = 'salary';
    const ENUM = 'enum';

    public function getTableName()
    {
        return 'db_test';
    }

    public function setFields()
    {
        $this->field(self::ID, DBModelField::SMALLINT)->primary()->unsigned();
        $this->field(self::NAME, DBModelField::VARCHAR)->null();
        $this->field(self::LOGIN, DBModelField::VARCHAR, 25)->unique();
        $this->field(self::PASSWORD, DBModelField::VARCHAR, 32);
        $this->field(self::ROLE, DBModelField::TINYINT)->default(2)->unsigned();
        $this->field(self::EMAIL, DBModelField::VARCHAR, 50)->unique();
        $this->field(self::SALARY, DBModelField::DECIMAL, [6, 2])->default(123.57);
        $this->field(self::ENUM, DBModelField::ENUM, ['apple','banana'])->default('banana');

        // ------ State Fields ------
        $this->addStateFields();
    }

}