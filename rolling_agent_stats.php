<?php
require("/srv/www/htdocs/vicidial/dbconnect_mysqli.php");
require("/srv/www/htdocs/vicidial/functions.php");

$settings = 'settings.json';
if (file_exists($settings)) {
	$settings_file = file_get_contents($settings);
	$settings_json = json_decode($settings_file);
	foreach ($settings_json as $key => $value) {
		if ($key == 'update_frequency') {
			$update_frequency = $value / 1000;
    } elseif ($key == 'sale_revenue') {
			$sale_revenue = $value;
		}
	}
}

$now = new DateTime();
$short_hour_ampm = date_format($now, 'ga');

$cache_file_seconds = 'cache_file_seconds.php';

if (file_exists($cache_file_seconds)) {
  $agents_array = include($cache_file_seconds);
} else {
  $agents_array = array();
}

function add_agent_data($key, $value) {
  global $agents_array;
  // Handle raw call data
  if (empty($agents_array[$key])) {
    $agents_array[$key] = array($value);
  } else {
    array_push($agents_array[$key], $value);
  }
  $agents_array[$key] = array_slice($agents_array[$key], -18);
}

if (isset($_GET['reset_stats'])) {
  $agents_array = array();
  add_agent_data('agents_in_call', 0);
  add_agent_data('agents_on_pause', 0);
  add_agent_data('agents_ready', 0);
  add_agent_data('overview_login_agents', 0);
  add_agent_data('total_talk', 0);
  add_agent_data('average_talk', 0);
  add_agent_data('average_pause', 0);
  add_agent_data('average_wrap', 0);
  add_agent_data('average_drops', 0);
  add_agent_data('overview_total_calls', 0); 
  add_agent_data('overview_total_sales', 0);
  add_agent_data('hourly_inbound_sales', 0);
  add_agent_data('hourly_outbound_sales', 0);
  add_agent_data('hourly_close_rate', 0.00);
  add_agent_data('hourly_sale_revenue', 0.00);
  file_put_contents($cache_file_seconds, '<?php return '.var_export($agents_array, true).';?>');
}

