<?php
    require '../init.php';

    if (isset($_GET['submitted'])) {
        $sql = "
            INSERT INTO p2p_listings (name, descr, price, trustap_listing_id)
            VALUES ('${_GET['name']}', '${_GET['descr']}', ${_GET['price']}, '${_GET['trustap_listing_id']}')
        ";
        if (!$mysql_conn->query($sql)) {
            die("Couldn't insert: " . $mysql_conn->error);
        }

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
            <br />
            Use Trustap: <div id="trustap"></div>
            <br />
            <input type="hidden" name="submitted" value="true" />
            <input type="submit" value="Submit" />
        </form>

        <script src="<?php echo $trustapHost; ?>/plugin.js"></script>
        <script src="/js/trustapi_config.js"></script>
        <script>
            const trustApi = trustap.api(trustApiConf);
            trustApi.p2p.singleUseListings.createUseTrustapCheckbox('trustap', 'use_trustap');
            trustApi.p2p.singleUseListings.beforeSubmit('listing', function (form) {
                const listingId = form.elements['trustap_listing_id'].value;
                if (!listingId) {
                    return null;
                }
                return {
                    'listing_id': listingId,
                    'description': form.elements['name'].value + ': ' + form.elements['descr'].value,
                };
            });
        </script>
    </body>
</html>
