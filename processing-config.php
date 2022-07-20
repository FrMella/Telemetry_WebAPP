<?php

/*
 * Processing configuration definitions for Telemetry WebAPP
 */

defined('Telemetry_ENGINE') or die('RESTRICTED ACCESS');

require_once('libraries/enumerator.php');

$settings_error = false;

if (file_exists(dirname(__FILE__)."/settings.php")) {
    require_once('default-settings.php');
    require_once('settings.php');
    if (!isset($settings)) {
        require_once('libraries/process_old_settings.php');
       // $settings_error = true;
       // $settings_error_title = "settings file error";
       // $settings_error_message = "need settings.php file tag1";
    } else {
        $settings = array_replace_recursive($_settings, $settings);
    }
} elseif (file_exists(dirname(__FILE__)."/settings.ini")) {
    $CONFIG_INI = parse_ini_file("default-settings.ini", true, INI_SCANNER_TYPED);
    $CUSTOM_INI = parse_ini_file("settings.ini", true, INI_SCANNER_TYPED);
#    $CONFIG_INI = parse_ini_file("default-settings.ini", true);
#    $CUSTOM_INI = parse_ini_file("settings.ini", true);
    $settings = ini_merge($CONFIG_INI, $CUSTOM_INI);
    // $settings = ini_check_envvars($settings);
    if (is_string($settings['feed']['engines_hidden'])) {
        $settings['feed']['engines_hidden'] = json_decode($settings['feed']['engines_hidden']);
    }
} else {
    $settings_error = true;
    $settings_error_title = "missing settings file";
    $settings_error_message = "Create a settings.ini file";
}

if ($settings_error) {
    if (PHP_SAPI === 'cli') {
        echo "$settings_error_title\n";
        echo "$settings_error_message\n";
    } else {
        echo "<div style='width:600px; background-color:#eee; padding:20px; font-family:arial,serif;'>";
        echo "<h3>$settings_error_title</h3>";
        echo "<p>$settings_error_message</p>";
        echo "</div>";
    }
    die;
}

if (is_dir($settings["Telemetry_dir"]."/ext_modules")) {
    $linked_modules_dir = $settings["Telemetry_dir"]."/ext_modules";
} else {
    $linked_modules_dir = $settings["Telemetry_dir"];
}

if (isset($settings["display_errors"]) && ($settings["display_errors"])) {
    error_reporting(E_ALL);
    ini_set('display_errors', 'on');
}

function ini_merge($defaults, $overrides)
{
    foreach ($overrides as $k => $v) {
        if (is_array($v)) {
            $defaults[$k] = ini_merge($defaults[$k], $overrides[$k]);
        } else {
            $defaults[$k] = resolve_env_vars($v, $defaults[$k]);
#            $defaults[$k] = $v;
        }
    }

    return $defaults;
};

function resolve_env_vars($value)
{
    if (!is_string($value) ||
        strpos($value, '{{') === false ||
        strpos($value, '}}') === false) {
        return $value;
    }

    preg_match_all('/{{([^}]+)}}/', $value, $matches);
    foreach ($matches[1] as $match) {
        $env_name = $match;
        $env_value = getenv($env_name);
        if ($env_value === false) {
            echo "<p>Error: environment var '${env_name}' not defined</p>";
            return $value;
        }

        $value = str_replace('{{'.$env_name.'}}', $env_value, $value);
    }

    if (strcasecmp($value, "true") == 0) {
        $value = true;
    } elseif (strcasecmp($value, 'false') == 0) {
        $value = false;

    } elseif (is_numeric($value)) {
        $value = $value + 0;
    }

    return $value;
}
