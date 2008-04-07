<?php
/*
Plugin Name: Reveal IDs for WP Admin 2.5
Version: 0.7.3
Plugin URI: http://www.schloebe.de/wordpress/reveal-ids-for-wp-admin-25-plugin/
Description: Reveals hidden IDs in Admin interface that have been removed with WordPress 2.5 (formerly known as Entry IDs in Manage Posts/Pages View for WP 2.5).
Author: Oliver Schl&ouml;be
Author URI: http://www.schloebe.de/
*/

function ridwpa_get_user_data($uservar) {
	global $userdata, $user_level;
	get_currentuserinfo();
	return $userdata->$uservar;
}

/* ********* Pages IDs start ********* */
function ridwpa_column_pages_id_25($defaults) {
    $wp_version = (!isset($wp_version)) ? get_bloginfo('version') : $wp_version;

    if ( $wp_version >= '2.5' ) {
    	if ( get_option("ridwpa_page_ids_enable") && ( ridwpa_get_user_data(ID) == '1' || ridwpa_get_user_data(user_level) >= get_option("ridwpa_page_ids_role") ) ) { // TODO: Use capabilities
        	$defaults['ridwpa_page_id_25'] = __('ID');
        }
        return $defaults;
    }
}

function ridwpa_custom_column_page_id_25($column_name, $id) {
    if( $column_name == 'ridwpa_page_id_25' ) {
        echo (int) $id;
    }
}

add_action('manage_pages_custom_column', 'ridwpa_custom_column_page_id_25', 5, 2);
add_filter('manage_pages_columns', 'ridwpa_column_pages_id_25', 5, 2);
/* ********* Pages IDs end ********* */

/* ********* Post IDs start ********* */
function ridwpa_column_post_id_25( $defaults ) {
	$wp_version = (!isset($wp_version)) ? get_bloginfo('version') : $wp_version;
	
	if ( $wp_version >= '2.5' ) {
		if ( get_option("ridwpa_post_ids_enable") && ( ridwpa_get_user_data(ID) == '1' || ridwpa_get_user_data(user_level) >= get_option("ridwpa_post_ids_role") ) ) { // TODO: Use capabilities
    		$defaults['ridwpa_post_id_25'] = __('ID');
    	}
    	return $defaults;
    }
}

function ridwpa_custom_column_post_id_25($column_name, $id) {
    if( $column_name == 'ridwpa_post_id_25' ) {
        echo (int) $id;
    }
}

add_action('manage_posts_custom_column', 'ridwpa_custom_column_post_id_25', 5, 2);
add_filter('manage_posts_columns', 'ridwpa_column_post_id_25', 5, 2);
/* ********* Post IDs end ********* */

/* ********* Link IDs start ********* */
function ridwpa_column_link_id_25( $defaults ) {
	$wp_version = (!isset($wp_version)) ? get_bloginfo('version') : $wp_version;
	
	if ( $wp_version >= '2.5' ) {
		if ( get_option("ridwpa_link_ids_enable") && ( ridwpa_get_user_data(ID) == '1' || ridwpa_get_user_data(user_level) >= get_option("ridwpa_link_ids_role") ) ) { // TODO: Use capabilities
 			$defaults['ridwpa_link_id_25'] = '<th>' . __('ID') . '</th>';
 		}
   		return $defaults;
    }
}

function ridwpa_custom_column_link_id_25($column_name, $id) {
    if( $column_name == 'ridwpa_link_id_25' ) {
        echo (int) $id;
    }
}

add_action('manage_link_custom_column', 'ridwpa_custom_column_link_id_25', 5, 2);
add_filter('manage_link_columns', 'ridwpa_column_link_id_25', 5, 2);
/* ********* Link IDs end ********* */

