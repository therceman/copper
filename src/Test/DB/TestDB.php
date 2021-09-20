<?php


namespace Copper\Test\DB;


use Copper\Component\DB\DBSelect;
use Copper\Component\DB\DBService;
use Copper\Component\DB\DBWhere;
use Copper\Component\DB\DBHandler;
use Copper\Component\DB\DBModel;
use Copper\Component\DB\DBOrder;
use Copper\FunctionResponse;
use Copper\Kernel;

class TestDB
{
    /** @var DBHandler */
    public $db;
    /** @var TestDBModel */
    public $model;

    public function __construct(DBHandler $db)
    {
        $this->db = $db;
        $this->model = new TestDBModel();
    }

    private function model()
    {
        $response = new FunctionResponse();

        $results = [];

        // -------------- ID --------------

        $field = $this->model->getFieldByName(TestDBModel::ID);

        if ($field->getAttr() !== $field::ATTR_UNSIGNED)
            $results[$field->getName()] = new FunctionResponse(false, 'Wrong Attr');

        if ($field->getIndex() !== $field::INDEX_PRIMARY)
            $results[$field->getName()] = new FunctionResponse(false, 'Wrong Index');

        if ($field->getIndexName() !== 'index_' . $field->getName())
            $results[$field->getName()] = new FunctionResponse(false, 'Wrong Index Name');

        if ($field->getAutoIncrement() !== true)
            $results[$field->getName()] = new FunctionResponse(false, 'Wrong AutoIncrement');

        if ($field->getLength() !== false)
            $results[$field->getName()] = new FunctionResponse(false, 'Wrong Length');

        if ($field->getNull() !== false)
            $results[$field->getName()] = new FunctionResponse(false, 'Wrong Null');

        if ($field->getNull() !== false)
            $results[$field->getName()] = new FunctionResponse(false, 'Wrong Null');

        // TODO -------------- NAME -------------- & others


        // Default Varchar Length
        if ($this->model->getFieldByName(TestDBModel::NAME)->getLength() !== $this->db->config->default_varchar_length)
            return $response->fail('Wrong Default Varchar Length');

        // Default Decimal Length
        if ($this->model->getFieldByName(TestDBModel::DEC_DEF)->getLength() !== $this->db->config->default_decimal_length)
            return $response->fail('Wrong Default Decimal Length');

        if (count($results) > 0)
            return $response->fail('Fail', $results);

        return $response->ok();
    }

    private function migrate()
    {
        $response = new FunctionResponse();

        $migrateResponse = DBService::migrateClassName(TestDBModel::class, $this->db, true);

        $query = $migrateResponse->result;

        if ($query !== "CREATE TABLE IF NOT EXISTS `" . Kernel::getDb()->config->dbname . "`.`db_test` ( `id` SMALLINT UNSIGNED  NOT NULL AUTO_INCREMENT , `name` VARCHAR(255) NULL DEFAULT NULL , `is` VARCHAR(255) NULL DEFAULT NULL , `login` VARCHAR(25) NOT NULL , `password` VARCHAR(32) NOT NULL , `role` ENUM('guest','user','moderator','admin','super_admin') NOT NULL DEFAULT 'guest' , `email` VARCHAR(50) NOT NULL , `salary` DECIMAL(6,2) NOT NULL DEFAULT '123.57' , `enum` ENUM('apple','banana') NOT NULL DEFAULT 'banana' , `dec_def` DECIMAL(9,2) NOT NULL DEFAULT 0 , `int` INT NOT NULL DEFAULT 0 , `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `updated_at` DATETIME on update CURRENT_TIMESTAMP  NULL DEFAULT NULL , `archived_at` DATETIME NULL DEFAULT NULL , `enabled` BOOLEAN NOT NULL DEFAULT 0, PRIMARY KEY (`id`), UNIQUE `index_login` (`login`), UNIQUE `index_email` (`email`)) ENGINE = InnoDB;")
            return $response->fail('Wrong Query', $query);

        if ($migrateResponse->hasError())
            return $response->fail($migrateResponse->msg);

        return $response->ok($migrateResponse->msg);
    }

    private function seed()
    {
        return DBService::seedClassName(TestDBSeed::class, $this->db);
    }

