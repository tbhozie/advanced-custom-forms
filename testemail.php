<?php
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

$to = 'sevenfold02@gmail.com';
$subject = 'Test email from balchem';
$headers = array('Content-Type: text/html; charset=UTF-8', 'From: Balchem <wordpress@balchem.com>');
wp_mail( $to, $subject, '<h1>Test</h1>', $headers );
