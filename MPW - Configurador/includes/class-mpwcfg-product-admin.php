<?php
/**
 * Pestaña "Configurador" en la página del producto (WooCommerce).
 * Toda la configuración del producto (pasos, opciones, precios y rótulos) se
 * define aquí, según las características de ese producto en concreto.
 *
 * @package MPW_Configurador_Productos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MPWCFG_Product_Admin {

	public function __construct() {
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'render_panel' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	}

	/**
	 * Añade la pestaña "Configurador".
	 */
	public function add_tab( $tabs ) {
		$tabs['mpwcfg_configurador'] = array(
			'label'    => __( 'Configurador', 'mpw-configurador-productos' ),
			'target'   => 'cc_product_data',
			'class'    => array( 'show_if_simple', 'show_if_variable' ),
			'priority' => 65,
		);
		return $tabs;
	}

	/**
	 * Carga assets del admin solo en la pantalla de edición de producto.
	 */
	public function assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || 'product' !== $screen->post_type ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'mpwcfg-admin', MPWCFG_URL . 'assets/css/admin.css', array(), MPWCFG_VERSION );
		wp_enqueue_script( 'mpwcfg-admin', MPWCFG_URL . 'assets/js/admin.js', array( 'jquery', 'jquery-ui-sortable' ), MPWCFG_VERSION, true );
		wp_localize_script(
			'mpwcfg-admin',
			'mpwcfg_admin_i18n',
			array(
				'chooseImage' => __( 'Selecciona una imagen de muestra', 'mpw-configurador-productos' ),
			)
		);
	}

	/**
	 * Renderiza el panel de la pestaña.
	 */
	public function render_panel() {
		global $post;
		$saved  = get_post_meta( $post->ID, MPWCFG_META_KEY, true );
		$saved  = is_array( $saved ) ? $saved : array();

		// Config propia si existe; si no, los valores de ejemplo como punto de partida.
		$config = ! empty( $saved ) ? wp_parse_args( $saved, MPWCFG_Pricing::default_config() ) : MPWCFG_Pricing::default_config();
		?>
		<div id="cc_product_data" class="panel woocommerce_options_panel mpwcfg-panel">

			<div class="mpwcfg-panel__head">
				<p class="mpwcfg-panel__intro">
					<?php esc_html_e( 'Elige un único modo para este producto. El configurador tradicional y el modo por combinaciones son independientes.', 'mpw-configurador-productos' ); ?>
				</p>
			</div>

			<div class="mpwcfg-mode-switch">
				<label><input type="radio" name="mpwcfg[mode]" value="traditional" <?php checked( 'traditional', $config['mode'] ?? 'traditional' ); ?> /> <span>Configurador tradicional</span><small>Pasos, opciones y cantidad</small></label>
				<label><input type="radio" name="mpwcfg[mode]" value="combinations" <?php checked( 'combinations', $config['mode'] ?? 'traditional' ); ?> /> <span>Configurador por combinaciones</span><small>Opciones dependientes y precio por combinación</small></label>
			</div>

			<div class="options_group">
				<?php
				MPWCFG_Config_Builder::render_globals( $config );
				?>
			</div>

			<div class="mpwcfg-mode-panel mpwcfg-mode-panel--traditional" data-mode-panel="traditional">
				<div class="mpwcfg-mode-panel__title"><strong>Configurador tradicional</strong><span>Este panel conserva el funcionamiento actual de Configurator Machine 1.8.1.</span></div>
				<?php MPWCFG_Config_Builder::render_steps( $config ); ?>
			</div>

			<div class="mpwcfg-mode-panel mpwcfg-mode-panel--combinations" data-mode-panel="combinations">
				<?php MPWCFG_Config_Builder::render_combinations( $config ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Guarda la configuración al guardar el producto.
	 *
	 * @param int $product_id ID del producto.
	 */
	public function save( $product_id ) {
		// Nonce de WooCommerce ya verificado en woocommerce_process_product_meta.
		if ( ! isset( $_POST['mpwcfg'] ) || ! is_array( $_POST['mpwcfg'] ) ) {
			return;
		}

		$raw    = wp_unslash( $_POST['mpwcfg'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$config = MPWCFG_Config_Builder::sanitize( $raw );

		update_post_meta( $product_id, MPWCFG_META_KEY, $config );
	}
}
