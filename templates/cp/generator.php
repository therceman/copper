<?php /** @var \Copper\Component\Templating\ViewHandler $view */

use Copper\Component\CP\CPController;
use Copper\Component\DB\DBModel;
use Copper\Component\HTML\HTML;
use Copper\Component\HTML\HTMLGroup;
use Copper\Handler\ArrayHandler;
use Copper\Resource\AbstractResource;
use Symfony\Component\Routing\Route;

// TODO when field is renamed and type is changed (old field should be removed if has data (maybe throw warning if some rows has data in old column)
// -- For example I have changed lang (VARCHAR 2) to language_id (TINYINT) and lang field was not renamed to language_id (because of type mismatch of old data)
// TODO ? Update default values for all rows (when changed default value)
// TODO validation type for field
// TODO rename resource
// TODO add new route
// TODO remove route

// TODO migrate when creating new resource

$default_varchar_length = $view->dataBag->get('default_varchar_length', 255);
$max_varchar_length = $view->dataBag->get('max_varchar_length', 2500);

/** @var AbstractResource[] $resource_list */
$resource_list = $view->dataBag->get('resource_list', []);

/** @var array $route_list */
$route_list = $view->dataBag->get('$route_list', []);

/** @var array $route_action_list */
$route_action_list = $view->dataBag->get('$route_action_list', []);

/** @var string $route_group */
$route_group = $view->dataBag->get('$route_group', null);

/** @var AbstractResource $resource */
$resource = $view->dataBag->get('resource', null);

$db_column_list = $view->dataBag->get('$db_column_list', []);

$demo = $view->queryBag->get('demo', false);

/** @var DBModel $model */
$model = null;

$resourceName = '';
$tableName = '';
$entityName = '';
$modelName = '';
$serviceName = '';
$controllerName = '';
$seedName = '';

if ($resource !== null) {
    $model = $resource::getModel();
    $resourceName = str_replace('App\\Resource\\', '', $resource);
    $tableName = $model->getTableName();
    $entityName = str_replace('App\\Entity\\', '', $resource::getEntityClassName());
    $modelName = str_replace('App\\Model\\', '', $resource::getModelClassName());
    $serviceName = str_replace('App\\Service\\', '', $resource::getServiceClassName());
    $controllerName = str_replace('App\\Controller\\', '', $resource::getControllerClassName());
    $seedName = str_replace('App\\Seed\\', '', $resource::getSeedClassName());
}

?>

<?= $view->render('cp/header', ['title' => 'Copper Panel :: Resource Generator']) ?>


<script>
    // ============== TYPE ==============

    // ------- Numeric -------

    /** A 1-byte integer, signed range is -128 to 127, unsigned range is 0 to 255 */
    const TINYINT = 'TINYINT';

    /** A 2-byte integer, signed range is -32,768 to 32,767, unsigned range is 0 to 65,535 */
    const SMALLINT = 'SMALLINT';

    /** A 3-byte integer, signed range is -8,388,608 to 8,388,607, unsigned range is 0 to 16,777,215 */
    const MEDIUMINT = 'MEDIUMINT';

    /** A 4-byte integer, signed range is -2,147,483,648 to 2,147,483,647, unsigned range is 0 to 4,294,967,295 */
    const INT = 'INT';

    /** An 8-byte integer, signed range is -9,223,372,036,854,775,808 to 9,223,372,036,854,775,807, unsigned range is 0 to 18,446,744,073,709,551,615 */
    const BIGINT = 'BIGINT';

    /** A fixed-point number (M, D) - the maximum number of digits (M) is 65 (default 10), the maximum number of decimals (D) is 30 (default 0) */
    const DECIMAL = 'DECIMAL';

    // /** A small floating-point number, allowable values are -3.402823466E+38 to -1.175494351E-38, 0, and 1.175494351E-38 to 3.402823466E+38 */
    // const FLOAT = 'FLOAT';
    //
    // /** A double-precision floating-point number, allowable values are -1.7976931348623157E+308 to -2.2250738585072014E-308, 0, and 2.2250738585072014E-308 to 1.7976931348623157E+308 */
    // const DOUBLE = 'DOUBLE';
    //
    // /** Synonym for DOUBLE (exception: in REAL_AS_FLOAT SQL mode it is a synonym for FLOAT) */
    // const REAL = 'REAL';
    //
    // /** A bit-field type (M), storing M of bits per value (default is 1, maximum is 64) */
    // const BIT = 'BIT';

    /** A synonym for TINYINT(1), a value of zero is considered false, nonzero values are considered true */
    const BOOLEAN = 'BOOLEAN';

    // /** An alias for BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE */
    // const SERIAL = 'SERIAL';

    // ------- Date and time -------

    /** A date, supported range is 1000-01-01 to 9999-12-31 */
    const DATE = 'DATE';

    /** A date and time combination, supported range is 1000-01-01 00:00:00 to 9999-12-31 23:59:59 */
    const DATETIME = 'DATETIME';

    // /** A timestamp, range is 1970-01-01 00:00:01 UTC to 2038-01-09 03:14:07 UTC, stored as the number of seconds since the epoch (1970-01-01 00:00:00 UTC) */
    // const TIMESTAMP = 'TIMESTAMP';

    /** A time, range is -838:59:59 to 838:59:59 */
    const TIME = 'TIME';

    /** A year in four-digit (4, default) the allowable values are 1901 to 2155 and 0000 */
    const YEAR = 'YEAR';

    // ------- String -------

    // /** A fixed-length (0-255, default 1) string that is always right-padded with spaces to the specified length when stored */
    // const CHAR = 'CHAR';

    /** A variable-length (0-65,535) string, the effective maximum length is subject to the maximum row size */
    const VARCHAR = 'VARCHAR';

    /** A TEXT column with a maximum length of 255 (2^8 - 1) characters, stored with a one-byte prefix indicating the length of the value in bytes */
    // const TINYTEXT = 'TINYTEXT';

    /** A TEXT column with a maximum length of 65,535 (2^16 - 1) characters, stored with a two-byte prefix indicating the length of the value in bytes */
    const TEXT = 'TEXT';

    /** A TEXT column with a maximum length of 16,777,215 (2^24 - 1) characters, stored with a three-byte prefix indicating the length of the value in bytes */
    const MEDIUMTEXT = 'MEDIUMTEXT';

    /** A TEXT column with a maximum length of 4,294,967,295 or 4GiB (2^32 - 1) characters, stored with a four-byte prefix indicating the length of the value in bytes */
    const LONGTEXT = 'LONGTEXT';

    // /** Similar to the CHAR type, but stores binary byte strings rather than non-binary character strings */
    // const BINARY = 'BINARY';
    //
    // /** Similar to the VARCHAR type, but stores binary byte strings rather than non-binary character strings */
    // const VARBINARY = 'VARBINARY';
    //
    // /** A BLOB column with a maximum length of 255 (2^8 - 1) bytes, stored with a one-byte prefix indicating the length of the value */
    // const TINYBLOB = 'TINYBLOB';
    //
    // /** A BLOB column with a maximum length of 65,535 (2^16 - 1) bytes, stored with a two-byte prefix indicating the length of the value */
    // const BLOB = 'BLOB';
    //
    // /** A BLOB column with a maximum length of 16,777,215 (2^24 - 1) bytes, stored with a three-byte prefix indicating the length of the value */
    // const MEDIUMBLOB = 'MEDIUMBLOB';
    //
    // /** A BLOB column with a maximum length of 4,294,967,295 or 4GiB (2^32 - 1) bytes, stored with a four-byte prefix indicating the length of the value */
    // const LONGBLOB = 'LONGBLOB';

    /** An enumeration, chosen from the list of up to 65,535 values or the special '' error value */
    const ENUM = 'ENUM';

    // /** A single value chosen from a set of up to 64 members */
    // const SET = 'SET';

    // // ------- Spatial -------
    //
    // /** A type that can store a geometry of any type */
    // const GEOMETRY = 'GEOMETRY';
    //
    // /** A point in 2-dimensional space */
    // const POINT = 'POINT';
    //
    // /** A curve with linear interpolation between points */
    // const LINESTRING = 'LINESTRING';
    //
    // /** A polygon */
    // const POLYGON = 'POLYGON';
    //
    // /** A collection of points */
    // const MULTIPOINT = 'MULTIPOINT';
    //
    // /** A collection of curves with linear interpolation between points */
    // const MULTILINESTRING = 'MULTILINESTRING';
    //
    // /** A collection of polygons */
    // const MULTIPOLYGON = 'MULTIPOLYGON';
    //
    // /** A collection of geometry objects of any type */
    // const GEOMETRYCOLLECTION = 'GEOMETRYCOLLECTION';

    // ------- JSON -------

    /** Stores and enables efficient access to data in JSON (JavaScript Object Notation) documents */
        // const JSON = 'JSON';

        // ============== Default ==============

    const DEFAULT_NONE = 'NONE';
    const DEFAULT_NULL = 'NULL';
    const DEFAULT_CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';

    // ============== Attributes ==============

    // const ATTR_BINARY = 'BINARY';
    // /** Unsigned type can be used to permit only nonnegative numbers in a column or when you need a larger upper numeric range for the column. e.g. signed -127 to 127 , unsigned 0 to 255 */
    const ATTR_UNSIGNED = 'UNSIGNED';
    // /** Pads the displayed value of the field with zeros up to the display width specified in the column definition (Type), e.g. INT(8) will fill up to 7 zeros - 00000001 */
    // const ATTR_UNSIGNED_ZEROFILL = 'UNSIGNED ZEROFILL';
    /** Updates field value to current timestamp when on UPDATE */
    const ATTR_ON_UPDATE_CURRENT_TIMESTAMP = 'on update CURRENT_TIMESTAMP';

    // ============== Index ==============

    const INDEX_PRIMARY = 'PRIMARY';
    const INDEX_UNIQUE = 'UNIQUE';
