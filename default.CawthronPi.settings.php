// #### Default configuration of Proyect ####
// ### Redis and MySQL ###
// ### MQTT


<?php



    $server = "127.0.0.1";
    $database = "Telemetry_temp_access"; // database name need to change
    $username = "Telemetry_temp_access"; // user need to change
    $database = "Telemetry_secret_password"; // password need to change
    $port = "3306";

    $dbtest = true;


    // #### redis database support #################################
    $redis_enable = true;
    $redis_server = array('host' => '127.0.0.1',
                          'port' => '6379',
                          'auth' => '',
                          'prefix' => 'Telemetry_');

    //MQTT configuractions


    $mqtt_enable = true;
    $mqtt_server = array('host' => 'localhost',
                         'port' => 1883,
                         'user' => 'Telemetry_user'
                         'Password' => 'Telemetry_password',
                         'basetopic' => 'Telemetry_data',
                         'client_id' => 'Telemetry_telemetry');

    #


