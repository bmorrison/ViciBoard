<?php
# save_settings.php - ViciBoard settings setter
#
# Copyright (C) 2016 Burk Morrison <burk@5gigahertz.com>

	if (isset($_POST["mysqli_location"])) { $mysqli_location = $_POST["mysqli_location"]; }
	if (isset($_POST["functions_location"])) { $db_user = $_POST["functions_location"]; }
	if (isset($_POST["update_frequency"])) { $update_frequency = $_POST["update_frequency"]; }
	if (isset($_POST["use_vicidial_auth"])) { $use_vicidial_auth = $_POST["use_vicidial_auth"]; }
  if (isset($_POST["sale_revenue"])) { $sale_revenue = $_POST["sale_revenue"]; }

	$settings = array(
		'mysqli_location' => $mysqli_location,
		'functions_location' => $functions_location,
		'update_frequency' => $update_frequency,
		'use_vicidial_auth' => $use_vicidial_auth,
    'sale_revenue' => $sale_revenue
	);
	$settings_filename = 'settings.json';
	file_put_contents($settings_filename, json_encode($settings));

	header("Location: settings.php?result=success");
	exit();	
?>