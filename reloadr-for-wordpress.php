<?php
/*
Plugin Name: Reloadr for WordPress
Version: 0.2
Description: A plugin based on "Reloadr" which watches web project files for change, and refreshes their page automatically. This is good for client-side assets(e.g. *.css) and server-side assets(e.g. *.php). Awesome scripts "Reloadr" were made by Daniel Bergey(https://github.com/dbergey).
Author: Tecking
Author URI: http://www.tecking.org/
License: GPLv2
*/

/*  Copyright 2013 Tecking (email : tecking@tecking.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/*
 * RFW class
 */
class RFW {
	public $option;
	public function __construct( $option ) {
		$this->option = $option;
	}

	public function get_path() {
		$stylesheet_dir = get_stylesheet_directory();
		if ( DIRECTORY_SEPARATOR === '\\' ) $stylesheet_dir = str_replace( '\\', '/', $stylesheet_dir );

		$key = array(
			'client' => 'rfw_client_assets',
			'server' => 'rfw_server_assets'
		);

		$default = array(
			'client' => '"' . get_stylesheet_directory_uri() . '/style.css"',
			'server' => '"' . $stylesheet_dir . '/*.php"'
		);

		$args = get_option( $key{$this->option} );
		if ( !empty( $args ) ) {
			$args = explode( ',', get_option( $key{$this->option} ) );
			if ( is_array( $args ) ) {
				foreach ( $args as $value ) {
					$value = trim( $value );
					$path .= ', "' . get_stylesheet_directory_uri() . esc_attr( $value ) . '"';
				}
			}
			else {
				$args = trim( $args );
				$path .= ', "' . get_stylesheet_directory_uri() . esc_attr( $args ) . '"';
			}
		}
		return $default{$this->option} . $path;
	}
}


/*
 * Place code in the head section
 */
add_action( 'wp_head', 'reloadr_for_wordpress' );
function reloadr_for_wordpress() {
	$client = new RFW( 'client' );
	$server = new RFW( 'server' );

	$str  = '<script type="text/javascript" src="' . plugin_dir_url( __FILE__ ) . 'Reloadr/reloadr.js"></script>';
	$str .= '
	<script>
		Reloadr.go({
			client: [
				' . $client->get_path() . '
			],
			server: [
				' . $server->get_path() . '
			],
			path: "' . plugin_dir_url( __FILE__ ) . 'Reloadr/reloadr.php"
		});
	</script>
	';
	echo $str;
}


/*
 * Remove keys if RFW is uninstalled
 */
if ( function_exists( 'register_uninstall_hook' ) ) register_uninstall_hook( __FILE__, 'rfw_uninstall_hook' );
function rfw_uninstall_hook() {
	delete_option( 'rfw_client_assets' );
	delete_option( 'rfw_server_assets' );
}


/*
 * Admin menu
 */
add_action( 'admin_menu', 'rfw_admin_menu' );
function rfw_admin_menu() {
	add_options_page(
		'Reloadr',
		'Reloadr',
		'manage_options',
		'rfw_settings',
		'rfw_settings'
	);
}
function rfw_settings() { ?>
	<div class="wrap">
		<h2>Reloadr for WordPress</h2>
		<form action="options.php" method="post">
			<?php wp_nonce_field( 'update-options' ); ?>
			<p>Separate values with commas.</p>
			<table class="form-table">
				<tr>
					<th>Client-side Assets</th>
					<td><input type="text" name="rfw_client_assets" value="<?php echo esc_attr( get_option( 'rfw_client_assets' ) ); ?>"><br>
						e.g. /your_css_directory/style.css (default: /style.css)</td>
				</tr>
				<tr>
					<th>Server-side Assets</th>
					<td><input type="text" name="rfw_server_assets" value="<?php echo esc_attr( get_option( 'rfw_server_assets' ) ); ?>"><br>
						Wildcard is available.<br />e.g. /your_includes_directory/*.php (default: /*.php)</td>
				</tr>
			</table>
			<input type="hidden" name="action" value="update">
			<input type="hidden" name="page_options" value="rfw_client_assets,rfw_server_assets">
			<p class="submit"><input type="submit" class="button-primary" value="Save Changes"></p>
		</form>
	</div>
<?php }
