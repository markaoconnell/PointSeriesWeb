<?php

require 'config.php';

$configuration_options = init_config();

function error_and_exit($error_string) {
  echo "<p>Unexpected script exit, no update or processing done.\n";
  echo "<p>ERROR: {$error_string}\n";
  exit(1);
}

$year_to_show = strftime("%Y");

if (isset($_GET["different_year"])) {
  $year_to_show = $_GET["different_year"];
  if (!preg_match("/^[0-9]{4}$/", $year_to_show)) {
    error_and_exit("Year {$year_to_show} must be 4 digits.\n");
  }
}

if (is_dir("./{$year_to_show}")) {
  $event_files = scandir("./{$year_to_show}/events");
  $event_files = array_diff($event_files, array(".", "..")); // Remove the annoying . and .. entries
  $event_files = array_map (basename, $event_files);
}
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Splits file management</title>
</head>
<body id="main_body" >

<?php
// print_r($configuration_options);
?>
	
<h1><a>Manage splits files (<?php echo $year_to_show; ?>)</a></h1>

<p>Options for reprocessing the splits files
<ul>
<li> Redoing the summary files is useful when a corrected splits file has been uploaded
<li> Reprocessing event files is useful when something has changed, e.g.
<ul>
<li> New members have been added
<li> New nicknames have been added
<li> One of the configuration files has been changed (scoring mode, etc)
</ul>
</ul>
<form method="post" action="./reprocess_splits.php">
<h2><label>Summary file processing options </label></h2>
<input type=hidden name=year_to_process value="<?php echo $year_to_show; ?>" />
<input type=hidden name=num_file_checkboxes value="<?php echo count($event_files); ?>" />
<ul>
<li>
<label for="redo_summary">Redo summary file</label>
<input id="redo_summary" name="redo_summary" type="checkbox" value="1" checked />
<li>
<label for="redo_events">Reprocess event files</label>
<input id="redo_events" name="redo_events" type="checkbox" value="1" />
</ul>

<p><p>
<label>Files to remove </label>
<ul>
<?php
if (count($event_files) > 0) {
  $file_number = 0;
  foreach ($event_files as $event_filename) {
    echo "<li>\n";
    echo "<input id=\"file{$file_number}\" name=\"file{$file_number}\" type=checkbox value=\"{$event_filename}\"/>\n";
    echo "<label for=\"file{$file_number}\">{$event_filename}</label>\n";
    $file_number++;
  }
}
else {
  echo "<li>No current event files.\n";
}
?>
</ul>

<p><p>
<input type="submit" name="submit" value="Reprocess Files" />
</form>	

<p><p><p><p>
<h2> Update the member file for <?php echo $year_to_show; ?> </h2>
<form method="post" action="./update_member_file.php" enctype="multipart/form-data" >
<input type="hidden" name="MAX_FILE_SIZE" value="1048576" />
<input type=hidden name=year_to_process value="<?php echo $year_to_show; ?>" />
<label for="new_member_file">New member file: </label>
<input id="new_member_file" name="new_member_file" type="file"/> 
<?php
$member_file_location = get_member_file_location($configuration_options);
if ($member_file_location != "") {
  echo "<br><label for=\"member_file_location\">Update member file from server ({$member_file_location}): </label>\n";
  echo "<input id=\"member_file_location\" name=\"member_file_location\" type=checkbox value=true/>\n";
}
?>
<br>
<input id="update_member_file" type="submit" name="submit" value="Update Member File" />
</form>

<p><p><p><p>
<h2> Look at results for a different year </h2>
<form method="put" action="./splits_file_management.php">
<label for="different_year">Year to show </label>
<input id="different_year" name="different_year" type="text" maxlength="255" value=""/> 
<p>
<input id="change_year" type="submit" name="submit" value="Change Year" />
</form>
</body>
</html>
