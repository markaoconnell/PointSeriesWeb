<?php


//591;Aaker;Aaron;M;1971
//171;Amram;Peter;M;1940
//1412;Anderson;Barbara;F;1970
//1411;Anderson;Brett;M;1971
// Check to see if this entry fits the pattern for a valid member file
function find_errors_for_member($member_entry) {
  $entries = explode(";", $member_entry);
  $num_fields = count($entries);
  if ($num_fields > 5) {
    return ("Too many fields");
  }
  else if (($num_fields == 1) && ($entries[0] == "")) {
    return ("");  // blank lines are acceptable
  }
  else if ($num_fields < 5) {
    return ("Too few fields: {$num_fields}");
  }

  if (!preg_match("/^[0-9]+$/", $entries[0])) {
    return("Invalid member number, may only contain digits.");
  }

  if (!preg_match("/^[A-Za-z '-]+$/", $entries[1]) || !preg_match("/^[A-Za-z '-]+$/", $entries[2])) {
    return("Invalid member name, may only contain characters and spaces.");
  }

  if (!preg_match("/^[MF]$/", $entries[3])) {
    return("Invalid member gender, may only be M or F.");
  }

  if (!preg_match("/^[0-9]+$/", $entries[4]) && ($entries[4] != "")) {
    return("Invalid member birth year, may only contain digits.");
  }

  return ("");
}

// Check to see if the member file looks valid
function is_valid_member_file($filename) {
  $file_contents = file_get_contents($filename);
  $file_lines = explode("\n", $file_contents);
  $error_array = array();

  foreach ($file_lines as $line_number => $line) {
    $error = find_errors_for_member($line);
    if ($error != "") {
      $error_array[] = "Line " . ($line_number + 1) . " : " . $error;
    }
  }

  if (count($error_array) == 0) {
    return "";
  }
  else {
    return (implode("\n", array_slice($error_array, 0, 30)));
  }
}

// Move the existing member file to a backup location and then
// make the file $new_member_file the new member file.
// It is assumed that $new_member_file has been validated and looks like
// a correct member file.
function update_member_file($existing_member_file, $new_member_file) {
  $backup_time = strftime("%Y-%m-%d-%H-%M");

  // Save a backup copy, just in case
  copy($existing_member_file, "{$existing_member_file}.{$backup_time}");

  // Update the member file.  Hopefully if this fails, the original member
  // file is still there.
  rename($new_member_file, $existing_member_file);
}


// Return a string with the elapsed time in seconds pretty printed
function formatted_time($time_in_seconds) {
  $hours = floor($time_in_seconds / 3600);
  $mins = floor(($time_in_seconds / 60) % 60);
  $secs = ($time_in_seconds % 60);

  if ($hours > 0) {
    return sprintf("%2dh:%02dm:%02ds", $hours, $mins, $secs);
  }
  else if ($mins > 0) {
    return sprintf("   %2dm:%02ds", $mins, $secs);
  }
  else {
    return sprintf("       %2ds", $secs);
  }
}


// Explode a comma separated string into an array
function explode_lines(&$item1, $key)
{
   $item1 = explode(",", $item1);
   array_walk($item1, 'html_decoder');
}

// Implode an array into a comma separated string
function implode_line(&$item1, $key)
{
   array_walk($item1, 'html_encoder');
   $item1 = implode(",", $item1);
}

// Decode an entry from html
// First translate from > to , to restore commas in the individual entries
// Then decode the other special characters
// This works because a natural > is one of the special characters to be decoded
// - it had been replaced by a &gt;!
function html_decoder(&$item1, $key)
{
   $item1 = str_replace(">", ",", $item1);
   $item1 = html_entity_decode($item1);
}

// Encode an entry to html
// First encode it, then convert , to > so that a true , can be used as
// an entry separator in the .csv file.  This is safe since any original
// > would be encoded by the htmlentities encoding!
function html_encoder(&$item1, $key)
{
   $item1 = htmlentities($item1);
   $item1 = str_replace(",", ">", $item1);
}


// Take file contents as an array of arrays, where the sub-arrays will become comma separated
// lines and the main arrays will each be a separate line in the output file.
// All will be html encoded, and ',' will be encoded as '>' in the file.
// NOTE: This function rather implicitly assumes that the filename is in the current directory and
//       ends with .csv!!!
function write_file($file_array, $filename)
{
  array_walk($file_array, 'implode_line');
  $file_array = implode("\n", $file_array);

  $file_base_name = basename($filename, ".csv");

// Make a backup copy, one per day
  rename($filename, $file_base_name . unixtojd(). ".csv");

// Then write the new file
  $handle = fopen($filename, "w");
  fwrite($handle, $file_array);
  fclose($handle);
}


// Read the file $filename into the array.  The file is assumed to be a .csv file.
// Each line of the file will be an entry in the array $file_array.  Each line will furthermore
// be split up into a subarray, with each element of the subarray one of the elements from the line,
// all assuming that the entries were comma separated to begin with.
function read_file(&$file_array, $filename)
{
$file_array = file($filename);
foreach ($file_array as $key => $value)
  {
  $file_array[$key] = rtrim($value);
  }

array_walk($file_array, 'explode_lines');
}
?>
