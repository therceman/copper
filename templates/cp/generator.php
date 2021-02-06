<?php /** @var \Copper\Component\Templating\ViewHandler $view */

use Copper\Component\CP\CPController;
use Copper\Component\DB\DBModel;
use Copper\Component\HTML\HTML;
use Copper\Resource\AbstractResource;

// TODO only entity fields
// TODO validation type for field

$default_varchar_length = $view->dataBag->get('default_varchar_length', 65535);

/** @var AbstractResource[] $resource_list */
$resource_list = $view->dataBag->get('resource_list', []);

/** @var AbstractResource $resource */
$resource = $view->dataBag->get('resource', null);

$demo = $view->query('demo', false);

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

<?= $view->render('header') ?>

<style>
    table {
        border-collapse: collapse;
        margin-bottom: 20px;
        width: 1400px;
    }

    table td {
        border: 1px solid black;
        text-align: center;
        padding: 3px 5px;
    }

    table tr:hover {
        background: lightgoldenrodyellow;
    }

    table tr.selected {
        background: lightgreen;
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
</style>

<body class="markdown-body">
<h4>DataBase Resource Generator</h4>

<div style="margin-bottom:10px;">
    <div style="margin-bottom: 5px;">
        <span>Resource</span>
        <?= HTML::input()->id('resource')->value($resourceName)->placeholder('Resource Name')->autofocus() ?>
        <span class="help">Hit [Enter] after input</span>
        <div style="float:right">
            <?php
            echo HTML::formGet($view->url(ROUTE_copper_cp_action, ['action' => CPController::ACTION_DB_GENERATOR]))
                ->addStyle('display', 'inline-block')
                ->addElement(HTML::select($resource_list, 'resource', $resource, true))
                ->addElement(HTML::button('Read'));
            ?>
            <?php
            echo HTML::formGet($view->url(ROUTE_copper_cp_action, ['action' => CPController::ACTION_DB_GENERATOR]))
                ->addStyle('display', 'inline-block')
                ->addElement(HTML::button('Clear'));
            ?>
            <?php
            echo HTML::formGet($view->url(ROUTE_copper_cp_action, ['action' => CPController::ACTION_DB_GENERATOR]))
                ->addStyle('display', 'inline-block')
                ->addElement(HTML::inputHidden('demo', 1))
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
    </div>
    <div style="clear: both"></div>
    <div style="margin-top: 10px;">
        <span>Files To Create: </span>
        <?= HTML::checkbox('Resource', ($resourceName !== ''), null, 'create_resource', false) ?>
        <?= HTML::checkbox('Entity', ($entityName !== ''), null, 'create_entity', false) ?>
        <?= HTML::checkbox('Model', ($modelName !== ''), null, 'create_model', false) ?>
        <?= HTML::checkbox('Service', ($serviceName !== ''), null, 'create_service', false) ?>
        <?= HTML::checkbox('Controller', ($controllerName !== ''), null, 'create_controller', false) ?>
        <?= HTML::checkbox('Seed', ($seedName !== ''), null, 'create_seed', false) ?>
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

<table id=fields class="controls">
    <thead>
    <tr>
        <td>Name</td>
        <td>Type</td>
        <td>Length</td>
        <td>Default</td>
        <td style="width: 260px;">Attributes</td>
        <td>Null</td>
        <td>Index</td>
        <td>Auto Increment</td>
        <td style="width: 115px;">Action</td>
    </tr>
    <tr>
        <td>
            <input id="name">
        </td>
        <td>
            <select id="type" style="width: 185px;">
                <option title="A 4-byte integer, signed range is -2,147,483,648 to 2,147,483,647, unsigned range is 0 to 4,294,967,295">
                    INT
                </option>
                <option title="A variable-length (0-65,535) string, the effective maximum length is subject to the maximum row size">
                    VARCHAR
                </option>
                <option title="A TEXT column with a maximum length of 65,535 (2^16 - 1) characters, stored with a two-byte prefix indicating the length of the value in bytes">
                    TEXT
                </option>
                <option title="A date, supported range is 1000-01-01 to 9999-12-31">DATE</option>
                <optgroup label="Numeric">
                    <option title="A 1-byte integer, signed range is -128 to 127, unsigned range is 0 to 255">TINYINT
                    </option>
                    <option
                            title="A 2-byte integer, signed range is -32,768 to 32,767, unsigned range is 0 to 65,535">
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
                    <option title="An alias for BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE">SERIAL</option>
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
            <input id="length" type="number">
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
            <input type="checkbox" id="null">
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
        <td>
            <button id="add">ADD</button>
            <button class="hidden" id="update" onclick="updateSelectedField()">✓</button>
            <button class="hidden" id="down" onclick="moveDownSelectedField()">↓</button>
            <button class="hidden" id="up" onclick="moveUpSelectedField()">↑</button>
            <button class="hidden" id="cancel" onclick="cancelFieldEdit()">X</button>
        </td>
    </tr>
    </thead>
    <tbody>

    </tbody>
</table>

<div style="float:right">
    <button id="generate">Generate Class Files</button>
</div>

<form method="post" action="<?= $view->url(ROUTE_copper_cp_action, ['action' => CPController::ACTION_LOGOUT]) ?>">
    <button type="submit">Logout</button>
</form>

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

<script>
    const DEFAULT_USER_DEFINED = 'USER_DEFINED';
</script>

<script>
    class Field {
        name;
        type;
        length;
        default;
        attr;
        null;
        index;
        auto_increment;

        constructor(name, type, length, def, attr, isNull, index, auto_increment) {
            this.name = name;
            this.type = type;
            this.length = length;
            this.default = def;
            this.attr = attr;
            this.null = isNull;
            this.index = index;
            this.auto_increment = auto_increment;
        }
    }
</script>

<script>

    /** @type {Field[]}*/
    let fields = [];

    function generateFields() {
        let $tbody = document.querySelector('#fields tbody');

        $tbody.innerHTML = '';

        fields.forEach((field, key) => {
            let TR = document.createElement('tr');

            TR.id = 'field_' + key;

            if (selectedFieldKey === key)
                TR.classList.add('selected');

            Object.keys(field).forEach(key => {
                let val = field[key]
                let TD = document.createElement('td');

                TD.innerText = val;

                TR.appendChild(TD);
            });

            let TD = document.createElement('td');

            let DEL = document.createElement('button');
            DEL.innerText = 'DEL';
            DEL.addEventListener('click', e => {
                delete fields[key];

                generateFields();
            })
            TD.appendChild(DEL);

            let EDIT = document.createElement('button');
            EDIT.innerText = 'EDIT';
            EDIT.addEventListener('click', e => {
                editSelectedField(key);
            })
            TD.appendChild(EDIT);

            //
            // let DOWN = document.createElement('button');
            // DOWN.innerHTML = '&darr;';
            // DOWN.addEventListener('click', e => {
            //     if (key === (fields.length - 1))
            //         return;
            //
            //     let temp = fields[key];
            //     fields[key] = fields[key + 1];
            //     fields[key + 1] = temp;
            //
            //     generateFields();
            // })
            // TD.appendChild(DOWN);
            //
            // let UP = document.createElement('button');
            // UP.innerHTML = '&uarr;';
            // UP.addEventListener('click', e => {
            //     if (key === 0)
            //         return;
            //
            //     let temp = fields[key];
            //     fields[key] = fields[key - 1];
            //     fields[key - 1] = temp;
            //
            //     generateFields();
            // })
            // TD.appendChild(UP);

            TR.appendChild(TD);

            $tbody.appendChild(TR);
        })
    }

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
        $default.value = field.default;
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

        generateFields();
    }

    function moveDownSelectedField() {
        let key = selectedFieldKey;

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

        if (key === 0)
            return;

        let temp = fields[key];
        fields[key] = fields[key - 1];
        fields[key - 1] = temp;

        selectedFieldKey = selectedFieldKey - 1;

        generateFields();
    }

    function cancelFieldEdit() {
        document.querySelector('#field_' + selectedFieldKey).classList.remove('selected');

        selectedFieldKey = null;

        $add.classList.remove('hidden');
        $up.classList.add('hidden');
        $down.classList.add('hidden');
        $cancel.classList.add('hidden');
        $update.classList.add('hidden');
    }

    $use_state_fields.addEventListener('change', e => {
        if ($use_state_fields.checked === true)
            return false;

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

        let confirmAgree = false;
        if (createdAtFound !== false && updatedAtFound !== false && removedAtFound !== false && enabledAtFound !== false)
            confirmAgree = confirm('Do you want to remove [created_at, updated_at, removed_at, enabled] fields?');

        if (confirmAgree === false)
            return false;

        delete fields[createdAtFound];
        delete fields[updatedAtFound];
        delete fields[removedAtFound];
        delete fields[enabledAtFound];

        generateFields();
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

        generateFields();
    })

    $name.addEventListener('input', e => {
        if ($name.value === 'id') {
            $auto_increment.checked = true;
            $index.value = INDEX_PRIMARY;
            $type.value = MEDIUMINT;
            $attributes.value = ATTR_UNSIGNED;
        }
    })

    $cancel_default_value.addEventListener('click', e => {
        $default_value.classList.toggle('hidden', true);
        $cancel_default_value.classList.toggle('hidden', true);
        $default.classList.toggle('hidden', false);

        $default.value = DEFAULT_NONE;
    })

    $null.addEventListener('input', e => {
        $default.querySelector(`option[value="${DEFAULT_NONE}"]`).disabled = ($null.checked);

        if ($default.value === DEFAULT_NONE)
            $default.value = DEFAULT_NULL
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

        $default.querySelector(`option[value="${DEFAULT_CURRENT_TIMESTAMP}"]`).disabled = false;
        // $attributes.querySelector(`option[value="${ATTR_BINARY}"]`).disabled = false;
        $attributes.querySelector(`option[value="${ATTR_UNSIGNED}"]`).disabled = true;
        // $attributes.querySelector(`option[value="${ATTR_UNSIGNED_ZEROFILL}"]`).disabled = true;
        $attributes.querySelector(`option[value="${ATTR_ON_UPDATE_CURRENT_TIMESTAMP}"]`).disabled = true;
        $auto_increment.disabled = true;

        $default_value.type = 'text';
        $default_value.removeAttribute('min');
        $default_value.removeAttribute('max');

        $length.type = 'number';
        $length.title = '';

        if ([DECIMAL, ENUM].indexOf(val) >= 0)
            $length.type = 'text';

        if (val === DECIMAL) {
            $length.value = '7,2';
            $length.title = '7,2 = 2 numbers for decimal and 7 for total, e.g. max number is 99999,99';
        }

        if (val === ENUM)
            $length.value = 'one, two';

        if (val === VARCHAR) {
            $length.value = <?= $default_varchar_length ?>;
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
        }
    })

    $relation.addEventListener('input', e => {
        $entity.disabled = ($relation.checked);
        $service.disabled = ($relation.checked);
        $controller.disabled = ($relation.checked);

        $create_entity.checked = ($relation.checked === false);
        $create_service.checked = ($relation.checked === false);
        $create_controller.checked = ($relation.checked === false);
    })

    $create_entity.addEventListener('input', e => {
        $entity.disabled = ($create_entity.checked === false);
        $entity_override.disabled = ($create_entity.checked === false);
    })

    $create_model.addEventListener('input', e => {
        $model.disabled = ($create_model.checked === false);
        $model_override.disabled = ($create_model.checked === false);
    })

    $create_service.addEventListener('input', e => {
        $service.disabled = ($create_service.checked === false);
        $service_override.disabled = ($create_service.checked === false);
    })

    $create_controller.addEventListener('input', e => {
        $controller.disabled = ($create_controller.checked === false);
        $controller_override.disabled = ($create_controller.checked === false);
    })

    $create_seed.addEventListener('input', e => {
        $seed.disabled = ($create_seed.checked === false);
        $seed_override.disabled = ($create_seed.checked === false);
    })

    $create_seed.dispatchEvent(new Event('input'));

    $resource.addEventListener('change', e => {
        let val = $resource.value;

        val = val.replace(/([a-z0-9])([A-Z])/g, '$1_$2').toLowerCase();

        let valParts = val.toLowerCase().trim().replace(/ /g, '_').split('_');

        let camelCaseVal = '';
        valParts.forEach(part => {
            camelCaseVal += part.charAt(0).toUpperCase() + part.slice(1);
        })

        $resource.value = camelCaseVal;
        $table.value = valParts.join('_');
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

    // --------- GENERATE ------------

    document.getElementById('generate').addEventListener('click', e => {
        let http = new XMLHttpRequest();
        let url = 'db_generator_run';

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

            "fields": fields
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

        let params = JSON.stringify(JSONParams);

        http.open('POST', url, true);

        http.setRequestHeader('Content-type', 'application/json');

        http.onreadystatechange = function () {
            if (http.readyState === 4 && http.status === 200) {
                alert(http.responseText);
            }
        }

        http.send(params);
    })

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
function fillModelFields(DBModel $model)
{
    if ($model === null)
        return;

    $modelFields = [];

    foreach ($model->fields as $field) {
        $modelFields[] = [
            'name' => $field->getName(),
            'type' => $field->getType(),
            'length' => $field->getLength(),
            'default' => $field->getDefault(),
            'attr' => $field->getAttr(),
            'null' => $field->getNull(),
            'index' => $field->getIndex(),
            'auto_increment' => $field->getAutoIncrement()
        ];
    }

    echo '<script> let modelFields = ' . json_encode($modelFields) . '</script>';
    echo '<script> let modelHasStateFields = ' . json_encode($model->hasStateFields()) . '</script>';
}

if ($model !== null)
    fillModelFields($model);
?>

<script>
    if (modelFields !== void 0) {
        fields = modelFields;
        generateFields();
    }

    if (modelHasStateFields === false)
        $use_state_fields.checked = false;
</script>

<h6>Developed on MySQL version() === 10.4.14-MariaDB</h6>

</body>