/* ********* Category IDs start ********* */
function ridwpa_column_cat_id_25( $output ) {
	if ( get_option("ridwpa_cat_ids_enable") && ( ridwpa_get_user_data(ID) == '1' || ridwpa_get_user_data(user_level) >= get_option("ridwpa_cat_ids_role") ) ) { // TODO: Use capabilities
	$wp_version = (!isset($wp_version)) ? get_bloginfo('version') : $wp_version;
	
	if ( $wp_version >= '2.5' && basename($_SERVER['SCRIPT_FILENAME']) == 'categories.php' ) {
		$name_override = false;
 		global $class;
 		$parent = 0; $level = 0;
 		ob_start();

		$args = array('hide_empty' => 0);
		if ( !empty($_GET['s']) ) {
			$args['search'] = $_GET['s'];
		}
		
		$categories = get_categories( $args );
		$children = _get_term_hierarchy('category');

		if ( $categories ) {
			foreach ($categories as $category) {
				$category->cat_name = wp_specialchars($category->cat_name);
				$pad = str_repeat('&#8212; ', $level);
				$parent = 0; $level = 0;
				$name = ( $name_override ? $name_override : $pad . ' ' . $category->name );
				if ( current_user_can( 'manage_categories' ) ) {
					$edit = "<a class='row-title' href='categories.php?action=edit&amp;cat_ID=$category->term_id' title='" . attribute_escape(sprintf(__('Edit "%s"'), $category->name)) . "'>$name</a>";
				} else {
					$edit = $name;
				}
				$class = " class='alternate'" == $class ? '' : " class='alternate'";
				
				$category->count = number_format_i18n( $category->count );
				$posts_count = ( $category->count > 0 ) ? "<a href='edit.php?cat=$category->term_id'>$category->count</a>" : $category->count;
				echo "<tr id='cat-$category->term_id'$class>
			 			<th scope='row' class='check-column'>";
			 	if ( absint(get_option( 'default_category' ) ) != $category->term_id ) {
					echo "<input type='checkbox' name='delete[]' value='$category->term_id' /></th>";
				} else {
					echo "&nbsp;";
				}
				echo "<td>$edit (ID $category->cat_ID)</td>
						<td>$category->category_description</td>
						<td class='num'>$posts_count</td>
						</tr>";
				if ( $category->parent == $parent ) {
					if ( isset($children[$category->term_id]) ) {
						$level = $level +1;
					}
				}
			}
		} else {
			echo '<tr><td colspan="4" style="text-align: center;">' . __('No categories found.') . '</td></tr>';
		}

		$output = ob_get_contents();
		ob_end_clean();
	
    }
	return $output;
    }
}

add_action('cat_rows', 'ridwpa_column_cat_id_25', 5, 1);
/* ********* Category IDs end ********* */

/* ********* Media IDs start ********* */
function ridwpa_column_media_id_25( $defaults ) {
	$wp_version = (!isset($wp_version)) ? get_bloginfo('version') : $wp_version;
	
	if ( $wp_version >= '2.5' ) {
		if ( get_option("ridwpa_media_ids_enable") && ( ridwpa_get_user_data(ID) == '1' || ridwpa_get_user_data(user_level) >= get_option("ridwpa_media_ids_role") ) ) { // TODO: Use capabilities
    		$defaults['ridwpa_media_id_25'] = __('ID');
    	}
    	return $defaults;
    }
}

function ridwpa_custom_column_media_id_25($column_name, $id) {
    if( $column_name == 'ridwpa_media_id_25' ) {
        echo (int) $id;
    }
}

add_action('manage_media_custom_column', 'ridwpa_custom_column_media_id_25', 5, 2);
add_filter('manage_media_columns', 'ridwpa_column_media_id_25', 5, 2);
/* ********* Media IDs end ********* */


/* ********* Admin area stuff ********* */
load_plugin_textdomain('reveal-ids-for-wp-admin-25', PLUGINDIR . '/reveal-ids-for-wp-admin-25');

add_action('admin_menu', 'ridwpa_add_optionpages');
add_action('init', 'ridwpa_DefaultSettings');

register_activation_hook( __FILE__, 'ridwpa_activate' );

function ridwpa_activate() {
	if( function_exists('os_column_page_id_25') ) {
		deactivate_plugins(__FILE__);
		wp_die(__('You still seem to have installed the former (less powerful) plugin release \'Entry IDs in Manage Posts/Pages View for WP 2.5\' (manage-posts-pages-id-25.php). Please deactivate/remove it first in order to be able installing this plugin. <a href="javascript:history.back()">&laquo; Back</a>', 'reveal-ids-for-wp-admin-25'));
	} else {
		ridwpa_DefaultSettings();
		return;
	}
}

function ridwpa_add_optionpages() {
	
	add_options_page(__('Reveal IDs Options', 'reveal-ids-for-wp-admin-25'), __('Reveal IDs for WP Admin 2.5', 'reveal-ids-for-wp-admin-25'), 8, __FILE__, 'ridwpa_options_page');
}

