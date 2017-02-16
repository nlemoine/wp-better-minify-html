<?php
/*
Plugin Name:  WP Better Minify HTML
Plugin URI:   https://wordpress.org/plugins/wp-better-minify-html
Description:  Minify HTML output
Version:      0.1.0
Author:       Nicolas Lemoine
Author URI:   https://github.com/nlemoine
License:      GPL v3
*/

if ( ! class_exists( 'WP_Better_Minify_HTML' ) ) {

	class WP_Better_Minify_HTML {

		function __construct() {

			if ( is_admin() ) {
				add_action( 'admin_init', array( $this, 'check_version' ) );
			} else {
				add_action( 'get_header', array( $this, 'process_output' ), 1 );
			}

		}

		function process_output() {

			if ( apply_filters( 'wbmh_enable', WP_DEBUG ) ) {
				return;
			}

			ob_start( array( $this, 'minify_html' ) );
		}

		function minify_html( $html ) {
			require 'vendor/autoload.php';
			$parser = \WyriHaximus\HtmlCompress\Factory::construct();
			return $parser->compress( $html );
		}

		function check_version() {

			if ( ! is_plugin_active( plugin_basename( __FILE__ ) ) ) {
				return;
			}

			if ( $this->compatible_version() ) {
				return;
			}

			deactivate_plugins( plugin_basename( __FILE__ ) );

			add_action( 'admin_notices', array( $this, 'admin_notice' ) );
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}

		}

		function admin_notice() {
			echo '<div class="error"><p><strong>' . sprintf(
				/* translators: PHP version. */
				esc_html__( 'WP Better Minify HTML requires PHP 5.4 or higher, your PHP version is %s', 'wp-better-minify-html' ),
				phpversion()
			)
			. '</strong></p></div>';
		}

		function compatible_version() {
			if ( version_compare( PHP_VERSION, '5.4', '<' ) ) {
				return false;
			}
			return true;
		}

	}

}

if ( class_exists( 'WP_Better_Minify_HTML' ) ) {
	$wp_better_minify_html = new WP_Better_Minify_HTML();
	register_activation_hook( __FILE__, array( $wp_better_minify_html, 'check_version' ) );
}
