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
register_activation_hook( __FILE__, 'nt_settings_init' );
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

// create options for the plugin
// add_action( 'admin_init', 'nt_settings_init' );

function nt_settings_init(){
	do_action('nt_set_default_setting');
}

function nt_set_defaults(){
	register_setting( 'nt_options_group', 'nt_fields');
	register_setting( 'nt_options_group', 'nt_form_layout');
	register_setting( 'nt_options_group', 'nt_testimonial_layout');



	$nt_fields_default = [
				[
					"name" => "Name",
					"tag" => "[name]",
					"type" => "text"
				],
				[
					"name" => "Rating",
					"tag" => "[rating]",
					"type" => "rating"
				],
				[
					"name" => "Comments",
					"tag" => "[comments]",
					"type" => "textarea"
				],
			
			];
	 update_option( 'nt_fields', $nt_fields_default );

	 $nt_form_layout_default = "[name] [rating] [comments][submit]";
	 update_option ('nt_form_layout',$nt_form_layout_default);

	 $nt_testimonial_layout_default = "<p>Name:[name]</p><p>[rating]</p><p>Comments:[comments]</p>";
	 update_option ('nt_testimonial_layout',$nt_testimonial_layout_default);
}
add_action('nt_set_default_setting','nt_set_defaults');


// drop table
function nt_drop_table(){
	global $wpdb;
	$table_name = $wpdb->prefix . "nicertestimonials"; 
	$sql ="DROP TABLE IF EXISTS $table_name";
	$wpdb->query($sql);

	delete_option( 'nt_fields' );
	delete_option( 'nt_form_layout' );
	delete_option( 'nt_testimonial_layout' );
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

// create options for the plugin
add_action( 'admin_init', 'nt_settings_init2' );

function nt_settings_init2(){
	register_setting( 'nt_options_group', 'nt_fields');
	register_setting( 'nt_options_group', 'nt_form_layout');
	register_setting( 'nt_options_group', 'nt_testimonial_layout');
}

// add a settings pages
add_action( 'admin_menu', 'nt_add_settings_page' );

function nt_add_settings_page(){
	add_submenu_page(
	'nt-reviews-list',                    //Parent
	__('Nicer Testimonials Settings'),    //Page Title
	__('Settings'),                       //Menu Title
	'administrator',                 //Capabilities
	'nt-reviews-settings',                // Slug
	'nt_render_settings_page');           //Callback
}

function nt_render_settings_page(){ 	?>
<div class="wrap">
	<h1>Nicer Testimonials Settings</h1>

	<p>
		Use <code>[NT_DISPLAY_FORM]</code> to display the submission form.
	</p>
	<p>
		Use <code>[NT_DISPLAY_TESTIMONIALS]</code> to display the testimonial list.
	</p>
	<p> Your are free to style the s**t out of your form and list. Go crazy.</p>
	<form method="post" action="options.php"> 
		<?php settings_fields( 'nt_options_group' ); ?>
		<?php do_settings_sections( 'nt_options_group' ); ?>

		<?php $nt_fields = get_option('nt_fields'); ?>
		
		<div class="nt_tab_links">
			<a href="nt_tab1" class="nt_active">Create your form fields</a>
			<a href="nt_tab2">Set your form layout </a>
			<a href="nt_tab3">Set your testimonial layout </a>
		</div>
		<div id="nt_tab1" class="nt_tab nt_active">
			<h2> Create your form fields </h2>
			<table class="form-table">
				<tr>
					
					<th>Field Name<br>
						<th>Field Tag</th>
						<th>Field Type</th>
						<th>Validation</th>
					</tr>
					<tr valign="top">
						<td>
							<input id="nt_fields[0][name]" type="text" name="nt_fields[0][name]" value="<?php echo $nt_fields['0']['name']; ?>" class="regular-text nt_input_name" placeholder="Field Name:" />
						</td>
						<td><input id="nt_fields[0][tag]" type="text" name="nt_fields[0][tag]" value="<?php echo $nt_fields['0']['tag']; ?>" class="regular-text nt_input_tag" readonly></td>
						<td>
							<select name="nt_fields[0][type]" id="nt_fields[0][type]">
								<option value="text" <?php selected($nt_fields['0']['type'], 'text' ); ?>>Text</option>
								<option value="textarea" <?php selected($nt_fields['0']['type'], 'textarea' ); ?>>Textarea</option>
								<option value="rating" <?php selected($nt_fields['0']['type'], 'rating' ); ?>>Rating</option>
							</select>
						</td>
						<td>
							<input type="checkbox" name="nt_fields[0][val][req]" value="required" <?php checked($nt_fields['0']['val']['req'], 'required' ); ?>>Required<br>
							<input type="checkbox" name="nt_fields[0][val][phone]" value="phone" <?php checked($nt_fields['0']['val']['phone'], 'phone' ); ?>>Phone Number<br>
							<input type="checkbox" name="nt_fields[0][val][email]" value="email"  <?php checked($nt_fields['0']['val']['email'], 'email' ); ?>>Email Address<br>
						</td>
					</tr>
					<tr valign="top">
						<td>
							<input id="nt_fields[1][name]" type="text" name="nt_fields[1][name]" value="<?php echo $nt_fields['1']['name']; ?>" class="regular-text nt_input_name" placeholder="Field Name:" />
						</td>
						<td><input id="nt_fields[1][tag]" type="text" name="nt_fields[1][tag]" value="<?php echo $nt_fields['1']['tag']; ?>" class="regular-text nt_input_tag" readonly></td>
						<td>
							<select name="nt_fields[1][type]" id="nt_fields[1][type]">
								<option value="text" <?php selected($nt_fields['1']['type'], 'text' ); ?>>Text</option>
								<option value="textarea" <?php selected($nt_fields['1']['type'], 'textarea' ); ?>>Textarea</option>
								<option value="rating" <?php selected($nt_fields['1']['type'], 'rating' ); ?>>Rating</option>
							</select>
						</td>
						<td>
							<input type="checkbox" name="nt_fields[1][val][req]" value="required" <?php checked($nt_fields['1']['val']['req'], 'required' ); ?>>Required<br>
							<input type="checkbox" name="nt_fields[1][val][phone]" value="phone" <?php checked($nt_fields['1']['val']['phone'], 'phone' ); ?>>Phone Number<br>
							<input type="checkbox" name="nt_fields[1][val][email]" value="email"  <?php checked($nt_fields['1']['val']['email'], 'email' ); ?>>Email Address<br>
						</td>
					</tr>
					<tr valign="top">
						<td>
							<input id="nt_fields[2][name]" type="text" name="nt_fields[2][name]" value="<?php echo $nt_fields['2']['name']; ?>" class="regular-text nt_input_name" placeholder="Field Name:" />
						</td>
						<td><input id="nt_fields[2][tag]" type="text" name="nt_fields[2][tag]" value="<?php echo $nt_fields['2']['tag']; ?>" class="regular-text nt_input_tag" readonly></td>
						<td>
							<select name="nt_fields[2][type]" id="nt_fields[2][type]">
								<option value="text" <?php selected($nt_fields['2']['type'], 'text' ); ?>>Text</option>
								<option value="textarea" <?php selected($nt_fields['2']['type'], 'textarea' ); ?>>Textarea</option>
								<option value="rating" <?php selected($nt_fields['2']['type'], 'rating' ); ?>>Rating</option>
							</select>
						</td>
						<td>
							<input type="checkbox" name="nt_fields[2][val][req]" value="required" <?php checked($nt_fields['2']['val']['req'], 'required' ); ?>>Required<br>
							<input type="checkbox" name="nt_fields[2][val][phone]" value="phone" <?php checked($nt_fields['2']['val']['phone'], 'phone' ); ?>>Phone Number<br>
							<input type="checkbox" name="nt_fields[2][val][email]" value="email"  <?php checked($nt_fields['2']['val']['email'], 'email' ); ?>>Email Address<br>
						</td>
					</tr>
					<tr valign="top">
						<td>
							<input id="nt_fields[3][name]" type="text" name="nt_fields[3][name]" value="<?php echo $nt_fields['3']['name']; ?>" class="regular-text nt_input_name" placeholder="Field Name:" />
						</td>
						<td><input id="nt_fields[3][tag]" type="text" name="nt_fields[3][tag]" value="<?php echo $nt_fields['3']['tag']; ?>" class="regular-text nt_input_tag" readonly></td>
						<td>
							<select name="nt_fields[3][type]" id="nt_fields[3][type]">
								<option value="text" <?php selected($nt_fields['3']['type'], 'text' ); ?>>Text</option>
								<option value="textarea" <?php selected($nt_fields['3']['type'], 'textarea' ); ?>>Textarea</option>
								<option value="rating" <?php selected($nt_fields['3']['type'], 'rating' ); ?>>Rating</option>
							</select>
						</td>
						<td>
							<input type="checkbox" name="nt_fields[3][val][req]" value="required" <?php checked($nt_fields['3']['val']['req'], 'required' ); ?>>Required<br>
							<input type="checkbox" name="nt_fields[3][val][phone]" value="phone" <?php checked($nt_fields['3']['val']['phone'], 'phone' ); ?>>Phone Number<br>
							<input type="checkbox" name="nt_fields[3][val][email]" value="email"  <?php checked($nt_fields['3']['val']['email'], 'email' ); ?>>Email Address<br>
						</td>
					</tr>
					<tr valign="top">
						<td>
							<input id="nt_fields[4][name]" type="text" name="nt_fields[4][name]" value="<?php echo $nt_fields['4']['name']; ?>" class="regular-text nt_input_name" placeholder="Field Name:" />
						</td>
						<td><input id="nt_fields[4][tag]" type="text" name="nt_fields[4][tag]" value="<?php echo $nt_fields['4']['tag']; ?>" class="regular-text nt_input_tag" readonly></td>
						<td>
							<select name="nt_fields[4][type]" id="nt_fields[4][type]">
								<option value="text" <?php selected($nt_fields['4']['type'], 'text' ); ?>>Text</option>
								<option value="textarea" <?php selected($nt_fields['4']['type'], 'textarea' ); ?>>Textarea</option>
								<option value="rating" <?php selected($nt_fields['4']['type'], 'rating' ); ?>>Rating</option>
							</select>
						</td>
						<td>
							<input type="checkbox" name="nt_fields[4][val][req]" value="required" <?php checked($nt_fields['4']['val']['req'], 'required' ); ?>>Required<br>
							<input type="checkbox" name="nt_fields[4][val][phone]" value="phone" <?php checked($nt_fields['4']['val']['phone'], 'phone' ); ?>>Phone Number<br>
							<input type="checkbox" name="nt_fields[4][val][email]" value="email"  <?php checked($nt_fields['4']['val']['email'], 'email' ); ?>>Email Address<br>
						</td>
					</tr>
				</table>
				<input type="submit" value="<?php _e( 'Save Fields'); ?>" class="button button-primary" />

			</div>
		<div id="nt_tab2" class="nt_tab">
				<h2>Form Layout</h2>
				<p>
					*You can add any valid HTML in your form. An example would be to wrap groups of fields in DIVS.
				</p>
				<p>Available inputs:
					<?php foreach ($nt_fields as $key){
							if($key['tag'] != ''){
						echo ' <code>'.$key['tag'].'</code>';
						}
					}

					echo "<code>[submit]</code>";?>


				</p>
				<textarea name="nt_form_layout" id="nt_form_layout" cols="30" rows="10"><?php echo stripslashes(get_option('nt_form_layout')); ?></textarea>
				<input type="submit" value="<?php _e( 'Save Form'); ?>" class="button button-primary" />
		</div>
		<div id="nt_tab3" class="nt_tab">
				<h2>Testimonial Layout</h2>
				<p>
					*You can add any valid HTML in your list.
				</p>
				<p>Available inputs:
					<?php foreach ($nt_fields as $key){
							if($key['tag'] != ''){
						echo ' <code>'.$key['tag'].'</code>';
						}
					}

					?>


				</p>
				<textarea name="nt_testimonial_layout" id="nt_testimonial_layout" cols="30" rows="10"><?php echo stripslashes(get_option('nt_testimonial_layout')); ?></textarea>
				
				<input type="submit" value="<?php _e( 'Save Form'); ?>" class="button button-primary" />
		</div>

			<?php //submit_button(); ?>
		</form>
	</div>
	<?php
}


// on nt_fields update

add_action('update_option_nt_fields', 'update_nt_fields'); 
function update_nt_fields() { 
	global $wpdb;
	$nt_fields = get_option('nt_fields');

	foreach ($nt_fields as $field){
		switch ($field['type']) {
			case 'textarea':
					$nt_ftype = "text NOT NULL";
				break;
			case 'rating':
					$nt_ftype = "float(2,1)";
				break;		
			default:
					$nt_ftype = "tinytext NOT NULL";
				break;
		}
		$row = $wpdb->get_results(  'SELECT '.str_replace(array( "[", "]" ), "", $field["tag"]).' FROM INFORMATION_SCHEMA.COLUMNS
		WHERE table_name = "'.$wpdb->prefix.'nicertestimonials" AND column_name = "'.str_replace(array( "[", "]" ), "", $field["tag"]).'"'  );
		if(empty($row)){
			$wpdb->query('ALTER TABLE '.$wpdb->prefix.'nicertestimonials ADD '.str_replace(array( "[", "]" ), "", $field["tag"]).' '.$nt_ftype);
		}
	}

	

	
}


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


function nt_display_form(){
	$nt_fields = get_option('nt_fields');
	$nt_layout = get_option('nt_form_layout');


	foreach ($nt_fields as $key){
		$nt_tag = str_replace(array( '[', ']' ), '', $key['tag']);
		switch ($key['type']) {
			case 'textarea':
				$nt_layout = str_replace($key['tag'],'<textarea name="'.$nt_tag.'" class="nt-textarea nt-textarea-'.$nt_tag.' nt-input-'.$nt_tag.'"></textarea>', $nt_layout);
				break;
			case 'rating':
				$nt_layout = str_replace($key['tag'],'<div class="nt-stars-contain nt-stars-contain-'.$nt_tag.'"><div class="nt-stars" data-str-target="'.$nt_tag.'"">
			<div class="nt-stars-half nt-star-half-left" data-rating="0.5"></div>
			<div class="nt-stars-half nt-star-half-right" data-rating="1.0"></div>
			<div class="nt-stars-half nt-star-half-left" data-rating="1.5"></div>
			<div class="nt-stars-half nt-star-half-right" data-rating="2.0"></div>
			<div class="nt-stars-half nt-star-half-left" data-rating="2.5"></div>
			<div class="nt-stars-half nt-star-half-right" data-rating="3.0"></div>
			<div class="nt-stars-half nt-star-half-left" data-rating="3.5"></div>
			<div class="nt-stars-half nt-star-half-right" data-rating="4.0"></div>
			<div class="nt-stars-half nt-star-half-left" data-rating="4.5"></div>
			<div class="nt-stars-half nt-star-half-right" data-rating="5.0"></div>
		</div>
		<input class="nt-current-rating nt-current-rating-'.$nt_tag.' nt-input-'.$nt_tag.'" value="" type="text" readonly name="'.$nt_tag.'"></div>', $nt_layout);
				break;
			default:
				$nt_layout = str_replace($key['tag'],'<input type="text" class="nt-text nt-text-'.$nt_tag.' nt-input-'.$nt_tag.'" name="'.$nt_tag.'">', $nt_layout);
				break;
		}
		
	}
	$nt_layout = str_replace('[submit]','<input type="submit" class="nt-submit" value="submit">', $nt_layout);
	return  '<form action="'.esc_url( admin_url('admin-post.php') ).'" method="post"class="nt_review_form">'.$nt_layout.'<input type="hidden" name="action" value="nt_rating_form"></form>';
}

// shortcodes
add_shortcode( 'NT_DISPLAY_FORM', 'nt_display_form' );


function nt_display_testimonials(){
	global $wpdb;
	$fields = "";
	$nt_testi_layout = get_option('nt_testimonial_layout');
	$nt_fields = get_option('nt_fields');
	$nt_testi_string ='<ul class="nt-master-list" >';

	$count = count($nt_fields);
	foreach ($nt_fields as $field) {
		$nt_tag = str_replace(array( '[', ']' ), '', $field['tag']);
		if($nt_tag != ''){
			$fields .= $nt_tag.", ";
		}
	}
	$fields = substr($fields, 0, -2);

	$nt_testis = $wpdb->get_results( 
	"SELECT ".$fields."
	FROM wp_nicertestimonials
	WHERE status = 'approved' 
	");
	
	foreach ($nt_testis as $nt_testi){
		$nt_testi_string .= '<li class="nt-single-review">';
		$nt_temp = $nt_testi_layout;

		foreach ($nt_testi as $key => $value) {
			$nt_temp = str_replace('['.$key.']',$value, $nt_temp);
		}

		$nt_testi_string .= $nt_temp . "</li>";
	}
	$nt_testi_string .= '</ul>';
	return $nt_testi_string;
}
add_shortcode( 'NT_DISPLAY_TESTIMONIALS', 'nt_display_testimonials' );



// admin styles
function load_nt_admin_styles(){
	wp_register_style( 'nt_admin_styles', plugin_dir_url( __FILE__ ) . 'styles/nt_admin.css', false, '1.0.0' );
	wp_enqueue_style( 'nt_admin_styles' );
}
add_action('admin_enqueue_scripts', 'load_nt_admin_styles');

//frontend styles
function load_nt_styles(){
	wp_register_style( 'nt_styles', plugin_dir_url( __FILE__ ) . 'styles/nt_styles.css', false, '1.0.0' );
	wp_enqueue_style( 'nt_styles' );
}
add_action('wp_enqueue_scripts', 'load_nt_styles');


// register,localize, and equeue the list table scripts
function nt_admin_scripts() {
	wp_register_script( 'nt_admin_scripts',plugin_dir_url( __FILE__ ) . 'scripts/nt_admin_scripts.js', array( 'jquery' ));
	wp_localize_script( 'nt_admin_scripts', 'nt_list_table_params', ['ajaxurl' => admin_url( 'admin-ajax.php', $protocol )] );
	wp_enqueue_script( 'nt_admin_scripts' );
}
add_action( 'admin_enqueue_scripts', 'nt_admin_scripts' ); 

//front end scripts
function nt_scripts() {
	wp_register_script( 'nt_scripts',plugin_dir_url( __FILE__ ) . 'scripts/nt_scripts.js', array( 'jquery' ));
	wp_localize_script( 'nt_scripts', 'nt_params', ['ajaxurl' => admin_url( 'admin-ajax.php', $protocol )] );
	wp_enqueue_script( 'nt_scripts' );
}
add_action( 'wp_enqueue_scripts', 'nt_scripts' ); 


add_action( 'admin_post_nopriv_nt_rating_form', 'nt_process_rating_form' );
add_action( 'admin_post_nt_rating_form', 'nt_process_rating_form' );
function nt_process_rating_form() {
  	global $wpdb;
  	$form = $_POST;
  	$data = array();
  	$table = $wpdb->prefix . "nicertestimonials";
  	foreach ($form as $col => $val) {
  		if($col != "action"){
  			$data[$col] = $val;
  		}
  	}

  	$data['status'] = 'unapproved';
  
  	if($wpdb->insert( $table, $data)){
  		 header("Location: http://localhost/naycer.com/");
  	}
  	else {
  		echo "fail";
  		echo '<pre>';
  		var_dump($form);
  		var_dump($data);
  		var_dump($table);
  		// var_dump($data);
  		echo '</pre>';
  	}

   
}





