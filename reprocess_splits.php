<?php

require 'config.php';

$configuration_options = init_config();

function error_and_exit($error_string) {
  echo "<p>Unexpected script exit, no update or processing done.\n";
  echo "<p>ERROR: {$error_string}\n";
  exit(1);
}

$processing_output = "";

$year_to_process = $_POST["year_to_process"];
$number_event_files = $_POST["num_file_checkboxes"];
$redo_summary_file = isset($_POST["redo_summary"]);
$redo_events = isset($_POST["redo_events"]);

$files_to_remove_list = [];
for ($i = 0; $i < $number_event_files; $i++) {
  if (isset($_POST["file{$i}"])) {
    $files_to_remove_list[] = $_POST["file{$i}"];
  }
}

if ($year_to_process != "") {
  if (!preg_match("/^[0-9]{4}$/", $year_to_process)) {
    error_and_exit("Year {$year_to_process} must be 4 digits.\n");
  }
}
else {
  error_and_exit("Year to process cannot be empty.\n");
}

if (!is_dir("./{$year_to_process}")) {
  error_and_exit("No events for year {$year_to_process}, is that a valid year?\n");
}

$processing_output .= "<p>Removing these files.\n";
$processing_output .= "<ul>\n";
if (count($files_to_remove_list) > 0) {
  foreach ($files_to_remove_list as $file_to_remove) {
    $processing_output .= "<li>{$file_to_remove}</li>\n";
    unlink("./{$year_to_process}/events/{$file_to_remove}");
    unlink("./{$year_to_process}/results/{$file_to_remove}");
  }
}
else {
  $processing_output .= "<li>No files to remove.</li>\n";
}
$processing_output .= "</ul>\n";

// Get the current list of event files (now that some might have been removed)
$event_files = scandir("./{$year_to_process}/events");
$event_files = array_diff($event_files, array(".", "..")); // Remove the annoying . and .. entries
$event_files = array_map (basename, $event_files);

if ($redo_events) {
  $processing_output .= "<p>Reprocessing the event files.\n";
  $processing_output .= "<pre>\n";

  foreach ($event_files as $event_file_name) {
    $processing_output .= "Working on {$event_file_name}.\n\n";
    $processing_output .= shell_exec("cd ./{$year_to_process}; /usr/bin/perl -I. -I.. ../process_upload.pl -y {$year_to_process} -o results/{$event_file_name} events/{$event_file_name} 2>&1");
    $processing_output .= "\n\n\n";
  }
  $processing_output .= "</pre>\n";
}
else {
  $processing_output .= "<p>No need to redo the event files.\n";
}

if ($redo_summary_file) {
  $processing_output .= "<p>Reprocessing the summary file.\n";
  $processing_output .= "<pre>\n";

  // Remove the existing summary file as it will be completely recreated
  unlink("./{$year_to_process}/summary.csv");

  foreach ($event_files as $event_file_name) {
    $processing_output .= "Working on {$event_file_name}.\n\n";
    $processing_output .= shell_exec("cd ./{$year_to_process}; /usr/bin/perl -I. -I.. ../update_summary.pl results/{$event_file_name} 2>&1");
  }

  // Update the summary file, if necessary
  $summary_file_location = get_summary_file_output_location($configuration_options);
  if ($summary_file_location != "") {
    $processing_output .= shell_exec("cd ./{$year_to_process}; /usr/bin/perl -I. -I.. ../show_summary.pl -year {$year_to_process} -o {$summary_file_location} 2>&1");
  }

  $processing_output .= "</pre>\n";
}
else {
  $processing_output .= "<p>No need to redo the summary file (then why is this being run?).\n";
}

?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Reprocess files</title>
</head>
<body id="main_body" >
	
<h1><a>Reprocess event and summary file</a></h1>
<?php echo "{$processing_output}"; ?>

</body>
</html>