// Let's cache these vaules unless waittime has elasped
if (file_exists($cache_file_seconds)) {
  if (time() - filemtime($cache_file_seconds) >= $update_frequency) {
    // Agent current status
    $stmt = "SELECT status FROM vicidial_live_agents WHERE status IN('INCALL','PAUSED','READY');";
    $rslt = mysql_to_mysqli($stmt, $link);
    $rows_to_print = mysqli_num_rows($rslt);
    $agents_in_call = 0;
    $agents_on_pause = 0;
    $agents_ready = 0;
    if ($rows_to_print > 0) {
    	while ($rowx = mysqli_fetch_row($rslt)) {
    		if ($rowx[0] == 'INCALL') {
    		  $agents_in_call += 1;
    		} elseif ($rowx[0] == 'PAUSED') {
    		  $agents_on_pause += 1;
    		} elseif ($rowx[0] == 'READY') {
    		  $agents_ready += 1;
    		}
    	}
    }
    add_agent_data('agents_in_call', $agents_in_call);
    add_agent_data('agents_on_pause', $agents_on_pause);
    add_agent_data('agents_ready', $agents_ready);
    add_agent_data('overview_login_agents', $rows_to_print);
    
    // As these stats update on the minute, let's cache them for 60 seconds
    $cache_file_minute = 'cache_file_minute.php';
    if (file_exists($cache_file_minute)) {
      if (time() - filemtime($cache_file_minute) > 60 || isset($_GET['reset_stats'])) {
        $stmt = "SELECT agent_custtalk_today,drops_today,agent_wait_today,agent_pause_today,agent_acw_today,drops_onemin_pct,agent_calls_today,calls_today FROM vicidial_campaign_stats WHERE calls_today>0;";
        $rslt = mysql_to_mysqli($stmt, $link);
        $rows_to_print = mysqli_num_rows($rslt);
        $agent_talk = 0;
        $total_drops = 0;
        $agent_wait = 0;
        $agent_pause = 0;
        $agent_wrap = 0;
        $drop_percent = 0;
        $number_agents = 0;
        $total_calls = 0;
        if ($rows_to_print > 0) {
          while ($rowx = mysqli_fetch_row($rslt)) {
            $agent_talk += $rowx[0];
            $total_drops += $rowx[1];
            $agent_wait += $rowx[2];
            $agent_pause += $rowx[3];
            $agent_wrap += $rowx[4];
            $drop_percent += $rowx[5];
            $agent_calls += $rowx[6];
            $total_calls += $rowx[7];
          }
        }
        add_agent_data('total_talk', $agent_talk);
        add_agent_data('average_talk', round($agent_talk / $agent_calls, 2));
        add_agent_data('average_pause', round($agent_pause / $agent_calls, 2));
        add_agent_data('average_wrap', round($agent_wrap / $agent_calls, 2));
        add_agent_data('average_drops', round($drop_percent / $rows_to_print, 2));
        add_agent_data('overview_total_calls', $total_calls);
        
        // Let's split up the query so we can avoid using a query within the notoriously slow "IN" when calculating sales totals
        $sale_statuses = '';
        $stmt = "SELECT status FROM vicidial_statuses WHERE sale='Y' UNION SELECT status FROM vicidial_campaign_statuses WHERE sale='Y' AND selectable IN('Y','N');";
        $rslt = mysql_to_mysqli($stmt, $link);
        $rows_to_print = mysqli_num_rows($rslt);
        if ($rows_to_print > 0) {
        	while ($rowx = mysqli_fetch_row($rslt)) {
            $temp_status = $rowx[0];
            $sale_statuses .= "'$temp_status',";
        	}
        }
        $sale_statuses = rtrim($sale_statuses, ',');
        
        $sales_count = 0;
        $stmt = "SELECT count(*) FROM vicidial_agent_log WHERE status IN($sale_statuses) AND DATE(event_time)=CURDATE() AND pause_sec<65000 AND wait_sec<65000 AND talk_sec<65000 AND dispo_sec<65000;";
        $rslt = mysql_to_mysqli($stmt, $link);
        $rows_to_print = mysqli_num_rows($rslt);
        if ($rows_to_print > 0) {
        	while ($rowx = mysqli_fetch_row($rslt)) {
            $sales_count = $rowx[0];
        	}
        }
        add_agent_data('overview_total_sales', intval($sales_count));
        
        // Agent calls vs. sales
        $agent_calls_sales = array();
        $last_user = '';
        $last_username = '';
        $temp_call_total = 0;
        $temp_sale_total = 0;
        $stmt = "SELECT count(*) AS calls,SUM(talk_sec) AS talk,full_name,vicidial_users.user,status FROM vicidial_users,vicidial_agent_log WHERE DATE(event_time)=CURDATE() AND vicidial_users.user=vicidial_agent_log.user AND pause_sec<65000 AND wait_sec<65000 AND talk_sec<65000 AND dispo_sec<65000 group by full_name,status order by user,full_name,status desc limit 500000;";
        $rslt = mysql_to_mysqli($stmt, $link);
        $rows_to_print = mysqli_num_rows($rslt);
        if ($rows_to_print > 0) {
          function check_sales($row_status, $value, $sales_string) {
            if (strpos($sales_string, $row_status) !== false) {
              $temp_sale_total += intval($value);
            }
          }
          
        	while ($rowx = mysqli_fetch_row($rslt)) {
            if ($last_user != $rowx[3]) {
              $sale_statuses_spaced = str_replace(',', ' ', $sale_statuses);
              if (empty($agent_calls_sales[$last_username]) == false) {
                $agent_calls_sales[$last_username] = array($temp_call_total, 0);
              }
              $temp_call_total = 0;
              $temp_sale_total = 0;
              $temp_call_total += intval($rowx[0]);
              check_sales($rowx[4], $rowx[0], $sale_statuses_spaced);
              $agent_calls_sales[$rowx[2]] = array($temp_call_total, $temp_sale_total);
              $last_user = $rowx[3];
              $last_username = $rowx[2];
            } else {
              $temp_call_total += intval($rowx[0]);
              check_sales($rowx[4], $rowx[0], $sale_statuses_spaced);
              //echo $temp_call_total;
            }
        	}
        }       
        add_agent_data('agent_calls_sales', $agent_calls_sales);
        
        file_put_contents($cache_file_minute, 'Touching minute cache.');
      }
    } else {
      file_put_contents($cache_file_minute, 'Seeding minute cache.');
    }
    
    // Hourly updated elements
    $cache_file_hourly = 'cache_file_hourly.php';
    if (file_exists($cache_file_hourly)) {
      if (time() - filemtime($cache_file_hourly) > (60 * 60) || isset($_GET['reset_stats'])) {
        // Let's split up the query so we can avoid using a query within the notoriously slow "IN" when calculating sales totals
        $sale_statuses = '';
        $stmt = "SELECT status FROM vicidial_statuses WHERE sale='Y' UNION SELECT status FROM vicidial_campaign_statuses WHERE sale='Y' AND selectable IN('Y','N');";
        $rslt = mysql_to_mysqli($stmt, $link);
        $rows_to_print = mysqli_num_rows($rslt);
        if ($rows_to_print > 0) {
        	while ($rowx = mysqli_fetch_row($rslt)) {
            $temp_status = $rowx[0];
            $sale_statuses .= "'$temp_status',";
        	}
        }
        $sale_statuses = rtrim($sale_statuses, ',');
        
        $inbound_sales_count = 0;
        $stmt = "SELECT count(*) FROM vicidial_closer_log WHERE status IN($sale_statuses) AND call_date>=DATE_SUB(NOW(), INTERVAL 1 HOUR) AND length_in_sec<65000;";
        $rslt = mysql_to_mysqli($stmt, $link);
        $rows_to_print = mysqli_num_rows($rslt);
        if ($rows_to_print > 0) {
        	while ($rowx = mysqli_fetch_row($rslt)) {
            $inbound_sales_count = intval($rowx[0]);
        	}
        }      
        add_agent_data('hourly_inbound_sales', $inbound_sales_count);
        
        $outbound_sales_count = 0;
        $stmt = "SELECT count(*) FROM vicidial_agent_log WHERE status IN($sale_statuses) AND event_time>=DATE_SUB(NOW(), INTERVAL 1 HOUR) AND pause_sec<65000 AND wait_sec<65000 AND talk_sec<65000 AND dispo_sec<65000;";
        $rslt = mysql_to_mysqli($stmt, $link);
        $rows_to_print = mysqli_num_rows($rslt);
        if ($rows_to_print > 0) {
        	while ($rowx = mysqli_fetch_row($rslt)) {
            $outbound_sales_count = intval($rowx[0]);
        	}
        }
        $outbound_sales_count -= $inbound_sales_count;
        add_agent_data('hourly_outbound_sales', $outbound_sales_count);
        
        $stmt = "SELECT calls_hour FROM vicidial_campaign_stats WHERE calls_hour>0;";
        $rslt = mysql_to_mysqli($stmt, $link);
        $rows_to_print = mysqli_num_rows($rslt);
        $total_calls_hour = 0;
        if ($rows_to_print > 0) {
          while ($rowx = mysqli_fetch_row($rslt)) {
            $total_calls_hour += $rowx[0];
          }
        }        
        $total_sales_hour = $outbound_sales_count + $inbound_sales_count;
        $close_percent_hour = $total_sales_hour / $total_calls_hour;
        $close_percent_hour = round(($close_percent_hour * 100), 2);       
        add_agent_data('hourly_close_rate', $close_percent_hour);
        
        // Hour revenue calculation.
        add_agent_data('hourly_sale_revenue', round(($sale_revenue * $total_sales_hour), 2));
        
        file_put_contents($cache_file_hourly, 'Touching hourly cache.');
      }
    } else {
      file_put_contents($cache_file_hourly, 'Seeding hourly cache.');
    }
  
    // Save the array to a file for the next update
    file_put_contents($cache_file_seconds, '<?php return '.var_export($agents_array, true).';?>');
  }
} else {
  file_put_contents($cache_file_seconds, '<?php return '.var_export($agents_array, true).';?>');
}

echo json_encode($agents_array);

?>