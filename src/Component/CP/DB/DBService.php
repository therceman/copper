<?php


namespace Copper\Component\CP\DB;


use Copper\Component\DB\DBHandler;
use Copper\Component\DB\DBModel;
use Copper\Component\DB\DBModelField;
use Copper\Kernel;

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

    public static function migrateClassName(string $className, DBHandler $db)
    {
        /** @var DBModel $model */
        $model = new $className();

        $query_start = 'CREATE ' . 'TABLE `' . $db->config->dbname . '`.`' . $model->tableName . '` ( ';

        $fields = [];
        $indexes = [DBModelField::INDEX_PRIMARY => [], DBModelField::INDEX_UNIQUE => []];

        foreach ($model->fields as $field) {
            $str = "`$field->name` $field->type";

            if ($field->attr !== false)
                $str .= ' ' . $field->attr . ' ';

            if ($field->length !== false)
                $str .= '(' . (is_array($field->length) ? "'" . join("','", $field->length) . "'" : $field->length) . ')';

            $str .= ($field->null === false) ? " NOT NULL" : " NULL";

            if ($field->default !== DBModelField::DEFAULT_NONE)
                $str .= " DEFAULT " . (in_array($field->default, [DBModelField::DEFAULT_NULL, DBModelField::DEFAULT_CURRENT_TIMESTAMP])
                        ? $field->default : "'$field->default'");

            if ($field->auto_increment)
                $str .= ' AUTO_INCREMENT';

            $fields[] = $str;

            if ($field->index !== false)
                $indexes[$field->index][] = $field->name;
        }

        $query_fields = join(' , ', $fields);

        $query_index_list = [];
        foreach ($indexes as $indexName => $indexList) {
            if (count($indexList) > 0)
                $query_index_list[] = $indexName . ' (`' . join('`, `', $indexList) . '`)';
        }
        $query_indexes = (count($query_index_list) > 0) ? ', ' . join(' , ', $query_index_list) : '';

        $query_end = ') ENGINE = ' . $db->config->engine . ';';

        $query = $query_start . $query_fields . $query_indexes . $query_end;

        var_dump($query);
    }

    public static function migrate(DBHandler $db)
    {
        $response = ["status" => false, "msg" => "Something Went Wrong"];

        $modelFolder = Kernel::getProjectPath() . '/src/Model';

        if (file_exists($modelFolder) === false)
            $response['msg'] = 'Model Folder not found';

        $modelFiles = array_diff(scandir($modelFolder), array('.', '..'));

        $classNames = [];

        foreach ($modelFiles as $file) {
            $model = str_replace('.php', '', $file);
            $namespace = self::extractNamespaceFromFile($modelFolder . '/' . $file);
            $classNames[] = $namespace . '\\' . $model;
        }

        self::migrateClassName($classNames[0], $db);

        return $response;
    }
}