</script>

<style>
    table {
        border-collapse: collapse;
        width: 1400px;
        font-size: 14px;
    }

    table td {
        border: 1px solid black;
        text-align: center;
        padding: 2px;
    }

    table td select.enum {
        width: 163px;
    }

    table tr:hover, table tr.selected {
        background: lightyellow;
    }

    table tr.del {
        background: red;
    }

    table tr.new {
        background: lightblue !important;
    }

    table tr.updated {
        background: lightgreen;
    }

    table td.changed {
        border-bottom: 3px solid #333;
    }

    #legend td {
        width: 52px;
    }

    #legend table {
        width: 335px;
    }

    #fields_size td {
        height: 0;
        padding: 0;
        margin: 0;
    }

    #table_container {
        max-height: 800px;
        width: 1414px;
        overflow-y: scroll;
        overflow-x: hidden;
        border-bottom: 1px solid #ccc;
        border-top: 1px solid #ccc;
        margin-top: -1px;
    }

    table tr.not_in_db {
        background: #f2f2f2;
    }

    body {
        width: 1400px;
    }

    input[type=checkbox] + span {
        margin-left: 5px;
    }

    .hidden {
        display: none;
    }

    #names input {
        width: 165px;
    }

    span.help {
        font-size: 10px;
    }

    #delete_res {
        background: #ffd9d9;
        border: 1px solid #767676;
        border-radius: 2px;
        color: #000;
        height: 21px;
    }

    #delete_popup {
        position: absolute;
        background: #fff;
        padding: 14px 28px;
        border: 1px solid #ccc;
    }


</style>


<style>
    #fields td {
        max-width: 190px;
    }
</style>

<body class="markdown-body">
<h4>Resource Generator</h4>

<?php if ($view->flashMessage->has('seed_result')): ?>
    <div style="border: 1px solid #ccc; padding: 10px; margin-bottom:15px; border-radius: 5px;">
        <span>Seed Result:</span>
        <code><?= $view->out($view->flashMessage->get('seed_result')) ?></code>
    </div>
<?php endif; ?>

<?php if ($view->flashMessage->has('migrate_result')): ?>
    <div style="border: 1px solid #ccc; padding: 10px; margin-bottom:15px; border-radius: 5px;">
        <span>Migrate Result:</span>
        <code><?= $view->out($view->flashMessage->get('migrate_result')) ?></code>
    </div>
<?php endif; ?>

<style>
    #edit_routes_popup {
        background: #fff;
        border: 1px solid #ccc;
        box-shadow: 1px 2px 5px -2px black;
        width: 805px;
        position: absolute;
        left: 347px;
        padding: 10px;
        min-height: 300px;
        top: 110px;
    }

    #edit_routes_popup h4 {
        margin: 5px;
        border-bottom: 1px solid #ccc;
        padding-bottom: 5px;
    }

    #edit_routes_popup .close {
        font-size: 20px;
        color: #333;
        position: absolute;
        right: 14px;
        top: 7px;
        cursor: pointer;
    }

    #edit_routes_popup .path, #edit_routes_popup .action {
        width: 314px;
    }

    #edit_routes_popup .routes_collection {
        padding: 10px;
        padding-bottom: 0px;
    }

    #edit_routes_popup .routes_collection ul {
        padding: 0 15px;
    }

    #edit_routes_popup .delete {
        height: 23px;
        margin-top: 3px;
        margin-bottom: 0px;
        display: inline-block;
    }

    #edit_routes_popup h5 {
        margin-top: 0;
        padding: 0px 5px;
        padding-bottom: 5px;
        margin-bottom: 10px;
        border-bottom: 1px dashed #ccc;
    }

    #edit_routes_popup .controls {
        text-align: center;
    }

    #edit_routes_popup .routes_collection .info div {
        display: inline-block;
        margin-right: 5px;
    }

    #edit_routes_popup .routes_collection .info .group input {
        width: 240px;
    }

    #edit_routes_popup .routes_collection .info .controller input {
        width: 383px;
    }

    #edit_routes_popup .action_select {
        width: 330px;
        margin-top: 10px;
        height: 21px;
    }

    #edit_routes_popup .update_controls {
        text-align: right;
        padding-right: 20px;
        padding-top: 5px;
    }

</style>

<?php if ($resource !== null): ?>
    <?= $view->render('cp/component/routes_editor', [
        '$route_action_list' => $route_action_list,
        '$route_group' => $route_group,
        '$route_list' => $route_list,
        '$resource' => $resource
    ]) ?>
<?php endif; ?>

<div style="margin-bottom:10px;">
    <div style="margin-bottom: 5px;">
        <span>Resource</span>
        <?= HTML::input()->id('resource')->value($resourceName)->placeholder('Resource Name')->autofocus() ?>
        <span class="help">Hit [Enter] after input</span>
        <div style="float:right">
            <button id="delete_res">Delete</button>
            <div id="delete_popup" class="hidden">
                <p>Files To Delete: </p>
                <?= HTML::checkboxGroup('Resource', false, null, 'delete_resource') ?>
                <?= HTML::checkboxGroup('Entity', false, null, 'delete_entity') ?>
                <?= HTML::checkboxGroup('Model', false, null, 'delete_model') ?>
                <?= HTML::checkboxGroup('Service', false, null, 'delete_service') ?>
                <?= HTML::checkboxGroup('Controller', false, null, 'delete_controller') ?>
                <?= HTML::checkboxGroup('Seed', false, null, 'delete_seed') ?>
                <?= HTML::checkboxGroup('DB Table', false, null, 'delete_table') ?>
                <div style="text-align: right;margin-top: 15px">
                    <button id="delete_confirm">Confirm</button>
                    <button id="delete_cancel" style="float: left">Cancel</button>
                </div>
            </div>
            <?php
            echo HTML::formGet($view->url(ROUTE_copper_cp_action, ['action' => CPController::ACTION_DB_GENERATOR]))
                ->addStyle('display', 'inline-block')
                ->addElement(HTML::select($resource_list, 'resource', $resource))
                ->addElement(HTML::button('Read'));
            ?>
            <?= HTML::button('Edit Routes')->onClick('edit_routes()')->disabled($resource === null); ?>
            <?php
            echo HTML::form($view->url(ROUTE_copper_cp_action, ['action' => CPController::ACTION_DB_GENERATOR]))
                ->id('migrate_form')->addStyle('display', 'inline-block')
                ->addElement(HTML::inputHidden('migrate', 1))
                ->addElement(HTML::inputHidden('migrate_force', false)->idAsName())
                ->addElement(HTML::inputHidden('resource', $resource))
                ->addElement(HTML::button('Migrate')->setAttr('type', 'submit')->id('migrate_btn'));
            ?>
            <?php
            echo HTML::form($view->url(ROUTE_copper_cp_action, ['action' => CPController::ACTION_DB_GENERATOR]))
                ->id('seed_form')->addStyle('display', 'inline-block')
                ->addElement(HTML::inputHidden('seed', 1))
                ->addElement(HTML::inputHidden('seed_force', false)->idAsName())
                ->addElement(HTML::inputHidden('resource', $resource))
                ->addElement(HTML::button('Seed')->setAttr('type', 'submit')->id('seed_btn'));
            ?>
            <?php
            echo HTML::formGet($view->url(ROUTE_copper_cp_action, ['action' => CPController::ACTION_DB_GENERATOR]))
                ->addStyle('display', 'inline-block')->addStyle('margin-left', '20px')
                ->addElement(HTML::button('Clear'));
            ?>
            <?php
            echo HTML::formGet($view->url(ROUTE_copper_cp_action, ['action' => CPController::ACTION_DB_GENERATOR]))
                ->addStyle('display', 'inline-block')
                ->addElement(HTML::inputHidden('demo', 1))
                ->addElement(HTML::inputHidden('resource', $resource))
                ->addElement(HTML::button('Demo'));
            ?>
        </div>
    </div>

    <div style="float:left;margin-top: 5px;">
        <input type="checkbox" id="use_state_fields" checked="checked">
        <label for="use_state_fields"
               title="State Fields Are: [created_at, update_at, removed_at, enabled]. This fields will be auto created for Model & Entity.">
            Use State Fields
        </label>
        <input type="checkbox" id="relation"><span>Is Relation Table ?</span>
    </div>
    <div style="float:right; margin-top: 5px;">
        <input type="checkbox" id="int_auto_unsigned" checked="checked"><span>INT type fields are auto UNSIGNED</span>
        <input type="checkbox" id="new_fields_null" checked="checked"><span>New fields are Null by default</span>
    </div>
    <div style="clear: both"></div>
    <div style="margin-top: 10px;">
        <span>Files To Create: </span>
        <?= HTML::checkboxGroup('Resource', ($resourceName !== ''), null, 'create_resource') ?>
        <?= HTML::checkboxGroup('Entity', ($entityName !== ''), null, 'create_entity') ?>
        <?= HTML::checkboxGroup('Model', ($modelName !== ''), null, 'create_model') ?>
        <?= HTML::checkboxGroup('Service', ($serviceName !== ''), null, 'create_service') ?>
        <?= HTML::checkboxGroup('Controller', ($controllerName !== ''), null, 'create_controller') ?>
        <?= HTML::checkboxGroup('Seed', ($seedName !== ''), null, 'create_seed') ?>
    </div>
    <div style="margin-top: -20px; float:right">
        <span>Files To Override: </span>
        <input type="checkbox" id="resource_override"><label for="resource_override">Resource</label>
        <input type="checkbox" id="entity_override"><label for="entity_override">Entity</label>
        <input type="checkbox" id="model_override"><label for="model_override">Model</label>
        <input type="checkbox" id="service_override"><label for="service_override">Service</label>
        <input type="checkbox" id="controller_override"><label for="controller_override">Controller</label>
        <input type="checkbox" id="seed_override"><label for="seed_override">Seed</label>
    </div>
