<?php

/*
 * Dabase schema definition
 */
defined('CAWTHRON_ENGINE') or die('RESTRICTED ACCESS');


function db_schema_diff_datatype($spec, $current)
{
    $changed = false;

    $spec_default = isset($spec['default']) ? $spec['default'] : null;

    if ($spec['type'] == 'text' && $spec_default !== null) {
        $spec_default = "'$spec_default'";
    }
    if ($spec_default != $current['Default']) {
        $changed = true;
    }

    if ($spec['type'] !== $current['Type']) {
        $changed = true;
    }

    if (isset($spec['Null']) && ($spec['Null'] === false || $spec['Null'] === "NO")) {
        $spec_null = "NO";
    }

    elseif (isset($spec['Key'])) {
        $spec_null = "NO";
    } else {
        $spec_null = "YES";
    }

    if ($spec_null != $current['Null']) {
        $changed = true;
    }


    $spec_extra = isset($spec['Extra']) ? $spec['Extra'] : false;
    if ($spec_extra && $current['Extra'] != "auto_increment") {
        $changed = true;
    }

    return $changed;
}


function db_schema_diff_key($spec, $current)
{

    $spec_primarykey = isset($spec['Key']) ? $spec['Key'] : false;
    if ($spec_primarykey && $current['Key'] != "PRI") {
        return true;
    } else {
        return false;
    }
}

function db_schema_make_field($mysqli, $name, $spec, $add_key = true)
{

    $str =  "`$name` ". $spec['type'];

    if (isset($spec['default'])) {
        $str .= " DEFAULT '{$mysqli->escape_string($spec['default'])}'";
    }

    $null = false;

    if (isset($spec['Null']) && ($spec['Null'] === "NO" || $spec['Null'] === false)) {
        $null = true;
    }

    elseif (isset($spec['Key']) && $spec['Key']) {
        $null = true;
    }

    if ($null) {
        $str .= " NOT NULL";
    }


    if (isset($spec['Extra']) && $spec['Extra']) {
        $str .= " auto_increment";
    }


    if ($add_key && isset($spec['Key']) && $spec['Key']) {
        $str .= " PRIMARY KEY";
    }

    return $str;
}


function db_schema_make_index($table, $field)
{
    return "CREATE INDEX IX_{$table}_{$field} ON $table ($field)";
}


function db_schema_make_compound_key($schema)
{
    $fields = array();
    foreach ($schema as $field => $spec) {
        if (isset($spec['Key']) && $spec['Key']) {
            array_push($fields, "`$field`");
        }
    }
    if (count($fields) < 2) {
        return "";
    }
    $fields = join(",", $fields);
    return ", PRIMARY KEY ($fields)";
}


function db_schema_make_table($mysqli, $table, $schema, &$operations)
{
    $fields = array();
    $indexes = array();
    $pk = db_schema_make_compound_key($schema);
    foreach ($schema as $field => $spec) {
        $fields[] = db_schema_make_field($mysqli, $field, $spec, $pk === "");
        if (isset($spec['Index'])) {
            $indexes[] = $field;
        }
    }

    $operations[] = "CREATE TABLE `$table` (" .join(', ', $fields). $pk . ") ENGINE=MYISAM";

    foreach ($indexes as $field) {
        $operations[] = db_schema_make_index($table, $field);
    }
}


function db_schema_add_column($mysqli, $table, $field, $spec, &$operations)
{
    $query = "ALTER TABLE `$table` ADD ";
    $query .= db_schema_make_field($mysqli, $field, $spec);
    $operations[] = $query;

    // Add an index
    if (isset($spec['Index'])) {
        $operations[] = db_schema_make_index($table, $field);
    }
}


