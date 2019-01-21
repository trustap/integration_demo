<?php
    require '../init.php';

    $stmt = $mysql_conn->prepare('SELECT * FROM basic_listings WHERE id = ?;');
    $stmt->bind_param('i', $_GET['id']);
    $stmt->execute();
    $rows = $stmt->get_result();
    $rows->data_seek(0);
    $row = $rows->fetch_assoc();
?>
<html>
    <body>
        <h1><?php echo htmlspecialchars($row['name']); ?></h1>

        Description:
        <div>
            <?php echo htmlspecialchars($row['descr']); ?>
        </div>

        Price:
        <div>
            $<?php echo htmlspecialchars($row['price']); ?>
        </div>

        <?php
            if ($row['trustap_listing_id']) {
                ?>
                    <div id="pay-with-trustap"></div>
                    <script src="<?php echo $trustapHost; ?>/plugin.js"></script>
                    <script src="/js/trustapi_config.js"></script>
                    <script>
                        const trustApi = trustap.api(trustApiConf);
                        trustApi.basic.singleUseListings.setSafePaymentWidget({
                            containerId: 'pay-with-trustap',
                            listingId: <?php echo $row['trustap_listing_id']; ?>,
                            onJoinTransaction: function () {
                                window.top.location.href = 'trustap.php';
                            },
                        });
                    </script>
                <?php
            } else {
                // TODO Add functionality to enable Trustap for a transaction.
                ?>
                    <p>Trustap is not enabled for this transaction</p>
                <?php
            }

            $stmt->close();
        ?>
    </body>
</html>
