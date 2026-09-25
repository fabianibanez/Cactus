<?php
/**
 * Integración con carrito y pedido.
 *
 * @package MPW_Configurador_Productos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MPWCFG_Cart {

	public function __construct() {
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'set_price' ), 20 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'item_data' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_name', array( $this, 'cart_item_name' ), 10, 3 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'order_line_item' ), 10, 4 );
	}

	/**
	 * Aplica el precio configurado (calculado en servidor) a la línea del carrito.
	 */
	public function set_price( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}
		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
			return;
		}

		foreach ( $cart->get_cart() as $item ) {
			if ( ! empty( $item['mpwcfg_config']['price'] ) ) {
				$item['data']->set_price( (float) $item['mpwcfg_config']['price'] );
			}
		}
	}

	/**
	 * Muestra el detalle de la configuración en carrito y checkout.
	 */
	public function item_data( $data, $cart_item ) {
		if ( empty( $cart_item['mpwcfg_config'] ) ) {
			return $data;
		}
		$config = $cart_item['mpwcfg_config'];

		if ( ! empty( $config['lines'] ) ) {
			foreach ( $config['lines'] as $line ) {
				$value = esc_html( $line['value'] );
				if ( isset( $line['price'] ) && $line['price'] > 0 ) {
					$value .= ' (+' . esc_html( MPWCFG_Pricing::format_clp( $line['price'] ) ) . ')';
				}
				$data[] = array(
					'key'   => esc_html( $line['label'] ),
					'value' => $value,
				);
			}
		}

		if ( ! empty( $config['file']['name'] ) ) {
			$data[] = array(
				'key'   => __( 'Diseño adjunto', 'mpw-configurador-productos' ),
				'value' => esc_html( $config['file']['name'] ),
			);
		}

		return $data;
	}

	/**
	 * Añade las unidades del tramo al nombre en el carrito.
	 */
	public function cart_item_name( $name, $cart_item, $cart_item_key ) {
		if ( ! empty( $cart_item['mpwcfg_config']['qty_units'] ) ) {
			$units = (int) $cart_item['mpwcfg_config']['qty_units'];
			$name .= ' <span class="mpwcfg-cart-units">· ' . esc_html( $units ) . ' ' . esc_html__( 'uds.', 'mpw-configurador-productos' ) . '</span>';
		}
		return $name;
	}

	/**
	 * Persiste la configuración como metadatos de la línea del pedido.
	 */
	public function order_line_item( $item, $cart_item_key, $values, $order ) {
		if ( empty( $values['mpwcfg_config'] ) ) {
			return;
		}
		$config = $values['mpwcfg_config'];

		if ( ! empty( $config['qty_units'] ) ) {
			$item->add_meta_data( __( 'Unidades', 'mpw-configurador-productos' ), (int) $config['qty_units'] );
		}
		if ( ! empty( $config['lines'] ) ) {
			foreach ( $config['lines'] as $line ) {
				$item->add_meta_data( $line['label'], $line['value'] );
			}
		}
		if ( ! empty( $config['file']['url'] ) ) {
			$item->add_meta_data( __( 'Diseño adjunto', 'mpw-configurador-productos' ), esc_url_raw( $config['file']['url'] ) );
		}
	}
}
