<?php


namespace Copper\Component\CP\DB;


use Copper\Component\DB\DBHandler;
use Copper\Component\DB\DBModel;
use Copper\Component\DB\DBModelField;
use Copper\Component\DB\DBSeed;
use Copper\FunctionResponse;
use Copper\Kernel;
use PDOException;

class DBService
{
    private static function extractNamespaceFromFile($file)
    {
        $src = file_get_contents($file);

        if (preg_match('#^namespace\s+(.+?);$#sm', $src, $m)) {
            return $m[1];
        }

        return null;
    }

    public static function tableExists($tableName, DBHandler $db)
    {
        try {
            $result = $db->pdo->query("SELECT 1 FROM $tableName LIMIT 1");
        } catch (\Exception $e) {
            return false;
        }

        return ($result !== false);
    }

    public static function tableEmpty($tableName, DBHandler $db)
    {
        try {
            $result = $db->pdo->query("SELECT EXISTS (SELECT 1 FROM $tableName)");
            $result = ($result->fetchAll()[0][0] === '0');
        } catch (\Exception $e) {
            return false;
        }

        return ($result !== false);
    }

    public static function escapeStr($str)
    {
        return str_replace("'", "\'", $str);
    }

    public static function escapeStrArray($strArray)
    {
        $array = [];

        foreach ($strArray as $str) {
            $array[] = self::escapeStr($str);
        }

        return $array;
    }

    public static function seedClassName(string $className, DBHandler $db, $force = false)
    {
        $response = new FunctionResponse();

        /** @var DBSeed $seed */
        $seed = new $className();
        /** @var DBModel $model */
        $model = new $seed->modelClassName();

        if (self::tableEmpty($model->tableName, $db) === false && $force === false)
            return $response->error("Table `$model->tableName` already seeded and " . '$force' . " flag is not true");

        $query = 'INSERT INTO ' . $model->tableName . '(' . join(', ', $model->getFieldNames()) . ') VALUES ';

        $query_values = [];

        foreach ($seed->seeds as $seedValueList) {
            $values = [];

            foreach ($seedValueList as $value) {
                if (is_string($value))
                    $value = "'" . str_replace("'", "''", $value) . "'";

                if ($value === null)
                    $value = 'NULL';

                $values[] = $value;
            }

            $query_values[] = '(' . join(', ', $values) . ')';
        }

        $query = $query . join(', ', $query_values);

        try {
            $db->pdo->setAttribute($db->pdo::ATTR_ERRMODE, $db->pdo::ERRMODE_EXCEPTION);
            $db->pdo->exec($query);
            return $response->success("Seeded `$model->tableName` Table");
        } catch (PDOException $e) {
            return $response->error($e->getMessage(), $query);
        }
    }

