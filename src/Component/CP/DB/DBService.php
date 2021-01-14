<?php


namespace Copper\Component\CP\DB;


use Copper\Component\DB\DBHandler;
use Copper\Component\DB\DBModel;
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

        // process fields
        $fields = [];
        foreach ($model->fields as $field) {
            $str = "`$field->name` $field->type";

            if ($field->length !== false)
                $str .= "($field->length)";

            $str .= ($field->null === false) ? " NOT NULL" : " NULL";

            if ($field->auto_increment)
                $str .= ' AUTO_INCREMENT';

            $fields[] = $str;
        }

        // process indexes
        $indexes = [];
        foreach ($model->fields as $field) {
            // do nothing
        }

        $query_fields = join(' , ', $fields);

        //`id` SMALLINT NOT NULL AUTO_INCREMENT , `login` VARCHAR(25) NOT NULL , `password` VARCHAR(32) NOT NULL , `role` TINYINT NOT NULL , `email` VARCHAR(50) NOT NULL , PRIMARY KEY (`password`, `id`)) ENGINE = InnoDB;';

        $query_end = ') ENGINE = ' . $db->config->engine . ';';

        $query = $query_start . $query_fields . $query_end;

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

        var_dump($classNames);

        self::migrateClassName($classNames[0], $db);

        return $response;
    }
}