    private function get()
    {
        $response = new FunctionResponse();

        /** @var TestDBEntity $entity */
        $entity = TestDBService::get($this->db, 2);

        if ($entity === null)
            return $response->fail('User with ID 2 should be returned.', $entity);

        if ($entity->role !== TestDBModel::ROLE__GUEST)
            return $response->fail('Role != guest (provided by default)', $entity->role);

        if ($entity->enabled !== true)
            return $response->fail('Type mismatch', $entity->enabled);

        if ($entity->name !== "Admin Сделанный lietotāj's")
            return $response->fail("Name doesn't match. DB Encoding problem", $entity->name);

        $entity = TestDBService::get($this->db, 5);

        if ($entity !== null)
            return $response->fail('User with ID 5 should not be returned.', $entity);

        $entity = TestDBService::get($this->db, 5, true);

        if ($entity === null)
            return $response->fail('User with ID 5 should be returned.', $entity);

        return $response->ok();
    }

    private function create()
    {
        $user = new TestDBEntity();

        $user->login = "new_user";
        $user->password = DBHandler::hashWithSalt('new_user_pass', TestDBSeed::HASH_SALT);
        $user->role = TestDBEntity::ROLE_USER;
        $user->email = 'new_user@arkadia_trade.com';
        $user->salary = ' 555.56 ';

        $response = TestDBService::create($this->db, $user);

        if ($response->hasError() === true)
            return $response->fail('User not created', $response->msg);

        /** @var TestDBEntity $entity */
        $entity = TestDBService::get($this->db, 6);

        if ($entity === null)
            return $response->fail('User with ID 6 is not found', $entity);

        if ($entity->enabled !== false)
            return $response->fail('User with should be disabled (by default)', $entity);

        if ($entity->created_at === null)
            return $response->fail('User created_at should not be NULL', $entity);

        if ($entity->salary !== 555.56)
            return $response->fail('User salary has wrong value', $entity);

        return $response->ok("success", null);
    }

    private function update()
    {
        $response = TestDBService::update($this->db, 6, [
            TestDBModel::SALARY => '56'
        ]);

        if ($response->hasError() === true)
            return $response->fail('User not updated', $response->msg);

        /** @var TestDBEntity $entity */
        $entity = TestDBService::get($this->db, 6);

        if ($entity->salary !== 56.00)
            return $response->fail('User salary has wrong value', $entity);

        if ($entity->updated_at === null)
            return $response->fail('User update_at should not be NULL', $entity);

        if ($entity->enabled !== false)
            return $response->fail('User should be disabled', $entity);

        // enable

        $response = TestDBService::enable($this->db, 6);

        if ($response->hasError() === true)
            return $response->fail('User not enabled', $response->msg);

        /** @var TestDBEntity $entity */
        $entity = TestDBService::get($this->db, 6);

        if ($entity->enabled !== true)
            return $response->fail('User should be enabled', $entity);

        // disable

        $response = TestDBService::disable($this->db, 6);

        if ($response->hasError() === true)
            return $response->fail('User not disabled', $response->msg);

        /** @var TestDBEntity $entity */
        $entity = TestDBService::get($this->db, 6);

        if ($entity->enabled !== false)
            return $response->fail('User should be disabled', $entity);

        return $response->ok("success", null);
    }

    private function archive()
    {
        /** @var TestDBEntity $entity */
        $entity = TestDBService::get($this->db, 3);

        if ($entity->enabled !== true)
            return new FunctionResponse(false, 'User->enabled should be true', $entity);

        // archive

        $response = TestDBService::archive($this->db, 3);

        if ($response->hasError() === true)
            return $response->fail('User is not archived', $response->msg);

        /** @var TestDBEntity $entity */
        $entity = TestDBService::get($this->db, 3, true);

        if ($entity->archived_at === null)
            return $response->fail('User->archived_at should not be null', $response->msg);

        if ($entity->enabled !== false)
            return $response->fail('User->enabled should be false', $response->msg);

        // undoArchive

        $response = TestDBService::undoArchive($this->db, 3);

        if ($response->hasError() === true)
            return $response->fail('User undo for removal failed', $response->msg);

        /** @var TestDBEntity $entity */
        $entity = TestDBService::get($this->db, 3);

        if ($entity->archived_at !== null)
            return $response->fail('User->archived_at should be null', $response->msg);

        if ($entity->enabled !== false)
            return $response->fail('User->enabled should be false', $response->msg);

        return $response->ok();
    }

