<?php
    require '../init.php';

    // This is a simple workaround for refreshing tokens. If the local token has
    // expired then `?refresh=1` can be added to the URL to issue a new one.
    if (isset($_GET['refresh'])) {
        unset($_SESSION['auth']);
    }

    if (isset($_POST['submitted']) || isset($_SESSION['login_redirect'])) {
        if (!isset($_SESSION['auth'])) {
            $state = sha1(openssl_random_pseudo_bytes(1024));

            $_SESSION['login_redirect'] = array(
                'state' => $state,
                'next' => '/f2f_backend/sell.php',
                'params' => $_POST,
            );

            header(
                'Location: https://sso.trustap.com/auth/realms/' . $realm . '/protocol/openid-connect/auth'
                . '?client_id=' . $client_id
                . '&redirect_uri=' . $redirect_uri
                . '&response_type=code'
                . '&scope=openid'
                . '&state=' . $state
            );
            die();
        }

        $params = $_POST;
        if (isset($_SESSION['login_redirect'])) {
            $params = $_SESSION['login_redirect']['params'];
            unset($_SESSION['login_redirect']);
        }

        $resp = $trustapi->call('POST', 'p2p/me/single_use_listings', NULL);
        if ($resp['status'] != 201) {
            // We don't handle refreshing of tokens in this tutorial so we simply
            // destroy the current token once it has been used.
            unset($_SESSION['auth']);

            die("Couldn't create Trustap listing (status ${resp['status']}): " .  json_encode($resp['body']));
        }
        $lsId = $resp['body']['id'];

        $resp = $trustapi->call(
            'POST',
            'p2p/single_use_listings/' . $lsId . '/set_description',
            array('description' => $params['name'] . ': ' . $params['descr'])
        );

        // We don't handle refreshing of tokens in this tutorial so we simply
        // destroy the current token once it has been used.
        unset($_SESSION['auth']);

        if ($resp['status'] != 200) {
            die("Couldn't update Trustap listing (status ${resp['status']}): " .  json_encode($resp['body']));
        }

        $stmt = $mysql_conn->prepare("
            INSERT INTO f2f_listings (name, descr, price, trustap_listing_id)
            VALUES (?, ?, ?, ?);
        ");
        $stmt->bind_param('ssis', $params['name'], $params['descr'], $params['price'], $lsId);
        if (!$stmt->execute()) {
            die("Couldn't insert: " . $stmt->error);
        }
        $stmt->close();

        header('Location: index.php');
        die();
    }
?>
<html>
    <head>
        <style>
#trustap {
    display: inline;
}
        </style>
    </head>
    <body>
        <h1>New Listing</h1>
        <form method="POST">
            <div>
                <label for="name">Name: </label><input type="text" id="name" name="name" value="Car" />
            </div>
            <div>
                <label for="descr">Description: </label><input type="text" id="descr" name="descr" value="Green" />
            </div>
            <div>
                <label for="price">Price: $</label><input type="number" id="price" name="price" value="1000" />
            </div>
            <input type="submit" name="submitted" value="Submit" />
        </form>
    </body>
</html>
