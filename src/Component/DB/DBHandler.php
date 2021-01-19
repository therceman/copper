<?php

namespace Copper\Component\DB;

use Envms\FluentPDO\Query;
use PDO;

class DBHandler
{
    /** @var PDO */
    public $pdo;

    /** @var Query */
    public $query;

    /** @var DBConfigurator */
    public $config;

    /**
     * DBHandler constructor.
     *
     * @param DBConfigurator $projectConfig
     * @param DBConfigurator $packageConfig
     */
    public function __construct(DBConfigurator $packageConfig, DBConfigurator $projectConfig = null)
    {
        $this->config = $this->mergeConfig($packageConfig, $projectConfig);
        date_default_timezone_set($this->config->timezone);
        $this->init();
    }

    private function init()
    {
        if (trim($this->config->dbname) === '' || $this->config->enabled === false)
            return;

        $dsn = 'mysql:host=' . $this->config->host . ';dbname=' . $this->config->dbname;

        $this->pdo = new PDO($dsn, $this->config->user, $this->config->password);

        $this->query = new Query($this->pdo);
    }

    private function mergeConfig(DBConfigurator $packageConfig, DBConfigurator $projectConfig = null)
    {
        if ($projectConfig === null)
            return $packageConfig;

        $vars = get_object_vars($projectConfig);

        foreach ($vars as $key => $value) {
            if ($value !== null || trim($value) !== "")
                $packageConfig->$key = $value;
        }

        return $packageConfig;
    }

    public static function escape($str, $surround = true)
    {
        $str = str_replace("'", "''", $str);

        return ($surround === true) ? "'$str'" : $str;
    }

    public static function datetime()
    {
        return date('Y-m-d H:i:s');
    }

    public static function year()
    {
        return date('Y');
    }

    public static function null()
    {
        return 'NULL';
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