<?php
/*
 * Router engine implementation
 */

defined('CAWTHRON_ENGINE') or die('RESTRICTED ACCESS');

class Route
{

    public $controller = '';
    public $action = '';
    public $subaction = '';
    public $subaction2 = '';
    public $query = '';
    public $method = 'GET';
    public $format = 'html';
    public $is_ajax = false;
    public function __construct($q, $documentRoot, $requestMethod)
    {
        $this->decode($q, $documentRoot, $requestMethod);
        $this->is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    public function decode($q, $documentRoot, $requestMethod)
    {
        $absolutePath = realpath(dirname(__FILE__));
        $relativeApplicationPath = str_replace($documentRoot, '', $absolutePath);
        if (!empty($relativeApplicationPath)) {
            $q = str_replace($relativeApplicationPath, '', $q);
        }
        $q = trim($q, '/');
        $q = preg_replace('/[^.\/_A-Za-z0-9-]/', '', $q);
        $args = preg_split('/[\/]/', $q);
        $lastArgIndex = sizeof($args) - 1;
        $lastArgSplit = preg_split('/[.]/', $args[$lastArgIndex]);
        if (count($lastArgSplit) > 1) {
            $this->format = $lastArgSplit[1];
        }
        $args[$lastArgIndex] = $lastArgSplit[0];

        if (count($args) > 0) {
            $this->controller = $args[0];
        }
        if (count($args) > 1) {
            $this->action = $args[1];
        }
        if (count($args) > 2) {
            $this->subaction = $args[2];
        }
        if (count($args) > 3) {
            $this->subaction2 = $args[3];
        }
        $this->query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);

        if (post('_method')=='DELETE') {
            $this->method = 'DELETE';
        } elseif (post('_method')=='PUT') {
            $this->method = 'PUT';
        } elseif (in_array($requestMethod, array('POST', 'DELETE', 'PUT'))) {
            $this->method = $requestMethod;
        } elseif ($requestMethod === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Headers: Authorization');
            header('Access-Control-Allow-Methods: GET');
            exit();
        }
    }

    public function isRouteNotDefined()
    {
        return empty($this->controller) && empty($this->action);
    }
}