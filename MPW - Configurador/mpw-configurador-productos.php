<?php
/**
 * Plugin Name:       MPW - Configurador de productos
 * Plugin URI:        https://miprimeraweb.cl/
 * Description:        Configurador visual de productos para WooCommerce + Elementor. Las opciones, precios y experiencia posterior al carrito se definen por producto.
 * Version:           1.10.34
 * Author:            Fabian Ibañez de MPW
 * Author URI:        https://miprimeraweb.cl/
 * License:            GPL-2.0-or-later
 * License URI:        https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mpw-configurador-productos
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 7.0
 *
 * @package MPW_Configurador_Productos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Acceso directo no permitido.
}

if ( ! defined( 'MPWCFG_VERSION' ) ) {
	define( 'MPWCFG_VERSION', '1.10.34' );
}
if ( ! defined( 'MPWCFG_FILE' ) ) {
	define( 'MPWCFG_FILE', __FILE__ );
}
if ( ! defined( 'MPWCFG_PATH' ) ) {
	define( 'MPWCFG_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'MPWCFG_URL' ) ) {
	define( 'MPWCFG_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'MPWCFG_META_KEY' ) ) {
	define( 'MPWCFG_META_KEY', '_mpwcfg_config' );
}

/**
 * Declarar compatibilidad con HPOS (almacenamiento de pedidos en tablas propias).
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', MPWCFG_FILE, true );
		}
	}
);

/**
 * Arranque del plugin: solo si WooCommerce está activo.
 */
add_action(
	'plugins_loaded',
	function () {
		load_plugin_textdomain( 'mpw-configurador-productos', false, dirname( plugin_basename( MPWCFG_FILE ) ) . '/languages' );

		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action(
				'admin_notices',
				function () {
					echo '<div class="notice notice-warning"><p>';
					echo esc_html__( 'MPW · Configurador necesita WooCommerce activo para funcionar.', 'mpw-configurador-productos' );
					echo '</p></div>';
				}
			);
			return;
		}

		require_once MPWCFG_PATH . 'includes/class-mpwcfg-pricing.php';
		require_once MPWCFG_PATH . 'includes/class-mpwcfg-config-builder.php';
		require_once MPWCFG_PATH . 'includes/class-mpwcfg-product-admin.php';
		require_once MPWCFG_PATH . 'includes/class-mpwcfg-settings.php';
		require_once MPWCFG_PATH . 'includes/class-mpwcfg-frontend.php';
		require_once MPWCFG_PATH . 'includes/class-mpwcfg-ajax.php';
		require_once MPWCFG_PATH . 'includes/class-mpwcfg-cart.php';
		require_once MPWCFG_PATH . 'includes/class-mpwcfg-plugin.php';

		MPWCFG_Plugin::instance();
	}
);
