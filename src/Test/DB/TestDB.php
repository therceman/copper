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

        return $response->ok();
    }

    public function run()
    {
        $response = new FunctionResponse();

        $results = [];

        $results[] = ['migrate', $this->migrate()];
        $results[] = ['seed', $this->seed()];
        $results[] = ['get', $this->get()];

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