function ridwpa_DefaultSettings () {
	if( !get_option("ridwpa_post_ids_enable") )
		add_option("ridwpa_post_ids_enable", "1");
	if( !get_option("ridwpa_post_ids_role") )
		add_option("ridwpa_post_ids_role", "0");
	if( !get_option("ridwpa_page_ids_enable") )
		add_option("ridwpa_page_ids_enable", "1");
	if( !get_option("ridwpa_page_ids_role") )
		add_option("ridwpa_page_ids_role", "0");
	if( !get_option("ridwpa_link_ids_enable") )
		add_option("ridwpa_link_ids_enable", "1");
	if( !get_option("ridwpa_link_ids_role") )
		add_option("ridwpa_link_ids_role", "0");
	if( !get_option("ridwpa_cat_ids_enable") )
		add_option("ridwpa_cat_ids_enable", "1");
	if( !get_option("ridwpa_cat_ids_role") )
		add_option("ridwpa_cat_ids_role", "0");
	if( !get_option("ridwpa_media_ids_enable") )
		add_option("ridwpa_media_ids_enable", "1");
	if( !get_option("ridwpa_media_ids_role") )
		add_option("ridwpa_media_ids_role", "0");
}

function ridwpa_options_page() {
	if (isset($_POST['action']) === true) {
		update_option("ridwpa_post_ids_enable", (int)$_POST['ridwpa_post_ids_enable']);
		update_option("ridwpa_post_ids_role", (int)$_POST['ridwpa_post_ids_role']);
		update_option("ridwpa_page_ids_enable", (int)$_POST['ridwpa_page_ids_enable']);
		update_option("ridwpa_page_ids_role", (int)$_POST['ridwpa_page_ids_role']);
		update_option("ridwpa_link_ids_enable", (int)$_POST['ridwpa_link_ids_enable']);
		update_option("ridwpa_link_ids_role", (int)$_POST['ridwpa_link_ids_role']);
		update_option("ridwpa_cat_ids_enable", (int)$_POST['ridwpa_cat_ids_enable']);
		update_option("ridwpa_cat_ids_role", (int)$_POST['ridwpa_cat_ids_role']);
		update_option("ridwpa_media_ids_enable", (int)$_POST['ridwpa_media_ids_enable']);
		update_option("ridwpa_media_ids_role", (int)$_POST['ridwpa_media_ids_role']);

		$successmessage = __('Settings saved.', 'reveal-ids-for-wp-admin-25');

		echo '<div id="message" class="updated fade">
			<p>
				<strong>
					' . $successmessage . '
				</strong>
			</p>
		</div><br />';
	}
		
		if( function_exists('os_column_page_id_25') ) {
			$errormessage = __('You still seem to have installed the former (less powerful) plugin release \'Entry IDs in Manage Posts/Pages View for WP 2.5\' (manage-posts-pages-id-25.php). Please deactivate/remove it in order for this plugin to work properly.', 'reveal-ids-for-wp-admin-25');
			echo '<div id="message" class="error fade">
			<p>
				<strong>
					' . $errormessage . '
				</strong>
			</p>
		</div>';
		}
?>
<style type="text/css">
table.ridwpa_table_disabled td, table.ridwpa_table_disabled th {
	background: #EBEBEB;
}
</style>
	
<script type="text/javascript">
function enable_options(area, status) {
	var i = 0, name;
	var form = document.ridwpa_form;
	for (i; i < form.length; i ++) {
		name = form.elements[i].name;
		if (name && name != 'ridwpa_' + area + '_enable' && name.lastIndexOf('ridwpa_' + area) != -1) {
			form.elements[i].disabled = status ? false : true;
		}
	}
	eval('form.ridwpa_' + area + '_enable').checked = status;
}
</script>

	<div class="wrap">
		<h2>
        <?php _e('Reveal IDs for WP Admin 2.5 Options', 'reveal-ids-for-wp-admin-25'); ?>
      	</h2>
      	<form name="ridwpa_form" id="ridwpa_form" action="" method="post">
      	<input type="hidden" name="action" value="edit" />
			<table class="form-table <?php echo (!get_option('ridwpa_post_ids_enable')) ? 'ridwpa_table_disabled' : ''; ?>">
 			<tr>
 				<th scope="row" valign="top"><?php _e('Show Post IDs', 'reveal-ids-for-wp-admin-25'); ?></th>
 				<td>
 					<label for="ridwpa_post_ids_enable">
					<input name="ridwpa_post_ids_enable" id="ridwpa_post_ids_enable" value="1" onchange="enable_options('post_ids', this.checked)" value="1" type="checkbox" <?php echo ( get_option('ridwpa_post_ids_enable')=='1' ) ? ' checked="checked"' : '' ?> /> <?php _e('Reveal IDs for the posts management', 'reveal-ids-for-wp-admin-25'); ?></label>
					<br />
					<small><em><?php _e('(This will add a new column to the posts management displaying the IDs)', 'reveal-ids-for-wp-admin-25'); ?></em></small>
 				</td>
 				<td align="right">
 					<strong><?php _e('What\'s the user role minimum allowed to see the IDs?', 'reveal-ids-for-wp-admin-25'); ?></strong>
					<br />
					<select name="ridwpa_post_ids_role" id="ridwpa_post_ids_role" style="width: 90%;" disabled="disabled">
						<option value="8" <?php if(get_option('ridwpa_post_ids_role') == "8") echo 'selected="selected"'; ?>><?php _e('Administrator', 'reveal-ids-for-wp-admin-25'); ?></option>
						<option value="5" <?php if(get_option('ridwpa_post_ids_role') == "5") echo 'selected="selected"'; ?>><?php _e('Editor', 'reveal-ids-for-wp-admin-25'); ?></option>
						<option value="2" <?php if(get_option('ridwpa_post_ids_role') == "2") echo 'selected="selected"'; ?>><?php _e('Author', 'reveal-ids-for-wp-admin-25'); ?></option>
						<option value="1" <?php if(get_option('ridwpa_post_ids_role') == "1") echo 'selected="selected"'; ?>><?php _e('Contributor', 'reveal-ids-for-wp-admin-25'); ?></option>
						<option value="0" <?php if(get_option('ridwpa_post_ids_role') == "0") echo 'selected="selected"';?>><?php _e('Subscriber', 'reveal-ids-for-wp-admin-25'); ?></option>
					</select>
 				</td>
 			</tr>
			</table>
			<?php if ( get_option('ridwpa_post_ids_enable') ) { ?>
      		<script type="text/javascript">
			enable_options('post_ids', true);
			</script>
			<?php } ?>
			
			<table class="form-table <?php echo (!get_option('ridwpa_page_ids_enable')) ? 'ridwpa_table_disabled' : ''; ?>">
 			<tr>
 				<th scope="row" valign="top"><?php _e('Show Page IDs', 'reveal-ids-for-wp-admin-25'); ?></th>
 				<td>
 					<label for="ridwpa_page_ids_enable">
					<input name="ridwpa_page_ids_enable" id="ridwpa_page_ids_enable" value="1" onchange="enable_options('page_ids', this.checked)" value="1" type="checkbox" <?php echo ( get_option('ridwpa_page_ids_enable')=='1' ) ? ' checked="checked"' : '' ?> /> <?php _e('Reveal IDs for the pages management', 'reveal-ids-for-wp-admin-25'); ?></label>
					<br />
					<small><em><?php _e('(This will add a new column to the pages management displaying the IDs)', 'reveal-ids-for-wp-admin-25'); ?></em></small>
 				</td>
 				<td align="right">
 					<strong><?php _e('What\'s the user role minimum allowed to see the IDs?', 'reveal-ids-for-wp-admin-25'); ?></strong>
					<br />
					<select name="ridwpa_page_ids_role" id="ridwpa_page_ids_role" style="width: 90%;" disabled="disabled">
						<option value="8" <?php if(get_option('ridwpa_page_ids_role') == "8") echo 'selected="selected"'; ?>><?php _e('Administrator', 'reveal-ids-for-wp-admin-25'); ?></option>
						<option value="5" <?php if(get_option('ridwpa_page_ids_role') == "5") echo 'selected="selected"'; ?>><?php _e('Editor', 'reveal-ids-for-wp-admin-25'); ?></option>
						<option value="2" <?php if(get_option('ridwpa_page_ids_role') == "2") echo 'selected="selected"'; ?>><?php _e('Author', 'reveal-ids-for-wp-admin-25'); ?></option>
						<option value="1" <?php if(get_option('ridwpa_page_ids_role') == "1") echo 'selected="selected"'; ?>><?php _e('Contributor', 'reveal-ids-for-wp-admin-25'); ?></option>
						<option value="0" <?php if(get_option('ridwpa_page_ids_role') == "0") echo 'selected="selected"';?>><?php _e('Subscriber', 'reveal-ids-for-wp-admin-25'); ?></option>
					</select>
 				</td>
 			</tr>
			</table>
			<?php if ( get_option('ridwpa_page_ids_enable') ) { ?>
      		<script type="text/javascript">
			enable_options('page_ids', true);
			</script>
			<?php } ?>
			
			<table class="form-table <?php echo (!get_option('ridwpa_link_ids_enable')) ? 'ridwpa_table_disabled' : ''; ?>">
 			<tr>
 				<th scope="row" valign="top"><?php _e('Show Link IDs', 'reveal-ids-for-wp-admin-25'); ?></th>
 				<td>
 					<label for="ridwpa_link_ids_enable">
					<input name="ridwpa_link_ids_enable" id="ridwpa_link_ids_enable" value="1" onchange="enable_options('link_ids', this.checked)" value="1" type="checkbox" <?php echo ( get_option('ridwpa_link_ids_enable')=='1' ) ? ' checked="checked"' : '' ?> /> <?php _e('Reveal IDs for the links management', 'reveal-ids-for-wp-admin-25'); ?></label>
					<br />
					<small><em><?php _e('(This will add a new column to the links management displaying the IDs)', 'reveal-ids-for-wp-admin-25'); ?></em></small>
 				</td>
 				<td align="right">
 					<strong><?php _e('What\'s the user role minimum allowed to see the IDs?', 'reveal-ids-for-wp-admin-25'); ?></strong>
					<br />
					<select name="ridwpa_link_ids_role" id="ridwpa_link_ids_role" style="width: 90%;" disabled="disabled">
						<option value="8" <?php if(get_option('ridwpa_link_ids_role') == "8") echo 'selected="selected"'; ?>><?php _e('Administrator', 'reveal-ids-for-wp-admin-25'); ?></option>
						<option value="5" <?php if(get_option('ridwpa_link_ids_role') == "5") echo 'selected="selected"'; ?>><?php _e('Editor', 'reveal-ids-for-wp-admin-25'); ?></option>
						<option value="2" <?php if(get_option('ridwpa_link_ids_role') == "2") echo 'selected="selected"'; ?>><?php _e('Author', 'reveal-ids-for-wp-admin-25'); ?></option>
						<option value="1" <?php if(get_option('ridwpa_link_ids_role') == "1") echo 'selected="selected"'; ?>><?php _e('Contributor', 'reveal-ids-for-wp-admin-25'); ?></option>
						<option value="0" <?php if(get_option('ridwpa_link_ids_role') == "0") echo 'selected="selected"';?>><?php _e('Subscriber', 'reveal-ids-for-wp-admin-25'); ?></option>
					</select>
 				</td>
 			</tr>
			</table>
			<?php if ( get_option('ridwpa_link_ids_enable') ) { ?>
      		<script type="text/javascript">
			enable_options('link_ids', true);
			</script>
			<?php } ?>
			
			<table class="form-table <?php echo (!get_option('ridwpa_cat_ids_enable')) ? 'ridwpa_table_disabled' : ''; ?>">
 			<tr>
 				<th scope="row" valign="top"><?php _e('Show Category IDs<br /><span style="color:#ff0000;font-weight:200;">Alpha (Use at your own risk!)</span>', 'reveal-ids-for-wp-admin-25'); ?></th>
 				<td>
 					<label for="ridwpa_cat_ids_enable">
					<input name="ridwpa_cat_ids_enable" id="ridwpa_cat_ids_enable" value="1" onchange="enable_options('cat_ids', this.checked)" value="1" type="checkbox" <?php echo ( get_option('ridwpa_cat_ids_enable')=='1' ) ? ' checked="checked"' : '' ?> /> <?php _e('Reveal IDs for the category management', 'reveal-ids-for-wp-admin-25'); ?></label>
					<br />
					<small><em><?php _e('(This will add a new table below the category management displaying the IDs)', 'reveal-ids-for-wp-admin-25'); ?></em></small>
 				</td>
 				<td align="right">
 					<strong><?php _e('What\'s the user role minimum allowed to see the IDs?', 'reveal-ids-for-wp-admin-25'); ?></strong>
					<br />
					<select name="ridwpa_cat_ids_role" id="ridwpa_cat_ids_role" style="width: 90%;" disabled="disabled">
						<option value="8" <?php if(get_option('ridwpa_cat_ids_role') == "8") echo 'selected="selected"'; ?>><?php _e('Administrator', 'reveal-ids-for-wp-admin-25'); ?></option>
						<option value="5" <?php if(get_option('ridwpa_cat_ids_role') == "5") echo 'selected="selected"'; ?>><?php _e('Editor', 'reveal-ids-for-wp-admin-25'); ?></option>
						<option value="2" <?php if(get_option('ridwpa_cat_ids_role') == "2") echo 'selected="selected"'; ?>><?php _e('Author', 'reveal-ids-for-wp-admin-25'); ?></option>
						<option value="1" <?php if(get_option('ridwpa_cat_ids_role') == "1") echo 'selected="selected"'; ?>><?php _e('Contributor', 'reveal-ids-for-wp-admin-25'); ?></option>
						<option value="0" <?php if(get_option('ridwpa_cat_ids_role') == "0") echo 'selected="selected"';?>><?php _e('Subscriber', 'reveal-ids-for-wp-admin-25'); ?></option>
					</select>
 				</td>
 			</tr>
			</table>
			<?php if ( get_option('ridwpa_cat_ids_enable') ) { ?>
      		<script type="text/javascript">
			enable_options('cat_ids', true);
			</script>
			<?php } ?>
			
			<table class="form-table <?php echo (!get_option('ridwpa_media_ids_enable')) ? 'ridwpa_table_disabled' : ''; ?>">
 			<tr>
 				<th scope="row" valign="top"><?php _e('Show Media IDs', 'reveal-ids-for-wp-admin-25'); ?></th>
 				<td>
 					<label for="ridwpa_media_ids_enable">
					<input name="ridwpa_media_ids_enable" id="ridwpa_media_ids_enable" value="1" onchange="enable_options('media_ids', this.checked)" value="1" type="checkbox" <?php echo ( get_option('ridwpa_media_ids_enable')=='1' ) ? ' checked="checked"' : '' ?> /> <?php _e('Reveal IDs for the media management', 'reveal-ids-for-wp-admin-25'); ?></label>
					<br />
					<small><em><?php _e('(This will add a new column to the media management displaying the IDs)', 'reveal-ids-for-wp-admin-25'); ?></em></small>
 				</td>
 				<td align="right">
 					<strong><?php _e('What\'s the user role minimum allowed to see the IDs?', 'reveal-ids-for-wp-admin-25'); ?></strong>
					<br />
					<select name="ridwpa_media_ids_role" id="ridwpa_media_ids_role" style="width: 90%;" disabled="disabled">
						<option value="8" <?php if(get_option('ridwpa_media_ids_role') == "8") echo 'selected="selected"'; ?>><?php _e('Administrator', 'reveal-ids-for-wp-admin-25'); ?></option>
						<option value="5" <?php if(get_option('ridwpa_media_ids_role') == "5") echo 'selected="selected"'; ?>><?php _e('Editor', 'reveal-ids-for-wp-admin-25'); ?></option>
						<option value="2" <?php if(get_option('ridwpa_media_ids_role') == "2") echo 'selected="selected"'; ?>><?php _e('Author', 'reveal-ids-for-wp-admin-25'); ?></option>
						<option value="1" <?php if(get_option('ridwpa_media_ids_role') == "1") echo 'selected="selected"'; ?>><?php _e('Contributor', 'reveal-ids-for-wp-admin-25'); ?></option>
						<option value="0" <?php if(get_option('ridwpa_media_ids_role') == "0") echo 'selected="selected"';?>><?php _e('Subscriber', 'reveal-ids-for-wp-admin-25'); ?></option>
					</select>
 				</td>
 			</tr>
			</table>
			<?php if ( get_option('ridwpa_media_ids_enable') ) { ?>
      		<script type="text/javascript">
			enable_options('media_ids', true);
			</script>
			<?php } ?>
			<p class="submit">
				<input type="submit" name="submit" value="<?php _e('Save Changes'); ?> &raquo;" />
			</p>
			</form>
      	<h3>
        	<?php _e('Help'); ?>
      	</h3>
		<table class="form-table">
 		<tr>
 			<td>
 				<?php _e('If you are new to using this plugin or cant understand what all these settings do, please read the documentation at <a href="http://www.schloebe.de/wordpress/simple-yearly-archive-plugin/" target="_blank">http://www.schloebe.de/wordpress/reveal-ids-for-wp-admin-25-plugin/</a>', 'reveal-ids-for-wp-admin-25'); ?>
 			</td>
 		</tr>
		</table>
 	</div>
<?php
}
?>