</div>


<div style="margin-bottom:10px;" id="names">
    <span>Table:</span> <?= HTML::input()->id('table')->value($tableName)->placeholder('Table name') ?>
    <span>Entity:</span> <?= HTML::input()->id('entity')->value($entityName)->placeholder('Entity name') ?>
    <span>Model:</span> <?= HTML::input()->id('model')->value($modelName)->placeholder('Model name') ?>
    <span>Service:</span> <?= HTML::input()->id('service')->value($serviceName)->placeholder('Service name') ?>
    <span>Controller:</span> <?= HTML::input()->id('controller')->value($controllerName)->placeholder('Controller name') ?>
    <span>Seed:</span> <?= HTML::input()->id('seed')->value($seedName)->placeholder('Seed name') ?>
</div>

<div id="talbe_header">
    <table class="controls">
        <thead>
        <tr>
            <td>Name</td>
            <td>Type</td>
            <td>Length</td>
            <td>Default</td>
            <td>Null</td>
            <td style="width: 245px;">Attributes</td>
            <td>Index</td>
            <td>Auto Increment</td>
            <td style="width: 80px;">DB</td>
            <td style="width: 115px;">Action</td>
        </tr>
        <tr>
            <td>
                <input id="name">
            </td>
            <td>
                <select id="type" style="width: 125px;">
                    <option title="A 2-byte integer, signed range is -32,768 to 32,767, unsigned range is 0 to 65,535">
                        SMALLINT
                    </option>
                    <option title="A fixed-point number (M, D) - the maximum number of digits (M) is 65 (default 10), the maximum number of decimals (D) is 30 (default 0)">
                        DECIMAL
                    </option>
                    <option title="A variable-length (0-65,535) string, the effective maximum length is subject to the maximum row size">
                        VARCHAR
                    </option>
                    <option title="A synonym for TINYINT(1), a value of zero is considered false, nonzero values are considered true">
                        BOOLEAN
                    </option>
                    <option title="A TEXT column with a maximum length of 65,535 (2^16 - 1) characters, stored with a two-byte prefix indicating the length of the value in bytes">
                        TEXT
                    </option>
                    <option title="A date, supported range is 1000-01-01 to 9999-12-31">
                        DATE
                    </option>
                    <option title="An enumeration, chosen from the list of up to 65,535 values or the special '' error value">
                        ENUM
                    </option>
                    <optgroup label="Numeric">
                        <option title="A 1-byte integer, signed range is -128 to 127, unsigned range is 0 to 255">
                            TINYINT
                        </option>
                        <option title="A 2-byte integer, signed range is -32,768 to 32,767, unsigned range is 0 to 65,535">
                            SMALLINT
                        </option>
                        <option title="A 3-byte integer, signed range is -8,388,608 to 8,388,607, unsigned range is 0 to 16,777,215">
                            MEDIUMINT
                        </option>
                        <option title="A 4-byte integer, signed range is -2,147,483,648 to 2,147,483,647, unsigned range is 0 to 4,294,967,295">
                            INT
                        </option>
                        <option title="An 8-byte integer, signed range is -9,223,372,036,854,775,808 to 9,223,372,036,854,775,807, unsigned range is 0 to 18,446,744,073,709,551,615">
                            BIGINT
                        </option>
                        <option disabled="disabled">-</option>
                        <option title="A fixed-point number (M, D) - the maximum number of digits (M) is 65 (default 10), the maximum number of decimals (D) is 30 (default 0)">
                            DECIMAL
                        </option>
                        <!--                    <option title="A small floating-point number, allowable values are -3.402823466E+38 to -1.175494351E-38, 0, and 1.175494351E-38 to 3.402823466E+38">-->
                        <!--                        FLOAT-->
                        <!--                    </option>-->
                        <!--                    <option title="A double-precision floating-point number, allowable values are -1.7976931348623157E+308 to -2.2250738585072014E-308, 0, and 2.2250738585072014E-308 to 1.7976931348623157E+308">-->
                        <!--                        DOUBLE-->
                        <!--                    </option>-->
                        <!--                    <option title="Synonym for DOUBLE (exception: in REAL_AS_FLOAT SQL mode it is a synonym for FLOAT)">-->
                        <!--                        REAL-->
                        <!--                    </option>-->
                        <!--                    <option disabled="disabled">-</option>-->
                        <!--                    <option title="A bit-field type (M), storing M of bits per value (default is 1, maximum is 64)">-->
                        <!--                        BIT-->
                        <!--                    </option>-->
                        <option title="A synonym for TINYINT(1), a value of zero is considered false, nonzero values are considered true">
                            BOOLEAN
                        </option>
                        <!--                    <option title="An alias for BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE">SERIAL</option>-->
                    </optgroup>
                    <optgroup label="Date and time">
                        <option title="A date, supported range is 1000-01-01 to 9999-12-31">DATE</option>
                        <option title="A date and time combination, supported range is 1000-01-01 00:00:00 to 9999-12-31 23:59:59">
                            DATETIME
                        </option>
                        <!--                    <option title="A timestamp, range is 1970-01-01 00:00:01 UTC to 2038-01-09 03:14:07 UTC, stored as the number of seconds since the epoch (1970-01-01 00:00:00 UTC)">-->
                        <!--                        TIMESTAMP-->
                        <!--                    </option>-->
                        <option title="A time, range is -838:59:59 to 838:59:59">TIME</option>
                        <option title="A year in four-digit (4, default) the allowable values are 1901 to 2155 and 0000">
                            YEAR
                        </option>
                    </optgroup>
                    <optgroup label="String">
                        <!--                    <option title="A fixed-length (0-255, default 1) string that is always right-padded with spaces to the specified length when stored">-->
                        <!--                        CHAR-->
                        <!--                    </option>-->
                        <option title="A variable-length (0-65,535) string, the effective maximum length is subject to the maximum row size">
                            VARCHAR
                        </option>
                        <option disabled="disabled">-</option>
                        <!--                    <option title="A TEXT column with a maximum length of 255 (2^8 - 1) characters, stored with a one-byte prefix indicating the length of the value in bytes">-->
                        <!--                        TINYTEXT-->
                        <!--                    </option>-->
                        <option title="A TEXT column with a maximum length of 65,535 (2^16 - 1) characters, stored with a two-byte prefix indicating the length of the value in bytes">
                            TEXT
                        </option>
                        <option title="A TEXT column with a maximum length of 16,777,215 (2^24 - 1) characters, stored with a three-byte prefix indicating the length of the value in bytes">
                            MEDIUMTEXT
                        </option>
                        <option title="A TEXT column with a maximum length of 4,294,967,295 or 4GiB (2^32 - 1) characters, stored with a four-byte prefix indicating the length of the value in bytes">
                            LONGTEXT
                        </option>
                        <!--                    <option disabled="disabled">-</option>-->
                        <!--                    <option title="Similar to the CHAR type, but stores binary byte strings rather than non-binary character strings">-->
                        <!--                        BINARY-->
                        <!--                    </option>-->
                        <!--                    <option title="Similar to the VARCHAR type, but stores binary byte strings rather than non-binary character strings">-->
                        <!--                        VARBINARY-->
                        <!--                    </option>-->
                        <!--                    <option disabled="disabled">-</option>-->
                        <!--                    <option title="A BLOB column with a maximum length of 255 (2^8 - 1) bytes, stored with a one-byte prefix indicating the length of the value">-->
                        <!--                        TINYBLOB-->
                        <!--                    </option>-->
                        <!--                    <option title="A BLOB column with a maximum length of 65,535 (2^16 - 1) bytes, stored with a two-byte prefix indicating the length of the value">-->
                        <!--                        BLOB-->
                        <!--                    </option>-->
                        <!--                    <option title="A BLOB column with a maximum length of 16,777,215 (2^24 - 1) bytes, stored with a three-byte prefix indicating the length of the value">-->
                        <!--                        MEDIUMBLOB-->
                        <!--                    </option>-->
                        <!--                    <option title="A BLOB column with a maximum length of 4,294,967,295 or 4GiB (2^32 - 1) bytes, stored with a four-byte prefix indicating the length of the value">-->
                        <!--                        LONGBLOB-->
                        <!--                    </option>-->
                        <option disabled="disabled">-</option>
                        <option title="An enumeration, chosen from the list of up to 65,535 values or the special '' error value">
                            ENUM
                        </option>
                        <!--                    <option title="A single value chosen from a set of up to 64 members">SET</option>-->
                    </optgroup>
                    <!--                <optgroup label="Spatial">-->
                    <!--                    <option title="A type that can store a geometry of any type">GEOMETRY</option>-->
                    <!--                    <option title="A point in 2-dimensional space">POINT</option>-->
                    <!--                    <option title="A curve with linear interpolation between points">LINESTRING</option>-->
                    <!--                    <option title="A polygon">POLYGON</option>-->
                    <!--                    <option title="A collection of points">MULTIPOINT</option>-->
                    <!--                    <option title="A collection of curves with linear interpolation between points">MULTILINESTRING-->
                    <!--                    </option>-->
                    <!--                    <option title="A collection of polygons">MULTIPOLYGON</option>-->
                    <!--                    <option title="A collection of geometry objects of any type">GEOMETRYCOLLECTION</option>-->
                    <!--                </optgroup>-->
                    <!--                <optgroup label="JSON">-->
                    <!--                    <option title="Stores and enables efficient access to data in JSON (JavaScript Object Notation) documents">-->
                    <!--                        JSON-->
                    <!--                    </option>-->
                    <!--                </optgroup>-->
                </select>
            </td>
            <td>
                <input id="length" type="number" style="width: 150px;">
            </td>
            <td>
                <input type="text" id="default_value" class="hidden" style="width: 134px;">
                <select id="default">
                    <option value="NONE" selected="selected">
                        None
                    </option>
                    <option value="USER_DEFINED">
                        As defined:
                    </option>
                    <option value="NULL">
                        NULL
                    </option>
                    <option value="CURRENT_TIMESTAMP">
                        CURRENT_TIMESTAMP
                    </option>
                </select>
                <button class=hidden id="cancel_default_value">X</button>
            </td>
            <td>
                <input type="checkbox" id="null">
            </td>
            <td>
                <select id="attributes">
                    <option value=""></option>
                    <!--                <option value="BINARY">-->
                    <!--                    BINARY-->
                    <!--                </option>-->
                    <option value="UNSIGNED">
                        UNSIGNED
                    </option>
                    <!--                <option value="UNSIGNED ZEROFILL">-->
                    <!--                    UNSIGNED ZEROFILL-->
                    <!--                </option>-->
                    <option value="on update CURRENT_TIMESTAMP">
                        on update CURRENT_TIMESTAMP
                    </option>
                </select>
            </td>
            <td>
                <select id="index">
                    <option></option>
                    <option value="PRIMARY">
                        PRIMARY
                    </option>
                    <option value="UNIQUE">
                        UNIQUE
                    </option>
                </select>
            </td>
            <td>
                <input type="checkbox" id="auto_increment">
            </td>
            <td></td>
            <td>
                <button id="add">ADD</button>
                <button class="hidden" id="update" onclick="updateSelectedField()">✓</button>
                <button class="hidden" id="up" onclick="moveUpSelectedField()">↑</button>
                <button class="hidden" id="down" onclick="moveDownSelectedField()">↓</button>
                <button class="hidden" id="cancel" onclick="cancelFieldEdit()">X</button>
            </td>
        </tr>
        </thead>
    </table>
