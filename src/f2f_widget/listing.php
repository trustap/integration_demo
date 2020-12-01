<?php
    require '../init.php';

    $stmt = $mysql_conn->prepare('SELECT * FROM f2f_listings WHERE id = ?;');
    $stmt->bind_param('i', $_GET['id']);
    $stmt->execute();
    $rows = $stmt->get_result();
    $rows->data_seek(0);
    $row = $rows->fetch_assoc();
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

        Description:
        <div>
            <?php echo htmlspecialchars($row['descr']); ?>
        </div>

        Price:
        <div>
            $<?php echo htmlspecialchars($row['price']) ?>
        </div>

        <div>
            <?php echo htmlspecialchars($row['joins']) ?> people
            applied for this item
        </div>

        <?php
            if ($row['trustap_listing_id'] == NULL) {
                // TODO Add functionality to enable Trustap for a transaction.
                ?>
                    <p>Trustap is not enabled for this transaction</p>
                <?php
            } else if (!$row['sold']) {
                ?>
                    <div id="pay-with-trustap"></div>
                    <script src="<?php echo $trustap_lib; ?>"></script>
                    <script src="/js/trustapi_config.js"></script>
                    <script>
                        const trustApi = trustap.api(trustApiConf);
                        trustApi.p2p.singleUseListings.setSafePaymentWidget({
                            containerId: 'pay-with-trustap',
                            listingId: '<?php echo $row['trustap_listing_id']; ?>',
                            onJoinTransaction: function () {
                                window.top.location.href = 'transactions.php';
                            },
                        });
                    </script>
                <?php
            }

            $stmt->close();
        ?>
    </body>
</html>
