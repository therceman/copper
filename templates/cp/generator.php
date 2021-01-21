<?php /** @var \Copper\Component\Templating\ViewHandler $view */

use Copper\Component\CP\CPController;

?>

<?= $view->render('header') ?>

<style>
    table {
        border-collapse: collapse;
        margin-bottom: 20px;
        width: 1600px;
    }

    table td {
        border: 1px solid black;
        text-align: center;
        padding: 3px 5px;
    }

    body {
        width: 1600px;
    }

    input[type=checkbox] + span {
        margin-left: 5px;
    }
</style>

<body class="markdown-body">
<h4>DataBase Class Files Generator</h4>

<div style="margin-bottom:20px;">
    <span>Table Name:</span> <input id=table autocomplete="off" placeholder="Table name" autofocus>
    <span>Entity Name:</span> <input id=entity autocomplete="off" placeholder="Entity name">
    <input type="checkbox" id="relation" style="margin-left:20px"><span>Is Relation Table ?</span>
    <div style="display: inline-block; float:right;">
        <input type="checkbox" id="int_auto_unsigned" checked="checked"><span>INT type fields are auto UNSIGNED</span>
    </div>
</div>

<table id=fields class="controls">
    <thead>
    <tr>
        <td>Name</td>
        <td>Type</td>
        <td>Length</td>
        <td>Default</td>
        <td>Attributes</td>
        <td>Null</td>
        <td>Index</td>
        <td>Auto Increment</td>
        <td>Action</td>
    </tr>
    <tr>
        <td>
            <input id="name">
        </td>
        <td>
            <select id="type">
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
                    <option title="A small floating-point number, allowable values are -3.402823466E+38 to -1.175494351E-38, 0, and 1.175494351E-38 to 3.402823466E+38">
                        FLOAT
                    </option>
                    <option title="A double-precision floating-point number, allowable values are -1.7976931348623157E+308 to -2.2250738585072014E-308, 0, and 2.2250738585072014E-308 to 1.7976931348623157E+308">
                        DOUBLE
                    </option>
                    <option title="Synonym for DOUBLE (exception: in REAL_AS_FLOAT SQL mode it is a synonym for FLOAT)">
                        REAL
                    </option>
                    <option disabled="disabled">-</option>
                    <option title="A bit-field type (M), storing M of bits per value (default is 1, maximum is 64)">
                        BIT
                    </option>
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
                    <option title="A timestamp, range is 1970-01-01 00:00:01 UTC to 2038-01-09 03:14:07 UTC, stored as the number of seconds since the epoch (1970-01-01 00:00:00 UTC)">
                        TIMESTAMP
                    </option>
                    <option title="A time, range is -838:59:59 to 838:59:59">TIME</option>
                    <option title="A year in four-digit (4, default) or two-digit (2) format, the allowable values are 70 (1970) to 69 (2069) or 1901 to 2155 and 0000">
                        YEAR
                    </option>
                </optgroup>
                <optgroup label="String">
                    <option title="A fixed-length (0-255, default 1) string that is always right-padded with spaces to the specified length when stored">
                        CHAR
                    </option>
                    <option title="A variable-length (0-65,535) string, the effective maximum length is subject to the maximum row size">
                        VARCHAR
                    </option>
                    <option disabled="disabled">-</option>
                    <option title="A TEXT column with a maximum length of 255 (2^8 - 1) characters, stored with a one-byte prefix indicating the length of the value in bytes">
                        TINYTEXT
                    </option>
                    <option title="A TEXT column with a maximum length of 65,535 (2^16 - 1) characters, stored with a two-byte prefix indicating the length of the value in bytes">
                        TEXT
                    </option>
                    <option title="A TEXT column with a maximum length of 16,777,215 (2^24 - 1) characters, stored with a three-byte prefix indicating the length of the value in bytes">
                        MEDIUMTEXT
                    </option>
                    <option title="A TEXT column with a maximum length of 4,294,967,295 or 4GiB (2^32 - 1) characters, stored with a four-byte prefix indicating the length of the value in bytes">
                        LONGTEXT
                    </option>
                    <option disabled="disabled">-</option>
                    <option title="Similar to the CHAR type, but stores binary byte strings rather than non-binary character strings">
                        BINARY
                    </option>
                    <option title="Similar to the VARCHAR type, but stores binary byte strings rather than non-binary character strings">
                        VARBINARY
                    </option>
                    <option disabled="disabled">-</option>
                    <option title="A BLOB column with a maximum length of 255 (2^8 - 1) bytes, stored with a one-byte prefix indicating the length of the value">
                        TINYBLOB
                    </option>
                    <option title="A BLOB column with a maximum length of 65,535 (2^16 - 1) bytes, stored with a two-byte prefix indicating the length of the value">
                        BLOB
                    </option>
                    <option title="A BLOB column with a maximum length of 16,777,215 (2^24 - 1) bytes, stored with a three-byte prefix indicating the length of the value">
                        MEDIUMBLOB
                    </option>
                    <option title="A BLOB column with a maximum length of 4,294,967,295 or 4GiB (2^32 - 1) bytes, stored with a four-byte prefix indicating the length of the value">
                        LONGBLOB
                    </option>
                    <option disabled="disabled">-</option>
                    <option title="An enumeration, chosen from the list of up to 65,535 values or the special '' error value">
                        ENUM
                    </option>
                    <option title="A single value chosen from a set of up to 64 members">SET</option>
                </optgroup>
                <optgroup label="Spatial">
                    <option title="A type that can store a geometry of any type">GEOMETRY</option>
                    <option title="A point in 2-dimensional space">POINT</option>
                    <option title="A curve with linear interpolation between points">LINESTRING</option>
                    <option title="A polygon">POLYGON</option>
                    <option title="A collection of points">MULTIPOINT</option>
                    <option title="A collection of curves with linear interpolation between points">MULTILINESTRING
                    </option>
                    <option title="A collection of polygons">MULTIPOLYGON</option>
                    <option title="A collection of geometry objects of any type">GEOMETRYCOLLECTION</option>
                </optgroup>
                <optgroup label="JSON">
                    <option title="Stores and enables efficient access to data in JSON (JavaScript Object Notation) documents">
                        JSON
                    </option>
                </optgroup>
            </select>
        </td>
        <td>
            <input id="length" type="number">
        </td>
        <td>
            <input type="text" id="default_value" disabled="disabled">
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
        </td>
        <td>
            <select id="attributes">
                <option value=""></option>
                <option value="BINARY">
                    BINARY
                </option>
                <option value="UNSIGNED">
                    UNSIGNED
                </option>
                <option value="UNSIGNED_ZEROFILL">
                    UNSIGNED_ZEROFILL
                </option>
                <option value="ON_UPDATE_CURRENT_TIMESTAMP">
                    ON_UPDATE_CURRENT_TIMESTAMP
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
        </td>
    </tr>
    </thead>
    <tbody>

    </tbody>