    /**
     * @param string $className
     * @param DBHandler $db
     * @param bool $force
     *
     * @return FunctionResponse
     */
    public static function migrateClassName(string $className, DBHandler $db, $force = false)
    {
        $response = new FunctionResponse();

        /** @var DBModel $model */
        $model = new $className();

        $tableExists = self::tableExists($model->tableName, $db);

        if ($tableExists && $force === false)
            return $response->error("Table `$model->tableName` already exists and " . '$force' . " flag is not true");

        if ($tableExists && $force === true) {
            $query = 'DROP TABLE `' . $db->config->dbname . '`.`' . $model->tableName . '`';

            try {
                $db->pdo->setAttribute($db->pdo::ATTR_ERRMODE, $db->pdo::ERRMODE_EXCEPTION);
                $db->pdo->exec($query);
            } catch (PDOException $e) {
                return $response->error($e->getMessage());
            }
        }

        $query_start = 'CREATE ' . 'TABLE IF NOT EXISTS `' . $db->config->dbname . '`.`' . $model->tableName . '` ( ';

        $fields = [];
        $primaryIndexList = [];
        $uniqueIndexList = [];

        foreach ($model->fields as $field) {
            $str = "`$field->name` $field->type";

            if ($field->attr !== false)
                $str .= ' ' . $field->attr . ' ';

            if ($field->type === DBModelField::VARCHAR && $field->length === false)
                $field->length = $db->config->default_varchar_length;

            if ($field->length !== false) {
                if ($field->type === DBModelField::DECIMAL)
                    $str .= '(' . join(",", self::escapeStrArray($field->length)) . ')';
                else
                    $str .= '(' . (is_array($field->length)
                            ? "'" . join("','", self::escapeStrArray($field->length)) . "'"
                            : self::escapeStr($field->length)) . ')';
            }

            $str .= ($field->null === false) ? " NOT NULL" : " NULL";

            if (is_bool($field->default) === true)
                $field->default = intval($field->default);

            if ($field->default !== DBModelField::DEFAULT_NONE)
                $str .= " DEFAULT " . (in_array($field->default, [DBModelField::DEFAULT_NULL, DBModelField::DEFAULT_CURRENT_TIMESTAMP])
                        ? $field->default : "'" . self::escapeStr($field->default) . "'");

            if ($field->auto_increment)
                $str .= ' AUTO_INCREMENT';

            $fields[] = $str;

            if ($field->index === DBModelField::INDEX_PRIMARY)
                $primaryIndexList[] = $field->name;

            if ($field->index === DBModelField::INDEX_UNIQUE)
                $uniqueIndexList[] = "UNIQUE `$field->indexName` (`$field->name`)";
        }

        $query_fields = join(' , ', $fields);

        $query_primary_indexes = (count($primaryIndexList) > 0) ? ', PRIMARY KEY (`' . join('`, `', $primaryIndexList) . '`)' : '';
        $query_unique_indexes = (count($uniqueIndexList) > 0) ? ', ' . join(', ', $uniqueIndexList) : '';

        $query_end = ') ENGINE = ' . $db->config->engine . ';';

        $query = $query_start . $query_fields . $query_primary_indexes . $query_unique_indexes . $query_end;

        try {
            $db->pdo->setAttribute($db->pdo::ATTR_ERRMODE, $db->pdo::ERRMODE_EXCEPTION);
            $db->pdo->exec($query);
            return $response->success("Created `$model->tableName` Table");
        } catch (PDOException $e) {
            return $response->error($e->getMessage(), $query);
        }
    }

    public static function getClassNames($folder)
    {
        $response = new FunctionResponse();

        $modelFolder = Kernel::getProjectPath() . '/src/' . $folder;

        if (file_exists($modelFolder) === false)
            return $response->error("[$folder] Folder not found");

        $modelFiles = array_diff(scandir($modelFolder), array('.', '..'));

        $classNames = [];

        foreach ($modelFiles as $file) {
            $model = str_replace('.php', '', $file);
            $namespace = self::extractNamespaceFromFile($modelFolder . '/' . $file);
            $classNames[] = $namespace . '\\' . $model;
        }

        $response->success("ok", $classNames);

        return $response;
    }

    /**
     * @return FunctionResponse
     */
    public static function getModelClassNames()
    {
        return self::getClassNames('Model');
    }

    /**
     * @return FunctionResponse
     */
    public static function getSeedClassNames()
    {
        return self::getClassNames('Seed');
    }

    /**
     * @param DBHandler $db
     *
     * @return FunctionResponse
     */
    public static function migrate(DBHandler $db)
    {
        $response = new FunctionResponse();

        $modelClassNamesResponse = self::getModelClassNames();

        if ($modelClassNamesResponse->hasError())
            return $modelClassNamesResponse;

        $migrateResponseHasError = false;

        $results = [];

        foreach ($modelClassNamesResponse->result as $className) {
            $migrateResponse = self::migrateClassName($className, $db);
            $results[$className] = $migrateResponse;

            if ($migrateResponse->hasError())
                $migrateResponseHasError = true;
        }

        if ($migrateResponseHasError)
            $response->error('Full Migration Failed', $results);
        else
            $response->success('Full Migration Completed!', $results);

        return $response;
    }

    public static function seed(DBHandler $db)
    {
        $response = new FunctionResponse();

        $seedClassNamesResponse = self::getSeedClassNames();

        if ($seedClassNamesResponse->hasError())
            return $seedClassNamesResponse;

        $seedResponseHasError = false;

        $results = [];

        foreach ($seedClassNamesResponse->result as $className) {
            $seedResponse = self::seedClassName($className, $db);
            $results[$className] = $seedResponse;

            if ($seedResponse->hasError())
                $seedResponseHasError = true;
        }

        if ($seedResponseHasError)
            $response->error('Full Seeding Failed', $results);
        else
            $response->success('Full Seeding Completed!', $results);

        return $response;
    }
}