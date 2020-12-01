<?php
    include 'init.php';

    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
        die('Unauthenticated access');
    } else if ($_SERVER['PHP_AUTH_USER'] != $webhook_user || $_SERVER['PHP_AUTH_PW'] != $webhook_pass) {
        die("Incorrect credentials");
    }

    // `get_ls_id` gets the listing ID associated with a transaction from a
    // transaction event.
    function get_ls_id($trustapi, $tx_event) {
        // If the optional snapshot of the transaction is provided with the
        // event then we use that, otherwise we make a request to retrieve the
        // transaction.
        if (isset($tx_event['target_preview'])) {
            $tx = $tx_event['target_preview'];
        } else {
            $tx_id = $tx_event['target_id'];
            $resp = $trustapi->call_with_api_key('GET', 'p2p/transactions/' . $tx_id, NULL);

            if ($resp['status'] != 200) {
                die("Couldn't get transaction (status ${resp['status']}): " . json_encode($resp['body']));
            }

            $tx = $resp['body'];
        }

        if (!isset($tx['listing_type'])) {
            die("Transaction wasn't created from a listing");
        }

        $ls_type = $tx['listing_type'];
        if ($ls_type != 'single_use') {
            die("Unexpected listing type: '" . $ls_type . "'");
        }

        return $tx['listing_id'];
    }

    $body = file_get_contents('php://input');
    $event = json_decode($body, true);

    if ($event['code'] == 'p2p_tx.joined') {
        $ls_id = get_ls_id($trustapi, $event);

        $stmt = $mysql_conn->prepare('UPDATE f2f_listings SET joins = joins + 1 WHERE trustap_listing_id = ?;');
        $stmt->bind_param('s', $ls_id);
        if (!$stmt->execute()) {
            die("Couldn't update joins");
        }
    } if ($event['code'] == 'p2p_tx.remainder_paid') {
        $ls_id = get_ls_id($trustapi, $event);

        $stmt = $mysql_conn->prepare('UPDATE f2f_listings SET sold = TRUE WHERE trustap_listing_id = ?;');
        $stmt->bind_param('s', $ls_id);
        if (!$stmt->execute()) {
            die("Couldn't update status");
        }
    }
?>
