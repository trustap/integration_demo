<?php
    require '../init.php';

    $rows = $mysql_conn->query('SELECT * FROM p2p_listings WHERE id = ' . $_GET['id']);
    $rows->data_seek(0);
    $row = $rows->fetch_assoc();
?>
<html>
    <head>
        <style>
.modal-mask {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, .5);
    display: table;
    transition: opacity .3s ease;
}

.modal-wrapper {
    display: table-cell;
    vertical-align: middle;
}

.modal-container {
    width: 300px;
    height: 300px;
    display: block;
    margin: 0px auto;
    background-color: #fff;
    border: none;
    border-radius: 2px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
    transition: all .3s ease;
}
        </style>
    </head>
    <body>
        <h1><?php echo $row['name'] ?></h1>

        Description:
        <div>
            <?php echo $row['descr'] ?>
        </div>

        Price:
        <div>
            $<?php echo $row['price'] ?>
        </div>

        <?php
            if ($row['trustap_listing_id']) {
                ?>
                    <a
                        id="pay-with-trustap"
                        href="#"
                        style="background-color: green; color: white;"
                        >
                        Safe Payment
                    </a>
                    <script src="<?php echo $trustapHost; ?>/plugin.js"></script>
                    <script src="/js/trustapi_config.js"></script>
                    <script>
                        const trustApi = trustap.api(trustApiConf);
                        trustApi.p2p.singleUseListings.setSafePaymentButton({
                            btnId: 'pay-with-trustap',
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
        ?>
    </body>
</html>
