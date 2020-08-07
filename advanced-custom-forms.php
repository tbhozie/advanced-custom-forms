<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' ); ?>
<?php
/**
 * Plugin Name: Advanced Custom Forms
 * Description: Create forms easily with Advanced Custom Fields. Plugin requires Advanced Custom Fields PRO to be installed.
 * Author: Tyler Hozie
 * Version: 1.1.1
 */


add_filter('acf/settings/save_json', 'advanced_custom_forms_save_point');
function advanced_custom_forms_save_point( $path ) {
    $path = plugin_dir_path(__FILE__) . 'acf-json';
    return $path;
}

add_filter('acf/settings/load_json', 'advanced_custom_forms_load_point');
function advanced_custom_forms_load_point( $paths ) {
    unset($paths[0]);
    $paths[] = plugin_dir_path(__FILE__) . 'acf-json';
    return $paths;
}

// Include our updater file
include_once( plugin_dir_path( __FILE__ ) . 'update.php');

$updater = new advanced_custom_forms_updater( __FILE__ ); // instantiate our class
$updater->set_username( 'tbhozie' ); // set username
$updater->set_repository( 'advanced-custom-forms' ); // set repo
$updater->initialize(); // initialize the updater


function advanced_custom_forms_notice() {
	?>
	<div class="error">
		<p>
			<?php
			_e(
				'Advanced Custom Fields PRO plugin is required for Advanced Custom Forms to work.'
			);
			?>
		</p>
	</div>
	<?php
}


// Install / Add Table for Entries
function advanced_custom_forms_install() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'advanced_forms_entries';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time text NOT NULL,
		name tinytext NOT NULL,
		email text NOT NULL,
		form text NOT NULL,
		data longtext NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

