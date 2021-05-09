<?php


namespace Copper\Component\DB;


use Copper\Handler\FileHandler;
use Copper\FunctionResponse;
use Copper\Handler\StringHandler;
use Copper\Kernel;
use PDOException;

class DBService
{
    public static function tableExists($tableName, DBHandler $db)
    {
        try {
            $result = $db->pdo->query("SELECT 1 FROM $tableName LIMIT 1");
        } catch (\Exception $e) {
            return false;
        }

        return ($result !== false);
    }

    public static function tableTruncate($tableName, DBHandler $db)
    {
        $response = new FunctionResponse();

        try {
            $result = $db->pdo->query('TRUNCATE TABLE ' . $tableName . ';')->execute();
            return $response->success("Truncated `$tableName` Table", $result);
        } catch (\Exception $e) {
            return $response->error($e->getMessage());
        }
    }

    public static function tableDelete($tableName, DBHandler $db)
    {
        $response = new FunctionResponse();

        if (self::tableExists($tableName, $db) === false)
            return $response->error('Table does not exists');

        try {
            $result = $db->pdo->query('DROP TABLE ' . $tableName . ';')->execute();
            return $response->success("Deleted `$tableName` Table", $result);
        } catch (\Exception $e) {
            return $response->error($e->getMessage());
        }
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
        $str = str_replace("\\", "\\\\", $str);

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

    /**
     * @param string $className - DBSeed Class Name
     * @param DBHandler $db
     * @var bool $force
     *
     * @return FunctionResponse
     */
    public static function seedClassName(string $className, DBHandler $db, $force = false)
    {
        $response = new FunctionResponse();

        /** @var DBSeed $seed */
        $seed = new $className();
        /** @var DBModel $model */
        $model = new $seed->modelClassName();

        if (count($seed->seeds) === 0)
            return $response->error("Table `$model->tableName` has empty seeds");

        if (self::tableExists($model->tableName, $db) === false)
            return $response->error("Table `$model->tableName` doesn't exist");

        if (self::tableEmpty($model->tableName, $db) === false && $force === false)
            return $response->error("Table `$model->tableName` already seeded and " . '$force' . " flag is not true");

        $fieldNames = '`' . join('`, `', $model->getFieldNames()) . '`';

        $query = 'INSERT INTO ' . $model->tableName . '(' . $fieldNames . ') VALUES ';

        $query_values = [];
        $query_data_values = [];

        foreach ($seed->seeds as $seedValueList) {
            $values = [];

            foreach ($seedValueList as $key => $value) {
                if (is_string($value))
                    $value = "'" . str_replace("'", "''", $value) . "'";

                if ($value === null)
                    $value = 'NULL';

                $values[str_replace('`', '', $key)] = $value;
            }

            $query_data_values[] = $values;
            $query_values[] = '(' . join(', ', $values) . ')';
        }

        $query = $query . join(', ', $query_values);

        try {
            $db->pdo->setAttribute($db->pdo::ATTR_ERRMODE, $db->pdo::ERRMODE_EXCEPTION);
            $db->pdo->exec($query);
            return $response->success("Seeded `$model->tableName` Table", $query);
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();

            $dataTooLong_key = StringHandler::regex($errorMsg, '/Data too long for column \'(.*)\'/m');
            $row = ((int)StringHandler::regex($errorMsg, '/at row (\d)/m')) - 1;

            if ($dataTooLong_key !== false)
                $errorMsg .= ' | Current Length for [' . $dataTooLong_key . ']: ' . strlen($query_data_values[$row][$dataTooLong_key]);

            return $response->error($errorMsg, $query);
        }
    }

    public static function prepareFieldStatementForDB(DBModelField $field)
    {
        $str = '`' . $field->getName() . '` ' . $field->getType();

        if ($field->getLength() !== false) {
            if ($field->getType() === DBModelField::DECIMAL)
                $str .= '(' . join(",", self::escapeStrArray($field->getLength())) . ')';
            else
                $str .= '(' . (is_array($field->getLength())
                        ? "'" . join("','", self::escapeStrArray($field->getLength())) . "'"
                        : self::escapeStr($field->getLength())) . ')';
        }

        if ($field->getAttr() !== false)
            $str .= ' ' . $field->getAttr() . ' ';

        $str .= ($field->getNull() === false) ? " NOT NULL" : " NULL";

        if (is_bool($field->getDefault()) === true)
            $field->default(intval($field->getDefault()));

        if ($field->getDefault() !== DBModelField::DEFAULT_NONE)
            $str .= " DEFAULT " . (in_array($field->getDefault(), [DBModelField::DEFAULT_NULL, DBModelField::DEFAULT_CURRENT_TIMESTAMP])
                    ? $field->getDefault() : "'" . self::escapeStr($field->getDefault()) . "'");

        if ($field->getAutoIncrement())
            $str .= ' AUTO_INCREMENT';

        return $str;
    }

    /**
     * @param string $className - DBModel Class Name
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
            $fields[] = self::prepareFieldStatementForDB($field);

            if ($field->getIndex() === DBModelField::INDEX_PRIMARY)
                $primaryIndexList[] = $field->getName();

            if ($field->getIndex() === DBModelField::INDEX_UNIQUE)
                $uniqueIndexList[] = 'UNIQUE `' . $field->getIndexName() . '` (`' . $field->getName() . '`)';
        }

        $query_fields = join(' , ', $fields);

        $query_primary_indexes = (count($primaryIndexList) > 0) ? ', PRIMARY KEY (`' . join('`, `', $primaryIndexList) . '`)' : '';
        $query_unique_indexes = (count($uniqueIndexList) > 0) ? ', ' . join(', ', $uniqueIndexList) : '';

        $query_end = ') ENGINE = ' . $db->config->engine . ';';

        $query = $query_start . $query_fields . $query_primary_indexes . $query_unique_indexes . $query_end;

        try {
            $db->pdo->setAttribute($db->pdo::ATTR_ERRMODE, $db->pdo::ERRMODE_EXCEPTION);
            $db->pdo->exec($query);
            return $response->success("Created `$model->tableName` Table", $query);
        } catch (PDOException $e) {
            return $response->error($e->getMessage(), $query);
        }
    }

    public static function getClassNames($folder)
    {
        $folderPath = Kernel::getAppPath() . '/src/' . $folder;

        return FileHandler::getClassNamesInFolder($folderPath);
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