<?php
/*
 *  #### Telemetry index definition and functions, software startup scripts ###
 *
 */


define('Telemetry_ENGINE', 1);

require "processing-config.php";
require "engine.core.php";
require "router.php";
require "parameters.php";


$Telemetry_version = ($settings['feed']['redisbuffer']['enabled'] ? "low-write " : "") . version();

$path = get_app_path($settings["domain"]);
$sidebarFixed = true;

require "Libraries/AppLogger.php";
$log = new AppLogger(__FILE__);

if ($settings['redis']['enabled']) {
    if (!extension_loaded('redis')) {
        echo "Error access to <b>Redis</b> review the settings <br> See <a href='". $path. "php-info.php'>PHP Info</a> (restricted to local access)";
        die;
    }
    $redis = new Redis();
    $connected = $redis->connect($settings['redis']['host'], $settings['redis']['port']);
    if (!$connected) {
        echo "Cannot connect to redis database at ".$settings['redis']['host'].":".$settings['redis']['port']." , installation";
        die;
    }
    if (!empty($settings['redis']['prefix'])) {
        $redis->setOption(Redis::OPT_PREFIX, $settings['redis']['prefix']);
    }
    if (!empty($settings['redis']['auth'])) {
        if (!$redis->auth($settings['redis']['auth'])) {
            echo "Cannot connect to redis ".$settings['redis']['host'].", autentication failed";
            die;
        }
    }
    if (!empty($settings['redis']['dbnum'])) {
        $redis->select($settings['redis']['dbnum']);
    }
} else {
    $redis = false;
}

$mqtt = false;

if (!extension_loaded('mysql') && !extension_loaded('mysqli')) {
    echo "Error at <b>MySQL extension(s)</b> review the settings <br> See <a href='". $path. "php-info.php'>PHP Info</a> (restricted to local access)";
    die;
}

if (!extension_loaded('gettext')) {
    echo "Error at: <b>gettext</b> review settings <br> See <a href='". $path. "php-info.php'>PHP Info</a> (restricted to local access)";
    die;
}

$mysqli = @new mysqli(
    $settings["sql"]["server"],
    $settings["sql"]["username"],
    $settings["sql"]["password"],
    $settings["sql"]["database"],
    $settings["sql"]["port"]
);

if ($mysqli->connect_error) {
    echo "Cannot access to database, review settings<br />";
    if ($settings["display_errors"]) {
        echo "Error message: <b>" . $mysqli->connect_error . "</b>";
    }
    die();
}

$mysqli->set_charset("utf8");

if (!$mysqli->connect_error && $settings["sql"]["dbtest"]==true) {
    require "Libraries/dbschemasetup.php";
    if (!db_check($mysqli, $settings["sql"]["database"])) {
        db_schema_setup($mysqli, load_db_schema(), true);
    }
}

require("ext_modules/user/user_model.php");
$user = new User($mysqli, $redis);

$apikey = false;
$devicekey = false;
if (isset($_GET['apikey'])) {
    $apikey = $_GET['apikey'];
} elseif (isset($_POST['apikey'])) {
    $apikey = $_POST['apikey'];
} elseif (isset($_GET['devicekey'])) {
    $devicekey = $_GET['devicekey'];
} elseif (isset($_POST['devicekey'])) {
    $devicekey = $_POST['devicekey'];
} elseif (isset($_SERVER["HTTP_AUTHORIZATION"])) {

    if (isset($_SERVER["CONTENT_TYPE"]) && $_SERVER["CONTENT_TYPE"]=="aes128cbc") {
    } else {
        $apikey = str_replace('Bearer ', '', $_SERVER["HTTP_AUTHORIZATION"]);
    }
}