</div>
<div id="table_container">
    <table id=fields>
        <thead>
        <tr id=fields_size style="height: 1px">
            <th style="width: 187px;"></th>
            <th style="width: 133px;"></th>
            <th style="width: 167px;"></th>
            <th style="width: 183px;"></th>
            <th style="width: 31px;"></th>
            <th style="width: 240px;"></th>
            <th style="width: 89px;"></th>
            <th style="width: 110px;"></th>
            <th style="width: 80px;"></th>
            <th style="width: 113px;"></th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
<div id="legend" style="margin-top: 10px;margin-bottom:10px;">
    <table style="font-size: 11px;">
        <tr>
            <td style="background: lightblue;width: 10px;"></td>
            <td style="width: 22px;">New</td>
            <td style="background: red;width: 10px;"></td>
            <td style="width: 40px;">To be deleted</td>
            <td style="background: lightgreen;width: 10px;"></td>
            <td style="width: 40px;">To be updated</td>
        </tr>
    </table>
</div>

<div style="float:right;margin-top: -35px;">
    <button id="create_js_source_files">Create Javascript Source Files</button>
    <button id="prepare_templates">Prepare Templates</button>
    <button id="generate">Generate</button>
</div>

<form method="post" action="<?= $view->url(ROUTE_copper_cp_action, ['action' => CPController::ACTION_LOGOUT]) ?>">
    <button type="submit">Logout</button>
</form>

<script>
    const DEFAULT_USER_DEFINED = 'USER_DEFINED';
</script>

<script>
    class Field {
        name;
        type;
        length;
        default;
        null;
        attr;
        index;
        auto_increment;

        orig_name;
        exists_in_db;

        constructor(name, type, length, def, attr, isNull, index, auto_increment) {
            this.name = name;
            this.type = type;
            this.length = length;
            this.default = def;
            this.null = isNull;
            this.attr = attr;
            this.index = index;
            this.auto_increment = auto_increment;
        }
    }
</script>

