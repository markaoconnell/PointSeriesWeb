<?php
$current_year = strftime("%Y");

function error_and_exit($error_string) {
  echo "<p>Unexpected script exit, no update or processing done.\n";
  echo "<p>ERROR: {$error_string}\n";
  exit(1);
}


if (isset($_GET["year"])) {
  $year_to_show = $_GET["year"];
  if (!preg_match("/^[0-9]{4}$/", $year_to_show)) {
    error_and_exit("Year of event ({$year_to_show}) must be 4 digits.");
  }
}
else {
  $year_to_show = $current_year;
}


//echo "Running command  - cd ./{$event_year}; perl -I. -I.. ../process_upload.pl -o results/{$original_basename} events/{$original_basename}\n";
if (is_dir("./{$year_to_show}")) {
  $processing_output = shell_exec("cd ./{$year_to_show}; /usr/bin/perl -I. -I.. ../show_summary.pl -year {$year_to_show} 2>&1");
}
else {
  $processing_output = "ERROR: No results for {$year_to_show}.\n";
}

?>

<?php echo "{$processing_output}"; ?>
