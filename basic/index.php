<?php
    require '../init.php';
?>
<html>
    <body>
        <h1>Home</h1>
        <ul>
            <li><a href="sell.php">Sell</a></li>
            <li><a href="transactions.php">Transactions</a></li>
        </ul>
        <h2>Listings</h2>
        <ul>
<?php
    $rows = $mysql_conn->query('SELECT * FROM basic_listings');
    $rows->data_seek(0);
    while ($row = $rows->fetch_assoc()) {
        echo '<li>';
        echo htmlspecialchars($row['name']) . '(' .  htmlspecialchars($row['descr']) . '): $' .  htmlspecialchars($row['price']);
        echo " <a href='listing.php?id=" . htmlspecialchars($row['id']) . "'>Visit</a>";
        echo htmlspecialchars($row['trustap_listing_id'] == NULL ? '' : ' (Trustap Enabled)');
        echo '</li>';
    }
?>
        </ul>
    </body>
</html>
