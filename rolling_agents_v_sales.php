<?php
require("/srv/www/htdocs/vicidial/dbconnect_mysqli.php");
require("/srv/www/htdocs/vicidial/functions.php");

$filename = "rolling_agents_v_sales_cache.php";
if (file_exists($filename)) {
  $barchart_data = include($filename);
} else {
  $barchart_data = array();
}
  
// Update results if older than a minute or if the cache comes up empty
if (time() - filemtime($filename) >= 60 || empty($barchart_data)) {
  $sale_statuses = array();
  $stmt = "SELECT status FROM vicidial_statuses WHERE sale='Y' UNION SELECT status FROM vicidial_campaign_statuses WHERE sale='Y' AND selectable IN('Y','N');";
  $rslt = mysql_to_mysqli($stmt, $link);
  $rows_to_print = mysqli_num_rows($rslt);
  if ($rows_to_print > 0) {
  	while ($rowx = mysqli_fetch_row($rslt)) {
      array_push($sale_statuses, $rowx[0]);
  	}
  }

  function check_sales($row_status, $value, $sales_array) {
    if (in_array($row_status, $sales_array)) {
      return intval($value);
    }
  }

  $agent_calls_sales = array();
  $last_user = '';
  $last_username = '';
  $temp_call_total = 0;
  $temp_sale_total = 0;
  $stmt = "SELECT count(*) AS calls,SUM(talk_sec) AS talk,full_name,vicidial_users.user,status FROM vicidial_users,vicidial_agent_log WHERE DATE(event_time)=CURDATE() AND vicidial_users.user=vicidial_agent_log.user AND pause_sec<65000 AND wait_sec<65000 AND talk_sec<65000 AND dispo_sec<65000 group by full_name,status order by user,full_name,status desc limit 500000;";
  $rslt = mysql_to_mysqli($stmt, $link);
  $rows_to_print = mysqli_num_rows($rslt);
  if ($rows_to_print > 0) {  
  	while ($rowx = mysqli_fetch_row($rslt)) {
      if ($last_user != $rowx[3]) {
        // 1 Write the last agent to the array
        if ($last_user != '') {
          $agent_calls_sales[$rowx[2]] = array($temp_call_total, $temp_sale_total, $last_user);
        } else {
          $agent_calls_sales[$rowx[2]] = array($temp_call_total, $temp_sale_total, $rowx[3]);
        }     
    
        // 2 Reset counters for this new agent
        $temp_call_total = 0;
        $temp_sale_total = 0;
    
        // 3 Add current row totals to this new agent
        $temp_call_total += intval($rowx[0]);
        $temp_sale_total += check_sales($rowx[4], $rowx[0], $sale_statuses);
    
        // 4 Set this new agent as the current "last agent"
        $last_user = $rowx[3];
        $last_username = $rowx[2];
      } else {
        $temp_call_total += intval($rowx[0]);
        $temp_sale_total += check_sales($rowx[4], $rowx[0], $sale_statuses);
      }
      $agent_calls_sales[$rowx[2]] = array($temp_call_total, $temp_sale_total, $last_user);
  	}
  }

  // Build the array of flots
  $barchart_data = array();
  $barchart_data[0]['label'] = 'Sales';
  $barchart_data[0]['data'] = array();
  $barchart_data[1]['label'] = 'Calls';
  $barchart_data[1]['data'] = array();

  $i = 1;
  foreach ($agent_calls_sales as $agent => $row) {
    $user_id = $row[2];
    array_push($barchart_data[0]['data'], array($user_id, $row[1]));
    array_push($barchart_data[1]['data'], array($user_id, $row[0]));
    $i++;
  }
  file_put_contents($filename, '<?php return '.var_export($barchart_data, true).';?>');
}

echo json_encode($barchart_data);