    private function getList()
    {
        $response = new FunctionResponse();

        // limit & offset

        /** @var TestDBEntity[] $entity */
        $entityList = TestDBService::getList($this->db, 2, 1);

        if (count($entityList) === 0)
            return $response->fail('User List should not be empty');

        if ($entityList[0]->id !== 2)
            return $response->fail('User List first entry should be with ID = 2');

        if ($entityList[1]->id !== 3)
            return $response->fail('User List second entry should be with ID = 3');

        // all list

        /** @var TestDBEntity[] $entity */
        $entityList = TestDBService::getList($this->db);

        if (count($entityList) !== 5)
            return $response->fail('User List should contain exactly 5 rows');

        if ($entityList[4]->id !== 6)
            return $response->fail('User List 4 entry should be with ID = 6');

        // all list with archived

        /** @var TestDBEntity[] $entity */
        $entityList = TestDBService::getList($this->db, 20, 0, false, true);

        if (count($entityList) !== 6)
            return $response->fail('User List should contain exactly 6 rows');

        if ($entityList[4]->id !== 5)
            return $response->fail('User List 4 entry should be with ID = 5');

        return $response->ok();
    }

    private function findFirst()
    {
        $response = new FunctionResponse();

        /** @var TestDBEntity $entity */
        $entity = TestDBService::findFirst($this->db, [
            TestDBModel::EMAIL => 'admin@arkadia_trade.com',
            TestDBModel::PASSWORD => DBHandler::hashWithSalt('admin_pass', TestDBSeed::HASH_SALT)
        ]);

        if ($entity === null)
            return $response->fail('User should be returned.', $entity);

        if ($entity->id !== 2)
            return $response->fail('User with ID 2 should be returned.', $entity);

        return $response->ok();
    }

    private function find()
    {
        $response = new FunctionResponse();

        /** @var TestDBEntity[] $entity */
        $entityList = TestDBService::find($this->db, [
            TestDBModel::ROLE => TestDBEntity::ROLE_USER,
        ]);

        if (count($entityList) !== 3)
            return $response->fail('User List should contain exactly 3 rows', $entity);

        if ($entityList[0]->id !== 3)
            return $response->fail('User List first entry should be with ID 3', $entity);

        return $response->ok();
    }

    private function db_order()
    {
        $response = new FunctionResponse();

        // Single Order

        /** @var TestDBEntity[] $entity */
        $entityList = TestDBService::find($this->db, [
            TestDBModel::ROLE => TestDBEntity::ROLE_USER,
        ], 20, 0, DBOrder::DESC(TestDBModel::ID));

        if ($entityList[0]->id !== 6)
            return $response->fail('User List first entry should be with ID 6', $entity);

        if ($entityList[2]->id !== 3)
            return $response->fail('User List last entry should be with ID 3', $entity);

        // Multi Order [second DESC]

        /** @var TestDBEntity[] $entity */
        $entityList = TestDBService::find($this->db, [
            TestDBModel::ROLE => TestDBEntity::ROLE_USER,
        ], 20, 0, DBOrder::ASC( TestDBModel::SALARY)->andDESC(TestDBModel::ID));

        if ($entityList[0]->id !== 6)
            return $response->fail('User List first entry should be with ID 6', $entity);

        if ($entityList[1]->id !== 4)
            return $response->fail('User List second entry should be with ID 4', $entity);

        // Multi Order [second ASC]

        /** @var TestDBEntity[] $entity */
        $entityList = TestDBService::find($this->db, [
            TestDBModel::ROLE => TestDBEntity::ROLE_USER,
        ], 20, 0, DBOrder::ASC( TestDBModel::SALARY)->andASC(TestDBModel::ID));

        if ($entityList[0]->id !== 6)
            return $response->fail('User List first entry should be with ID 6', $entity);

        if ($entityList[1]->id !== 3)
            return $response->fail('User List second entry should be with ID 3', $entity);

        return $response->ok();
    }

