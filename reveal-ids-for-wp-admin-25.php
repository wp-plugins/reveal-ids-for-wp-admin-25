<?php
/**
 * The main plugin file
 *
 * @package WordPress_Plugins
 * @subpackage RevealIDsForWPAdmin
 */
 
/*
Plugin Name: Reveal IDs for WP Admin
Version: 1.0.2
Plugin URI: http://www.schloebe.de/wordpress/reveal-ids-for-wp-admin-25-plugin/
Description: <strong>WordPress 2.5+ only.</strong> Reveals hidden IDs in Admin interface that have been removed with WordPress 2.5 (formerly known as Entry IDs in Manage Posts/Pages View for WP 2.5). See <a href="options-general.php?page=reveal-ids-for-wp-admin-25/reveal-ids-for-wp-admin-25.php">Options Page</a> for options and information.
Author: Oliver Schl&ouml;be
Author URI: http://www.schloebe.de/


Copyright 2008 Oliver Schlöbe (email : webmaster@schloebe.de)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Pre-2.6 compatibility
 */
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );


/**
 * Define the plugin path slug
 */
define("RIDWPA_PLUGINPATH", "/" . plugin_basename( dirname(__FILE__) ) . "/");

/**
 * Define the plugin full url
 */
define("RIDWPA_PLUGINFULLURL", WP_PLUGIN_URL . RIDWPA_PLUGINPATH );

/**
 * Define the plugin full dir
 */
define("RIDWPA_PLUGINFULLDIR", WP_PLUGIN_DIR . RIDWPA_PLUGINPATH );

/**
 * Define the plugin version
 */
define("RIDWPA_VERSION", "1.0.2");


/**
 * Get all the WordPress user roles using for capability stuff
 *
 * @since 0.7
 * @author scripts@schloebe.de
 *
 * @param string $capability
 * @return string
 */
function ridwpa_get_role( $capability ) {
	$check_order = array("subscriber", "contributor", "author", "editor", "administrator");

	$args = array_slice(func_get_args(), 1);
	$args = array_merge(array($capability), $args);

	foreach ($check_order as $role) {
		$check_role = get_role($role);
		
		if ( empty($check_role) )
			return false;
			
		if (call_user_func_array(array(&$check_role, 'has_cap'), $args))
			return $role;
	}
	return false;
}


/**
 * Set the user capabilities using for permission stuff
 *
 * @since 0.7
 * @author scripts@schloebe.de
 *
 * @param string $lowest_role
 * @param string $capability
 * @return mixed
 */
function ridwpa_set_capability( $lowest_role, $capability ) {
	$check_order = array("subscriber", "contributor", "author", "editor", "administrator");

	$add_capability = false;
	
	foreach ($check_order as $role) {
		if ($lowest_role == $role)
			$add_capability = true;
			
		$the_role = get_role($role);
		
		if ( empty($the_role) )
			continue;
			
		$add_capability ? $the_role->add_cap($capability) : $the_role->remove_cap($capability) ;
	}
	
}


/**
 * Add action link(s) to plugins page
 * 
 * @since 1.0
 * @author scripts@schloebe.de
 * @copyright Dion Hulse, http://dd32.id.au/wordpress-plugins/?configure-link
 */
function ridwpa_filter_plugin_actions($links, $file){
	static $this_plugin;

	if( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);

	if( $file == $this_plugin ){
		$settings_link = '<a href="options-general.php?page=reveal-ids-for-wp-admin-25/reveal-ids-for-wp-admin-25.php">' . __('Settings') . '</a>';
		$links = array_merge( array($settings_link), $links); // before other links
	}
	return $links;
}

add_filter('plugin_action_links', 'ridwpa_filter_plugin_actions', 10, 2);


/**
 * Add a new 'ID' column to the page management view
 *
 * @since 0.7
 * @author scripts@schloebe.de
 *
 * @param array
 * @return array
 */
