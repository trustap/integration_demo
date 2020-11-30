<?php
    require '../init.php';

    if (isset($_GET['submitted'])) {
        $trustap_listing_id = NULL;
        if ($_GET['trustap_listing_id'] != '') {
            $trustap_listing_id = $_GET['trustap_listing_id'];
        }
        $stmt = $mysql_conn->prepare("
            INSERT INTO online_listings (name, descr, price, trustap_listing_id)
            VALUES (?, ?, ?, ?);
        ");
        $stmt->bind_param('ssis', $_GET['name'], $_GET['descr'], $_GET['price'], $trustap_listing_id);
        if (!$stmt->execute()) {
            die("Couldn't insert: " . $stmt->error);
        }
        $stmt->close();

        header('Location: index.php');
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
        <form id="listing">
            <label for="name">Name: </label><input type="text" id="name" name="name" value="Car" />
            <br />
            <label for="descr">Description: </label><input type="text" id="descr" name="descr" value="Green" />
            <br />
            <label for="price">Price: $</label><input type="number" id="price" name="price" value="1000" />
            <span id="trustap_fee"></span>
            <br />
            <div id="trustap"></div>
            <br />
            <input type="hidden" name="submitted" value="true" />
            <input type="submit" value="Submit" />
        </form>

        <script src="<?php echo $trustap_lib; ?>"></script>
        <script src="/js/trustapi_config.js"></script>
        <script>
            const trustApi = trustap.api(trustApiConf);
            const useTrustapWidget =
                trustApi.basic.singleUseListings.setUseTrustapWidget({containerId: 'trustap'});

            const trustapFee = document.getElementById('trustap_fee');

            let pricing;
            let renderTrustapFee = function (trustapEnabled) {
                const price = document.getElementById('price').valueAsNumber * 100;
                if (trustapEnabled && price > 0.50) {
                    trustApi.basic.getCharge('usd', price).then(function (pricing_) {
                        pricing = pricing_;
                        trustapFee.innerHTML = '&plus; $' + (pricing.charge / 100) + ' Trustap fee';
                    });
                } else {
                    trustapFee.innerHTML = '';
                }
            };

            document.getElementById('price').addEventListener('input', function (e) {
                renderTrustapFee(useTrustapWidget.enabled());
            });
            useTrustapWidget.onChange(renderTrustapFee);

            trustApi.basic.singleUseListings.beforeSubmit('listing', function (form) {
                const listingId = form.elements['trustap_listing_id'].value;
                if (!listingId) {
                    return null;
                }
                return {
                    'listing_id': listingId,
                    'description': form.elements['name'].value + ': ' + form.elements['descr'].value,
                    'currency': 'usd',
                    'price': form.elements['price'].value * 100,
                    'charge': pricing.charge,
                    'charge_calculator_version': pricing.charge_calculator_version,
                };
            });
        </script>
    </body>
</html>
