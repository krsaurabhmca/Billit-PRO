<?php
/**
 * ============================================================================
 * EXPORT DATABASE TO SQL
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

require_role('admin');

// Increase time limit
set_time_limit(600);
ini_set('memory_limit', '512M');

$filename = "Billit_Backup_" . date('Y-m-d_H-i') . ".sql";

// Headers
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"$filename\"");

// Header Info
echo "-- --------------------------------------------------------\n";
echo "-- Billit Pro SQL Dump\n";
echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
echo "-- Host: " . DB_HOST . "\n";
echo "-- Database: " . DB_NAME . "\n";
echo "-- --------------------------------------------------------\n\n";

echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
echo "SET time_zone = \"+00:00\";\n\n";
echo "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
echo "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
echo "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
echo "/*!40101 SET NAMES utf8mb4 */;\n\n";

// Get tables
$tables = [];
$result = mysqli_query($connection, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

foreach ($tables as $table) {
    echo "--\n-- Table structure for table `$table`\n--\n\n";
    echo "DROP TABLE IF EXISTS `$table`;\n";
    
    // Create Table Structure
    $row2 = mysqli_fetch_row(mysqli_query($connection, "SHOW CREATE TABLE `$table`"));
    echo $row2[1] . ";\n\n";
    
    // Dump Data
    echo "--\n-- Dumping data for table `$table`\n--\n\n";
    
    $result = mysqli_query($connection, "SELECT * FROM `$table`");
    $num_fields = mysqli_num_fields($result);
    
    while ($row = mysqli_fetch_row($result)) {
        echo "INSERT INTO `$table` VALUES(";
        for ($j = 0; $j < $num_fields; $j++) {
            $row[$j] = addslashes($row[$j]);
            // Handle newlines
            $row[$j] = str_replace("\n", "\\n", $row[$j]);
            
            if (isset($row[$j])) {
                 echo '"' . $row[$j] . '"';
            } else {
                 echo 'NULL';
            }
            if ($j < ($num_fields - 1)) {
                 echo ',';
            }
        }
        echo ");\n";
    }
    echo "\n\n";
}

echo "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
echo "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
echo "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";

exit;
?>
