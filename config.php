<?php

$MEMBER_FILE_ID_FIELD_KEY = "member_file_id_field";
$MEMBER_FILE_LAST_NAME_FIELD_KEY = "member_file_last_name_field";
$MEMBER_FILE_FIRST_NAME_FIELD_KEY = "member_file_first_name_field";
$MEMBER_FILE_GENDER_FIELD_KEY = "member_file_gender_field";
$MEMBER_FILE_BIRTH_YEAR_FIELD_KEY = "member_file_birth_year_field";
$MEMBER_FILE_FIELD_SEPARATOR_KEY = "member_file_field_separator";
$WEB_PAGE_TITLE_PREFIX_KEY = "web_page_title_prefix";
$WEB_SUMMARY_HTML_AT_BEGIN_KEY = "web_summary_html_at_begin";
$WEB_SUMMARY_HTML_CLASS_FOR_TABLES_KEY = "web_summary_html_class_for_tables";
$WEB_SUMMARY_HTML_AT_END_KEY = "web_summary_html_at_end";
$MEMBER_FILE_LOCATION_KEY="member_file_location";
$SUMMARY_FILE_LOCATION_KEY="summary_file_location";
$ERRORS_KEY = "errors";


// Initialize the configuration array with default values
// and any values in the user configurable file
function init_config() {
  global $MEMBER_FILE_FIELD_SEPARATOR_KEY;
  global $ERRORS_KEY;

  // Initialize the default values
  $config_hash = array();
  $config_hash[$MEMBER_FILE_FIELD_SEPARATOR_KEY] = ";";

  if (!file_exists("./config_options.csv")) {
    return($config_hash);
  }

  $file_contents = file_get_contents("./config_options.csv");
  $file_lines = explode("\n", $file_contents);
  $error_array = array();

  foreach ($file_lines as $line_number => $line) {
    $comment_start = strpos($line, "#");
    if ($comment_start === false) {
      // No comment on this line, just trim it
      $trimmed_line = trim($line);
    }
    else {
      // Strip off everything starting at the comment character
      $trimmed_line = trim(substr($line, 0, $comment_start));
    }

    // Skip lines that are blank or otherwise commented out
    if ($trimmed_line == "") {
      continue;
    }

    $fields = explode(";", $line);
    if (count($fields) == 2) {
      $config_hash[trim($fields[0])] = trim($fields[1]);
    }
    else {
      $error_array[] = "Malformatted line " . ($line_number + 1) . ": \"{$line}\" ignored.";
    }
  }

  $config_hash[$ERRORS_KEY] = implode("\n", $error_array);
  return($config_hash);
}

function get_summary_file_output_location($config_hash) {
  global $SUMMARY_FILE_LOCATION_KEY;

  return($config_hash[$SUMMARY_FILE_LOCATION_KEY]);
}

function get_member_file_location($config_hash) {
  global $MEMBER_FILE_LOCATION_KEY;

  return($config_hash[$MEMBER_FILE_LOCATION_KEY]);
}

?>
