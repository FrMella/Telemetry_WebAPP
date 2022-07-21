/*
    Configuraciones adicionales
    //todo: implementar un sistema de configuracion ordenado y accesible, agragar una interface abstracta
*/
<?php


// Configuracion mysql
    $server = "3.129.108.124";
    $database = "monitorAppDB"; // database name need to change
    $username = "FrAdminDB"; // user need to change
    $database = "264HE3394k&264HE3394k"; // password need to change
    $port = "3306";
    $dbtest = true;

// configuracion redis
    $redis_enable = false;
    $redis_server = array('host' => '127.0.0.1',
                          'port' => '6379',
                          'auth' => '',
                          'prefix' => 'Telemetry_');

// configuracion MQTT
    $mqtt_enable = false;
    $mqtt_server = array('host' => 'localhost',
                         'port' => 1883,
                         'user' => 'Telemetry_user'
                         'Password' => 'Telemetry_password',
                         'basetopic' => 'Telemetry_data',
                         'client_id' => 'Telemetry_telemetry');

    #


