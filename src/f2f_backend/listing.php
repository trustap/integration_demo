<?php
    require '../init.php';

    $stmt = $mysql_conn->prepare('SELECT * FROM f2f_listings WHERE id = ?;');
    $stmt->bind_param('i', $_GET['id']);
    $stmt->execute();
    $rows = $stmt->get_result();
    $rows->data_seek(0);
    $row = $rows->fetch_assoc();

    if (isset($_POST['submitted']) || isset($_SESSION['login_redirect'])) {
        if (!isset($_SESSION['auth'])) {
            $state = sha1(openssl_random_pseudo_bytes(1024));

            $_SESSION['login_redirect'] = array(
                'state' => $state,
                'next' => '/f2f_backend/listing.php?id=' . $_GET['id'],
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
        unset($_SESSION['login_redirect']);

        $resp = $trustapi->call(
            'POST',
            'p2p/single_use_listings/' . $row['trustap_listing_id'] . '/create_transaction',
            NULL
        );

        // We don't handle refreshing of tokens in this tutorial so we simply
        // destroy the current token once it has been used.
        unset($_SESSION['auth']);

        if ($resp['status'] != 201) {
            die("Couldn't create Trustap transaction (status ${resp['status']}): " .  json_encode($resp['body']));
        }

        header('Location: transactions.php');
        die();
    }
?>
<html>
    <body>
        <h1>
            <?php
                echo htmlspecialchars($row['name']);
                if ($row['sold']) {
                    echo ' (Sold)';
                }
            ?>
        </h1>

        <p>
            <strong>Description:</strong>
            <span>
                <?php echo htmlspecialchars($row['descr']); ?>
            </span>
        </p>
        <p>
            <strong>Price:</strong>
            <span>
                $<?php echo htmlspecialchars($row['price']) ?>
            </span>
        </p>
        <p>
            <?php echo htmlspecialchars($row['joins']) ?> people
            applied for this item
        </p>
        <?php
            if ($row['trustap_listing_id'] == NULL) {
                ?>
                    <p>Trustap is not enabled for this transaction</p>
                <?php
            } else if (!$row['sold']) {
                ?>
                    <form method="POST">
                        <input type="submit" name="submitted" value="Pay With Trustap" />
                    </form>
                <?php
            }

            $stmt->close();
        ?>
    </body>
</html>
