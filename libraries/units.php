<?php
/*
 * This function build a json with units to be used in the main app
 * Esta funcion codifica en JSON una lista de unidades para ser usadas en la APP principal
 */


namespace Telemetry\units;

$config['units'] = array(
    array("short" => "W", "long" => "Watt"),
    array("short" => "kW", "long" => "Kilowatt"),
    array("short" => "kWh", "long" => "Kilowatt Hour"),
    array("short" => "Wh", "long" => "Watt-Hour"),
    array("short" => "V", "long" => "Volt"),
    array("short" => "VA", "long" => "Volt-Ampere"),
    array("short" => "A", "long" => "Ampere"),
    array("short" => "°C", "long" => "Celsius"),
    array("short" => "K", "long" => "Kelvin"),
    array("short" => "°F", "long" => "Fahrenheit"),
    array("short" => "%", "long" => "Percent"),
    array("short" => "Hz", "long" => "Hertz"),
    array("short" => "pulses", "long" => "Pulses"),
    array("short" => "dB", "long" => "Decibel"),
    array("short" => "hPa", "long" => "Hectopascal"),
    array("short" => "ppm", "long" => "Parts per million"),
    array("short" => "µg/m³", "long" => "micro grams per m3"),
    array("short" => "m³", "long" => "m3"),
    array("short" => "m³/h", "long" => "m3/h")
);

$includes = get_included_files();

if (array_search(__FILE__, $includes)>0) {

    if (!empty($config['units'])) {
        define('UNITS', $config['units']);
    }
} else {

    header('Content-Type: application/json');
    echo json_encode($config['units'], JSON_UNESCAPED_UNICODE);
}
