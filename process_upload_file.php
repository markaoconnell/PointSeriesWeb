<?php
require 'config.php';
require 'member_file_validation.php';

$current_year = strftime("%Y");
$MINIMUM_FIELD_COUNT = 40;

$configuration_options = init_config();

function error_and_exit($error_string) {
  echo "<p>Unexpected script exit, no update or processing done.\n";
  echo "<p>ERROR: {$error_string}\n";
  exit(1);
}

function enough_fields($field_count) {
  global $MINIMUM_FIELD_COUNT;
  return($field_count >= $MINIMUM_FIELD_COUNT);
}

function semicolon_explode($string_to_explode) {
  return(explode(";", $string_to_explode));
}

if (!isset($_POST["submit"]) || !isset($_FILES["uploaded_file"])) {
  error_and_exit("Incorrectly called: No data submitted");
}


$file_contents = "";
if ($_FILES["uploaded_file"]["size"] > 0) {
  $file_contents = file_get_contents($_FILES["uploaded_file"]["tmp_name"]);
}
else {
  error_and_exit("\nNo (or zero length) input file specified.");
}

$original_basename = basename($_FILES["uploaded_file"]["name"]);
$event_name = trim($_POST["event_name"]);
$event_year = trim($_POST["event_year"]);
$update_summary = isset($_POST["update_summary_checkbox"]);

// Validate that this looks like a true splits file
// Should be semi-colon separated and all lines with more than some number of fields
$result_list = explode("\n", $file_contents);
$field_count_list = array_map(count, array_map(semicolon_explode, $result_list));
$sufficient_fields = array_map(enough_fields, $field_count_list);
$result = array_search(false, $sufficient_fields);
if ($result && ($result < (count($sufficient_fields) - 1))) {
  error_and_exit("Splits file looks incorrect, some lines have too few fields.");
}

if (($event_name == "") || ($event_year == "")) {
  error_and_exit("Name of event ({$event_name}) and year of event ({$event_year}) must both be specified.");
}

if (!preg_match("/^[0-9]{4}$/", $event_year)) {
  error_and_exit("Year of event ({$event_year}) must be 4 digits.");
}

if (strpos($event_name, ";") !== false) {
  error_and_exit("Name of event ({$event_name}) cannot contain a semicolon (;).");
}

// print_r($field_count_list);

// echo "<p>Uploaded {$original_basename} for ${event_year}, friendly name is {$event_name}.\n";

// All looks okay, set up for processing
if (file_exists("./{$event_year}")) {
  if (!is_dir("./{$event_year}")) {
    error_and_exit("Internal error, year ({$event_year}) exists but is not a directory.");
  }
}
else {
  // Create the directory and copy the configuration files into the directory
  $temp_event_year = "./${event_year}.tmp";
  mkdir("./{$temp_event_year}");
  mkdir("./{$temp_event_year}/events");
  mkdir("./{$temp_event_year}/results");
  $files_to_copy_array = array("config_options.csv", "course_adjustment.csv", "course_to_scoring_mode.csv", "members.csv", "nicknames.csv", "year_to_course.csv");
  foreach ($files_to_copy_array as $file_to_copy) {
    copy("./{$file_to_copy}", "./{$temp_event_year}/{$file_to_copy}");
  }
  file_put_contents("./{$temp_event_year}/friendly_names.csv", "");

  // Update the member file (if this option is in effect)
  $member_file_location = get_member_file_location($configuration_options);
  if (($member_file_location != "") && file_exists($member_file_location)) {
    $error_list = is_valid_member_file($member_file_location);
    if ($error_list == "") {
      copy($member_file_location, "./{$temp_event_year}/members.csv");
    }
  }

  // Rename the temporary directory to the real directory
  rename("./{$temp_event_year}", "./{$event_year}");
}


// Add the file to the events directory
$current_friendly_names = file_get_contents("./{$event_year}/friendly_names.csv");
$current_friendly_names .= "{$original_basename};{$event_name};\n";
file_put_contents("./{$event_year}/friendly_names.csv", $current_friendly_names);

copy($_FILES["uploaded_file"]["tmp_name"], "./{$event_year}/events/{$original_basename}");

//echo "Running command  - cd ./{$event_year}; perl -I. -I.. ../process_upload.pl -o results/{$original_basename} events/{$original_basename}\n";
$processing_output = shell_exec("cd ./{$event_year}; /usr/bin/perl -I. -I.. ../process_upload.pl -y {$event_year} -o results/{$original_basename} events/{$original_basename} 2>&1");
if ($update_summary) {
  $processing_output .= shell_exec("cd ./{$event_year}; /usr/bin/perl -I. -I.. ../update_summary.pl results/{$original_basename} 2>&1");
  $summary_file_location = get_summary_file_output_location($configuration_options);
  if ($summary_file_location != "") {
    $processing_output .= shell_exec("cd ./{$event_year}; /usr/bin/perl -I. -I.. ../show_summary.pl -year {$event_year} -o {$summary_file_location} 2>&1");
  }
}

?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Upload splits file result</title>
</head>
<body id="main_body" >
	
<h1>Splits file <?php echo "{$original_basename}"; ?> uploaded</h1>
<?php
echo "<p>Uploaded {$original_basename} for ${event_year}, friendly name is {$event_name}.\n";
if (!$update_summary) {
  echo "<p><h3>Warning: Summary file has not been updated.</h3>\n";
}
?>
<p><p>
Raw result of upload:<p>
<pre>
<?php echo "{$processing_output}"; ?>
</pre>

</body>
</html>