    private function db_where()
    {
        $response = new FunctionResponse();

        // is

        $entityList = TestDBService::find($this->db,
            DBWhere::is(TestDBModel::NAME, "Admin Сделанный lietotāj's")
        );

        if ($entityList[0]->id !== 2)
            return $response->fail('[is] User List first entry should be with ID 2', $entityList[0]);

        // is (array)

        $entityList = TestDBService::find($this->db,
            DBWhere::is(TestDBModel::ID, [2,3,4])
        );

        if ($entityList[0]->id !== 2)
            return $response->fail('[is array] User List first entry should be with ID 2', $entityList[0]);

        // is (empty array)

        $entityList = TestDBService::find($this->db,
            DBWhere::is(TestDBModel::ID, [])
        );

        if (count($entityList) !== 0)
            return $response->fail('[is empty array] User List count should be 0', $entityList);

        // in (array)

        $entityList = TestDBService::find($this->db,
            DBWhere::in(TestDBModel::ID, [2,3,4])
        );

        if ($entityList[0]->id !== 2)
            return $response->fail('[in array] User List first entry should be with ID 2', $entityList[0]);

        // in (empty array)

        $entityList = TestDBService::find($this->db,
            DBWhere::in(TestDBModel::ID, [])
        );

        if (count($entityList) !== 0)
            return $response->fail('[in empty array] User List count should be 0', $entityList);

        // not (value)

        $entityList = TestDBService::find($this->db,
            DBWhere::not(TestDBModel::NAME, null)
        );

        if ($entityList[0]->id !== 2)
            return $response->fail('[not] User List first entry should be with ID 2', $entityList[0]);

        // not (array of values)

        $entityList = TestDBService::find($this->db,
            DBWhere::not(TestDBModel::ID, [1,2,3,4,5])
        );

        if ($entityList[0]->id !== 6)
            return $response->fail('[not array] User List first entry should be with ID 6', $entityList[0]);

        // not (single value)

        $entityList = TestDBService::find($this->db,
            DBWhere::not(TestDBModel::ID, 1)
        );

        if ($entityList[0]->id !== 2)
            return $response->fail('[not array] User List first entry should be with ID 2', $entityList[0]);

        // notIn (array of values)

        $entityList = TestDBService::find($this->db,
            DBWhere::notIn(TestDBModel::ID, [1,2,3,4,5])
        );

        if ($entityList[0]->id !== 6)
            return $response->fail('[notIn array] User List first entry should be with ID 6', $entityList[0]);

        // not (empty array)

        $entityList = TestDBService::find($this->db,
            DBWhere::not(TestDBModel::ID, []), 50, 0, false, true
        );

        if (count($entityList) !== 6)
            return $response->fail('[not empty array] User List count should be with 6', $entityList);

        // notIn (empty array)

        $entityList = TestDBService::find($this->db,
            DBWhere::notIn(TestDBModel::ID, []), 50, 0, false, true
        );

        if (count($entityList) !== 6)
            return $response->fail('[not empty array] User List count should be with 6', $entityList);

        // lt

        $entityList = TestDBService::find($this->db,
            DBWhere::lt(TestDBModel::SALARY, 57)
        );

        if ($entityList[0]->id !== 6)
            return $response->fail('[lt] User List first entry should be with ID 6', $entityList[0]);

        // ltOrEq

        $entityList = TestDBService::find($this->db,
            DBWhere::ltOrEq(TestDBModel::SALARY, 57)
        );

        if ($entityList[1]->id !== 6) // by default sorted by ID ASC
            return $response->fail('[ltOrEq] User List second entry should be with ID 1', $entityList[1]);

        // gt

        $entityList = TestDBService::find($this->db,
            DBWhere::gt(TestDBModel::SALARY, 56)
        );

        if ($entityList[0]->id !== 1)
            return $response->fail('[gt] User List first entry should be with ID 1', $entityList[0]);

        // gt or eq

        $entityList = TestDBService::find($this->db,
            DBWhere::gtOrEq(TestDBModel::SALARY, 56)
        );

        if ($entityList[4]->id !== 6) // by default sorted by ID ASC
            return $response->fail('[gtOrEq] User List 4 entry should be with ID 6', $entityList[4]);

        // between

        $entityList = TestDBService::find($this->db,
            DBWhere::between(TestDBModel::SALARY, 57, 150)
        );

        if (count($entityList) !== 2)
            return $response->fail('[between] User List should contain exactly 2 rows', $entityList);

        if ($entityList[1]->id !== 4)
            return $response->fail('[between] User List 1 entry should be with ID 4', $entityList[1]);

        // between include

        $entityList = TestDBService::find($this->db,
            DBWhere::betweenInclude(TestDBModel::SALARY, 57, 150)
        );

        if (count($entityList) !== 4)
            return $response->fail('[betweenInclude] User List should contain exactly 4 rows', $entityList);

        if ($entityList[0]->id !== 1)
            return $response->fail('[betweenInclude] User List 1 entry should be with ID 1', $entityList[0]);

        // not between

        $entityList = TestDBService::find($this->db,
            DBWhere::notBetween(TestDBModel::SALARY, 57, 150)
        );

        if (count($entityList) !== 1)
            return $response->fail('[notBetween] User List should contain exactly 1 rows', $entityList);

        if ($entityList[0]->id !== 6)
            return $response->fail('[notBetween] User List 1 entry should be with ID 6', $entityList[0]);

        // not between include

        $entityList = TestDBService::find($this->db,
            DBWhere::notBetweenInclude(TestDBModel::SALARY, 57, 150)
        );

        if (count($entityList) !== 3)
            return $response->fail('[notBetweenInclude] User List should contain exactly 3 rows', $entityList);

        if ($entityList[0]->id !== 1)
            return $response->fail('[notBetweenInclude] User List 1 entry should be with ID 1', $entityList[0]);

        if ($entityList[2]->id !== 6)
            return $response->fail('[notBetweenInclude] User List last entry should be with ID 6', $entityList[2]);

        // like

        $entityList = TestDBService::find($this->db,
            DBWhere::like(TestDBModel::LOGIN, "__m%"),
            20, 0, false, true);

        if (count($entityList) !== 2)
            return $response->fail('[like] User List should contain exactly 2 rows', $entityList);

        if ($entityList[1]->id !== 5)
            return $response->fail('[like] User List 2 entry should be with ID 5', $entityList[1]);

        // like array

        $entityList = TestDBService::find($this->db,
            DBWhere::like([TestDBModel::NAME, TestDBModel::ROLE, TestDBModel::IS], "%min%"),
            20, 0, false, true);

        if (count($entityList) !== 3)
            return $response->fail('[like array] User List should contain exactly 3 rows', $entityList);

        // not like

        $entityList = TestDBService::find($this->db,
            DBWhere::notLike(TestDBModel::LOGIN, "%_user"),
            20, 0, false, true);

        if (count($entityList) !== 3)
            return $response->fail('[notLike] User List should contain exactly 3 rows', $entityList);

        if ($entityList[2]->id !== 3)
            return $response->fail('[notLike] User List 3 entry should be with ID 3', $entityList[2]);

        // not like array

        $entityList = TestDBService::find($this->db,
            DBWhere::notLike([TestDBModel::NAME, TestDBModel::ROLE, TestDBModel::IS], "%min%"),
            20, 0, false, true);

        if (count($entityList) !== 3)
            return $response->fail('[not like array] User List should contain exactly 3 rows', $entityList);

        if ($entityList[0]->id !== 4)
            return $response->fail('[not like array] User List 1 entry should be with ID 4', $entityList[0]);

        // in

        $entityList = TestDBService::find($this->db,
            DBWhere::in(TestDBModel::LOGIN, ['admin', 'user']),
            20, 0, false, true);

        if (count($entityList) !== 2)
            return $response->fail('[in] User List should contain exactly 2 rows', $entityList);

        if ($entityList[1]->id !== 3)
            return $response->fail('[in] User List 2 entry should be with ID 3', $entityList[1]);

        // not in

        $entityList = TestDBService::find($this->db,
            DBWhere::notIn(TestDBModel::LOGIN, ['admin', 'user']),
            20, 0, false, true);

        if (count($entityList) !== 4)
            return $response->fail('[notIn] User List should contain exactly 4 rows', $entityList);

        if ($entityList[3]->id !== 6)
            return $response->fail('[notIn] User List 4 entry should be with ID 6', $entityList[1]);

        // ----------- Chains -----------

        // or chain

        $entityList = TestDBService::find($this->db,
            DBWhere::is(TestDBModel::NAME, "Admin Сделанный lietotāj's")
                ->or(TestDBModel::EMAIL, "user_disabled@arkadia_trade.com")
                ->or(TestDBModel::SALARY, 57)
        );

        if (count($entityList) !== 3)
            return $response->fail('[or-chain] User List should contain exactly 2 rows', $entityList);

        if ($entityList[1]->id !== 2)
            return $response->fail('[or-chain] User List last entry should be with ID 2', $entityList[1]);

        // and chain

        $entityList = TestDBService::find($this->db,
            DBWhere::isLike(TestDBModel::LOGIN, "_e%")
                ->and(TestDBModel::ROLE, TestDBModel::ROLE__USER)
                ->andBetweenInclude(TestDBModel::SALARY, 56, 200),
            20, 0, false, true);

        if (count($entityList) !== 2)
            return $response->fail('[and-chain] User List should contain exactly 2 rows', $entityList);

        if ($entityList[0]->id !== 5)
            return $response->fail('[and-chain] User List first entry should be with ID 5', $entityList[0]);

        return $response->ok();
    }

