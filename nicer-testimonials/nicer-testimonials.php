<?php
/*
Plugin Name: Nicer Testimonials
Plugin URI:  https://developer.wordpress.org/plugins/the-basics/
Description: An attempt to make an easy Wordpress testimonials plugin.
Version:     1.0
Author:      Naycer Jeremy G. Tulas
Author URI:  ntulas.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wporg
Domain Path: /languages
*/
register_activation_hook( __FILE__, 'nt_install' );
register_activation_hook( __FILE__, 'nt_first_data' );
register_deactivation_hook( __FILE__, 'nt_drop_table' );

// create database
function nt_install () {
	global $wpdb;
	$table_name = $wpdb->prefix . "nicertestimonials"; 
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE $table_name (
	id mediumint(9) NOT NULL AUTO_INCREMENT,
	time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	name tinytext NOT NULL,
	comments text NOT NULL,
	rating float(2,1),
	status tinytext NOT NULL,
	PRIMARY KEY  (id)
	) $charset_collate;";
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
dbDelta( $sql );
}

// populate database
function nt_first_data() {
	global $wpdb;

	$nt_name = "John Doe";
	$nt_comments = "Conratz, you've completed the installation! This is a sample comment";
	$nt_rating = 4.5;
	$nt_status = "unapproved";

	$table_name = $wpdb->prefix . 'nicertestimonials';

	$wpdb->insert( 
		$table_name, 
		array( 
			'time' => current_time( 'mysql' ), 
			'name' => $nt_name, 
			'comments' => $nt_comments,
			'rating' => $nt_rating,
			'status' => $nt_status
		) 
	);
}

// drop table
function nt_drop_table(){
	global $wpdb;
	$table_name = $wpdb->prefix . "nicertestimonials"; 
	$sql ="DROP TABLE IF EXISTS $table_name";
	$wpdb->query($sql);
}


// require the list table class
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * LOAD THE CHILD CLASS
 * 
 * Next, we need to create and load a child class that extends WP_List_Table.
 * Most of the work will be done there. Open the file now and take a look.
 */
require dirname( __FILE__ ) . '/includes/class-nicer-testimonials-list-table.php';


add_action( 'admin_menu', 'nt_add_menu_page' );
/**
 * REGISTER THE EXAMPLE ADMIN PAGE
 *
 * Now we just need to define an admin page. For this example, we'll add a top-level
 * menu item to the bottom of the admin menus.
 */

function nt_add_menu_page() {
	add_menu_page(
		__( 'Nicer Testimonials'), // Page title.
		__( 'Nicer Testimonials', 'wp-list-table-example' ),        // Menu Title
		'activate_plugins',                                         // Capability
		'nt-reviews-list',                                          // Menu Slug
		'nt_render_reviews_page',                                   // Callback Function
		'',                                                         // Icon Url
		3                                                           // Position
	);
}

function nt_render_reviews_page() {
	// Create an instance of our package class.
	$nt_list_table = new NT_List_table();
	// Fetch, prepare, sort, and filter our data.
	$nt_list_table->prepare_items();
	?>
	<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="nt-reviews-filter" method="get">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<!-- Now we can render the completed list table -->
		<?php $nt_list_table->display() ?>
	</form>

</div>
<?php 
}

// stylesheet for the list table
add_action( 'admin_head', 'nt_testimonials_table_styles' );

function nt_testimonials_table_styles() {
		echo '<style type="text/css">';
		echo '.toplevel_page_nt-reviews-list  .wp-list-table .id, .toplevel_page_nt-reviews-list  .wp-list-table .column-id { width: 5%; }';
		echo '.toplevel_page_nt-reviews-list  .wp-list-table .name, .toplevel_page_nt-reviews-list  .wp-list-table .column-name { width: 30%; }';
		echo '.toplevel_page_nt-reviews-list  .wp-list-table .comments, .toplevel_page_nt-reviews-list  .wp-list-table .column-comments  { width: 45%; }';
		echo '.toplevel_page_nt-reviews-list  .wp-list-table .rating, .toplevel_page_nt-reviews-list  .wp-list-table .column-rating { width: 10%; }';
		echo '.toplevel_page_nt-reviews-list  .wp-list-table .status, .toplevel_page_nt-reviews-list  .wp-list-table .column-status { width: 10%; }';
		echo '</style>';
}


// add a settings pages
add_action( 'admin_menu', 'nt_add_settings_page' );

function nt_add_settings_page(){
	add_submenu_page(
	'nt-reviews-list',                    //Parent
	__('Nicer Testimonials Settings'),    //Page Title
	__('Settings'),                       //Menu Title
	'edit_theme_options',                 //Capabilities
	'nt-reviews-settings',                // Slug
	'nt_render_settings_page');           //Callback
}

function nt_render_settings_page(){ 	?>
	<div class="wrap">
<h1>Nicer Testimonials Settings</h1>
<form method="post" action="options.php"> 
<?php settings_fields( 'nt_options_group' ); ?>
<?php $nt_options = get_option('nt_options') ?>

	<table class="form-table">
		   <tr valign="top">
			 <th scope="row">
				<?php _e( 'Phone'); ?>
			 </th>
			 <td>
				<input id="nt_options[phone]" type="text" name="nt_options[phone]" value="" class="regular-text" />
			 </td>
		   </tr>
		</table>

<?php submit_button(); ?>
</form>
</div>
<?php
}

// create options for the plugin
add_action( 'admin_init', 'nt_settings_init' );

function nt_settings_init(){
	register_setting( 'nt_options_group', 'nt_options');
}

// register,localize, and equeue the list table scripts
function nt_list_table_scripts() {
	wp_register_script( 'nt_list_table_scripts',plugin_dir_url( __FILE__ ) . 'scripts/nt_list_table_scripts.js', array( 'jquery' ));
	wp_localize_script( 'nt_list_table_scripts', 'nt_list_table_params', ['ajaxurl' => admin_url( 'admin-ajax.php', $protocol )] );
	wp_enqueue_script( 'nt_list_table_scripts' );
}
add_action( 'admin_enqueue_scripts', 'nt_list_table_scripts' ); 


// set review to approved
function nt_app_rev(){
	global $wpdb;
	$wpdb->update($wpdb->prefix.'nicertestimonials',['status' => 'approved'],['id' => $_POST['id']]);
	// wp_send_json($return);
}
add_action( 'wp_ajax_nt_app_rev','nt_app_rev');

// set review to unapproved
function nt_unapp_rev(){
	global $wpdb;
	$wpdb->update($wpdb->prefix.'nicertestimonials',['status' => 'unapproved'],['id' => $_POST['id']]);
	// wp_send_json($return);
}
add_action( 'wp_ajax_nt_unapp_rev','nt_unapp_rev');

// delete review
function nt_del_rev(){
	global $wpdb;
	$wpdb->delete($wpdb->prefix.'nicertestimonials',['id' => $_POST['id']]);
}
add_action( 'wp_ajax_nt_del_rev','nt_del_rev');

