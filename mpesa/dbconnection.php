<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'id22171673_root');
define('DB_PASSWORD', '');
define('DB_NAME', 'id22171673_mypharmacydatabase');

try {
    $db = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if (!$db) {
        die("Connection failed: " . mysqli_connect_error());
        echo "Connection failed";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
