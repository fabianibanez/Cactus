<?php
/**
 * Widget de Elementor: MPW · Configurador de Productos.
 * Renderiza SOLO la columna de opciones y expone el look & feel como controles
 * mapeados a variables CSS (--mpwcfg-*). La lógica vive en el producto / categoría.
 *
 * @package MPW_Configurador_Productos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class MPWCFG_Widget_Configurator extends Widget_Base {

	public function get_name() {
		return 'mpwcfg_configurador';
	}

	public function get_title() {
		return __( 'MPW · Configurador de Productos', 'mpw-configurador-productos' );
	}

	public function get_icon() {
		return 'eicon-product-add-to-cart';
	}

	public function get_categories() {
		return array( 'mpwcfg' );
	}

	public function get_keywords() {
		return array( 'mpwcfg', 'configurador', 'woocommerce', 'producto', 'precio' );
	}

	/**
	 * Lista de productos para el selector.
	 */
	private function product_options() {
		$options  = array( 0 => __( '— Selecciona —', 'mpw-configurador-productos' ) );
		$products = wc_get_products(
			array(
				'status'  => 'publish',
				'limit'   => 100,
				'orderby' => 'title',
				'order'   => 'ASC',
			)
		);
		foreach ( $products as $p ) {
			$options[ $p->get_id() ] = $p->get_name() . ' (#' . $p->get_id() . ')';
		}
		return $options;
	}

	protected function register_controls() {
		$this->controls_content();
		$this->controls_colors();
		$this->controls_typography();
		$this->controls_shape();
	}

	/* ------------------------------------------------------------------ */
	/* CONTENIDO                                                          */
	/* ------------------------------------------------------------------ */
	private function controls_content() {
		$this->start_controls_section(
			'sec_content',
			array(
				'label' => __( 'Producto', 'mpw-configurador-productos' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'use_current',
			array(
				'label'        => __( 'Usar producto actual', 'mpw-configurador-productos' ),
				'description'  => __( 'Actívalo dentro de una plantilla de producto único.', 'mpw-configurador-productos' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Sí', 'mpw-configurador-productos' ),
				'label_off'    => __( 'No', 'mpw-configurador-productos' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->add_control(
			'product_id',
			array(
				'label'     => __( 'Producto', 'mpw-configurador-productos' ),
				'type'      => Controls_Manager::SELECT2,
				'options'   => $this->product_options(),
				'default'   => 0,
				'condition' => array( 'use_current!' => 'yes' ),
			)
		);

		$this->add_control(
			'content_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'Los pasos, opciones y precios se definen en la pestaña <strong>Configurador</strong> de cada producto. Aquí solo se ajusta el aspecto visual.', 'mpw-configurador-productos' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->end_controls_section();
	}

	/* ------------------------------------------------------------------ */
	/* COLORES                                                            */
	/* ------------------------------------------------------------------ */
	private function controls_colors() {
		$this->start_controls_section(
			'sec_colors',
			array(
				'label' => __( 'Colores', 'mpw-configurador-productos' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$vars = array(
			'c_bg'         => array( __( 'Fondo', 'mpw-configurador-productos' ), '--mpwcfg-bg', '#190b1f' ),
			'c_surface'    => array( __( 'Superficie (botones)', 'mpw-configurador-productos' ), '--mpwcfg-surface', '#251331' ),
			'c_surface_2'  => array( __( 'Superficie hover', 'mpw-configurador-productos' ), '--mpwcfg-surface-2', '#2e1840' ),
			'c_border'     => array( __( 'Borde', 'mpw-configurador-productos' ), '--mpwcfg-border', '#3a2348' ),
			'c_accent'     => array( __( 'Acento (fucsia)', 'mpw-configurador-productos' ), '--mpwcfg-accent', '#ff2e7e' ),
			'c_accent_ink' => array( __( 'Texto sobre acento', 'mpw-configurador-productos' ), '--mpwcfg-accent-ink', '#ffffff' ),
			'c_mint'       => array( __( 'Secundario / descuentos', 'mpw-configurador-productos' ), '--mpwcfg-mint', '#36d6cf' ),
			'c_success'    => array( __( 'Sellos de confianza', 'mpw-configurador-productos' ), '--mpwcfg-success', '#4bd99a' ),
			'c_text'       => array( __( 'Texto principal', 'mpw-configurador-productos' ), '--mpwcfg-text', '#f7f0f4' ),
			'c_text_muted' => array( __( 'Texto secundario', 'mpw-configurador-productos' ), '--mpwcfg-text-muted', '#c3b0cf' ),
			'c_text_dim'   => array( __( 'Texto tenue / labels', 'mpw-configurador-productos' ), '--mpwcfg-text-dim', '#8b779a' ),
		);

		foreach ( $vars as $id => $data ) {
			list( $label, $css_var, $default ) = $data;
			$this->add_control(
				$id,
				array(
					'label'     => $label,
					'type'      => Controls_Manager::COLOR,
					'default'   => $default,
					'selectors' => array(
						'{{WRAPPER}} .mpwcfg-configurator' => $css_var . ': {{VALUE}};',
					),
				)
			);
		}

		$this->end_controls_section();
	}

	/* ------------------------------------------------------------------ */
	/* TIPOGRAFÍA                                                         */
	/* ------------------------------------------------------------------ */
	private function controls_typography() {
		$this->start_controls_section(
			'sec_type',
			array(
				'label' => __( 'Tipografía', 'mpw-configurador-productos' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'font_display',
			array(
				'label'     => __( 'Fuente principal', 'mpw-configurador-productos' ),
				'type'      => Controls_Manager::FONT,
				'default'   => 'Space Grotesk',
				'selectors' => array(
					'{{WRAPPER}} .mpwcfg-configurator' => '--mpwcfg-font-display: "{{VALUE}}", sans-serif;',
				),
			)
		);

		$this->add_control(
			'font_mono',
			array(
				'label'     => __( 'Fuente mono / etiquetas', 'mpw-configurador-productos' ),
				'type'      => Controls_Manager::FONT,
				'default'   => 'JetBrains Mono',
				'selectors' => array(
					'{{WRAPPER}} .mpwcfg-configurator' => '--mpwcfg-font-mono: "{{VALUE}}", monospace;',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'total_typo',
				'label'    => __( 'Total (precio grande)', 'mpw-configurador-productos' ),
				'selector' => '{{WRAPPER}} .mpwcfg-total',
			)
		);

		$this->end_controls_section();
	}

	/* ------------------------------------------------------------------ */
	/* FORMA                                                              */
	/* ------------------------------------------------------------------ */
	private function controls_shape() {
		$this->start_controls_section(
			'sec_shape',
			array(
				'label' => __( 'Forma', 'mpw-configurador-productos' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'radius',
			array(
				'label'      => __( 'Radio de bordes', 'mpw-configurador-productos' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 32 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 16 ),
				'selectors'  => array(
					'{{WRAPPER}} .mpwcfg-configurator' => '--mpwcfg-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'pad',
			array(
				'label'      => __( 'Relleno interior', 'mpw-configurador-productos' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 64 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 0 ),
				'selectors'  => array(
					'{{WRAPPER}} .mpwcfg-configurator' => 'padding: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/* ------------------------------------------------------------------ */
	/* RENDER                                                             */
	/* ------------------------------------------------------------------ */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$frontend = MPWCFG_Plugin::instance()->frontend;

		$use_current = ( 'yes' === $settings['use_current'] );
		$product_id  = $use_current ? 0 : (int) $settings['product_id'];

		// En el editor de Elementor "producto actual" no existe: usa el seleccionado como vista previa.
		if ( $use_current && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			$resolved = $frontend->resolve_product( 0 );
			if ( ! $resolved && (int) $settings['product_id'] ) {
				$product_id = (int) $settings['product_id'];
			}
		}

		echo $frontend->render( $product_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
