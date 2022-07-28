

<?php
/*
    ### Telemetry Telemetry proposal ###
    #### Proof of concept engine core ###
    is_http_request: revisa que estas accediendo desde el browser
    get_app_path: busca el inicio del programa
    db_check: devuelve que las tablas en la base de datos fueron creadas correctamente
    controller: busca controlladores externos de acuerdo al patron de diseno MVC
    view : devuelve la vista
    get : get a http
    post : post a http request
    prop:
    request_header: devuelve el encabezado del index
    version: return de version


*/

defined('Telemetry_ENGINE') or die('RESTRICTED ACCESS');



function is_http_request() {
    if (server('HTTPS') == 'on') {
        return true;
    } elseif (server('HTTP_X_FORWARDED_PROTO') == "https") {
        return true;
    } elseif (server('HTTP_X_FORWARDED_PROTO') == "https") {
        return true;
    }
    return false;
}

function get_app_path($manual_domain = false) {
    if (is_http_request()){
        $proto = "https";
    }else {
        $proto = "http";
    }

    if ($manual_domain) {
        return "$proto://".$manual_domain."/";
    }
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])){
        $path = dirname("$proto://". server('HTTP_X_FORWARDED_HOST'). server('SCRIPT_NAME')) . "/";

    }else {
        $path = dirname("$proto://" . server('HTTP_HOST') . server ('SCRIPT_NAME')) . "/";
    }

    return $path;
}

function db_check($sql, $database){
    $query_result = $sql->query("SELECT count(table_schemes) from information_schema.tables WHERE table_schemes = '$database'");
    $row = $query_result->fetch_array();
    if ($row['0']>0){
        return true;
    }else {
        return false;
    }
}


function controller ($controller_name)
{
    $route_output = array('content'=>EMPTY_ROUTE);

    if ($controller_name) {
        $controller = $controller_name."_controller";
        $controllerScript = "Ext_Modules/".$controller_name."/".$controller.".php";
        if (is_file($controllerScript)) {
            require_once $controllerScript;
            $route_output = $controller();
            if (!is_array($route_output) || !isset($route_output["content"])) {
                $route_output = array("content"=>$route_output);
            }
        }
    }
    return $route_output;
}

function view($filepath, array $args = array()) {
    global $path;
    $args['path'] = $path;
    $content = '';
    if (file_exists($filepath)) {
        extract($args);
        ob_start();
        include "$filepath";
        $content = ob_get_clean();
    }
    return $content;
}

function get($index, $missing_error = false, $default=null) {

    $val = $default;
    if (isset($_GET[$index])) {
        $val = rawurldecode($_GET[$index]);
    } else if ($missing_error) {
        header('Content-Type: text/plain');
        die("missing $index parameter");
    }

    $val = stripslashes($val);
    return $val;
}

function post($index, $missing_error = false, $default=null) {
    $value_return = $default;
    if (isset($_POST[$index])) {
        if (!is_array($_POST[$index])) {
            $value_return = rawurldecode($_POST[$index]);
        } else {
            $SANTIZED_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            if (!empty($SANTIZED_POST[$index])) {
                $value_return = $SANTIZED_POST[$index];
            }
        }
    } else if ($missing_error) {
        header('Content-Type: text/plain');
        die("missing $index parameter");
    }

    if (is_array($value_return)) {
        $value_return = array_map("stripslashes", $value_return);
    } else {
        $value_return = stripslashes($value_return);
    }
    return $value_return;
}

function prop($index, $missing_error = false, $default=null){
    $value_return = $default;
    if (isset($_GET[$index])) {
        $value_return = $_GET[$index];
    }
    else if (isset($_POST[$index])) {
        $value_return = $_POST[$index];
    }
    else if ($error_if_missing) {
        header('Content-Type: text/plain');
        die("missing $index parameter");
    }

    if (is_array($value_return)) {
        $value_return = array_map("stripslashes", $value_return);
    } else {
        $value_return = stripslashes($value_return);
    }
    return $value_return;
}

