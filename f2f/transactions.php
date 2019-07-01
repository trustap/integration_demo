<?php
    require '../init.php';
?>
<html>
    <head>
        <style>
body {
    background-color: #ddd;
}

#txs {
    width: 400px;
    height: 300px;
    margin: 2rem auto;
}
        </style>
    </head>
    <body>
        <div id="txs"></div>

        <script src="<?php echo $trustapLib; ?>"></script>
        <script src="/js/trustapi_config.js"></script>
        <script>
            const trustApi = trustap.api(trustApiConf);
            trustApi.setTrustapWidget('txs');
        </script>
    </body>
</html>
