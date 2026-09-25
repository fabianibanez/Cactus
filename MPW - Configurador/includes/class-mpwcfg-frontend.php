<?php
/**
 * Frontend: encola assets, renderiza el configurador (solo opciones) y expone
 * un shortcode de respaldo.
 *
 * @package MPW_Configurador_Productos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MPWCFG_Frontend {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_shortcode( 'mpwcfg_configurador', array( $this, 'shortcode' ) );
	}

	/**
	 * Registra (no encola) assets del front. Se encolan al renderizar.
	 */
	public function register_assets() {
		wp_register_style(
			'mpwcfg-fonts',
			'https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap',
			array(),
			MPWCFG_VERSION
		);
		wp_register_style( 'mpwcfg-configurador', MPWCFG_URL . 'assets/css/configurator.css', array( 'mpwcfg-fonts' ), MPWCFG_VERSION );
		wp_register_script( 'mpwcfg-configurador', MPWCFG_URL . 'assets/js/configurator.js', array(), MPWCFG_VERSION, true );
		wp_register_style( 'mpwcfg-combinations', MPWCFG_URL . 'assets/css/combinations.css', array( 'mpwcfg-configurador' ), MPWCFG_VERSION );
		wp_register_script( 'mpwcfg-combinations', MPWCFG_URL . 'assets/js/combinations.js', array(), MPWCFG_VERSION, true );

		wp_add_inline_style( 'mpwcfg-configurador', $this->theme_css() );

		wp_localize_script(
			'mpwcfg-configurador',
			'MPWCFG_DATA',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'mpwcfg_nonce' ),
				'cart_url' => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '',
				'i18n'     => array(
					'adding'     => __( 'Agregando…', 'mpw-configurador-productos' ),
					'added'      => __( 'Ir al carrito', 'mpw-configurador-productos' ),
					'go_to_cart' => __( 'Ir al carrito', 'mpw-configurador-productos' ),
					'error'      => __( 'No se pudo agregar. Inténtalo de nuevo.', 'mpw-configurador-productos' ),
					'uploading'  => __( 'Subiendo…', 'mpw-configurador-productos' ),
					'file_error' => __( 'Archivo no válido.', 'mpw-configurador-productos' ),
				),
			)
		);
	}

	/**
	 * Bloque CSS con las variables del panel de apariencia (dashboard).
	 * Es el diseño por defecto; un widget de Elementor con sus propios
	 * controles de estilo sigue teniendo prioridad (selector más específico).
	 *
	 * @return string
	 */
	private function theme_css() {
		if ( ! class_exists( 'MPWCFG_Settings' ) ) {
			return '';
		}
		$t = MPWCFG_Settings::get();

		$decls  = '';
		$decls .= '--mpwcfg-bg:' . $t['bg'] . ';';
		$decls .= '--mpwcfg-surface:' . $t['surface'] . ';';
		$decls .= '--mpwcfg-surface-2:' . $t['surface_2'] . ';';
		$decls .= '--mpwcfg-border:' . $t['border'] . ';';
		$decls .= '--mpwcfg-accent:' . $t['accent'] . ';';
		$decls .= '--mpwcfg-accent-ink:' . $t['accent_ink'] . ';';
		$decls .= '--mpwcfg-mint:' . $t['mint'] . ';';
		$decls .= '--mpwcfg-success:' . $t['success'] . ';';
		$decls .= '--mpwcfg-whatsapp:' . $t['whatsapp'] . ';';
		$decls .= '--mpwcfg-text:' . $t['text'] . ';';
		$decls .= '--mpwcfg-text-muted:' . $t['text_muted'] . ';';
		$decls .= '--mpwcfg-text-dim:' . $t['text_dim'] . ';';
		$decls .= '--mpwcfg-font-display:"' . $t['font_display'] . '", system-ui, sans-serif;';
		$decls .= '--mpwcfg-font-mono:"' . $t['font_mono'] . '", ui-monospace, monospace;';
		$decls .= '--mpwcfg-radius:' . (int) $t['radius'] . 'px;';
		if ( ! empty( $t['padding'] ) ) {
			$decls .= 'padding:' . (int) $t['padding'] . 'px;';
		}

		return '.mpwcfg-configurator{' . $decls . '}';
	}

	/**
	 * Encola los assets del front cuando realmente se va a renderizar.
	 */
	public function enqueue() {
		wp_enqueue_style( 'mpwcfg-fonts' );
		wp_enqueue_style( 'mpwcfg-configurador' );
		wp_enqueue_script( 'mpwcfg-configurador' );
	}

	/**
	 * Resuelve el producto a usar (explícito o el actual del loop).
	 *
	 * @param int $product_id ID o 0 para "actual".
	 * @return WC_Product|null
	 */
	public function resolve_product( $product_id = 0 ) {
		if ( $product_id > 0 ) {
			$product = wc_get_product( $product_id );
			return $product ? $product : null;
		}
		global $product;
		if ( $product instanceof WC_Product ) {
			return $product;
		}
		$id = get_the_ID();
		if ( $id ) {
			$p = wc_get_product( $id );
			return $p ? $p : null;
		}
		return null;
	}

	/**
	 * Renderiza el configurador para un producto. Devuelve HTML.
	 *
	 * @param int   $product_id ID del producto.
	 * @param array $opts        Reservado para futuras opciones de presentación.
	 * @return string
	 */
	public function render( $product_id = 0, $opts = array() ) {
		$product = $this->resolve_product( $product_id );
		if ( ! $product ) {
			return $this->notice( __( 'Selecciona un producto para el configurador.', 'mpw-configurador-productos' ) );
		}

		$config = MPWCFG_Pricing::get_config( $product->get_id() );
		if ( empty( $config['enabled'] ) || 'yes' !== $config['enabled'] ) {
			return $this->notice( __( 'El configurador está desactivado en este producto.', 'mpw-configurador-productos' ) );
		}

		if ( 'combinations' === ( $config['mode'] ?? 'traditional' ) ) {
			// El modo combinaciones usa el mismo motor AJAX, nonce y carrito que
			// el configurador tradicional. Encolamos primero el asset base para
			// que MPWCFG_DATA exista también en este modo.
			$this->enqueue();
			wp_enqueue_style( 'mpwcfg-combinations' );
			wp_enqueue_script( 'mpwcfg-combinations' );
			$pid = $product->get_id();
			ob_start();
			include MPWCFG_PATH . 'templates/combinations.php';
			return ob_get_clean();
		}

		$this->enqueue();

		// Cálculo inicial con los valores por defecto de cada paso.
		$initial = MPWCFG_Pricing::calculate( $config, array() );

		ob_start();
		include MPWCFG_PATH . 'templates/configurator.php';
		return ob_get_clean();
	}

	/**
	 * Mensaje contenedor con la clase del configurador (para que herede el tema).
	 */
	private function notice( $text ) {
		return '<div class="mpwcfg-configurator mpwcfg-configurator--empty"><p class="mpwcfg-empty">' . esc_html( $text ) . '</p></div>';
	}

	/**
	 * Shortcode de respaldo: [mpwcfg_configurador id="123"].
	 */
	public function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'mpwcfg_configurador'
		);

		return $this->render( (int) $atts['id'] );
	}
}
