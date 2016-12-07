<?php
require("/srv/www/htdocs/vicidial/dbconnect_mysqli.php");
require("/srv/www/htdocs/vicidial/functions.php");

$number_of_points = 20;

if (file_exists('rolling_calls_update_cache.php')) {
	$calls_array = include('rolling_calls_update_cache.php');
} elseif (empty($call_data)) {
    $calls_array = array();
}

$stmt = "SELECT COUNT(*) FROM vicidial_auto_calls WHERE status NOT IN('XFER');";
$rslt = mysql_to_mysqli($stmt, $link);
$rows_to_print = mysqli_num_rows($rslt);
$total_calls = 0;
if ($rows_to_print > 0) {
	while ($rowx = mysqli_fetch_row($rslt)) {
		$total_calls += $rowx[0];
	}
}

// Handle raw call data
array_push($calls_array, $total_calls);
$calls_array = array_slice($calls_array, -$number_of_points);

// Build the array of flots
$call_data['label'] = 'Current Active Calls';

foreach ($calls_array as $key => $value) {
  if (empty($call_data['data'])) {
    $call_data['data'][0] = array($key, $value);
  } else {
    array_push($call_data['data'], array($key, $value));
  }
}

echo json_encode($call_data);

// Save the array to a file for the next update
file_put_contents('rolling_calls_update_cache.php', '<?php return '.var_export($calls_array, true).';?>');
?>