function ridwpa_column_pages_id_25($defaults) {
    $wp_version = (!isset($wp_version)) ? get_bloginfo('version') : $wp_version;

    if ( version_compare( $wp_version, '2.5', '>=' ) ) {
    	if ( get_option("ridwpa_page_ids_enable") && current_user_can('Reveal IDs See Page IDs') ) {
        	$defaults['ridwpa_page_id_25'] = '<abbr style="cursor:help;" title="' . __('Enhanced by Reveal IDs for WP Admin 2.5 Plugin', 'reveal-ids-for-wp-admin-25') . '">' . __('ID') . '</abbr>';
        }
        return $defaults;
    }
}

/**
 * Adds content to the new 'ID' column to the page management view
 *
 * @since 0.7
 * @author scripts@schloebe.de
 *
 * @param string
 * @param int
 */
function ridwpa_custom_column_page_id_25($column_name, $id) {
    if( $column_name == 'ridwpa_page_id_25' ) {
        echo (int) $id;
    }
}

add_action('manage_pages_custom_column', 'ridwpa_custom_column_page_id_25', 5, 2);
add_filter('manage_pages_columns', 'ridwpa_column_pages_id_25', 5, 2);


/**
 * Add a new 'ID' column to the post management view
 *
 * @since 0.7
 * @author scripts@schloebe.de
 *
 * @param array
 * @return array
 */
function ridwpa_column_post_id_25( $defaults ) {
	$wp_version = (!isset($wp_version)) ? get_bloginfo('version') : $wp_version;
	
	if ( version_compare( $wp_version, '2.5', '>=' ) ) {
		if ( get_option("ridwpa_post_ids_enable") && current_user_can('Reveal IDs See Post IDs') ) {
    		$defaults['ridwpa_post_id_25'] = '<abbr style="cursor:help;" title="' . __('Enhanced by Reveal IDs for WP Admin 2.5 Plugin', 'reveal-ids-for-wp-admin-25') . '">' . __('ID') . '</abbr>';
    	}
    	return $defaults;
    }
}

/**
 * Adds content to the new 'ID' column in the post management view
 *
 * @since 0.7
 * @author scripts@schloebe.de
 *
 * @param string
 * @param int
 */
function ridwpa_custom_column_post_id_25($column_name, $id) {
    if( $column_name == 'ridwpa_post_id_25' ) {
        echo (int) $id;
    }
}

add_action('manage_posts_custom_column', 'ridwpa_custom_column_post_id_25', 5, 2);
add_filter('manage_posts_columns', 'ridwpa_column_post_id_25', 5, 2);


/**
 * Add a new 'ID' column to the link management view
 *
 * @since 0.7
 * @author scripts@schloebe.de
 *
 * @param array
 * @return array
 */
function ridwpa_column_link_id_25( $defaults ) {
	$wp_version = (!isset($wp_version)) ? get_bloginfo('version') : $wp_version;
	
	if ( version_compare( $wp_version, '2.5', '>=' ) ) {
		if ( get_option("ridwpa_link_ids_enable") && current_user_can('Reveal IDs See Link IDs') ) {
 			$defaults['ridwpa_link_id_25'] = '<th><abbr style="cursor:help;" title="' . __('Enhanced by Reveal IDs for WP Admin 2.5 Plugin', 'reveal-ids-for-wp-admin-25') . '">' . __('ID') . '</abbr></th>';
 		}
   		return $defaults;
    }
}

/**
 * Adds content to the new 'ID' column in the link management view
 *
 * @since 0.7
 * @author scripts@schloebe.de
 *
 * @param string
 * @param int
 */
function ridwpa_custom_column_link_id_25($column_name, $id) {
    if( $column_name == 'ridwpa_link_id_25' ) {
        echo (int) $id;
    }
}

add_action('manage_link_custom_column', 'ridwpa_custom_column_link_id_25', 5, 2);
add_filter('manage_link_columns', 'ridwpa_column_link_id_25', 5, 2);


