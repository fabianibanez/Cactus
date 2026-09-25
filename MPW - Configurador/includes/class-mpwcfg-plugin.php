<?php
/**
 * Loader principal: orquesta los componentes del plugin.
 *
 * @package MPW_Configurador_Productos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MPWCFG_Plugin {

	/** @var MPWCFG_Plugin */
	private static $instance = null;

	/** @var MPWCFG_Frontend */
	public $frontend;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		new MPWCFG_Product_Admin();
		new MPWCFG_Settings();
		new MPWCFG_Ajax();
		new MPWCFG_Cart();
		$this->frontend = new MPWCFG_Frontend();

		// Integración con Elementor (solo si está activo).
		add_action( 'elementor/widgets/register', array( $this, 'register_widget' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
	}

	/**
	 * Categoría propia en el panel de Elementor.
	 */
	public function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'mpwcfg',
			array(
				'title' => __( 'MPW', 'mpw-configurador-productos' ),
				'icon'  => 'fa fa-plug',
			)
		);
	}

	/**
	 * Registra el widget del configurador.
	 */
	public function register_widget( $widgets_manager ) {
		require_once MPWCFG_PATH . 'includes/elementor/class-mpwcfg-widget-configurator.php';
		$widgets_manager->register( new MPWCFG_Widget_Configurator() );
	}
}
