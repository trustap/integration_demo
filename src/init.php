<?php
// `init.php` does general initialisation, such as setting up database
// connections, defining helper functions, etc.

require 'config.php';

session_start();

$mysql_conn = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
if ($mysql_conn->connect_error) {
        die("Connection failed: " . $mysql_conn->connect_error);
}

class TrustAPI {
    var $api_uri;
    var $api_key;
    var $access_token;

    function __construct($api_uri, $api_key, $access_token) {
        $this->api_uri = $api_uri;
        $this->api_key = $api_key;
        $this->access_token = $access_token;
    }

    function call($method, $path, $body) {
        return $this->_call($method, $path, $body, false);
    }

    function _call($method, $path, $body, $use_api_key) {
        $url = $this->api_uri . $path;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $header = array('Content-type: application/json');

        if ($use_api_key) {
            curl_setopt($curl, CURLOPT_USERPWD, $this->api_key . ":");
        } else {
            $header[] = 'Authorization: Bearer ' . $this->access_token;
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        if ($method == 'POST' || $method == 'PUT') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
        }
        $body_res = curl_exec($curl);
        if ($body_res === false) {
            die("Couldn't perform `$method $url`: " . curl_error($curl));
        }

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $body = json_decode($body_res, true);

        return array('status' => $status, 'body' => $body);
    }

    function call_with_api_key($method, $path, $body) {
        return $this->_call($method, $path, $body, true);
    }
}

$trustapi = new TrustAPI(
    $api_uri,
    $api_key,
    isset($_SESSION['auth']) ? $_SESSION['auth']['access_token'] : NULL
);
?>
