<?php
# Begin reading ViciBoard settings
$settings = 'settings.json';
if (file_exists($settings)) {
	$settings_file = file_get_contents($settings);
	$settings_json = json_decode($settings_file);
	foreach ($settings_json as $key => $value) {
		if ($key == 'mysqli_location') {
			$mysqli_location = $value;
		} elseif ($key == 'functions_location') {
			$functions_location = $value;
		} elseif ($key == 'update_frequency') {
			$update_frequency = $value;
		} elseif ($key == 'use_vicidial_auth') {
			$use_vicidial_auth = $value;
		} elseif ($key == 'sale_revenue') {
			$sale_revenue = $value;
		}
	}
}
# End reading ViciBoard settings

# Begin Vicidial authenticaiton
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
  <meta charset="utf-8">
  <!-- Title and other stuffs -->
  <title>Settings | ViciBoard</title>
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
  <div class="conjtainer">
  </div>
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
        </div>
        <!-- Logo ends -->

        <!-- Saved message -->
	  		<?php if (isset($_GET["result"])) {
	      	  if ($_GET['result'] == "success") {
              echo "<div class='col-md-3'>";
  			      echo "<div class='alert alert-success alert-dismissible fade in' role='alert'>";
  				    echo "<button type='button' class='close' data-dismiss='alert' aria-label='Close'>";
  				    echo "<span aria-hidden='true'>&times;</span>";
  				    echo "</button>";
  				    echo "<strong>Updated!</strong> Your settings have been saved.";
  			      echo "</div>"; 
              echo "</div>"; }
            } 	
	  		?>
        <!-- Saved message ends -->
          
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
          <li><a href="index.php"><i class="fa fa-home"></i> Dashboard</a>
            <!-- Sub menu markup 
            <ul>
              <li><a href="#">Submenu #1</a></li>
              <li><a href="#">Submenu #2</a></li>
              <li><a href="#">Submenu #3</a></li>
            </ul>-->
          </li>
          <li class="open"><a href="settings.php"><i class="fa fa-cog"></i> Settings</a></li>
        </ul>
    </div>

    <!-- Sidebar ends -->

  	<!-- Main bar -->
  	<div class="mainbar">
      
	    <!-- Page heading -->
	    <div class="page-head">
        <!-- Page heading -->
	      <h2 class="pull-left"><i class="fa fa-cog"></i> Settings</h2>


        <!-- Breadcrumb -->
        <div class="bread-crumb pull-right">
          <a href="index.php"><i class="fa fa-home"></i> Home</a> 
          <!-- Divider -->
          <span class="divider">/</span> 
          <a href="#" class="bread-current">Settings</a>
        </div>

        <div class="clearfix"></div>

	    </div>
	    <!-- Page heading ends -->



	    <!-- Matter -->

	    <div class="matter">
        <div class="container">

          <div class="row">

            <div class="col-md-12">


              <div class="widget wgreen">
                
                <div class="widget-head">
                  <div class="pull-left">ViciDIAL Database Connection</div>
                  <div class="clearfix"></div>
                </div>

                <div class="widget-content">
				
                  <div class="padd">

                    <br />
                    <!-- Form starts.  -->
                     <form class="form-horizontal" role="form" action="save_settings.php" method="post">						 
  	                    <div class="form-group">
  	                      <label class="col-lg-2 control-label">dbconnect_mysqli.php Location (optional)</label>
  	                      <div class="col-lg-5">
  	                        <input type="text" class="form-control" name="mysqli_location" id="mysqli_location" placeholder="/srv/www/htdocs/vicidial/dbconnect_mysqli.php" value="<?php echo $mysqli_location; ?>">
  	                      </div>
  	                    </div>
                        
  	                    <div class="form-group">
  	                      <label class="col-lg-2 control-label">functions.php Location (optional)</label>
  	                      <div class="col-lg-5">
  	                        <input type="text" class="form-control" name="functions_location" id="functions_location" placeholder="/srv/www/htdocs/vicidial/functions.php" value="<?php echo $functions_location; ?>">
  	                      </div>
  	                    </div>
                        
  	                    <div class="form-group">
  	                      <label class="col-lg-2 control-label">Est. Revenue Per Sale</label>
  	                      <div class="col-lg-5">
  	                        <input type="text" class="form-control" name="sale_revenue" id="sale_revenue" placeholder="0.00" value="<?php echo $sale_revenue; ?>">
  	                      </div>
  	                    </div>

  	                    <div class="form-group">
  	                      <label class="col-lg-2 control-label">Update Frequency</label>
  	                      <div class="col-lg-5">
  	                        <div class="radio">
  	                          <label>
  	                            <input type="radio" name="update_frequency" id="update_frequency_4" value="4000" <?php if ($update_frequency == '4000') { echo "checked"; } ?>>
  	                            4 seconds
  	                          </label>
  	                        </div>
  	                        <div class="radio">
  	                          <label>
  	                            <input type="radio" name="update_frequency" id="update_frequency_10" value="10000" <?php if ($update_frequency == '10000') { echo "checked"; } ?>>
  	                            10 seconds
  	                          </label>
  	                        </div>
  	                        <div class="radio">
  	                          <label>
  	                            <input type="radio" name="update_frequency" id="update_frequency_20" value="20000" <?php if ($update_frequency == '20000') { echo "checked"; } ?>>
  	                            20 seconds
  	                          </label>
  	                        </div>
  	                      </div>
  	                    </div>
                      
                        <div class="form-group">
                          <label class="col-lg-2 control-label">Use Vicidial Auth?</label>
                          <div class="col-lg-2">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="use_vicidial_auth" id="use_vicidial_auth" value="1" <?php if ($use_vicidial_auth == '1') { echo "checked"; } ?>> enabled
                            </label>
                          </div>
                        </div>
						
                        <div class="form-group">
                          <div class="col-lg-offset-2 col-lg-6">
                            <button type="submit" class="btn btn-sm btn-primary" id="submit" value="submit">Submit</button>
                          </div>
                        </div>                                                                                   
                     </form>
                  </div>
                </div>
                  <div class="widget-foot">
                    <!-- Footer goes here -->
                  </div>
              </div>  

            </div>

          </div>

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

</body>
</html>