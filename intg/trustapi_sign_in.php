<?php
    require '../init.php';
?>
<!DOCTYPE html>
<html>
    <head>
    </head>
    <body>
        <script src="<?php echo $trustapHost; ?>/plugin.js"></script>
        <script>
            trustap.signIn();
        </script>
    </body>
</html>