<script>

    let resourceClassName = '<?=$view->output->js($resource)?>';

    /** @type {Field[]}*/
    let fields = [];

    /** @type {Field[]}*/
    let new_db_fields = [];

    /** @type {Field[]}*/
    let removed_db_fields = [];

    /** @type {Field[]}*/
    let updated_db_fields = [];

    function findFieldKeyInArray(array, field) {
        let found_new_field_key = null

        array.forEach((f, k) => {
            if (f.name === field.name)
                found_new_field_key = k;
        });

        return found_new_field_key
    }

    function arrayHasField(array, field) {
        return (findFieldKeyInArray(array, field) !== null)
    }

    function get_field_key_in_new_db_fields(field) {
        return findFieldKeyInArray(new_db_fields, field);
    }

    function get_field_key_in_removed_db_fields(field) {
        return findFieldKeyInArray(removed_db_fields, field);
    }

    function get_field_key_in_updated_db_fields(field) {
        return findFieldKeyInArray(updated_db_fields, field);
    }

    function is_in_new_db_fields(field) {
        return arrayHasField(new_db_fields, field);
    }

    function is_in_removed_db_fields(field) {
        return arrayHasField(removed_db_fields, field);
    }

    function is_in_updated_db_fields(field) {
        return arrayHasField(updated_db_fields, field);
    }

    function updateDBField(new_field) {
        let prevUpdateKey = null;

        if (new_field.orig_name === void 0)
            return;

        updated_db_fields.forEach((field, key) => {
            if (field.orig_name === new_field.orig_name)
                prevUpdateKey = key;
        })

        if (prevUpdateKey === null)
            updated_db_fields.push(new_field);
        else
            updated_db_fields[prevUpdateKey] = new_field;

        $model_override.checked = true;
        $entity_override.checked = true;
    }

    function newDBField(field) {
        new_db_fields.push(field);

        $model_override.checked = true;
        $entity_override.checked = true;
    }

    function removeDBField(field) {
        let found_new_field_key = null;
        new_db_fields.forEach((f, k) => {
            if (f.name === field.name)
                found_new_field_key = k;
        });

        if (found_new_field_key !== null)
            new_db_fields.splice(found_new_field_key, 1);
        else
            removed_db_fields.push(field);

        $model_override.checked = true;
        $entity_override.checked = true;

        selectedFieldKey = null
    }

    function generateFields() {
        fields = fields.filter(function (element) {
            return element !== undefined;
        });

        let $tbody = document.querySelector('#fields tbody');

        $tbody.innerHTML = '';

        fields.forEach((field, key) => {
            if (field === void 0)
                return;

            let TR = document.createElement('tr');

            TR.id = 'field_' + key;

            if (selectedFieldKey === key)
                TR.classList.add('selected');

            if (field.exists_in_db === false)
                TR.classList.add('not_in_db');

            if (is_in_new_db_fields(field))
                TR.classList.add('new');

            if (is_in_removed_db_fields(field))
                TR.classList.add('del');

            if (is_in_updated_db_fields(field))
                TR.classList.add('updated');

            Object.keys(field).forEach(key => {
                if (key === 'orig_name' || key === 'exists_in_db')
                    return;

                let val = field[key]
                let TD = document.createElement('td');

                TD.innerText = val;

                if (field.type === ENUM && key === 'length') {
                    let SELECT = document.createElement('select');
                    SELECT.classList.add('enum');
                    TD.innerText.split(',').forEach(entry => {
                        let OPTION = document.createElement('option');
                        OPTION.setAttribute('value', entry);
                        OPTION.innerText = entry;
                        SELECT.add(OPTION);
                    })
                    TD.innerHTML = SELECT.outerHTML;
                }

                if (key === 'null' && val === true)
                    TD.innerHTML = '✔';

                if (key === 'default' && val === DEFAULT_NONE)
                    TD.innerHTML = '';

                if (val === false)
                    TD.innerHTML = '';

                if (is_in_updated_db_fields(field) && typeof modelFields !== "undefined")
                    modelFields.forEach(f => {
                        if (f.orig_name === field.orig_name && f[key] != field[key])
                            TD.classList.add('changed');
                    })

                TR.appendChild(TD);
            });

            let TD = document.createElement('td');

            let DB_PLUS = document.createElement('button');
            DB_PLUS.innerHTML = '+';
            DB_PLUS.classList.add('db_plus');
            DB_PLUS.addEventListener('click', e => {
                dbAddField(key);
            })
            TD.appendChild(DB_PLUS);

            if (field.exists_in_db === true || is_in_new_db_fields(field))
                DB_PLUS.disabled = true;

            let DB_MINUS = document.createElement('button');
            DB_MINUS.innerHTML = '-';
            DB_MINUS.classList.add('db_minus');
            DB_MINUS.setAttribute('style', 'margin-left: 10px;');
            DB_MINUS.addEventListener('click', e => {
                dbDelField(key);
            })
            TD.appendChild(DB_MINUS);

            let DB_CANCEL = document.createElement('button');
            DB_CANCEL.innerHTML = 'CANCEL';
            DB_CANCEL.classList.add('db_cancel');
            DB_CANCEL.classList.add('hidden');
            DB_CANCEL.addEventListener('click', e => {
                dbCancel(key);
            })
            TD.appendChild(DB_CANCEL);

            if (field.exists_in_db !== true || is_in_removed_db_fields(field))
                DB_MINUS.disabled = true;

            TR.appendChild(TD);

            TD = document.createElement('td');

            let EDIT = document.createElement('button');
            EDIT.innerText = 'EDIT';
            EDIT.addEventListener('click', e => {
                editSelectedField(key);
            })
            TD.appendChild(EDIT);

            let DEL = document.createElement('button');
            DEL.innerText = 'DEL';
            DEL.setAttribute('style', 'margin-left: 10px;');
            DEL.addEventListener('click', e => {
                removeDBField(fields[key]);
                fields.splice(key, 1);
                generateFields();
            })
            TD.appendChild(DEL);

            TR.appendChild(TD);

            $tbody.appendChild(TR);
        })
    }

    let $migrate_btn = document.querySelector('#migrate_btn');
    let $migrate_form = document.querySelector('#migrate_form');
    let $migrate_force = document.querySelector('#migrate_force');
    let $seed_btn = document.querySelector('#seed_btn');
    let $seed_form = document.querySelector('#seed_form');
    let $seed_force = document.querySelector('#seed_force');

    let $add = document.querySelector('#add');
    let $name = document.querySelector('#name');

    let $resource = document.querySelector('#resource');

    let $table = document.querySelector('#table');
    let $entity = document.querySelector('#entity');
    let $model = document.querySelector('#model');
    let $service = document.querySelector('#service');
    let $controller = document.querySelector('#controller');
    let $seed = document.querySelector('#seed');

    let $relation = document.querySelector('#relation');
    let $attributes = document.querySelector('#attributes');
    let $default = document.querySelector('#default');
    let $default_value = document.querySelector('#default_value');
    let $type = document.querySelector('#type');
    let $index = document.querySelector('#index');
    let $null = document.querySelector('#null');
    let $length = document.querySelector('#length');
    let $auto_increment = document.querySelector('#auto_increment');
    let $int_auto_unsigned = document.querySelector('#int_auto_unsigned');
    let $new_fields_null = document.querySelector('#new_fields_null');
    let $cancel_default_value = document.querySelector('#cancel_default_value');
    let $use_state_fields = document.querySelector('#use_state_fields');

    let $create_resource = document.querySelector('#create_resource');
    let $create_entity = document.querySelector('#create_entity');
    let $create_model = document.querySelector('#create_model');
    let $create_service = document.querySelector('#create_service');
    let $create_controller = document.querySelector('#create_controller');
    let $create_seed = document.querySelector('#create_seed');

    let $resource_override = document.querySelector('#resource_override');
    let $entity_override = document.querySelector('#entity_override');
    let $model_override = document.querySelector('#model_override');
    let $service_override = document.querySelector('#service_override');
    let $controller_override = document.querySelector('#controller_override');
    let $seed_override = document.querySelector('#seed_override');

    let $update = document.querySelector('#update');
    let $cancel = document.querySelector('#cancel');
    let $up = document.querySelector('#up');
    let $down = document.querySelector('#down');

    let selectedFieldKey = null;

    if (resourceClassName === '') {
        $migrate_btn.disabled = true;
        $seed_btn.disabled = true;
    }

    $seed_form.addEventListener('submit', e => {
        $seed_force.value = confirm('Force Seed ?');
    })

    $migrate_form.addEventListener('submit', e => {
        $migrate_force.value = confirm('Force Migrate ?');
    })

    function dbAddField(key) {
        let $field = document.querySelector('#field_' + key);

        $field.classList.add('new');

        $field.querySelector('.db_plus').classList.add('hidden');
        $field.querySelector('.db_minus').classList.add('hidden');
        $field.querySelector('.db_cancel').classList.remove('hidden');

        let field = fields[key];

        if (is_in_new_db_fields(field) === false)
            newDBField(field)
    }

    function dbCancel(key) {
        let $field = document.querySelector('#field_' + key);

        let field = fields[key];

        if (is_in_removed_db_fields(field))
            removed_db_fields.splice(get_field_key_in_removed_db_fields(field), 1);

        if (is_in_new_db_fields(field))
            new_db_fields.splice(get_field_key_in_new_db_fields(field), 1);

        $field.classList.remove('new');
        $field.classList.remove('del');

        $field.querySelector('.db_plus').classList.remove('hidden');
        $field.querySelector('.db_minus').classList.remove('hidden');
        $field.querySelector('.db_cancel').classList.add('hidden');

        let status = (new_db_fields.length > 0 || removed_db_fields.length > 0 || updated_db_fields > 0);

        $model_override.checked = status;
        $entity_override.checked = status;
    }

    function dbDelField(key) {
        let $field = document.querySelector('#field_' + key);

        $field.classList.remove('new');
        $field.classList.add('del');

        $field.querySelector('.db_plus').classList.add('hidden');
        $field.querySelector('.db_minus').classList.add('hidden');
        $field.querySelector('.db_cancel').classList.remove('hidden');

        let field = fields[key];

        if (is_in_removed_db_fields(field) === false)
            removeDBField(field)
    }

    function editSelectedField(key) {
        if (selectedFieldKey !== null)
            document.querySelector('#field_' + selectedFieldKey).classList.remove('selected');

        selectedFieldKey = key;

        $add.classList.add('hidden');
        $up.classList.remove('hidden');
        $down.classList.remove('hidden');
        $cancel.classList.remove('hidden');
        $update.classList.remove('hidden');

        let field = fields[selectedFieldKey];

        $name.value = field.name;
        $name.dispatchEvent(new Event('input'));
        $type.value = field.type;
        $type.dispatchEvent(new Event('input'));
        $length.value = field.length;
        $length.dispatchEvent(new Event('input'));

        if ([DEFAULT_CURRENT_TIMESTAMP, DEFAULT_NONE, DEFAULT_NULL].includes(field.default) === false) {
            $default_value.value = field.default;
            $default.value = DEFAULT_USER_DEFINED;
        } else {
            $default.value = field.default;
        }
        $default.dispatchEvent(new Event('input'));

        $attributes.value = field.attr;
        $null.checked = (field.null === true);
        $index.value = field.index;
        $auto_increment.checked = (field.auto_increment === true);

        document.querySelector('#field_' + selectedFieldKey).classList.add('selected');
    }

    function updateSelectedField() {
        fields[selectedFieldKey].name = $name.value;
        fields[selectedFieldKey].type = $type.value;
        fields[selectedFieldKey].length = ($length.value === '') ? false : $length.value;
        fields[selectedFieldKey].default = ($default.value === DEFAULT_USER_DEFINED) ? $default_value.value : $default.value;
        fields[selectedFieldKey].attr = ($attributes.value === '') ? false : $attributes.value;
        fields[selectedFieldKey].null = ($null.checked === true);
        fields[selectedFieldKey].index = ($index.value === '') ? false : $index.value;
        fields[selectedFieldKey].auto_increment = ($auto_increment.checked === true);

        updateDBField(fields[selectedFieldKey])

        generateFields();

        cancelFieldEdit();
    }

    function moveDownSelectedField() {
        let key = selectedFieldKey;

        updateDBField(fields[selectedFieldKey]);

        if (key === (fields.length - 1))
            return;

        let temp = fields[key];
        fields[key] = fields[key + 1];
        fields[key + 1] = temp;

        selectedFieldKey = selectedFieldKey + 1;

        generateFields();
    }

    function moveUpSelectedField() {
        let key = selectedFieldKey;

        updateDBField(fields[selectedFieldKey]);

        if (key === 0)
            return;

        let temp = fields[key];
        fields[key] = fields[key - 1];
        fields[key - 1] = temp;

        selectedFieldKey = selectedFieldKey - 1;

        generateFields();
    }

    function cancelFieldEdit() {
        if (selectedFieldKey !== null)
            document.querySelector('#field_' + selectedFieldKey).classList.remove('selected');

        selectedFieldKey = null;

        $add.classList.remove('hidden');
        $up.classList.add('hidden');
        $down.classList.add('hidden');
        $cancel.classList.add('hidden');
        $update.classList.add('hidden');
    }

    function removeStateFields(ask = false) {
        let createdAtFound = false;
        let updatedAtFound = false;
        let removedAtFound = false;
        let enabledAtFound = false;

        fields.forEach((v, k) => {
            if (v.name === 'created_at')
                createdAtFound = k;
            if (v.name === 'updated_at')
                updatedAtFound = k;
            if (v.name === 'removed_at')
                removedAtFound = k;
            if (v.name === 'enabled')
                enabledAtFound = k;
        });

        let confirmAgree = true;
        if (ask && createdAtFound !== false && updatedAtFound !== false && removedAtFound !== false && enabledAtFound !== false)
            confirmAgree = confirm('Do you want to remove [created_at, updated_at, removed_at, enabled] fields?');

        if (confirmAgree === false)
            return false;

        delete fields[createdAtFound];
        delete fields[updatedAtFound];
        delete fields[removedAtFound];
        delete fields[enabledAtFound];

        generateFields();
    }

    $use_state_fields.addEventListener('change', e => {
        if ($use_state_fields.checked === true)
            return false;

        removeStateFields(true);
    })

    $add.addEventListener('click', e => {
        let field = new Field();

        field.name = $name.value;
        field.type = $type.value;
        field.length = ($length.value === '') ? false : $length.value;
        field.default = ($default.value === DEFAULT_USER_DEFINED) ? $default_value.value : $default.value;
        field.attr = ($attributes.value === '') ? false : $attributes.value;
        field.null = ($null.checked === true);
        field.index = ($index.value === '') ? false : $index.value;
        field.auto_increment = ($auto_increment.checked === true);

        if (field.name.trim() === '')
            return alert('Name can not be blank.');

        let primaryExists = false;
        let fieldExists = false;
        let autoIncrementExists = false;
        fields.forEach(f => {
            if (f === void 0)
                return;

            if (f.name === field.name)
                fieldExists = true;
            if (f.index === INDEX_PRIMARY && field.index === INDEX_PRIMARY)
                primaryExists = f.name;
            if (f.auto_increment === true && field.auto_increment === true)
                autoIncrementExists = f.name;
        })

        if (autoIncrementExists)
            return alert(`Failed to add [${field.name}]. Field with Auto Increment already exists: [${autoIncrementExists}]`);

        if (fieldExists)
            return alert(`Failed to add [${field.name}]. Field already exists.`);

        if (primaryExists !== false)
            return alert(`Failed to add [${field.name}]. Field with index = PRIMARY already exists: [${primaryExists}]`);

        fields.push(field);

        newDBField(field);

        generateFields();
    })

    $name.addEventListener('input', e => {
        if ($name.value === 'id') {
            $auto_increment.checked = true;
            $index.value = INDEX_PRIMARY;
            $type.value = SMALLINT;
            $attributes.value = ATTR_UNSIGNED;
        }
    })

    $cancel_default_value.addEventListener('click', e => {
        $default_value.classList.toggle('hidden', true);
        $cancel_default_value.classList.toggle('hidden', true);
        $default.classList.toggle('hidden', false);

        if ($null.checked === false && $default.querySelector(`option[value="${DEFAULT_NONE}"]`).disabled === false)
            $default.value = DEFAULT_NONE;
    })

    $new_fields_null.addEventListener('input', e => {
        $null.checked = $new_fields_null.checked;
        $null.dispatchEvent(new Event('input'));
    })

    $null.addEventListener('input', e => {
        $default.querySelector(`option[value="${DEFAULT_NONE}"]`).disabled = ($null.checked);

        if ($null.checked === true)
            $default.value = DEFAULT_NULL;

        if ($null.checked === false && $default.value === DEFAULT_NULL)
            $default.value = DEFAULT_NONE;
    });

    $default.addEventListener('input', e => {
        let isUserDefined = ($default.value !== DEFAULT_USER_DEFINED);

        if ($default.value === DEFAULT_NULL)
            $null.checked = true;

        $default_value.classList.toggle('hidden', isUserDefined);
        $cancel_default_value.classList.toggle('hidden', isUserDefined);
        $default.classList.toggle('hidden', (isUserDefined === false));
    })

    $auto_increment.addEventListener('input', e => {
        let checked = ($auto_increment.checked === true);

        if (checked && [DEFAULT_NONE, DEFAULT_NULL].indexOf($default.value) < 0) {
            alert(`Field with Auto Increment can't have default value`);
            $auto_increment.checked = false;
        }

        $default.querySelector(`option[value="${DEFAULT_CURRENT_TIMESTAMP}"]`).disabled = (checked);
        $default.querySelector(`option[value="${DEFAULT_USER_DEFINED}"]`).disabled = (checked);
    });

    $type.addEventListener('input', e => {
        let val = $type.value;

        $length.disabled = false;
        $attributes.disabled = false;

        $default.querySelector(`option[value="${DEFAULT_NONE}"]`).disabled = false;
        $default.querySelector(`option[value="${DEFAULT_CURRENT_TIMESTAMP}"]`).disabled = false;
        $default.querySelector(`option[value="${DEFAULT_USER_DEFINED}"]`).disabled = false;
        // $attributes.querySelector(`option[value="${ATTR_BINARY}"]`).disabled = false;
        $attributes.querySelector(`option[value="${ATTR_UNSIGNED}"]`).disabled = true;
        // $attributes.querySelector(`option[value="${ATTR_UNSIGNED_ZEROFILL}"]`).disabled = true;
        $attributes.querySelector(`option[value="${ATTR_ON_UPDATE_CURRENT_TIMESTAMP}"]`).disabled = true;
        $auto_increment.disabled = true;

        $default_value.type = 'text';
        $default_value.removeAttribute('min');
        $default_value.removeAttribute('max');
        $length.removeAttribute('min');
        $length.removeAttribute('max');

        $length.type = 'number';
        $length.title = '';

        $null.checked = false;
        if ($new_fields_null.checked) {
            $null.checked = true
            $null.dispatchEvent(new Event('input'));
        }

        if ([DECIMAL, ENUM].indexOf(val) >= 0)
            $length.type = 'text';

        if (val === DECIMAL) {
            $length.value = '7,2';
            $length.title = '7,2 = 2 numbers for decimal and 7 for total, e.g. max number is 99999,99';
            $default.querySelector(`option[value="${DEFAULT_CURRENT_TIMESTAMP}"]`).disabled = true;
        }

        if ([TEXT, MEDIUMTEXT, LONGTEXT].indexOf(val) >= 0) {
            $default.querySelector(`option[value="${DEFAULT_CURRENT_TIMESTAMP}"]`).disabled = true;
            $default.querySelector(`option[value="${DEFAULT_USER_DEFINED}"]`).disabled = true;
            $cancel_default_value.dispatchEvent(new Event('click'));
        }

        if (val === ENUM)
            $length.value = 'one, two';

        if (val === VARCHAR) {
            $length.value = <?= $default_varchar_length ?>;
            $length.min = 0;
            $length.max = <?= $max_varchar_length ?>;
            $length.title = '7,2 = 2 numbers for decimal and 7 for total, e.g. max number is 99999,99';
        }

        $attributes.value = '';

        if ([DATE, DATETIME, /*TIMESTAMP,*/ TIME].indexOf(val) >= 0) {
            $attributes.querySelector(`option[value="${ATTR_ON_UPDATE_CURRENT_TIMESTAMP}"]`).disabled = false;
            $length.disabled = true;
            $length.value = '';
        }

        if ([YEAR, BOOLEAN].indexOf(val) >= 0) {
            $length.disabled = true;
            $length.value = '';
            $attributes.disabled = true;
            $default.querySelector(`option[value=${DEFAULT_CURRENT_TIMESTAMP}]`).disabled = true;
            $default_value.type = 'number';

            if (val === BOOLEAN) {
                $default.querySelector(`option[value=${DEFAULT_NULL}]`).disabled = true;
                $default_value.min = 0;
                $default_value.max = 1;
            } else {
                $default_value.min = 1901;
                $default_value.max = 2155;
            }
        }

        if ([INT, TINYINT, SMALLINT, MEDIUMINT, BIGINT].indexOf(val) >= 0) {
            $length.disabled = true;
            $auto_increment.disabled = false;
            $length.value = '';

            if ($int_auto_unsigned.checked)
                $attributes.value = ATTR_UNSIGNED;

            if ($default.value === DEFAULT_CURRENT_TIMESTAMP)
                $default.value = DEFAULT_NONE;

            $default.querySelector(`option[value=${DEFAULT_CURRENT_TIMESTAMP}]`).disabled = true;
            // $attributes.querySelector(`option[value=${ATTR_BINARY}]`).disabled = true;
            $attributes.querySelector(`option[value=${ATTR_UNSIGNED}]`).disabled = false;
            // $attributes.querySelector(`option[value="${ATTR_UNSIGNED_ZEROFILL}"]`).disabled = false;
        } else {
            if ($auto_increment.disabled === true)
                $auto_increment.checked = false;
        }
    })

    $length.addEventListener('input', e => {
        let val = parseInt($length.value);
        let min = $length.getAttribute('min');
        let max = $length.getAttribute('max');

        if (max !== null && val >= max)
            $length.value = max;

        if (min !== null && val <= min)
            $length.value = min;
    });

    $relation.addEventListener('input', e => {
        $create_entity.checked = true;
        $entity.disabled = false;

        $create_model.checked = true;
        $model.disabled = false;

        $service.disabled = ($relation.checked);
        $controller.disabled = ($relation.checked);

        $create_service.checked = ($relation.checked === false);
        $create_controller.checked = ($relation.checked === false);

        $create_resource.checked = true;
    })

    $create_entity.addEventListener('input', e => {
        $entity.disabled = ($create_entity.checked === false);
        $entity_override.disabled = ($create_entity.checked === false);
        $entity.value = getResourceCamelCaseValue() + 'Entity';
        $resource_override.checked = true;
    })

    $create_model.addEventListener('input', e => {
        $model.disabled = ($create_model.checked === false);
        $model_override.disabled = ($create_model.checked === false);
        $model.value = getResourceCamelCaseValue() + 'Model';
        $resource_override.checked = true;
    })

    $create_service.addEventListener('input', e => {
        $service.disabled = ($create_service.checked === false);
        $service_override.disabled = ($create_service.checked === false);
        $service.value = getResourceCamelCaseValue() + 'Service';
        $resource_override.checked = true;
    })

    $create_controller.addEventListener('input', e => {
        $controller.disabled = ($create_controller.checked === false);
        $controller_override.disabled = ($create_controller.checked === false);
        $controller.value = getResourceCamelCaseValue() + 'Controller';

        if ($create_controller.checked) {
            $create_service.checked = true;
            $create_service.dispatchEvent(new Event('input'));
        }

        $resource_override.checked = true;
    })

    $create_seed.addEventListener('input', e => {
        $seed.disabled = ($create_seed.checked === false);
        $seed_override.disabled = ($create_seed.checked === false);
        $seed.value = getResourceCamelCaseValue() + 'Seed';
        $resource_override.checked = true;
    })

    $create_seed.dispatchEvent(new Event('input'));

    function getResourceValueParts() {
        let val = $resource.value;

        val = val.replace(/([a-z0-9])([A-Z])/g, '$1_$2').toLowerCase();

        return val.toLowerCase().trim().replace(/ /g, '_').split('_');
    }

    function getResourceCamelCaseValue() {
        let valParts = getResourceValueParts();

        if ($resource.value[0] === $resource.value[0].toUpperCase())
            return $resource.value;

        let camelCaseVal = '';
        valParts.forEach(part => {
            camelCaseVal += part.charAt(0).toUpperCase() + part.slice(1);
        })

        return camelCaseVal;
    }

    $resource.addEventListener('change', e => {
        let camelCaseVal = getResourceCamelCaseValue();

        $resource.value = camelCaseVal;
        $table.value = getResourceValueParts().join('_');
        $entity.value = camelCaseVal + 'Entity';
        $model.value = camelCaseVal + 'Model';
        $service.value = camelCaseVal + 'Service';
        $controller.value = camelCaseVal + 'Controller';
        $seed.value = camelCaseVal + 'Seed';
    });

    $table.addEventListener('input', e => {
        let tableVal = $table.value;
        let entityVal = '';
        let valParts = tableVal.split('_');

        valParts.forEach(val => {
            val = val.charAt(0).toUpperCase() + val.slice(1);

            if (val[val.length - 1].toLowerCase() === 's' && val[val.length - 2].toLowerCase() !== 's')
                val = val.substr(0, val.length - 1);

            entityVal += val;
        })
    });

    $type.dispatchEvent(new Event('input'));

    // ---- autogen ID field

    $name.value = 'id';
    $name.dispatchEvent(new Event('input'));
    $add.dispatchEvent(new Event('click'));

    $auto_increment.checked = false;
    $index.value = '';
    $attributes.value = '';
    $type.value = VARCHAR;
    $name.value = '';

    $type.dispatchEvent(new Event('input'));

    $create_resource.checked = true;
    $create_entity.checked = true;
    $create_model.checked = true;

    $create_service.dispatchEvent(new Event('input'));
    $create_controller.dispatchEvent(new Event('input'));
    $create_seed.dispatchEvent(new Event('input'));

    new_db_fields = [];

    $resource_override.checked = false;
    $model_override.checked = false;
    $entity_override.checked = false;

    // --------- PREPARE TEMPLATES ---------

    if (resourceClassName === '')
        document.getElementById('prepare_templates').disabled = true;

    function prepareTemplates(force = false) {
        let url = 'db_generator';

        let action = 'prepare_templates';

        let data = {
            "action" : action,
            "resource" : resourceClassName,
            "force" : force
        };

        copper.requestHandler.post(url, data, function (response) {
            alert(JSON.stringify(response));
        })
    }

    document.getElementById('prepare_templates').addEventListener('click', e => {
        prepareTemplates(true);
    });

    // --------- Create Javascript Source Files ---------

    function createJsSourceFiles(force = false) {
        let url = 'db_generator';

        let action = 'create_js_source_files';

        let data = {
            "action" : action,
            "resource" : resourceClassName,
            "force" : force
        };

        copper.requestHandler.post(url, data, function (response) {
            alert(JSON.stringify(response));
        })
    }

    document.getElementById('create_js_source_files').addEventListener('click', e => {
        createJsSourceFiles(true);
    });

    function listFieldsByName(fields) {
        let names = [];

        fields.forEach(field => {
            names.push(field.name)
        })

        return names.join(', ');
    }

    // --------- DELETE -------------
    document.getElementById('delete_res').addEventListener('click', function (e) {
        document.getElementById('delete_popup').classList.toggle('hidden');
    });

    document.getElementById('delete_cancel').addEventListener('click', function (e) {
        document.getElementById('delete_popup').classList.add('hidden');
    })

    document.getElementById('delete_resource').addEventListener('click', function (e) {
        let inputs_enabled = true;

        if (this.checked)
            inputs_enabled = false;

        let fields = ['delete_entity', 'delete_model', 'delete_service', 'delete_controller', 'delete_seed', 'delete_table'];

        fields.forEach(function (field) {
            document.getElementById(field).checked = (inputs_enabled === false);
            document.getElementById(field).disabled = (inputs_enabled === false);
        })
    });

    document.getElementById('delete_confirm').addEventListener('click', e => {
        let url = '<?=CPController::ACTION_DB_GENERATOR_DEL?>';

        let JSONParams = {
            "resource": document.getElementsByName('resource')[0].value,
            "delete_resource": document.getElementById('delete_resource').checked,
            "delete_entity": document.getElementById('delete_entity').checked,
            "delete_model": document.getElementById('delete_model').checked,
            "delete_service": document.getElementById('delete_service').checked,
            "delete_controller": document.getElementById('delete_controller').checked,
            "delete_seed": document.getElementById('delete_seed').checked,
            "delete_table": document.getElementById('delete_table').checked,
        }

        if (confirm('Are you sure you want to delete resource objects?') === false)
            return;

        copper.requestHandler.postJSON(url, JSONParams, function (response) {
            alert(JSON.stringify(response));

            if (response.result.delete_resource && response.result.delete_resource.status === true)
                window.location = 'db_generator';
        })
    })

    // --------- GENERATE ------------

    document.getElementById('generate').addEventListener('click', e => {
        let url = 'db_generator_run';

        let updatedDBFieldsArray = [];

        Object.keys(updated_db_fields).forEach(key => {
            updatedDBFieldsArray.push(updated_db_fields[key]);
        })

        let JSONParams = {
            "table": $table.value,

            "resource": $resource.value,
            "entity": $entity.value,
            "model": $model.value,
            "service": $service.value,
            "controller": $controller.value,
            "seed": $seed.value,

            "create_resource": ($create_resource.checked === true),
            "create_entity": ($create_entity.checked === true),
            "create_model": ($create_model.checked === true),
            "create_service": ($create_service.checked === true),
            "create_controller": ($create_controller.checked === true),
            "create_seed": ($create_seed.checked === true),

            "resource_override": ($resource_override.checked === true),
            "entity_override": ($entity_override.checked === true),
            "model_override": ($model_override.checked === true),
            "service_override": ($service_override.checked === true),
            "controller_override": ($controller_override.checked === true),
            "seed_override": ($seed_override.checked === true),

            "use_state_fields": ($use_state_fields.checked === true),

            "fields": fields,
            "new_fields": new_db_fields,
            "new_fields_db_update": false,
            "removed_fields": removed_db_fields,
            "removed_fields_db_update": false,
            "updated_fields": updatedDBFieldsArray,
            "updated_fields_db_update": false
        }

        if ($model_override.checked === true && resourceClassName !== '') {
            if (new_db_fields.length > 0)
                JSONParams.new_fields_db_update = confirm('Do you want to add new fields to DB ?\r\n' + listFieldsByName(new_db_fields));

            if (removed_db_fields.length > 0)
                JSONParams.removed_fields_db_update = confirm('Do you want to delete removed fields from DB ?\r\n' + listFieldsByName(removed_db_fields));

            if (updatedDBFieldsArray.length > 0)
                JSONParams.updated_fields_db_update = confirm('Do you want to update changed fields in DB ?\r\n' + listFieldsByName(updatedDBFieldsArray));
        }

        if (JSONParams.create_resource && $resource.value.trim() === '')
            return alert("Resource name is empty");

        if (JSONParams.create_controller && $controller.value.trim() === '')
            return alert("Controller name is empty");

        if (JSONParams.create_seed && $seed.value.trim() === '')
            return alert("Seed name is empty");

        if (JSONParams.create_entity && $entity.value.trim() === '')
            return alert("Entity name is empty");

        if (JSONParams.create_model && ($model.value.trim() === '' || $table.value.trim() === ''))
            return alert("Model or Table name is empty");

        if (JSONParams.create_service && $service.value.trim() === '')
            return alert("Service name is empty");

        copper.requestHandler.postJSON(url, JSONParams, function (response) {
            processGenerateResponse(response);
            alert(JSON.stringify(response));
        })
    })

    function processGenerateResponse(response) {
        if (response.status !== true)
            return false;

        let result = response.result;

        window.location.href = result.return_url;
    }