</table>

<div style="float:right">
    <button id="generate">Generate Class Files</button>
</div>

<form method="post" action="<?= $view->path(ROUTE_copper_cp_action, ['action' => CPController::ACTION_LOGOUT]) ?>">
    <button type="submit">Logout</button>
</form>

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

            let DOWN = document.createElement('button');
            DOWN.innerHTML = '&darr;';
            DOWN.addEventListener('click', e => {
                if (key === (fields.length - 1))
                    return;

                let temp = fields[key];
                fields[key] = fields[key + 1];
                fields[key + 1] = temp;

                generateFields();
            })
            TD.appendChild(DOWN);

            let UP = document.createElement('button');
            UP.innerHTML = '&uarr;';
            UP.addEventListener('click', e => {
                if (key === 0)
                    return;

                let temp = fields[key];
                fields[key] = fields[key - 1];
                fields[key - 1] = temp;

                generateFields();
            })
            TD.appendChild(UP);

            TR.appendChild(TD);

            $tbody.appendChild(TR);
        })
    }

    let $add = document.querySelector('#add');
    let $name = document.querySelector('#name');
    let $table = document.querySelector('#table');
    let $entity = document.querySelector('#entity');
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

    $add.addEventListener('click', e => {
        let field = new Field();

        field.name = $name.value;
        field.type = $type.value;
        field.length = ($length.value === '') ? false : $length.value;
        field.default = ($default.value === 'USER_DEFINED') ? $default_value : $default.value;
        field.attr = ($attributes.value === '') ? false : $attributes.value;
        field.null = ($null.checked === true);
        field.index = ($index.value === '') ? false : $index.value;
        field.auto_increment = ($auto_increment.checked === true);

        if (field.name.trim() === '')
            return alert('Name can not be blank.');

        let primaryExists = false;
        let fieldExists = false;
        fields.forEach(f => {
            if (f.name === field.name)
                fieldExists = true;
            if (f.index === 'PRIMARY' && field.index === 'PRIMARY')
                primaryExists = f.name;
        })

        if (fieldExists)
            return alert(`Field with name [${field.name}] already exists.`);

        if (primaryExists !== false)
            return alert('Field with index = PRIMARY already exists: [' + primaryExists + ']');

        fields.push(field);

        generateFields();
    })

    $name.addEventListener('input', e => {
        if ($name.value === 'id') {
            $auto_increment.checked = true;
            $index.value = 'PRIMARY';
            $type.value = 'MEDIUMINT';
            $attributes.value = 'UNSIGNED'
        }
    })

    $default.addEventListener('input', e => {
        $default_value.disabled = ($default.value !== 'USER_DEFINED');

        if ($default.value === 'NULL')
            $null.checked = true;
    })

    $type.addEventListener('input', e => {
        let val = $type.value;

        $length.disabled = false;
        $default.querySelector('option[value=CURRENT_TIMESTAMP]').disabled = false;
        $attributes.querySelector('option[value=BINARY]').disabled = false;
        $attributes.querySelector('option[value=UNSIGNED]').disabled = true;
        $attributes.querySelector('option[value=UNSIGNED_ZEROFILL]').disabled = true;
        $attributes.querySelector('option[value=ON_UPDATE_CURRENT_TIMESTAMP]').disabled = true;
        $auto_increment.disabled = true;

        $attributes.value = '';

        if (['DATE', 'DATETIME', 'TIMESTAMP', 'TIME'].indexOf(val) >= 0)
            $attributes.querySelector('option[value=ON_UPDATE_CURRENT_TIMESTAMP]').disabled = false;

        if (['INT', 'TINYINT', 'SMALLINT', 'MEDIUMINT', 'BIGINT'].indexOf(val) >= 0) {
            $length.disabled = true;
            $auto_increment.disabled = false;
            $length.value = '';

            if ($int_auto_unsigned.checked)
                $attributes.value = 'UNSIGNED';

            $default.querySelector('option[value=CURRENT_TIMESTAMP]').disabled = true;
            $attributes.querySelector('option[value=BINARY]').disabled = true;
            $attributes.querySelector('option[value=UNSIGNED]').disabled = false;
            $attributes.querySelector('option[value=UNSIGNED_ZEROFILL]').disabled = false;
        }
    })

    $relation.addEventListener('input', e => {
        $entity.disabled = ($relation.checked);
    })

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

        $entity.value = entityVal;
    });

    $type.dispatchEvent(new Event('input'));

    // ---- autogen ID field

    $name.value = 'id';
    $name.dispatchEvent(new Event('input'));
    $add.dispatchEvent(new Event('click'));

    $auto_increment.checked = false;
    $index.value = '';
    $attributes.value = '';
    $type.value = 'VARCHAR';
    $name.value = '';

    // --------- GENERATE ------------

    document.getElementById('generate').addEventListener('click', e => {
        let http = new XMLHttpRequest();
        let url = 'db_generator_run';

        let params = JSON.stringify({
            "table" : $table.value,
            "entity" : $entity.value,
            "relation" : ($relation.checked === true),
            "fields" : fields
        });

        http.open('POST', url, true);

        http.setRequestHeader('Content-type', 'application/json');

        http.onreadystatechange = function () {
            if (http.readyState === 4 && http.status === 200) {
                alert(http.responseText);
            }
        }

        http.send(params);
    })

    // ------------- DEMO ------------------

    $name.value = 'name';
    $type.value = 'VARCHAR';
    $length.value = 200;
    $type.dispatchEvent(new Event('input'));
    $add.dispatchEvent(new Event('click'));

    $name.value = 'desc';
    $type.value = 'TEXT';
    $length.value = '';
    $type.dispatchEvent(new Event('input'));
    $add.dispatchEvent(new Event('click'));

    $name.value = 'package_id';
    $type.value = 'TINYINT';
    $type.dispatchEvent(new Event('input'));
    $add.dispatchEvent(new Event('click'));

    $table.value = 'products';
    $table.dispatchEvent(new Event('input'));


</script>
</body>
