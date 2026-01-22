<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

echo "<h2>Implementing Returns Module...</h2>";

$sql = file_get_contents('database/returns_extension.sql');
$statements = explode(';', $sql);

foreach($statements as $statement) {
    if (trim($statement) != '') {
        if(mysqli_query($connection, $statement)) {
            echo "Executed: " . substr($statement, 0, 50) . "...<br>";
        } else {
            echo "<span style='color:red'>Error: " . mysqli_error($connection) . "</span><br>";
        }
    }
}

echo "<h3>Done! Tables Created.</h3>";
echo "<a href='index.php'>Go Home</a>";
?>
