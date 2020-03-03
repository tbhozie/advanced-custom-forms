<?php
$formID = $a['id'];
$formInstance = $a['instance'];
// Handle POST
$message = '';
$messageClass = '';
if(!empty($formInstance)) {
} else {
  $formInstance = '1';
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && $formID == $_POST['form-id'] && $formInstance == $_POST['form-instance']) {
  
  // Basic Form Data
  if(!empty($_POST['your-name'])) {
    $myname = sanitizeString($_POST['your-name']);
  } else {
    $myname = sanitizeString($_POST['first-name']) . ' ' . sanitizeString($_POST['last-name']);
  }
  $email = sanitizeString($_POST['email-address']);
  $time = current_time( 'Y-m-d g:i a' );

  // Get Field Data
  $fieldData = '';
  $data = array();
  $errors = array();
  if( have_rows('form_rows', $formID) ):
      while ( have_rows('form_rows', $formID) ) : the_row();
        if( have_rows('fields') ):

            while ( have_rows('fields') ) : the_row();
              $name = get_sub_field('name');
              $required = get_sub_field('required');
              $fieldName = str_replace(' ', '-', strtolower($name));
              $fieldValue = sanitizeString($_POST[$fieldName]);
              if($required === TRUE) {
                if(!empty($fieldValue)){
                  $fieldData .= '<h4>'.$name.'</h4>
                  <p>'.$fieldValue.'</p>';
                } else {
                  $errors[] = $name.' is required<br />';
                }
              } else {
                if(!empty($fieldValue)){
                  $fieldData .= '<h4>'.$name.'</h4>
                  <p>'.$fieldValue.'</p>';
                }
              }
              ?>
            <?php endwhile;

        endif;
      endwhile;
  endif;

  // Check for empty fields if required
  if(!empty($errors)) {
    $message = '';
    $messageClass = 'error';
    foreach($errors as $error) {
      $message .= $error;
    }
  } else {

    // Save to database
    global $wpdb;
    $table_name = $wpdb->prefix . 'advanced_forms_entries';
    $dont_save = get_field('dont_save_to_database', $formID);
    if($dont_save != TRUE) {
      $wpdb->insert(
        $table_name,
        array(
          'time' => $time,
          'name' => $myname,
          'email' => $email,
          'form' => $formID,
          'data' => $fieldData
        )
      );
    }

    // Send notifications
    $from_name = get_field('from_name', $formID);
    $from_email = get_field('from_email', $formID);

    $to_mapping  = get_field('to_mapping', $formID);
    if($to_mapping === TRUE) {
      while ( have_rows('mappings', $formID) ) : the_row();
        $map_field = get_sub_field('map_field');
        $map_equals = get_sub_field('map_equals');

        if($_POST[$map_field] == $map_equals) {
          $to = get_sub_field('mail_to');
        }
      endwhile;
    } else {
      $to = get_field('mail_to', $formID);
    }
    $subject = get_field('subject', $formID);
    if(!empty($to)) {
      ob_start();
      include('notification.php');
      $body = ob_get_clean();
      $headers = array('Content-Type: text/html; charset=UTF-8', 'From: '.$from_name.' <'.$from_email.'>');
      wp_mail( $to, $subject, $body, $headers );
    }

    // Show success message
    $success = get_field('success_message', $formID);
    $message = $success;
    $messageClass = 'success';

    // Download
    $download = get_field('download', $formID);
    $dl_file = $_POST['download'];
    if($download === TRUE) {
      wp_redirect('download.php?dl='.$dl_file);
    }

  }

}