</script>

<?php if ($resource === null && $demo !== false) : ?>
    <script>
        // ------------- DEMO ------------------

        $name.value = 'name';
        $type.value = VARCHAR;
        $length.value = 200;
        $type.dispatchEvent(new Event('input'));
        $add.dispatchEvent(new Event('click'));

        $name.value = 'desc';
        $type.value = TEXT;
        $length.value = '';
        $default.value = DEFAULT_NULL;
        $default.dispatchEvent(new Event('input'));
        $type.dispatchEvent(new Event('input'));
        $add.dispatchEvent(new Event('click'));

        $default.value = DEFAULT_NONE;
        $default.dispatchEvent(new Event('input'));
        $null.checked = false;

        $name.value = 'package_id';
        $type.value = TINYINT;
        $index.value = INDEX_UNIQUE;
        $default.value = DEFAULT_USER_DEFINED;
        $default.dispatchEvent(new Event('input'));
        $default_value.value = '1';
        $type.dispatchEvent(new Event('input'));
        $add.dispatchEvent(new Event('click'));

        $auto_increment.checked = false;
        $index.value = '';

        $name.value = 'enum';
        $type.value = ENUM;
        $type.dispatchEvent(new Event('input'));
        $default.value = DEFAULT_USER_DEFINED;
        $default.dispatchEvent(new Event('input'));
        $default_value.value = 'banana';
        $length.value = 'apple,banana';
        $add.dispatchEvent(new Event('click'));

        $name.value = 'dec';
        $type.value = DECIMAL;
        $type.dispatchEvent(new Event('input'));
        $default.value = DEFAULT_USER_DEFINED;
        // $attributes.value = ATTR_UNSIGNED_ZEROFILL;
        $default.dispatchEvent(new Event('input'));
        $length.value = '6,3';
        $default_value.value = '125.33';
        $add.dispatchEvent(new Event('click'));

        $default.value = DEFAULT_NONE;
        $default_value.value = '';
        $default.dispatchEvent(new Event('input'));

        $name.value = 'date';
        $type.value = DATETIME;
        $type.dispatchEvent(new Event('input'));
        $default.value = DEFAULT_CURRENT_TIMESTAMP;
        $default.dispatchEvent(new Event('input'));
        $attributes.value = ATTR_ON_UPDATE_CURRENT_TIMESTAMP;
        $add.dispatchEvent(new Event('click'));

        $default.value = DEFAULT_NONE;
        $default_value.value = '';
        $default.dispatchEvent(new Event('input'));

        $table.value = 'products';
        $table.dispatchEvent(new Event('input'));

    </script>