function request_header($index) {
    $value_return = null;
    $headers = apache_request_headers();
    if (isset($headers[$index])) {
        $value_return = $headers[$index];
    }
    return $value_return;
}

function server($index)   {
    $value_return = null;
    if (isset($_SERVER[$index])) {
        $value_return = $_SERVER[$index];
    }
    return $value_return;
}

function delete($index) {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $value_return = null;
    if (isset($_DELETE[$index])) {
        $value_return = $_DELETE[$index];
    }

    if (is_array($value_return)) {
        $value_return = array_map("stripslashes", $value_return);
    } else {
        $value_return = stripslashes($value_return);
    }
    return $value_return;
}

function put($index)
{
    parse_str(file_get_contents("php://input"), $_PUT);
    $value_return = null;
    if (isset($_PUT[$index])) {
        $value_return = $_PUT[$index];
    }

    if (is_array($value_return)) {
        $value_return = array_map("stripslashes", $value_return);
    } else {
        $value_return = stripslashes($value_return);
    };
    return $value_return;
}


function version() {
    $version_file = json_decode(file_get_contents('./version.json'));
    return $version_file->version;
}

function load_db_scheme(){
    $schema = array();
    $dir = scandir("ext_modules");
    for ($i=2; $i<count($dir); $i++) {
        if (filetype("Ext_Modules/".$dir[$i])=='dir' || filetype("Ext_Modules/".$dir[$i])=='link') {
            if (is_file("Ext_Modules/".$dir[$i]."/".$dir[$i]."_schema.php")) {
                require "Ext_Modules/".$dir[$i]."/".$dir[$i]."_schema.php";
            }
        }
    }
    return $schema;
}

function load_menu(){

    global $menu;
    $dir = scandir("ext_modules");
    for ($i=2; $i<count($dir); $i++)
    {
        if (filetype("Ext_Modules/".$dir[$i])=='dir' || filetype("ext_odules/".$dir[$i])=='link')
        {
            if (is_file("Ext_Modules/".$dir[$i]."/".$dir[$i]."_menu.php"))
            {
                require "Ext_Modules/".$dir[$i]."/".$dir[$i]."_menu.php";
            }
        }
    }
}

function http_request($method, $url, $data){
    $options = array();

    if ($method=="GET") {
        $urlencoded = http_build_query($data);
        $url = "$url?$urlencoded";
    } elseif ($method=="POST") {
        $options[CURLOPT_POST] = 1;
        $options[CURLOPT_POSTFIELDS] = $data;
    }

    $options[CURLOPT_URL] = $url;
    $options[CURLOPT_RETURNTRANSFER] = 1;
    $options[CURLOPT_CONNECTTIMEOUT] = 2;
    $options[CURLOPT_TIMEOUT] = 5;

    $curl = curl_init();
    curl_setopt_array($curl, $options);
    $resp = curl_exec($curl);
    curl_close($curl);
    return $resp;
}

function system_error($message){
    return array("success"=>false, "message"=>$message);
}

function call_hook($function_name, $args) {
    $dir = scandir("ext_modules");
    for ($i=2; $i<count($dir); $i++) {
        if (filetype("Ext_Modules/".$dir[$i])=='dir' || filetype("Ext_Modules/".$dir[$i])=='link') {
            if (is_file("Ext_Modules/".$dir[$i]."/".$dir[$i]."_hooks.php")) {
                require "Ext_Modules/".$dir[$i]."/".$dir[$i]."_hooks.php";
                if (function_exists($dir[$i].'_'.$function_name)==true) {
                    $hook = $dir[$i].'_'.$function_name;
                    return $hook($args);
                }
            }
        }
    }
}

function get_client_ip_env() {
    $ipaddress = filter_var(getenv('REMOTE_ADDR'), FILTER_VALIDATE_IP);
    if (empty($ipaddress)) {
        $ipaddress = '';
    }
    return $ipaddress;
}

function generate_secure_key($length) {
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes($length));
    } else {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
}

