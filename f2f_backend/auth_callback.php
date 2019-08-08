<?php
require '../init.php';

if (!isset($_SESSION['login_redirect'])) {
    die('Authorisation is not in progress');
}
$login_redirect = $_SESSION['login_redirect'];

if ($login_redirect['state'] != $_GET['state']) {
    die('Mismatched `state` parameter');
}

$curl = curl_init('https://sso.trustap.com/auth/realms/' . $realm .  '/protocol/openid-connect/token');
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$header = array(
    'Content-type: application/x-www-form-urlencoded',
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');

$body = (
    'grant_type=authorization_code'
    . '&client_id=' . $client_id
    . '&client_secret=' . $client_secret
    . '&code=' . $_GET['code']
    . '&redirect_uri=' . $redirect_uri
);
curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

$resp_body = curl_exec($curl);
$resp_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ($resp_status != 200) {
    die("Unexpected response from authorisation server (status ${resp_status}): ${resp_body}");
}

$_SESSION['auth'] = json_decode($resp_body, true);

header('Location: ' . $_SESSION['login_redirect']['next']);
?>