<?php endif; ?>

<?php
function fillModelFields(DBModel $model, $db_column_list)
{
    if ($model === null)
        return;

    $modelFields = [];

    foreach ($model->fields as $field) {
        $modelFields[] = [
            'orig_name' => $field->getName(),
            'exists_in_db' => ArrayHandler::hasValue($db_column_list, $field->getName()),

            'name' => $field->getName(),
            'type' => $field->getType(),
            'length' => $field->getLength(),
            'default' => $field->getDefault(),
            'null' => $field->getNull(),
            'attr' => $field->getAttr(),
            'index' => $field->getIndex(),
            'auto_increment' => $field->getAutoIncrement()
        ];
    }

    echo '<script> let modelFields = ' . json_encode($modelFields) . '</script>';
    echo '<script> let modelHasStateFields = ' . json_encode($model->hasStateFields()) . '</script>';
}

if ($model !== null)
    fillModelFields($model, $db_column_list);
?>

<script>
    if (typeof modelFields !== 'undefined') {
        fields = JSON.parse(JSON.stringify(modelFields));
        generateFields();
    }

    if (typeof modelHasStateFields !== 'undefined' && modelHasStateFields === false)
        $use_state_fields.checked = false;
    else if (typeof modelHasStateFields !== 'undefined' && modelHasStateFields)
        removeStateFields(false);
</script>

<h6>Developed on MySQL version() === 10.4.14-MariaDB</h6>

</body>
