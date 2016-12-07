<?php
# Begin reading ViciBoard settings
$settings = 'settings.json';
if (file_exists($settings)) {
	$settings_file = file_get_contents($settings);
	$settings_json = json_decode($settings_file);
	foreach ($settings_json as $key => $value) {
		if ($key == 'db_address') {
			$db_address = $value;
		} elseif ($key == 'db_user') {
			$db_user = $value;
		} elseif ($key == 'db_pass') {
			$db_pass = $value;
		} elseif ($key == 'update_frequency') {
			$update_frequency = $value;
		}
    elseif ($key == 'use_vicidial_auth') {
			$use_vicidial_auth = $value;
		}
	}
}
# End reading ViciBoard settings

# Begin Vicidial authenticaiton. See vicidial.com for more.
if ($use_vicidial_auth == "1") {
  if (empty($mysqli_location)) {
    require("/srv/www/htdocs/vicidial/dbconnect_mysqli.php");
  } else {
    require($mysqli_location);
  } 
  if (empty($functions_location)) {
    require("/srv/www/htdocs/vicidial/functions.php");
  } else {
    require($functions_location);
  }
    
  $PHP_AUTH_USER=$_SERVER['PHP_AUTH_USER'];
  $PHP_AUTH_PW=$_SERVER['PHP_AUTH_PW'];
  $PHP_SELF=$_SERVER['PHP_SELF'];

  $stmt="SELECT selected_language from vicidial_users where user='$PHP_AUTH_USER';";
  if ($DB) {echo "|$stmt|\n";}
  $rslt=mysql_to_mysqli($stmt, $link);
  $sl_ct = mysqli_num_rows($rslt);
  if ($sl_ct > 0)
  	{
  	$row=mysqli_fetch_row($rslt);
  	$VUselected_language =		$row[0];
  	}

  $auth=0;
  $reports_auth=0;
  $admin_auth=0;
  $auth_message = user_authorization($PHP_AUTH_USER,$PHP_AUTH_PW,'REPORTS',1);
  if ($auth_message == 'GOOD')
  	{$auth=1;}

  if ($auth > 0)
  	{
  	$stmt="SELECT count(*) from vicidial_users where user='$PHP_AUTH_USER' and user_level > 7 and view_reports='1';";
  	if ($DB) {echo "|$stmt|\n";}
  	$rslt=mysql_to_mysqli($stmt, $link);
  	$row=mysqli_fetch_row($rslt);
  	$admin_auth=$row[0];

  	$stmt="SELECT count(*) from vicidial_users where user='$PHP_AUTH_USER' and user_level > 6 and view_reports='1';";
  	if ($DB) {echo "|$stmt|\n";}
  	$rslt=mysql_to_mysqli($stmt, $link);
  	$row=mysqli_fetch_row($rslt);
  	$reports_auth=$row[0];

  	if ($reports_auth < 1)
  		{
  		$VDdisplayMESSAGE = _QXZ("You are not allowed to view the dashboard");
  		Header ("Content-type: text/html; charset=utf-8");
  		echo "$VDdisplayMESSAGE: |$PHP_AUTH_USER|$auth_message|\n";
  		exit;
  		}
  	if ( ($reports_auth > 0) and ($admin_auth < 1) )
  		{
  		$ADD=999999;
  		$reports_only_user=1;
  		}
  	}
  else
  	{
  	$VDdisplayMESSAGE = _QXZ("Login incorrect, please try again");
  	if ($auth_message == 'LOCK')
  		{
  		$VDdisplayMESSAGE = _QXZ("Too many login attempts, try again in 15 minutes");
  		Header ("Content-type: text/html; charset=utf-8");
  		echo "$VDdisplayMESSAGE: |$PHP_AUTH_USER|$auth_message|\n";
  		exit;
  		}
  	Header("WWW-Authenticate: Basic realm=\"VICIBOARD-DASHBOARD\"");
  	Header("HTTP/1.0 401 Unauthorized");
  	echo "$VDdisplayMESSAGE: |$PHP_AUTH_USER|$PHP_AUTH_PW|$auth_message|\n";
  	exit;
  	}
  # End Vicidial authentication
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <!-- Title and other stuffs -->
  <title>ViciBoard by 5Gigahertz, LLC</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="keywords" content="">
  <meta name="author" content="">


  <!-- Stylesheets -->
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <!-- Font awesome icon -->
  <link rel="stylesheet" href="css/font-awesome.min.css"> 
  <!-- jQuery UI -->
  <link rel="stylesheet" href="css/jquery-ui.css">
  <!-- Data tables -->
  <link rel="stylesheet" href="css/jquery.dataTables.css">
  <!-- Main stylesheet -->
  <link href="css/style.css" rel="stylesheet">
  <!-- Widgets stylesheet -->
  <link href="css/widgets.css" rel="stylesheet">
  
  <script src="js/respond.min.js"></script>
  <!--[if lt IE 9]>
  	<script src="js/html5shiv.js"></script>
  <![endif]-->

  <!-- Favicon -->
  <link rel="shortcut icon" href="img/favicon/favicon.png">
</head>

<body>

<div class="navbar navbar-fixed-top bs-docs-nav" role="banner">
  
</div>


<!-- Header starts -->
  <header>
    <div class="container">
      <div class="row">

        <!-- Logo section -->
        <div class="col-md-4">
          <!-- Logo. -->
          <div class="logo">
            <h1><a href="#">Vici<span class="bold">Board</span></a></h1>
            <p class="meta">ViciDIAL&reg; Dashboard by <a href="https://5gigahertz.com">5Gigahertz, LLC</a></p>
          </div>
          <!-- Logo ends -->
        </div>

        <!-- Data section -->

        <div class="col-md-4">
          <div class="header-data">

            <!-- Total calls -->
            <div class="hdata">
              <div class="mcol-left">
                <!-- Icon with red background -->
                <i class="fa fa-signal bred"></i> 
              </div>
              <div class="mcol-right">
                <!-- Number of calls -->
                <p><span id="overview_total_calls">0</span> <em>calls</em></p>
              </div>
              <div class="clearfix"></div>
            </div>

            <!-- Agent data -->
            <div class="hdata">
              <div class="mcol-left">
                <!-- Icon with blue background -->
                <i class="fa fa-user bblue"></i> 
              </div>
              <div class="mcol-right">
                <!-- Number of agents -->
              	<p><span id="overview_login_agents">0</span> <em>agents</em></p>
              </div>
              <div class="clearfix"></div>
            </div>

            <!-- revenue data -->
            <div class="hdata">
              <div class="mcol-left">
                <!-- Icon with green background -->
                <i class="fa fa-money bgreen"></i> 
              </div>
              <div class="mcol-right">
                <!-- Number of sales -->
                <p><span id="overview_total_sales">0</span> <em>sales</em></p>
              </div>
              <div class="clearfix"></div>
            </div>                        

          </div>
        </div>

      </div>
    </div>
  </header>

<!-- Header ends -->

<!-- Main content starts -->

<div class="content">

  	<!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-dropdown"><a href="#">Navigation</a></div>

        <!--- Sidebar navigation -->
        <!-- If the main navigation has sub navigation, then add the class "has_sub" to "li" of main navigation. -->
        <ul id="nav">
          <!-- Main menu with font awesome icon -->
          <li class="open"><a href="index.php"><i class="fa fa-home"></i> Dashboard</a></li>    	  
          <li><a href="settings.php"><i class="fa fa-cog"></i> Settings</a></li>
        </ul>
    </div>
    <!-- Sidebar ends -->

  	<!-- Main bar -->
	<div class="mainbar">
      
	    <!-- Page heading -->
	    <div class="page-head">
	      <h2 class="pull-left"><i class="fa fa-home"></i> Dashboard</h2>

        <!-- Reset Statistics Button -->
        <div class="bread-crumb pull-right">
          <button type="button" class="btn btn-sm btn-warning" id="reset_stats">Reset Stats</button>
        </div>

        <div class="clearfix"></div>

	    </div>
	    <!-- Page heading ends -->



	    <!-- Matter -->

	    <div class="matter">
        <div class="container">

          <!-- Today status. jQuery Sparkline plugin used. -->

          <div class="row">
            <div class="col-md-12"> 
              <!-- List starts -->
              <ul class="today-datas">
                <!-- List #1 -->
                <li>
                  <!-- Graph -->
                  <div><span id="hourly_inbound_sales" class="spark"></span></div>
                  <!-- Text -->
                  <div class="datas-text"><span id="hourly_inbound_sales_text">0</span> Inbound Sales Last Hour</div>
                </li>
                <li>
                  <div><span id="hourly_outbound_sales" class="spark"></span></div>
                  <div class="datas-text"><span id="hourly_outbound_sales_text">0</span> Outbound Sales Last Hour</div>
                </li>
                <li>
                  <div><span id="hourly_close_rate" class="spark"></span></div>
                  <div class="datas-text"><span id="hourly_close_rate_text">0.00</span>% Close Rate Last Hour</div>
                </li>
                <li>
                  <div><span id="hourly_sale_revenue" class="spark"></span></div>
                  <div class="datas-text">$<span id="hourly_sale_revenue_text">0.00</span> Revenue / Hour</div>
                </li> 
                <li>
                  <div><span id="average_drops" class="spark"></span></div>
                  <div class="datas-text"><span id="average_drops_text">45</span>% drops/1m</div>
                </li>                                                                                                              
              </ul> 
            </div>
          </div>
          <!-- Today status ends -->

          <!-- Dashboard Graph starts -->
          <div class="row">
            <div class="col-md-8">

              <!-- Widget -->
              <div class="widget">
                <!-- Widget head -->
                <div class="widget-head">
                  <div class="pull-left">Calls (Rolling)</div>
                  <div class="widget-icons pull-right">
                    <a href="#" class="wminimize"><i class="fa fa-chevron-up"></i></a> 
                    <a href="#" class="wclose"><i class="fa fa-times"></i></a>
                  </div>  
                  <div class="clearfix"></div>
                </div>              

                <!-- Widget content -->
                <div class="widget-content">
                  <div class="padd">

                    <!-- Curve chart (Blue color). jQuery Flot plugin used. -->
                    <div id="calls-rolling-chart"></div>

                    <hr />
                    <!-- Hover location -->
                    <div id="hoverdata">Mouse hovers at
                    (<span id="x">0</span>, <span id="y">0</span>). <span id="clickdata"></span></div>          

                    <!-- Skil this line. <div class="uni"><input id="enableTooltip" type="checkbox">Enable tooltip</div> -->

                  </div>
                </div>
                <!-- Widget ends -->
              </div>
            </div>
			<!-- Dashboard Graph ends -->

            <div class="col-md-4">

              <div class="widget">

                <div class="widget-head">
                  <div class="pull-left">Agent Stats</div>
                  <div class="widget-icons pull-right">
                    <a href="#" class="wminimize"><i class="fa fa-chevron-up"></i></a> 
                    <a href="#" class="wclose"><i class="fa fa-times"></i></a>
                  </div>  
                  <div class="clearfix"></div>
                </div>             

                <div class="widget-content">
                  <div class="padd">

                    <!-- Visitors, pageview, bounce rate, etc., Sparklines plugin used -->
                    <ul class="current-status">
                      <li>
                        <span id="agents_on_call"></span> <span class="bold">Agents On Call :</span><span id="agents_on_call_text" class="bold"></span></b>
                      </li>
                      <li>
                        <span id="agents_on_pause"></span> <span class="bold">Agents Paused :</span><span id="agents_on_pause_text" class="bold"></span></b>
                      </li>
                      <li>
                        <span id="agents_ready"></span> <span class="bold">Agents Ready :</span><span id="agents_ready_text" class="bold"></span></b>
                      </li>
                      <li>
                        <span id="total_talk"></span> <span class="bold">Total Talk :</span><span id="total_talk_text" class="bold"></span></b>
                      </li>
                      <li>
                        <span id="average_talk"></span> <span class="bold">Average Talk :</span><span id="average_talk_text" class="bold"></span> sec</b>
                      </li>
                      <li>
                        <span id="average_wrap"></span> <span class="bold">Average Wrap :</span><span id="average_wrap_text" class="bold"></span> sec</b>
                      </li>   
                      <li>
                        <span id="average_pause"></span> <span class="bold">Average Pause :</span><span id="average_pause_text" class="bold"></span> sec</b>
                      </li>                                                                                                            
                    </ul>

                  </div>
                </div>

              </div>


              </div> 
            </div>

          <!-- Bar Chart starts -->
		  <div class="row">
            <div class="col-md-12">
              <div class="widget wblack">
                <div class="widget-head">
                  <div class="pull-left">Calls to Sales</div>
                  <div class="widget-icons pull-right">
                    <a href="#" class="wminimize"><i class="fa fa-chevron-up"></i></a> 
                    <a href="#" class="wclose"><i class="fa fa-times"></i></a>
                  </div>  
                  <div class="clearfix"></div>
                </div>
                <div class="widget-content">
                  <div class="padd">
                    
                   <div id="agent_v_sales"></div>

                  </div>
                  <div class="widget-foot">
                    <!-- Footer goes here -->
                  </div>
                </div>
              </div> 
             </div>            
            </div> 
		  <!-- Bar Chart ends -->           
          </div>  


        </div>

		<!-- Matter ends -->



	</div>
	<!-- Mainbar ends -->
   	
	<div class="clearfix"></div>

</div>
<!-- Content ends -->

<!-- Footer starts -->
<footer>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
            <!-- Copyright info -->
            <p class="copy">VICIDIAL is a registered trademark of VICIDIAL Group. | <a href="https://5gigahertz.com">ViciDial Dashboard by Burk Morrison | 5Gigahertz, LLC</a> </p>
      </div>
    </div>
  </div>
</footer> 	

<!-- Footer ends -->

<!-- Scroll to top -->
<span class="totop"><a href="#"><i class="fa fa-chevron-up"></i></a></span> 

<!-- JS -->
<script src="js/jquery.js"></script> <!-- jQuery -->
<script src="js/bootstrap.min.js"></script> <!-- Bootstrap -->
<script src="js/jquery-ui.min.js"></script> <!-- jQuery UI -->
<script src="js/jquery.slimscroll.min.js"></script> <!-- jQuery Slim Scroll -->
<script src="js/jquery.dataTables.min.js"></script> <!-- Data tables -->

<!-- jQuery Flot -->
<script src="js/excanvas.min.js"></script>
<script src="js/jquery.flot.js"></script>
<script src="js/jquery.flot.resize.js"></script>
<script src="js/jquery.flot.categories.js"></script>
<script src="js/jquery.flot.stack.js"></script>

<script src="js/sparklines.js"></script> <!-- Sparklines -->
<script src="js/custom.js"></script> <!-- Custom codes -->
<script src="js/charts.js"></script> <!-- Charts & Graphs -->
<script src="js/calls_rolling.js"></script> <!-- Rolling calls graph -->
<script src="js/agent_v_sales.js"></script> <!-- Agent versus sales bar chart -->
<script src="js/jquery.animateNumber.min.js"></script> <!-- Number animator -->

<!-- Script for this page -->
<script type="text/javascript">
$(function () {    
  $('#reset_stats').click(function() {
    $.ajax({
        url: 'rolling_agent_stats.php',
        type: 'GET',
        data: { reset_stats:true },
        success: function (result) {
          alert("Statistics have been reset…");
        }
    });  
  });
});
</script>

</body>
</html>