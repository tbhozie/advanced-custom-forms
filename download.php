<?php
$theFile = $_GET['dl'];
if(!empty($theFile)) {
  header('Content-Description: File Transfer');
  header('Content-Type: application/octet-stream');
  header('Content-Disposition: attachment; filename="'.basename($theFile).'"');
  header('Pragma: public');
  readfile($theFile);
  exit;
}
?>
