<?php

namespace Copper\Component\DB;

use Copper\Traits\ComponentHandlerTrait;
use Envms\FluentPDO\Query;
use PDO;

class DBHandler
{
    use ComponentHandlerTrait;

    /** @var PDO */
    public $information_schema_pdo;

    /** @var PDO */
    public $pdo;

    /** @var Query */
    public $query;

    /** @var Query */
    public $information_schema_query;

    /** @var DBConfigurator */
    public $config;

    /**
     * DBHandler constructor.
     *
     * @param string $configFilename
     * @param DBConfigurator|null $config
     */
    public function __construct(string $configFilename, DBConfigurator $config = null)
    {
        $this->config = $config ?? $this->configure(DBConfigurator::class, $configFilename);
        $this->init();
    }

    private function init()
    {
        if (trim($this->config->dbname) === '' || $this->config->enabled === false)
            return;

        $dsn = 'mysql:host=' . $this->config->host . ';dbname=' . $this->config->dbname;
        $this->pdo = new PDO($dsn, $this->config->user, $this->config->password);

        $is_dsn = 'mysql:host=' . $this->config->host . ';dbname=information_schema';
        $this->information_schema_pdo = new PDO($is_dsn, $this->config->user, $this->config->password);

        $this->query = new Query($this->pdo);
        $this->information_schema_query = new Query($this->information_schema_pdo);
    }

    public static function hashWithSalt($str, $salt)
    {
        return md5($salt . $str);
    }

    public function hash($str)
    {
        return self::hashWithSalt($str, $this->config->hashSalt);
    }
}