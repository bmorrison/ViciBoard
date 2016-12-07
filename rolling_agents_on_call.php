<?php
require("/srv/www/htdocs/vicidial/dbconnect_mysqli.php");
require("/srv/www/htdocs/vicidial/functions.php");

$number_of_points = 18;
$filename = 'PY9Xbh8UBDtc.php';

if (file_exists($filename)) {
	$agents_array = include($filename);
} elseif (empty($call_data)) {
    $agents_array = array();
}

$stmt = "SELECT COUNT(*) FROM vicidial_live_agents WHERE status='INCALL';";
$rslt = mysql_to_mysqli($stmt, $link);
$rows_to_print = mysqli_num_rows($rslt);
$agents_in_call = 0;
if ($rows_to_print > 0) {
	while ($rowx = mysqli_fetch_row($rslt)) {
		$agents_in_call += $rowx[0];
	}
}

// Handle raw call data
array_push($agents_array, $agents_in_call);
$agents_array = array_slice($agents_array, -$number_of_points);

echo json_encode($agents_array);

// Save the array to a file for the next update
file_put_contents($filename, '<?php return '.var_export($agents_array, true).';?>');