?>
<form class="advanced-custom-form <?php echo $messageClass; ?>" method="POST" data-form="<?php echo $formID; ?>" action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>">
  <input type="hidden" name="form-id" value="<?php echo $formID; ?>" />
  <input type="hidden" name="form-instance" value="<?php echo $formInstance; ?>" />
  <?php
  $col_class = '';
  if( have_rows('form_rows', $formID) ):

      while ( have_rows('form_rows', $formID) ) : the_row();

      $col = get_sub_field('column_size');
      if($col == '3 Columns') {
        $col_class = 'three_col';
      } elseif ($col == '2 Columns') {
        $col_class = 'two_col';
      } else {
        $col_class = 'one_col';
      }
      ?>

          <div class="<?php echo $col_class; ?>">
            <?php
            if( have_rows('fields') ):

              while ( have_rows('fields') ) : the_row(); ?>

              <?php
              $name = get_sub_field('name');
              $fieldName = str_replace(' ', '-', strtolower($name));
              $required = get_sub_field('required');
              if( get_row_layout() == 'text_field' ): ?>
                <div class="col">
                  <label class="field-label"><?php echo $name; ?></label>
                  <input <?php if($required === TRUE):?>required<?php endif; ?>  type="text" name="<?php echo $fieldName; ?>" value="" placeholder="<?php the_sub_field('placeholder'); ?>" />
                </div>
              <?php elseif( get_row_layout() == 'email_field' ): ?>
                <div class="col">
                  <label class="field-label"><?php echo $name; ?></label>
                  <input <?php if($required === TRUE):?>required<?php endif; ?>  type="email" name="<?php echo $fieldName; ?>" value="" placeholder="<?php the_sub_field('placeholder'); ?>" />
                </div>
              <?php elseif( get_row_layout() == 'select_menu' ): ?>
                <div class="col select">
                  <label class="field-label"><?php echo $name; ?></label>
                  <select name="<?php echo $fieldName; ?>" <?php if($required === TRUE):?>required<?php endif; ?>>
                    <?php
                    if(have_rows('options')):
                    while(have_rows('options')): the_row(); ?>
                    <option value="<?php the_sub_field('value'); ?>">
                      <?php the_sub_field('name'); ?>
                    </option>
                  <?php endwhile; endif; ?>
                  </select>
                </div>
              <?php elseif( get_row_layout() == 'radio_menu' ): ?>
                <div class="col radio">
                  <label class="field-label"><?php echo $name; ?></label>
                  <?php
                    if(have_rows('options')):
                    while(have_rows('options')): the_row(); ?>
                      <?php if(get_row_index() === 1) : ?>
                        <label class="field"><input checked <?php if($required === TRUE):?>required<?php endif; ?> name="<?php echo $fieldName; ?>" type="radio" value="<?php the_sub_field('value'); ?>"> <?php the_sub_field('name'); ?></label>
                      <?php else : ?>
                        <label class="field"><input name="<?php echo $fieldName; ?>" type="radio" value="<?php the_sub_field('value'); ?>"> <?php the_sub_field('name'); ?></label>
                      <?php endif; ?>
                  <?php endwhile; endif; ?>
                </div>
              <?php elseif( get_row_layout() == 'textarea_field' ): ?>
                <div class="col textarea">
                  <label class="field-label"><?php echo $name; ?></label>
                  <textarea <?php if($required === TRUE):?>required<?php endif; ?> name="<?php echo $fieldName; ?>" value="" placeholder="<?php the_sub_field('placeholder'); ?>"></textarea>
                </div>
              <?php elseif( get_row_layout() == 'hidden_field' ): ?>
                <div class="col hidden">
                  <input type="hidden" name="<?php echo $fieldName; ?>" class="<?php echo $fieldName; ?>" value="" />
                </div>
              <?php endif;
              endwhile;

            endif; ?>
          </div>

      <?php endwhile;

  endif;

  ?>

  <?php
  $download = get_field('download', $formID);
  if(isset($_POST['download'])) {
    $download_file = $_POST['download'];
  } else {
    $download_file = $a['download'];
  }
  if($download === TRUE) : ?>
    <input type="hidden" name="download" class="download" value="<?php echo $download_file; ?>" />
  <?php endif; ?>

  <div class="buttons clear">
    <?php if(!empty(get_field('denotes_message', $formID))) : ?>
      <div class="denotes"><?php the_field('denotes_message', $formID); ?></div>
    <?php endif; ?>
    <div class="submit">
      <input type="submit" value="<?php the_field('button_text', $formID); ?>" />
    </div>

  </div>
  <div class="message <?php echo $messageClass; ?>"><?php echo $message; ?></div>
</form>
