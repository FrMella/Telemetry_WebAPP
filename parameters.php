<?php

/*
 * Parameters definition
 */

defined('CAWTHRON_ENGINE') or die('RESTRICTED ACCESS');

class Param
{
    private $route;
    private $user;
    private $params = array();

    private $allowed_apis = array("input/post","input/bulk");

    public $sha256base64_response = false;

    public function __construct($route, $user)
    {
        $this->route = $route;
        $this->user = $user;
        $this->load();
    }

    public function load()
    {
        $this->params = array();

        foreach ($_GET as $key => $val) {
            if (is_array($val)) {
                $val = array_map("stripslashes", $val);
            } else {
                $val = stripslashes($val);
            }
            $this->params[$key] = $val;
        }
        foreach ($_POST as $key => $val) {
            if (is_array($val)) {
                $val = array_map("stripslashes", $val);
            } else {
                $val = stripslashes($val);
            }
            $this->params[$key] = $val;
        }

        $allowed_apis = array_flip($this->allowed_apis);
        $api = $this->route->controller."/".$this->route->action;
        if (!isset($allowed_apis[$api])) {
            return false;
        }


        if (isset($_SERVER["CONTENT_TYPE"]) && $_SERVER["CONTENT_TYPE"]=="aes128cbc") {
            if (!isset($_SERVER["HTTP_AUTHORIZATION"])) {
                echo "missing authorization header";
                die;
            }
            $authorization = explode(":", $_SERVER["HTTP_AUTHORIZATION"]);
            if (count($authorization)!=2) {
                echo "authorization header format should be userid:hmac";
                die;
            }
            $userid = $authorization[0];
            $hmac1 = $authorization[1];

            $apikey = $this->user->get_apikey_write($userid);
            if ($apikey===false) {
                echo "Username not found";
                die;
            }

            $base64EncryptedData = file_get_contents('php://input');
            if ($base64EncryptedData=="") {
                echo "no content";
                die;
            }

            $encryptedData = base64_decode(str_replace(array('-','_'), array('+','/'), $base64EncryptedData));

            $dataString = @openssl_decrypt(substr($encryptedData, 16), 'AES-128-CBC', hex2bin($apikey), OPENSSL_RAW_DATA, substr($encryptedData, 0, 16));

            $hmac2 = hash_hmac('sha256', $dataString, hex2bin($apikey));

            if (!hash_equals($hmac1, $hmac2)) {
                echo "invalid data";
                die;
            }

            global $session;
            $session["write"] = true;
            $session["read"] = true;
            $session["userid"] = $userid;

            foreach (explode('&', $dataString) as $chunk) {
                $param = explode("=", $chunk);
                if (count($param)==2) {
                    $key = $param[0];
                    $val = $param[1];
                    $this->params[$key] = $val;
                }
            }

            $this->sha256base64_response = str_replace(array('+','/'), array('-','_'), base64_encode(hash("sha256", $dataString, true)));
        }
    }

    public function val($index)
    {
        if (isset($this->params[$index])) {
            return $this->params[$index];
        } else {
            return null;
        }
    }

    public function exists($index)
    {
        if (isset($this->params[$index])) {
            return true;
        } else {
            return false;
        }
    }
}
