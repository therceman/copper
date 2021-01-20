<?php


namespace Copper\Test\DB;


use Copper\Component\CP\DB\DBService;
use Copper\Component\DB\DBHandler;
use Copper\FunctionResponse;

class TestDB
{
    /** @var DBHandler */
    public $db;

    public function __construct(DBHandler $db)
    {
        $this->db = $db;
    }

    private function migrate()
    {
        return DBService::migrateClassName(TestDBModel::class, $this->db, true);
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

        if ($entity->role !== 2)
            return $response->fail('Role != 2 (provided by default)', $entity->role);

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
        $user->salary = 555.56;

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

    private function delete()
    {

    }

    private function getList()
    {

    }

    private function find()
    {

    }

    public function run()
    {
        $response = new FunctionResponse();

        $results = [];

        $results[] = ['migrate', $this->migrate()];
        $results[] = ['seed', $this->seed()];
        $results[] = ['get', $this->get()];
        $results[] = ['create', $this->create()];
        $results[] = ['update', $this->update()];

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