$device = false;
if ($apikey) {
    $session = $user->apikey_session($apikey);
    if (empty($session)) {
        header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized");
        header('WWW-Authenticate: Bearer realm="API KEY", error="invalid_apikey", error_description="Invalid API key"');
        print "Invalid API key";
        $log->error("Invalid API key | ".$_SERVER["REMOTE_ADDR"]);
        exit();
    }
} elseif ($devicekey && (@include "ext_modules/device/device_model.php")) {
    $device = new Device($mysqli, $redis);
    $session = $device->devicekey_session($devicekey);
    if (empty($session)) {
        header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized");
        header('WWW-Authenticate: Bearer realm="Device KEY", error="invalid_devicekey", error_description="Invalid device key"');
        print "Invalid device key";
        $log->error("Invalid device key");
        exit();
    }
} else {
    $session = $user->emon_session_start();
}


if (!isset($session['lang'])) {
    $session['lang']='';
}


define('EMPTY_ROUTE', "#UNDEFINED#");

$route = new Route(get('q'), server('DOCUMENT_ROOT'), server('REQUEST_METHOD'));

$param = new Param($route, $user);

if ($route->controller=="describe") {
    header('Content-Type: text/plain');
    header('Access-Control-Allow-Origin: *');
    if ($redis && $redis->exists("describe")) {
        $type = $redis->get("describe");
    } else {
        $type = 'Telemetry';
    }
    echo $type;
    die;
}

if ($route->controller=="version") {
    header('Content-Type: text/plain; charset=utf-8');
    echo version();
    exit;
}

if (get('embed')==1) {
    $embed = 1;
} else {
    $embed = 0;
}


if ($route->isRouteNotDefined()) {
    if ($settings["interface"]["enable_admin_ui"]) {
        if (file_exists("Modules/setup")) {
            require "ext_modules/setup/setup_model.php";
            $setup = new Setup($mysqli);
            if ($setup->status()=="unconfigured") {
                $settings["interface"]["default_controller"] = "setup";
                $settings["interface"]["default_action"] = "";
                $_SESSION['setup_access'] = true;
            }
        }
    }

    if (!isset($session['read']) || (isset($session['read']) && !$session['read'])) {

        $route->controller = $settings["interface"]["default_controller"];
        $route->action = $settings["interface"]["default_action"];
        $route->subaction = "";
    } else {
        if (isset($session["startingpage"]) && $session["startingpage"]!="") {
            header('Location: '.$session["startingpage"]);
            die;
        } else {

            $route->controller = $settings["interface"]["default_controller_auth"];
            $route->action = $settings["interface"]["default_action_auth"];
            $route->subaction = "";
        }
    }
}

if ($devicekey && !($route->controller == 'input' && ($route->action == 'bulk' || $route->action == 'post'))) {
    header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized");
    print "Unauthorized. Device key autentication ";
    $log->error("Unauthorized. Device key autentication");
    exit();
}

if ($route->controller == 'input' && $route->action == 'bulk') {
    $route->format = 'json';
} elseif ($route->controller == 'input' && $route->action == 'post') {
    $route->format = 'json';
}

$output = controller($route->controller);

if ($output['content'] == EMPTY_ROUTE && $settings["public_profile"]["enabled"] && $route->controller!='admin') {
    $userid = $user->get_id($route->controller);
    if ($userid) {
        $route->subaction = $route->action;
        $session['userid'] = $userid;
        $session['username'] = $route->controller;
        $session['read'] = 1;
        $session['profile'] = 1;
        $route->controller = $settings["public_profile"]["controller"];
        $route->action = $settings["public_profile"]["action"];
        $output = controller($route->controller);

        if ($output["content"]=="" && $route->subaction=="graph") {
            $route->controller = "graph";
            $route->action = "";
            $_GET['userid'] = $userid;
            $output = controller($route->controller);
        }
    }
}