    private function db_model()
    {
        $response = new FunctionResponse();

        $model = new TestDBModel();

        // doSelect

        $entityList = $model->doSelect();

        if (count($entityList) !== 6)
            return $response->fail('[doSelect] User List should have 6 entries', $entityList);

        // doSelectWhere

        $entityList = $model->doSelectWhere(DBWhere::is(TestDBModel::ROLE, TestDBModel::ROLE__USER));

        if (count($entityList) !== 4)
            return $response->fail('[doSelectWhere] User List should have 4 entries', $entityList);

        // doSelectFirstWhere

        /** @var TestDBEntity $entity */
        $entity = $model->doSelectFirstWhere(DBWhere::is(TestDBModel::ID, 3));

        if ($entity->enum !== 'banana')
            return $response->fail('[doSelectFirstWhere] User with ID 3 should have enum = banana', $entity);

        // doSelectUnique

        $entityList = $model->doSelectUnique(TestDBModel::ROLE);

        if (count($entityList) !== 3)
            return $response->fail('[doSelectUnique] User List should have 3 entries', $entityList);

        // doSelectUnique with DBSelect

        $entityList = $model->doSelectUnique(TestDBModel::ROLE, DBSelect::where(
            DBWhere::lt(TestDBModel::ROLE, TestDBModel::ROLE__USER)
        ));

        if (count($entityList) !== 2 && $entityList[1]->id !== 2)
            return $response->fail('[doSelectUnique + args] User List should have 2 entries and last one should have ID = 2', $entityList);

        // doSelectLimit

        $entityList = $model->doSelectLimit(3, 2);

        if (count($entityList) !== 3 && $entityList[2]->id !== 5)
            return $response->fail('[doSelectLimit] User List should have 3 entries and last one should have ID = 5', $entityList);

        // doSelectById

        $entity = $model->doSelectById(3);

        if ($entity->login !== 'user')
            return $response->fail('[doSelectById] User with ID 3 should have login = user', $entity);

        // TODO doUpdate...

        // TODO doDelete..

        // TODO doTruncate, doMigrate, do... other

        $entity = new TestDBEntity();
        $entity->int = '    1236 ';
        $entity->login = '  demo2    ';
        $entity->password = 'demo123    ';
        $entity->email = '  demo123@qwe.com';
        $entity->enum = ' banana    ';
        $entity->enabled = ' true    ';

        $model->doInsert($entity);

        $entity = new TestDBEntity();
        $entity->int = '    12361 ';
        $entity->login = '  demo21    ';
        $entity->password = 'demo123 1   ';
        $entity->email = '  demo1231@qwe.com';
        $entity->enum = ' banana    ';
        $entity->enabled = ' e';

        $model->doInsert($entity);

        $entity = $model->doSelectById(7);

        if ($entity->int !== 1236)
            return $response->fail('$entity->int should be 1236');

        if ($entity->login !== 'demo2')
            return $response->fail("\$entity->login should be 'demo2'");

        if ($entity->password !== 'demo123')
            return $response->fail("\$entity->password should be 'demo123'");

        if ($entity->email !== 'demo123@qwe.com')
            return $response->fail("\$entity->email should be 'demo123@qwe.com'");

        if ($entity->enum !== 'banana')
            return $response->fail("\$entity->enum should be 'banana'");

        if ($entity->enabled !== true)
            return $response->fail("\$entity->enabled should be true");

        $entity = $model->doSelectById(8);

        if ($entity->enabled !== false)
            return $response->fail("\$entity->enabled should be false");

        // doCount

        $entityCount = $model->doCount();

        if ($entityCount !== 8)
            return $response->fail('[doCount] User List should have count of 8', $entityCount);

        return $response->ok();
    }

    public function run()
    {
        $response = new FunctionResponse();

        $results = [];

        $results[] = ['model', $this->model()];
        $results[] = ['migrate', $this->migrate()];
        $results[] = ['seed', $this->seed()];
        $results[] = ['get', $this->get()];
        $results[] = ['create', $this->create()];
        $results[] = ['update', $this->update()];
        $results[] = ['archive', $this->archive()];
        $results[] = ['getList', $this->getList()];
        $results[] = ['findFirst', $this->findFirst()];
        $results[] = ['find', $this->find()];
        $results[] = ['DBOrder', $this->db_order()];
        $results[] = ['DBWhere', $this->db_where()];
        $results[] = ['DBModel', $this->db_model()];

        $failedTests = [];

        foreach ($results as $result) {
            if ($result[1]->hasError())
                $failedTests[] = $result[0];
        }

        if (count($failedTests) > 0)
            return $response->fail('Failed Tests: ' . join(', ', $failedTests), $results);

        return $response->result($results);
    }
}