function db_schema_update_column($mysqli, $table, $field, $spec, &$operations)
{
    $result = $mysqli->query("DESCRIBE `$table` `$field`");
    $current = $result->fetch_array();

    $diff_datatype = db_schema_diff_datatype($spec, $current, $field);
    $add_key = db_schema_diff_key($spec, $current);

    if ($diff_datatype || $add_key) {
        $field_spec = db_schema_make_field($mysqli, $field, $spec, $add_key);
        $operations[] = "ALTER TABLE `$table` MODIFY $field_spec";
    }

    if (isset($spec['Index'])) {
        $result = $mysqli->query("SHOW INDEX FROM $table");

        $found = false;
        while ($array = $result->fetch_array()) {
            if ($array['Column_name'] == $field) {
                $found = true;
            }
        }

        if ($found === false) {
            $operations[] = db_schema_make_index($table, $field);
        }
    }
}

function db_schema_setup($mysqli, $schema, $apply)
{
    $operations = array();

    foreach ($schema as $table => $fields) {
        $result = $mysqli->query("SHOW TABLES LIKE '$table'");

        if ($result == null || $result->num_rows == 0) {
            db_schema_make_table($mysqli, $table, $schema[$table], $operations);
        } else {
            foreach ($fields as $field => $spec) {
                $result = $mysqli->query("SHOW COLUMNS FROM `$table` LIKE '$field'");

                if ($result->num_rows == 0) {
                    db_schema_add_column($mysqli, $table, $field, $spec, $operations);
                } else {
                    db_schema_update_column($mysqli, $table, $field, $spec, $operations);
                }
            }
        }
    }

    if ($apply) {
        $error = null;

        foreach ($operations as $query) {
            if (!$mysqli->query($query)) {
                $error = $mysqli->error;
                break;
            }
        }

        if ($error) {
            $operations['error'] = $error;
        }
    }

    return $operations;
}

function db_schema_test($mysqli)
{
    // Test 1
    $output = db_schema_make_field($mysqli, "field", array('type' => 'int',
                                                           'default' => '0',
                                                           'Null' => true,
                                                           'Extra' => true));
    $expected = "`field` int DEFAULT '0' auto_increment";
    if ($output != $expected) {
        echo "Test 1 failed<br>";
        echo "Expected: <code>$expected</code><br>";
        echo "Output: <code>$output</code><br>";
    }

    // Test 2
    $output = db_schema_make_field($mysqli, "tags", array('type' => 'text',
                                                          'default' => null,
                                                          'Null' => true));
    $expected = "`tags` text";
    if ($output != $expected) {
        echo "Test 2 failed<br>";
        echo "Expected: <code>$expected</code><br>";
        echo "Output: <code>$output</code><br>";
    }

    // Test 3
    // intento de agregar null
    $spec = array('type' => 'int',
                  'default' => 0,
                  'Null' => false,
                  'Extra' => true);
    $current = array('Default' => '0',
                     'Null' => 'YES',
                     'Extra' => 'auto_increment');
    if (db_schema_diff_datatype($spec, $current) == false) {
        echo "Test 3 failed";
    }

    // Test 4
    // intentar remover un  NOT NULL
    $spec = array('type' => 'int',
                  'default' => 0,
                  'Null' => true,
                  'Extra' => true);
    $current = array('Default' => '0',
                     'Null' => 'NO',
                     'Extra' => 'auto_increment');
    if (db_schema_diff_datatype($spec, $current) == false) {
        echo "Test 4 failed";
    }

    // Test 5
    // Comparar los defectos unos con otros
    $spec = array('type' => 'int',
                  'default' => '');
    $current = array('Default' => '',
                     'Null' => 'YES',
                     'Extra' => '');
    if (db_schema_diff_datatype($spec, $current) == true) {
        echo "Test 5 failed";
    }

    // Test index
    $schema = array(
        'test' => array(
            'id' => array('type' => 'int(11)', 'Null'=>false, 'Key'=>true, 'Extra'=>true),
            'userid' => array('type' => 'int(11)', 'Index'=>true),
            'name' => array('type' => 'varchar(30)')
        )
    );
    $operations = db_schema_setup($mysqli, $schema, false);
    $found = 0;
    foreach ($operations as $query) {
        if (strpos($query, "CREATE INDEX") !== false) {
            $found++;
        }
    }

    if ($found !== 1) {
        echo "Test 6 failed";
    }
}