if ($output['content'] === EMPTY_ROUTE) {

    $actions = implode("/", array_filter(array($route->action, $route->subaction)));
    $message = sprintf(_('%s cannot respond to %s'), sprintf("<strong>%s</strong>", ucfirst($route->controller)), sprintf('<strong>"%s"</strong>', $actions));
    header($_SERVER["SERVER_PROTOCOL"]." 406 Not Acceptable");
    $title = _('406 Not Acceptable');
    $plain_text = _('Route not found');
    $intro = sprintf('%s %s', _('URI not acceptable.'), $message);
    $text = _('Try another link from the menu.');
    if ($route->format==='html') {
        $output['content'] = sprintf('<h2>%s</h2><p class="lead">%s.</p><p>%s</p>', $title, $intro, $text);
    } else {
        $output['content'] = array(
            'success'=> false,
            'message'=> sprintf('%s. %s', $title, $plain_text)
        );
    }
    $log->warn(sprintf('%s|%s', $title, implode('/', array_filter(array($route->controller,$route->action,$route->subaction)))));
}


if ($output['content'] == "" && (!isset($session['read']) || (isset($session['read']) && !$session['read']))) {
    $log->error(sprintf('%s|%s', _('Not Authenticated'), implode('/', array_filter(array($route->controller,$route->action,$route->subaction)))));
    $route->controller = "user";
    $route->action = "login";
    $route->subaction = "";
    $message = urlencode(_('Authentication Required'));
    $referrer = urlencode(base64_encode(filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL)));
    $route->query = sprintf("msg=%s&ref=%s", $message, $referrer);
    $output = controller($route->controller);
}

$output['route'] = $route;
$output['session'] = $session;


if ($route->format == 'json') {
    if ($route->controller=='time') {
        header('Content-Type: text/plain');
        print $output['content'];
    } elseif ($route->controller=='input' && $route->action=='post') {
        header('Content-Type: text/plain');
        print $output['content'];
    } elseif ($route->controller=='input' && $route->action=='bulk') {
        header('Content-Type: text/plain');
        print $output['content'];
    } else {
        header('Content-Type: application/json');
        if (!empty($output['message'])) {
            header(sprintf('X-CAW-message: %s', $output['message']));
        }
        print json_encode($output['content']);
        if (json_last_error()!=JSON_ERROR_NONE) {
            switch (json_last_error()) {
                case JSON_ERROR_DEPTH:
                    $log->error("json_encode - $route->controller: Maximum stack depth exceeded");
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $log->error("json_encode - $route->controller: Underflow or the modes mismatch");
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $log->error("json_encode - $route->controller: Unexpected control character found");
                    break;
                case JSON_ERROR_SYNTAX:
                    $log->error("json_encode - $route->controller: Syntax error, malformed JSON");
                    break;
                case JSON_ERROR_UTF8:
                    $log->error("json_encode - $route->controller: Malformed UTF-8 characters, possibly incorrectly encoded");
                    break;
                default:
                    $log->error("json_encode - $route->controller: Unknown error");
                    break;
            }
        }
    }
} else if ($route->format == 'html') {
    if ($embed == 1) {
        print view("Frontend/embed.php", $output);
    } else {
        $menu = array();
        $menu["setup"] = array("name"=>"Setup", "order"=>1, "icon"=>"menu", "default"=>"feed/view", "l2"=>array());
        if (!$session["write"]) $menu["setup"]["name"] = "Telemetry";

        load_menu();
        $output['menu'] = $menu;

        $output['svg_icons'] = view("Theme/svg_icons.svg", array());

        $output['page_classes'][] = $route->controller;

        if (!$session['read']) {
            $output['page_classes'][] = 'collapsed manual';
        } else {
            if (!in_array("manual",$output['page_classes'])) $output['page_classes'][] = 'auto';
        }
        print view("Frontend/Telemetrytheme.php", $output);
    }

} elseif ($route->format == 'text') {
    header('Content-Type: text/plain');
    print $output['content'];
} elseif ($route->format == 'csv') {
    header('Content-Type: text/csv');
    print $output['content'];
} else {
    header($_SERVER["SERVER_PROTOCOL"]." 406 Not Acceptable");
    print "URI not acceptable. Unknown format '".$route->format."'.";
}
