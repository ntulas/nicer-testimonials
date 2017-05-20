<?php
/**
 * Nicer Testimonials Edit Page
 *
 * @package   WPListTableExample
 * @author    Matt van Andel
 * @copyright 2016 Matthew van Andel
 * @license   GPL-2.0+
 */

?>
<div class="wrap">
<h1>Edit</h1>
<form method="post" action=""> 
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