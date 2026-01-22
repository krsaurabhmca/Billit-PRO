<?php
/**
 * ============================================================================
 * EXPORT DATABASE TO MULTI-SHEET EXCEL (XML FORMAT)
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

require_role('admin');

// Increase time limit for large databases
set_time_limit(300);
ini_set('memory_limit', '512M');

// Filename
$filename = "Billit_Backup_" . date('Y-m-d_H-i') . ".xls";

// Get all tables
$tables = [];
$result = mysqli_query($connection, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

// Function to clean data for XML
function clean_xml_data($str) {
    if (is_null($str)) return '';
    // Remove control characters
    $str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $str);
    return htmlspecialchars($str);
}

// Headers for Excel Download
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// START XML OUTPUT
echo '<?xml version="1.0"?>';
echo '<?mso-application progid="Excel.Sheet"?>';
?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Author><?php echo APP_NAME; ?></Author>
  <Created><?php echo date('c'); ?></Created>
 </DocumentProperties>
 <Styles>
  <Style ss:ID="HeaderStyle">
   <Font ss:Bold="1" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#4f46e5" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="DefaultStyle">
   <Alignment ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   </Borders>
  </Style>
 </Styles>

<?php
foreach ($tables as $table) {
    // Clean Tab Name (Excel limit 31 chars, no special chars)
    $sheet_name = substr($table, 0, 31);
    
    echo '<Worksheet ss:Name="' . $sheet_name . '">';
    echo '<Table>';
    
    // Get Columns
    $columns = [];
    $col_res = mysqli_query($connection, "SHOW COLUMNS FROM `$table`");
    
    // HEADER ROW
    echo '<Row>';
    while ($col = mysqli_fetch_assoc($col_res)) {
        $columns[] = $col['Field'];
        echo '<Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">' . clean_xml_data(strtoupper(str_replace('_', ' ', $col['Field']))) . '</Data></Cell>';
    }
    echo '</Row>';
    
    // DATA ROWS
    $data_res = mysqli_query($connection, "SELECT * FROM `$table`");
    while ($row = mysqli_fetch_assoc($data_res)) {
        echo '<Row>';
        foreach ($columns as $col_name) {
            $val = $row[$col_name];
            // Detect Type
            $type = "String";
            if (is_numeric($val) && (strlen($val) < 15)) { // Long numbers as strings to prevent scientific notation
                 $type = "Number";
            }
            
            echo '<Cell ss:StyleID="DefaultStyle"><Data ss:Type="' . $type . '">' . clean_xml_data($val) . '</Data></Cell>';
        }
        echo '</Row>';
    }
    
    echo '</Table>';
    echo '</Worksheet>';
}
?>
</Workbook>
<?php exit; ?>
