<?php
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
function advanced_custom_forms_export_form() {
  // header('Content-Description: File Transfer');
  // header('Content-Type: text/csv');
  // header('Content-Disposition: attachment; filename="export.csv"');

	global $wpdb;

	$table_name = $wpdb->prefix . 'advanced_forms_entries';

	$headers = array();
  $headers[] = 'id';
  $headers[] = 'time';
  $headers[] = 'name';
  $headers[] = 'email';
  $headers[] = 'form';
	$exportID = $_GET['exportID'];
  if( have_rows('form_rows', $exportID) ):
    while ( have_rows('form_rows', $exportID) ) : the_row();
      if( have_rows('fields') ):
        while ( have_rows('fields') ) : the_row();
        $name = get_sub_field('name');
        $fieldName = str_replace(' ', '-', strtolower($name));
        if(!empty($name)) {
          $headers[] = $fieldName;
        }
        ?>
        <?php endwhile;
      endif;
    endwhile;
  endif;

	$results = $wpdb->get_results( "SELECT * FROM $table_name WHERE form = $exportID ORDER BY id DESC");

	$fp = fopen('php://output', 'w');

	fputcsv($fp, $headers);

  foreach($results as $result) {
    
    $dataFields = array();
    $paragraphs =  explode('</p>', $result->data);
    $dataFields[] = $result->id;
    $dataFields[] = $result->time;
    $dataFields[] = $result->name;
    $dataFields[] = $result->email;
    $dataFields[] = $result->form;
    foreach($paragraphs as $paragraph) {
      $paragraph = preg_replace("'<h4>(.*?)</h4>'", '', $paragraph);
      $paragraph = strip_tags($paragraph);
      $paragraph = trim($paragraph);
      $dataFields[] = ''.$paragraph.'';
    }

    fputcsv($fp, $dataFields);

  }

	fclose($fp);
}
advanced_custom_forms_export_form();