/**
 * Adds the category 'ID' to the category management view
 *
 * @since 0.7
 * @author scripts@schloebe.de
 * @deprecated Deprecated since version 1.0
 * @see ridwpa_cat_js_header()
 *
 * @param string
 * @return string
 */
function ridwpa_column_cat_id_25( $output ) {
	if ( get_option("ridwpa_cat_ids_enable") && current_user_can('Reveal IDs See Category IDs') ) {
	$wp_version = (!isset($wp_version)) ? get_bloginfo('version') : $wp_version;
	
	if ( version_compare( $wp_version, '2.5', '>=' ) && basename($_SERVER['SCRIPT_FILENAME']) == 'categories.php' ) {
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
    }
	return $output;
}

//add_action('cat_rows', 'ridwpa_column_cat_id_25', 5, 1);


/**
 * Adds the category 'ID' js file to the category page's header
 *
 * @since 1.0
 * @author scripts@schloebe.de
 */
function ridwpa_cat_js_header() {
	if ( get_option("ridwpa_cat_ids_enable") && current_user_can('Reveal IDs See Category IDs') ) {
		add_action('admin_head', wp_enqueue_script( 'id-reader-cat', RIDWPA_PLUGINFULLURL . "js/id-reader-cat.js", array('jquery'), RIDWPA_VERSION ) );
	}
}

/**
 * Adds the user 'ID' js file to the authors page's header
 *
 * @since 1.0
 * @author scripts@schloebe.de
 */
function ridwpa_user_js_header() {
	if ( get_option("ridwpa_user_ids_enable") && current_user_can('Reveal IDs See Category IDs') ) {
		add_action('admin_head', wp_enqueue_script( 'id-reader-user', RIDWPA_PLUGINFULLURL . "js/id-reader-user.js", array('jquery'), RIDWPA_VERSION ) );
	}
}

add_action('admin_print_scripts-categories.php', 'ridwpa_cat_js_header');
add_action('admin_print_scripts-users.php', 'ridwpa_user_js_header');


/**
 * Adds content to the new 'ID' column to the media management view
 *
 * @since 0.7
 * @author scripts@schloebe.de
 *
 * @param array
 * @return array
 */
function ridwpa_column_media_id_25( $defaults ) {
	$wp_version = (!isset($wp_version)) ? get_bloginfo('version') : $wp_version;
	
	if ( version_compare( $wp_version, '2.5', '>=' ) ) {
		if ( get_option("ridwpa_media_ids_enable") && current_user_can('Reveal IDs See Media IDs') ) {
    		$defaults['ridwpa_media_id_25'] = '<abbr style="cursor:help;" title="' . __('Enhanced by Reveal IDs for WP Admin 2.5 Plugin', 'reveal-ids-for-wp-admin-25') . '">' . __('ID') . '</abbr>';
    	}
    	return $defaults;
    }
}

/**
 * Adds content to the new 'ID' column in the media management view
 *
 * @since 0.7
 * @author scripts@schloebe.de
 *
 * @param string
 * @param int
 */
function ridwpa_custom_column_media_id_25($column_name, $id) {
    if( $column_name == 'ridwpa_media_id_25' ) {
        echo (int) $id;
    }
}

add_action('manage_media_custom_column', 'ridwpa_custom_column_media_id_25', 5, 2);
add_filter('manage_media_columns', 'ridwpa_column_media_id_25', 5, 2);


function ridwpa_load_textdomain() {
	if ( function_exists('load_plugin_textdomain') ) {
		/**
		* Load all the l18n data from languages path
		*/
		if ( !defined('WP_PLUGIN_DIR') ) {
			load_plugin_textdomain('reveal-ids-for-wp-admin-25', str_replace( ABSPATH, '', dirname(__FILE__)) . '/languages' );
		} else {
			load_plugin_textdomain('reveal-ids-for-wp-admin-25', false, dirname(plugin_basename(__FILE__)) . '/languages' );
		}
	}
}

add_action('init', 'ridwpa_load_textdomain');
add_action('admin_menu', 'ridwpa_add_optionpages');
add_action('admin_menu', 'ridwpa_DefaultSettings');

register_activation_hook( __FILE__, 'ridwpa_activate' );


/**
 * Check for the former plugin version and deactivates it, otherwise set default settings
 *
 * @since 0.7
 * @author scripts@schloebe.de
 *
 * @return bool
 */
function ridwpa_activate() {
	if( function_exists('os_column_page_id_25') ) {
		deactivate_plugins(__FILE__);
		wp_die(__('You still seem to have installed the former (less powerful) plugin release \'Entry IDs in Manage Posts/Pages View for WP 2.5\' (manage-posts-pages-id-25.php). Please deactivate/remove it first in order to be able installing this plugin. <a href="javascript:history.back()">&laquo; Back</a>', 'reveal-ids-for-wp-admin-25'));
	} else {
		ridwpa_DefaultSettings();
		//return;
	}
}

/**
 * Adds the plugin's options page
 *
 * @since 0.7
 * @author scripts@schloebe.de
 */
function ridwpa_add_optionpages() {
	add_options_page(__('Reveal IDs Options', 'reveal-ids-for-wp-admin-25'), __('Reveal IDs for WP Admin', 'reveal-ids-for-wp-admin-25'), 8, __FILE__, 'ridwpa_options_page');
}


if( version_compare($wp_version, '2.5', '>=') ) {
	set_include_path( dirname(__FILE__) . PATH_SEPARATOR . get_include_path() );
	/** 
	 * This file holds all the author plugins functions
	 */
	require_once(dirname (__FILE__) . '/' . 'authorplugins.inc.php');
	restore_include_path();
}


/**
 * Adds the plugin's default settings
 *
 * @since 0.7
 * @author scripts@schloebe.de
 */
function ridwpa_DefaultSettings() {
	if( !get_option("ridwpa_post_ids_enable") ) {
		add_option("ridwpa_post_ids_enable", "1");
	}
	if( !get_option("ridwpa_page_ids_enable") ) {
		add_option("ridwpa_page_ids_enable", "1");
	}
	if( !get_option("ridwpa_link_ids_enable") ) {
		add_option("ridwpa_link_ids_enable", "1");
	}
	if( !get_option("ridwpa_cat_ids_enable") ) {
		add_option("ridwpa_cat_ids_enable", "1");
	}
	if( !get_option("ridwpa_media_ids_enable") ) {
		add_option("ridwpa_media_ids_enable", "1");
	}
	if( !get_option("ridwpa_user_ids_enable") ) {
		add_option("ridwpa_user_ids_enable", "1");
	}
	if( !get_option("ridwpa_reassigned_075_options") ) {
		add_option("ridwpa_reassigned_075_options", "0");
	}
	if( !get_option("ridwpa_version") ) {
		add_option("ridwpa_version", RIDWPA_VERSION);
	}
	if( get_option("ridwpa_version") != RIDWPA_VERSION ) {
		update_option("ridwpa_version", RIDWPA_VERSION);
	}
}

/**
 * Adds content to the plugin's options page
 *
 * @since 0.7
 * @author scripts@schloebe.de
 */
function ridwpa_options_page() {
	global $wp_version;
	if (isset($_POST['action']) === true) {
		update_option("ridwpa_post_ids_enable", (int)$_POST['ridwpa_post_ids_enable']);
		update_option("ridwpa_page_ids_enable", (int)$_POST['ridwpa_page_ids_enable']);
		update_option("ridwpa_link_ids_enable", (int)$_POST['ridwpa_link_ids_enable']);
		update_option("ridwpa_cat_ids_enable", (int)$_POST['ridwpa_cat_ids_enable']);
		update_option("ridwpa_media_ids_enable", (int)$_POST['ridwpa_media_ids_enable']);
		update_option("ridwpa_user_ids_enable", (int)$_POST['ridwpa_user_ids_enable']);
		update_option("ridwpa_reassigned_075_options", (int)'1');
		ridwpa_set_capability($_POST['ridwpa_post_ids_cap'], "Reveal IDs See Post IDs");
		ridwpa_set_capability($_POST['ridwpa_page_ids_cap'], "Reveal IDs See Page IDs");
		ridwpa_set_capability($_POST['ridwpa_link_ids_cap'], "Reveal IDs See Link IDs");
		ridwpa_set_capability($_POST['ridwpa_cat_ids_cap'], "Reveal IDs See Category IDs");
		ridwpa_set_capability($_POST['ridwpa_media_ids_cap'], "Reveal IDs See Media IDs");
		ridwpa_set_capability($_POST['ridwpa_user_ids_cap'], "Reveal IDs See User IDs");

		$successmessage = __('Settings saved.', 'reveal-ids-for-wp-admin-25');

		echo '<div id="message0" class="updated fade">
			<p>
				<strong>
					' . $successmessage . '
				</strong>
			</p>
		</div><br />';
	
		echo '<script type="text/javascript">
		function OptionsUpdated() {
			window.location.href = "' . $_SERVER['REQUEST_URI'] . '";
		}

		window.setTimeout("OptionsUpdated()", 2000);
		</script>';
	}
		
	if( function_exists('os_column_page_id_25') ) {
		$errormessage = __('You still seem to have installed the former (less powerful) plugin release \'Entry IDs in Manage Posts/Pages View for WP 2.5\' (manage-posts-pages-id-25.php). Please deactivate/remove it in order for this plugin to work properly.', 'reveal-ids-for-wp-admin-25');
		echo '<div id="message1" class="error fade">
		<p>
			<strong>
				' . $errormessage . '
			</strong>
		</p>
	</div>';
	}
		
	if( get_option('ridwpa_reassigned_075_options') == '0' ) {
		$upgrade075message = __('You appearently updated from version 0.7.4. There have been some changes in role management behaviour in this plugin. Please re-assign the options to apply the changes and to make this message disappear. ;-)', 'reveal-ids-for-wp-admin-25');
		echo '<div id="message2" class="error fade">
		<p>
			<strong>
				' . $upgrade075message . '
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
        <?php _e('Reveal IDs for WP Admin Options', 'reveal-ids-for-wp-admin-25'); ?>
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
					<select name="ridwpa_post_ids_cap" id="ridwpa_post_ids_cap" style="width:325px;" disabled="disabled">
						<?php wp_dropdown_roles( ridwpa_get_role('Reveal IDs See Post IDs') ); ?>
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
					<select name="ridwpa_page_ids_cap" id="ridwpa_page_ids_cap" style="width:325px;" disabled="disabled">
						<?php wp_dropdown_roles( ridwpa_get_role('Reveal IDs See Page IDs') ); ?>
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
					<select name="ridwpa_link_ids_cap" id="ridwpa_link_ids_cap" style="width:325px;" disabled="disabled">
						<?php wp_dropdown_roles( ridwpa_get_role('Reveal IDs See Link IDs') ); ?>
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
 				<th scope="row" valign="top"><?php _e('Show Category IDs', 'reveal-ids-for-wp-admin-25'); ?></th>
 				<td>
 					<label for="ridwpa_cat_ids_enable">
					<input name="ridwpa_cat_ids_enable" id="ridwpa_cat_ids_enable" value="1" onchange="enable_options('cat_ids', this.checked)" value="1" type="checkbox" <?php echo ( get_option('ridwpa_cat_ids_enable')=='1' ) ? ' checked="checked"' : '' ?> /> <?php _e('Reveal IDs for the category management', 'reveal-ids-for-wp-admin-25'); ?></label>
					<br />
					<small><em><?php _e('(This will add the ID after the category title)', 'reveal-ids-for-wp-admin-25'); ?></em></small>
 				</td>
 				<td align="right">
 					<strong><?php _e('What\'s the user role minimum allowed to see the IDs?', 'reveal-ids-for-wp-admin-25'); ?></strong>
					<br />
					<select name="ridwpa_cat_ids_cap" id="ridwpa_cat_ids_cap" style="width:325px;" disabled="disabled">
						<?php wp_dropdown_roles( ridwpa_get_role('Reveal IDs See Category IDs') ); ?>
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
					<select name="ridwpa_media_ids_cap" id="ridwpa_media_ids_cap" style="width:325px;" disabled="disabled">
						<?php wp_dropdown_roles( ridwpa_get_role('Reveal IDs See Media IDs') ); ?>
					</select>
 				</td>
 			</tr>
			</table>
			<?php if ( get_option('ridwpa_media_ids_enable') ) { ?>
      		<script type="text/javascript">
			enable_options('media_ids', true);
			</script>
			<?php } ?>
			
			<table class="form-table <?php echo (!get_option('ridwpa_user_ids_enable')) ? 'ridwpa_table_disabled' : ''; ?>">
 			<tr>
 				<th scope="row" valign="top"><?php _e('Show User IDs', 'reveal-ids-for-wp-admin-25'); ?></th>
 				<td>
 					<label for="ridwpa_user_ids_enable">
					<input name="ridwpa_user_ids_enable" id="ridwpa_user_ids_enable" value="1" onchange="enable_options('user_ids', this.checked)" value="1" type="checkbox" <?php echo ( get_option('ridwpa_user_ids_enable')=='1' ) ? ' checked="checked"' : '' ?> /> <?php _e('Reveal IDs for the user management', 'reveal-ids-for-wp-admin-25'); ?></label>
					<br />
					<small><em><?php _e('(This will add the ID after the user name)', 'reveal-ids-for-wp-admin-25'); ?></em></small>
 				</td>
 				<td align="right">
 					<strong><?php _e('What\'s the user role minimum allowed to see the IDs?', 'reveal-ids-for-wp-admin-25'); ?></strong>
					<br />
					<select name="ridwpa_user_ids_cap" id="ridwpa_user_ids_cap" style="width:325px;" disabled="disabled">
						<?php wp_dropdown_roles( ridwpa_get_role('Reveal IDs See User IDs') ); ?>
					</select>
 				</td>
 			</tr>
			</table>
			<?php if ( get_option('ridwpa_user_ids_enable') ) { ?>
      		<script type="text/javascript">
			enable_options('user_ids', true);
			</script>
			<?php } ?>
			<p class="submit">
				<input type="submit" name="submit" value="<?php _e('Save Changes'); ?> &raquo;" />
			</p>
			</form>
		<?php if( version_compare($wp_version, '2.5', '>=') ) { ?>
      	<h3>
        	<?php _e('More of my WordPress plugins', 'reveal-ids-for-wp-admin-25'); ?>
      	</h3>
		<table class="form-table">
 		<tr>
 			<td>
 				<?php _e('You may also be interested in some of my other plugins:', 'reveal-ids-for-wp-admin-25'); ?>
				<p id="authorplugins-wrap"><input id="authorplugins-start" value="<?php _e('Show other plugins by this author inline &raquo;', 'reveal-ids-for-wp-admin-25'); ?>" class="button-secondary" type="button"></p>
				<div id="authorplugins-wrap">
					<div id='authorplugins'>
						<div class='authorplugins-holder full' id='authorplugins_secondary'>
							<div class='authorplugins-content'>
								<ul id="authorpluginsul">
									
								</ul>
							</div>
						</div>
					</div>
				</div>
 				<?php _e('More information at: <a href="http://extend.schloebe.de" target="_blank">http://extend.schloebe.de</a>', 'reveal-ids-for-wp-admin-25'); ?>
 			</td>
 		</tr>
		</table>
		<?php } ?>
      	<h3>
        	<?php _e('Help', 'reveal-ids-for-wp-admin-25'); ?>
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