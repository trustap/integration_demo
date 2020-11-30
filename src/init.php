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
    var $access_token;

    function __construct($api_uri, $access_token) {
        $this->api_uri = $api_uri;
        $this->access_token = $access_token;
    }

    function call($method, $path, $body) {
        $url = $this->api_uri . $path;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $header = array(
            'Content-type: application/json',
            'Authorization: Bearer ' . $this->access_token,
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        if ($method == 'POST' || $method == 'PUT') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
        }
        $body_res = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $body = json_decode($body_res, true);

        return array('status' => $status, 'body' => $body);
    }
}

$trustapi = new TrustAPI(
    $api_uri,
    isset($_SESSION['auth']) ? $_SESSION['auth']['access_token'] : NULL
);
?>
