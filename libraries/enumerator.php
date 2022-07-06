<?php
/* Enumera constantes y variables
** Enumerator of constants and variables

*/


class ProcessArg
{
    const VALUE = 0;
    const INPUTID = 1;
    const FEEDID = 2;
    const NONE = 3;
    const TEXT = 4;
    const SCHEDULEID = 5;
}

class DataType
{
    const UNDEFINED = 0;
    const REALTIME = 1;
    const DAILY = 2;
    const HISTOGRAM = 3;
}

class Engine
{
    const MYSQL = 0;
    const TIMESTORE = 1;
    const PHPTIMESERIES = 2;
    const GRAPHITE = 3;
    const PHPTIMESTORE = 4;
    const PHPFINA = 5;
    const PHPFIWA = 6;
    const VIRTUALFEED = 7;
    const MYSQLMEMORY = 8;
    const REDISBUFFER = 9;
    const CASSANDRA = 10;
    
    public static function get_all()
    {
        return array(
        'MYSQL' => Engine::MYSQL,
        'TIMESTORE' => Engine::TIMESTORE,
        'PHPTIMESERIES' => Engine::PHPTIMESERIES,
        'GRAPHITE' => Engine::GRAPHITE,
        'PHPTIMESTORE' => Engine::PHPTIMESTORE,
        'PHPFINA' => Engine::PHPFINA,
        'PHPFIWA' => Engine::PHPFIWA,
        'VIRTUALFEED' => Engine::VIRTUALFEED,
        'MYSQLMEMORY' => Engine::MYSQLMEMORY,
        'REDISBUFFER' => Engine::REDISBUFFER,
        'CASSANDRA' => Engine::CASSANDRA
        );
    }

    /*
     * todo: escribir documentacion de los diferentes motores de procesamiento
     * todo: agregar nuevos motores de procesamiento
     * Funcion que describe los motores de procesamiento disponibles.
     * Function describe the diff processing engines availables
     */
    
    public static function get_all_descriptive()
    {
        return array(
            array("id"=>Engine::PHPFINA,"description"=>"Fixed Interval TimeSeries"),
            array("id"=>Engine::PHPTIMESERIES,"description"=>"Variable Interval TimeSeries"),
            array("id"=>Engine::MYSQL,"description"=>"MYSQL TimeSeries"),
            array("id"=>Engine::MYSQLMEMORY,"description"=>"MYSQL Memory"),
            array("id"=>Engine::CASSANDRA,"description"=>"CASSANDRA TimeSeries")
        );
    }

    /*
     * descriptor de los intervalos disponibles
     * function available intervals
     * todo: revisar si estos intervalos funcionan con la aplicacion del equipamiento
     */

    public static function available_intervals() 
    {
        return array(
            array("interval"=>10, "description"=>"10s"),
            array("interval"=>15, "description"=>"15s"),
            array("interval"=>20, "description"=>"20s"),
            array("interval"=>30, "description"=>"30s"),
            array("interval"=>60, "description"=>"60s"),
            array("interval"=>120, "description"=>"2m"),
            array("interval"=>180, "description"=>"3m"),
            array("interval"=>300, "description"=>"5m"),
            array("interval"=>600, "description"=>"10m"),
            array("interval"=>900, "description"=>"15m"),
            array("interval"=>1200, "description"=>"20m"),
            array("interval"=>1800, "description"=>"30m"),
            array("interval"=>3600, "description"=>"1h"),
            array("interval"=>7200, "description"=>"2h"),
            array("interval"=>10800, "description"=>"3h"),
            array("interval"=>14400, "description"=>"4h"),
            array("interval"=>18000, "description"=>"5h"),
            array("interval"=>21600, "description"=>"6h"),
            array("interval"=>43200, "description"=>"12h"),
            array("interval"=>86400, "description"=>"1d")
        );
    }
     
    public static function is_valid($engineid)
    {
        return in_array($engineid, Engine::get_all());
    }
}