// Register Custom Post Type
function advanced_custom_forms_post_type() {

	$labels = array(
		'name'                  => _x( 'Form', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'Forms', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Forms', 'text_domain' ),
		'name_admin_bar'        => __( 'Forms', 'text_domain' ),
		'archives'              => __( '', 'text_domain' ),
		'attributes'            => __( '', 'text_domain' ),
		'parent_item_colon'     => __( '', 'text_domain' ),
		'all_items'             => __( 'All Forms', 'text_domain' ),
		'add_new_item'          => __( 'Add New Form', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
		'new_item'              => __( 'New Form', 'text_domain' ),
		'edit_item'             => __( 'Edit Form', 'text_domain' ),
		'update_item'           => __( 'Update Form', 'text_domain' ),
		'view_item'             => __( 'View Form', 'text_domain' ),
		'view_items'            => __( 'View Forms', 'text_domain' ),
		'search_items'          => __( 'Search Forms', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Featured Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
		'items_list'            => __( 'Items list', 'text_domain' ),
		'items_list_navigation' => __( 'Items list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter items list', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'Forms', 'text_domain' ),
		'description'           => __( 'Advanced Custom Forms Post Type', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => array( 'title' ),
		'hierarchical'          => false,
		'public'                => false,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 20,
		'menu_icon'             => 'dashicons-feedback',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => true,
		'rewrite'               => false,
		'capability_type'       => 'page',
	);
	register_post_type( 'advanced_custom_form', $args );

}
if ( ! defined( 'ACF' ) ) {
	add_action( 'admin_notices','advanced_custom_forms_notice');
} else {
	add_action( 'init', 'advanced_custom_forms_post_type', 0 );
}

// Add Metabox to Forms for shortcode display
function advanced_custom_forms_shortcode_display() {

    add_meta_box(
        'advanced-custom-forms-shortcode',
        __( 'Form Shortcode', 'advanced-custom-forms' ),
        'advanced_custom_form_shortcode_callback',
        'advanced_custom_form',
				'side'
    );
}

add_action( 'add_meta_boxes', 'advanced_custom_forms_shortcode_display' );

// Add field to metabox for the shortcode
function advanced_custom_form_shortcode_callback($post) {

	?>
	<input type="text" style="width:100%;" readonly value="[form id='<?php echo $post->ID; ?>']" />

<?php }

// [form id=""]
// Form shortcode
function advanced_custom_forms_form_shortcode( $atts ) {
	$a = shortcode_atts( array(
		'id' => $id,
		'download' => $download,
		'instance' => $instance
	), $atts );
	ob_start(); ?>

	<?php include 'form.php'; ?>

	<?php return ob_get_clean();
}

// Create entries page
function advanced_custom_forms_entries() {

	add_submenu_page(
		'edit.php?post_type=advanced_custom_form',
		__( 'Entries', 'text_domain' ),
		__( 'Entries', 'text_domain' ),
		'manage_options',
		'advanced_custom_form_entries',
		'advanced_custom_forms_entries_html'
	);

}

// Entries page content
function advanced_custom_forms_entries_html() {
	?>
	<div id="entry" class="form-data wrap">
		<div class="postbox">
			<h2>Entry Details:</h2>
			<div class="data">

			</div>
			<span class="close">+</span>
		</div>
	</div>
	<div class="wrap entries">
		<h1 class="wp-heading-inline">Form Entries</h1>
		<div class="filters">
			<span class="label">Form</span>
			<?php
			if(isset($_GET['deleteRecord'])) {
				advanced_custom_forms_delete_record();
			}
			$formID = $_GET['form_id'];
			$pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
			$args = array(
				'post_type' => 'advanced_custom_form',
				'posts_per_page' => -1,
			);
			$the_query = new WP_Query( $args ); ?>

			<?php if ( $the_query->have_posts() ) : ?>
				<select class="form-select">
					<option value="0">
					-- All Forms --
					</option>
					<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
						<option <?php if($formID == get_the_ID()):?>selected<?php endif; ?> value="<?php the_ID(); ?>">
							<?php the_title(); ?>
						</option>
					<?php endwhile; ?>
				</select>
				<?php wp_reset_postdata(); ?>
			<?php endif; ?>
			<button class="export"><a href="#">Export Entries</a></button>
		</div>
		<table style="width:100%;" cellpadding="0" cellspacing="0">
			<tr>
				<th>Name</th>
				<th>Email</th>
				<th>Form</th>
				<th>Date Submitted</th>
				<th>Delete Record</th>
			</tr>
			<?php
			global $wpdb;
			$table_name = $wpdb->prefix . 'advanced_forms_entries';
			$limit = 30; // number of rows in page
			$offset = ( $pagenum - 1 ) * $limit;
			if(isset($formID)) {
				$total = $wpdb->get_var( "SELECT COUNT(`id`) FROM $table_name WHERE form=$formID" );
				$num_of_pages = ceil( $total / $limit );
				$results = $wpdb->get_results( "SELECT * FROM $table_name WHERE form=$formID ORDER BY id DESC LIMIT $offset, $limit");
			} else {
				$total = $wpdb->get_var( "SELECT COUNT(`id`) FROM $table_name" );
				$num_of_pages = ceil( $total / $limit );
				$results = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY id DESC LIMIT $offset, $limit");
			}
			if(!empty($results)) :
				foreach($results as $row) :
					$form = $row->form;
					$form_data = get_post($form);
					$form_name = $form_data->post_title;
					$date = strtotime($row->time);
					$new_date = date('F j, Y g:ia', $date);
				?>
			<tr data-id="<?php echo $row->id; ?>" class="entry">
				<td><?php echo $row->name; ?></td>
				<td><?php echo $row->email; ?></td>
				<td><?php echo $form_name; ?></td>
				<td><?php echo $new_date; ?></td>
				<td><a class="delete" href="#" data-id="<?php echo $row->id; ?>">Delete</a></td>
			</tr>
		<?php endforeach; endif; ?>
		</table>
		<?php
		$page_links = paginate_links( array(
			    'base' => add_query_arg( 'pagenum', '%#%' ),
			    'format' => '',
			    'prev_text' => __( '&laquo;', 'text-domain' ),
			    'next_text' => __( '&raquo;', 'text-domain' ),
			    'total' => $num_of_pages,
			    'current' => $pagenum
			) );

			if ( $page_links ) {
				echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
			}
		?>
	</div>
	<?php
}

function advanced_custom_forms_get_data() {
	global $wpdb;
	$theID = $_GET["entry_id"];
	$table_name = $wpdb->prefix . 'advanced_forms_entries';
	$result = $wpdb->get_results( "SELECT data FROM $table_name WHERE id = $theID");
	echo $result[0]->data;
	wp_die();
}

function advanced_custom_forms_delete_record() {
	global $wpdb;
	$deleteRecord = $_GET['deleteRecord'];
	if(isset($deleteRecord)) {
		$wpdb->delete( $wpdb->prefix . 'advanced_forms_entries', array( 'id' => $deleteRecord ) );
	}
}

function advanced_custom_forms_styles_scripts() {
	wp_enqueue_style( 'advanced-custom-form-admin-styles', plugins_url( '/css/admin-style.css', __FILE__ ) );
	wp_enqueue_script( 'advanced-custom-form-admin-script', plugins_url( '/js/scripts.js?ver=1', __FILE__ ) );
	wp_localize_script( 'advanced-custom-form-admin-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}

function advanced_custom_forms_frontend() {
	wp_enqueue_script( 'advanced-custom-form-frontend-script', plugins_url( '/js/frontend.js', __FILE__ ), array('jquery'), $ver, true );
}

function advanced_custom_forms_download_field( $field ) {

	$current_post = $_GET['post'];

	$all_fields = array();

	if( have_rows('form_rows', $current_post) ):

			while ( have_rows('form_rows', $current_post) ) : the_row();

				if( have_rows('fields') ):

					while ( have_rows('fields') ) : the_row();
						$name = get_sub_field('name');
						$all_fields[] = $name;
					endwhile;

				endif;

			endwhile;

		endif;


	  $field['choices'] = $all_fields;

  return $field;

}

function advanced_custom_forms_mappings_field( $field ) {

	$current_post = $_GET['post'];

	$all_fields = array();

	if( have_rows('form_rows', $current_post) ):

			while ( have_rows('form_rows', $current_post) ) : the_row();

				if( have_rows('fields') ):

					while ( have_rows('fields') ) : the_row();
						$name = get_sub_field('name');
						$fieldName = str_replace(' ', '-', strtolower($name));
						$all_fields[$fieldName] = $name;
					endwhile;

				endif;

			endwhile;

		endif;


	  $field['choices'] = $all_fields;

  return $field;

}

function sanitizeString($var) {
	$var = stripslashes($var);
	$var = htmlentities($var);
	$var = strip_tags($var);
	return $var;
}

// Actions
register_activation_hook( __FILE__, 'advanced_custom_forms_install' );
add_action( 'admin_menu', 'advanced_custom_forms_entries' );
add_action( 'admin_print_styles', 'advanced_custom_forms_styles_scripts');
add_action( 'wp_enqueue_scripts', 'advanced_custom_forms_frontend', 20);
add_action( 'wp_ajax_my_action', 'advanced_custom_forms_get_data' );
add_shortcode( 'form', 'advanced_custom_forms_form_shortcode' );
add_filter('acf/load_field/name=download_field', 'advanced_custom_forms_download_field');
add_filter('acf/load_field/name=map_field', 'advanced_custom_forms_mappings_field');
 ?>
