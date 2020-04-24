<?php
require 'member_file_validation.php';
require 'config.php';

$configuration_options = init_config();

function error_and_exit($error_string) {
  echo "<p>Unexpected script exit, no update or processing done.\n";
  if (strpos($error_string, "\n") === 0) {
    echo "<p>ERROR: {$error_string}\n";
  }
  else {
    echo "<p><pre>ERROR: {$error_string}\n</pre>";
  }
  exit(1);
}

if (isset($_POST["year_to_process"])) {
  $year_to_process = $_POST["year_to_process"];
  if (!preg_match("/^[0-9]{4}$/", $year_to_process)) {
    error_and_exit("Year {$year_to_process} must be 4 digits.\n");
  }
}
else {
  error_and_exit("Must specify the year to be processed.\n");
}

if (!is_dir("./{$year_to_process}") || !file_exists("./{$year_to_process}/members.csv")) {
  error_and_exit("No member file found for {$year_to_process}, is that a valid year?");
}

if (isset($_POST["member_file_location"])) {
  $location_of_member_file = tempnam("/tmp", "new_member_file-");
  $server_side_member_file = get_member_file_location($configuration_options);

  if (($server_side_member_file != "") && file_exists($server_side_member_file)) {
    copy($server_side_member_file, $location_of_member_file);
  }
  else {
    error_and_exit("No member file found on server in specified location: {$server_side_member_file}");
  }
}
else if ($_FILES["new_member_file"]["size"] > 0) {
  $location_of_member_file = $_FILES["new_member_file"]["tmp_name"];
}
else {
  error_and_exit("No member file specified.");
}

// Validate the contents and update the file
$error_list = is_valid_member_file($location_of_member_file);
if ($error_list != "") {
  error_and_exit("Errors found in member file.\n" . $error_list);
}
update_member_file("./{$year_to_process}/members.csv", $location_of_member_file);
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Update Member File</title>
</head>
<body id="main_body" >
	

<p><p><p><p>
Member file validation and upload successful.
</body>
</html>
