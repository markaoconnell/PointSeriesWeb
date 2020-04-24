<?php
$current_year = strftime("%Y");
$max_file_size = 1024 * 1024;  // 1 MB
?>


<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Upload splits file</title>
</head>
<body id="main_body" >
	
<h1><a>Splits file upload</a></h1>

<form id="upload" enctype="multipart/form-data" method="post" action="./process_upload_file.php">
<label for="uploaded_file">Splits file for upload </label>
<input id="uploaded_file" name="uploaded_file" type="file"/> 
<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_file_size; ?>" />
<p>
<label for="event_name">Printable name of event </label>
<input id="event_name" name="event_name" type="text" maxlength="255" value=""/> 
<p>
<label for="update_summary_checkbox">Update the summary file? </label>
<input id="update_summary_checkbox" name="update_summary_checkbox" type=checkbox value=1 checked/>
<p><p>
<label class="description" for="event_year">Year of event (if not current year) </label>
<input id="event_year" name="event_year" class="element text medium" type="text" maxlength="255" value="<?php echo $current_year; ?>"/> 
<p><p>
<input type="submit" name="submit" value="Submit" />
</form>	
</body>
</html>
