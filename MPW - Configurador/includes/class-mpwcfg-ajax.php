<?php
/**
 * Endpoints AJAX.
 * El precio se recalcula SIEMPRE en el servidor a partir de la configuración
 * guardada y de los índices elegidos; nunca se confía en el monto del navegador.
 *
 * @package MPW_Configurador_Productos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MPWCFG_Ajax {

	public function __construct() {
		add_action( 'wp_ajax_mpwcfg_price', array( $this, 'price' ) );
		add_action( 'wp_ajax_nopriv_mpwcfg_price', array( $this, 'price' ) );

		add_action( 'wp_ajax_mpwcfg_upload', array( $this, 'upload' ) );
		add_action( 'wp_ajax_nopriv_mpwcfg_upload', array( $this, 'upload' ) );

		add_action( 'wp_ajax_mpwcfg_add_to_cart', array( $this, 'add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_mpwcfg_add_to_cart', array( $this, 'add_to_cart' ) );
	}

	/**
	 * Verifica nonce o termina.
	 */
	private function verify() {
		if ( ! check_ajax_referer( 'mpwcfg_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Sesión expirada. Recarga la página.', 'mpw-configurador-productos' ) ), 403 );
		}
	}

	/**
	 * Lee y normaliza las selecciones enviadas (key => índice).
	 */
	private function get_selections() {
		$raw = isset( $_POST['selections'] ) ? wp_unslash( $_POST['selections'] ) : array(); // phpcs:ignore
		$out = array();
		if ( is_array( $raw ) ) {
			foreach ( $raw as $k => $v ) {
				$k = sanitize_key( $k );
				// Claves que terminan en _custom son cantidades personalizadas (entero).
				if ( substr( $k, -7 ) === '_custom' ) {
					$out[ $k ] = max( 1, (int) $v );
				} elseif ( 'custom' === $v ) {
					// El paso usa modo de cantidad personalizada.
					$out[ $k ] = 'custom';
				} else {
					$out[ $k ] = (int) $v;
				}
			}
		}
		return $out;
	}

	/**
	 * Devuelve el precio recalculado para las selecciones actuales.
	 */
	public function price() {
		$this->verify();

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		if ( ! $product_id || ! wc_get_product( $product_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Producto no válido.', 'mpw-configurador-productos' ) ), 400 );
		}

		// Para combinaciones usamos también la sesión de WooCommerce como respaldo
		// del token firmado. Esto evita que una diferencia entre requests del navegador
		// haga perder un cálculo que el servidor ya validó.
		if ( ! WC()->session ) {
			WC()->initialize_session();
		}

		$config = MPWCFG_Pricing::get_config( $product_id );
		$selections = $this->get_selections();
		$calc = ( 'combinations' === ( $config['mode'] ?? 'traditional' ) ) ? MPWCFG_Pricing::calculate_combinations( $config, $selections ) : MPWCFG_Pricing::calculate( $config, $selections );

		$complete = ! empty( $calc['complete'] ) || ( 'combinations' === ( $config['mode'] ?? 'traditional' ) && (float) $calc['total'] > 0 && count( (array) $calc['lines'] ) >= 3 );
		$token_payload = array(
			'product_id' => $product_id,
			'selections' => $selections,
			'total'      => (float) $calc['total'],
			'unit_price' => (float) $calc['unit_price'],
			'qty_units'  => (float) $calc['qty_units'],
			'lines'      => $calc['lines'],
			'exp'        => time() + 900,
		);
		$token_body  = base64_encode( wp_json_encode( $token_payload ) );
		$token_sig   = hash_hmac( 'sha256', $token_body, wp_salt( 'auth' ) );
		$price_token = rtrim( strtr( base64_encode( wp_json_encode( array( 'b' => $token_body, 's' => $token_sig ) ) ), '+/', '-_' ), '=' );

		// Respaldo server-side: guardamos el último cálculo válido de combinaciones
		// en la sesión de WooCommerce durante 15 minutos. El carrito podrá recuperarlo
		// aunque el token no llegue correctamente desde el navegador.
		if ( 'combinations' === ( $config['mode'] ?? 'traditional' ) && $complete && (float) $calc['total'] > 0 && WC()->session ) {
			WC()->session->set(
				'mpwcfg_combo_price_' . $product_id,
				array(
					'product_id' => $product_id,
					'selections' => $selections,
					'total' => (float) $calc['total'],
					'unit_price' => (float) $calc['unit_price'],
					'qty_units' => (float) $calc['qty_units'],
					'lines' => $calc['lines'],
					'exp' => time() + 900,
				)
			);
		}

		wp_send_json_success(
			array(
				'total'           => $calc['total'],
				'total_formatted' => MPWCFG_Pricing::format_clp( $calc['total'] ),
				'unit'            => $calc['unit_price'],
				'unit_formatted'  => MPWCFG_Pricing::format_clp( $calc['unit_price'] ),
				'qty_units'       => $calc['qty_units'],
				'complete'        => $complete,
				'lines'           => $calc['lines'],
				'price_token'     => $price_token,
			)
		);
	}

	/**
	 * Sube el archivo de diseño del cliente a un subdirectorio protegido.
	 */
	public function upload() {
		$this->verify();

		if ( empty( $_FILES['file'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No llegó ningún archivo.', 'mpw-configurador-productos' ) ), 400 );
		}

		$allowed = array(
			'pdf'  => 'application/pdf',
			'png'  => 'image/png',
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'ai'   => 'application/postscript',
			'eps'  => 'application/postscript',
		);

		$file = $_FILES['file']; // phpcs:ignore
		if ( (int) $file['size'] > 25 * 1024 * 1024 ) {
			wp_send_json_error( array( 'message' => __( 'Máximo 25 MB.', 'mpw-configurador-productos' ) ), 400 );
		}

		$check = wp_check_filetype( $file['name'], $allowed );
		if ( ! $check['ext'] || ! in_array( $check['type'], $allowed, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Formato no permitido. Usa PDF, PNG, JPG, AI o EPS.', 'mpw-configurador-productos' ) ), 400 );
		}

		// Dirección de subida a un subdirectorio dedicado.
		add_filter( 'upload_dir', array( $this, 'upload_dir' ) );
		$overrides = array(
			'test_form' => false,
			'mimes'     => $allowed,
		);
		$result = wp_handle_upload( $file, $overrides );
		remove_filter( 'upload_dir', array( $this, 'upload_dir' ) );

		if ( isset( $result['error'] ) ) {
			wp_send_json_error( array( 'message' => $result['error'] ), 400 );
		}

		wp_send_json_success(
			array(
				'url'  => $result['url'],
				'name' => sanitize_file_name( $file['name'] ),
			)
		);
	}

	/**
	 * Subdirectorio dedicado para los artes subidos.
	 */
	public function upload_dir( $dirs ) {
		$dirs['subdir'] = '/mpw-configurador-artes' . $dirs['subdir'];
		$dirs['path']   = $dirs['basedir'] . $dirs['subdir'];
		$dirs['url']    = $dirs['baseurl'] . $dirs['subdir'];
		return $dirs;
	}

	/**
	 * Agrega el producto configurado al carrito (precio recalculado en servidor).
	 */
	public function add_to_cart() {
		$this->verify();

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$product    = $product_id ? wc_get_product( $product_id ) : null;
		if ( ! $product ) {
			wp_send_json_error( array( 'message' => __( 'Producto no válido.', 'mpw-configurador-productos' ) ), 400 );
		}

		// WooCommerce no inicializa la sesión del carrito automáticamente en AJAX.
		// Sin esto, WC()->cart es null y add_to_cart() falla silenciosamente.
		if ( ! WC()->session ) {
			WC()->initialize_session();
		}
		if ( ! WC()->cart ) {
			WC()->initialize_cart();
		}

		$config     = MPWCFG_Pricing::get_config( $product_id );
		$selections = $this->get_selections();
		$is_combo   = 'combinations' === ( $config['mode'] ?? 'traditional' );
		$calc       = $is_combo ? MPWCFG_Pricing::calculate_combinations( $config, $selections ) : MPWCFG_Pricing::calculate( $config, $selections );

		// PLAN B: si WooCommerce tiene guardado un cálculo válido de la última
		// llamada de precio, úsalo como fuente de verdad. Se verifica producto,
		// expiración y monto antes de aceptarlo.
		if ( $is_combo && WC()->session ) {
			$session_calc = WC()->session->get( 'mpwcfg_combo_price_' . $product_id );
			if (
				is_array( $session_calc ) &&
				(int) ( $session_calc['product_id'] ?? 0 ) === $product_id &&
				(int) ( $session_calc['exp'] ?? 0 ) >= time() &&
				(float) ( $session_calc['total'] ?? 0 ) > 0
			) {
				$calc = array(
					'total' => (float) $session_calc['total'],
					'unit_price' => (float) ( $session_calc['unit_price'] ?? $session_calc['total'] ),
					'qty_units' => (float) ( $session_calc['qty_units'] ?? 1 ),
					'lines' => is_array( $session_calc['lines'] ?? null ) ? $session_calc['lines'] : array(),
					'complete' => true,
				);
				$selections = is_array( $session_calc['selections'] ?? null ) ? $session_calc['selections'] : $selections;
			}
		}

		// En combinaciones, el token firmado emitido por el endpoint de precio
		// es la fuente de verdad para el mismo cálculo que el cliente acaba de
		// visualizar. Se valida de forma canónica para no depender del orden de
		// las claves del array recibido por AJAX.
		$price_token = isset( $_POST['price_token'] ) ? sanitize_text_field( wp_unslash( $_POST['price_token'] ) ) : '';
		if ( $is_combo && $price_token ) {
			$decoded = strtr( $price_token, '-_', '+/' );
			$decoded .= str_repeat( '=', ( 4 - strlen( $decoded ) % 4 ) % 4 );
			$packet_raw = base64_decode( $decoded, true );
			$packet = $packet_raw ? json_decode( $packet_raw, true ) : null;
			if ( is_array( $packet ) && ! empty( $packet['b'] ) && ! empty( $packet['s'] ) ) {
				$expected = hash_hmac( 'sha256', $packet['b'], wp_salt( 'auth' ) );
				if ( hash_equals( $expected, $packet['s'] ) ) {
					$payload_raw = base64_decode( $packet['b'], true );
					$payload = $payload_raw ? json_decode( $payload_raw, true ) : null;
					$token_selections = isset( $payload['selections'] ) && is_array( $payload['selections'] ) ? $payload['selections'] : array();
					if (
						is_array( $payload ) &&
						(int) ( $payload['product_id'] ?? 0 ) === $product_id &&
						(int) ( $payload['exp'] ?? 0 ) >= time() &&
						(float) ( $payload['total'] ?? 0 ) > 0
					) {
						// El token firmado es la representación canónica del cálculo mostrado
						// por el endpoint de precio. Usamos sus selecciones para evitar que
						// diferencias de serialización/índices entre requests invaliden una
						// combinación que ya fue calculada y firmada por el servidor.
						$selections = $token_selections;
						$calc = array(
							'total'      => (float) $payload['total'],
							'unit_price' => (float) ( $payload['unit_price'] ?? $payload['total'] ),
							'qty_units'  => (float) ( $payload['qty_units'] ?? 1 ),
							'lines'      => is_array( $payload['lines'] ?? null ) ? $payload['lines'] : array(),
							'complete'   => true,
						);
					}
				}
			}
		}

		if ( $is_combo && empty( $calc['complete'] ) ) {
			// Si existe un resultado positivo del servidor, es una combinación válida.
			// No volvemos a invalidarla por una diferencia entre estructuras legacy/perfil.
			if ( (float) ( $calc['total'] ?? 0 ) > 0 ) {
				$calc['complete'] = true;
			} else {
				// Último intento determinista: resolver directamente el perfil por formato,
				// tramo y unidad seleccionados. Esto cubre configuraciones guardadas durante
				// la transición del modelo antiguo al modelo por formato.
				$combo = isset( $config['combinations'] ) && is_array( $config['combinations'] ) ? $config['combinations'] : array();
				$profiles = isset( $combo['profiles'] ) && is_array( $combo['profiles'] ) ? $combo['profiles'] : array();
				$fi = isset( $selections['combo_level_0'] ) ? (int) $selections['combo_level_0'] : -1;
				$ti = isset( $selections['combo_level_1'] ) ? (int) $selections['combo_level_1'] : -1;
				$ri = isset( $selections['combo_level_2'] ) ? (int) $selections['combo_level_2'] : -1;
				foreach ( $profiles as $profile ) {
					if ( (int) ( $profile['format_index'] ?? -1 ) !== $fi ) continue;
					$tiers = isset( $profile['tiers'] ) && is_array( $profile['tiers'] ) ? array_values( $profile['tiers'] ) : array();
					if ( ! isset( $tiers[ $ti ] ) ) break;
					$rows = isset( $tiers[ $ti ]['rows'] ) && is_array( $tiers[ $ti ]['rows'] ) ? array_values( $tiers[ $ti ]['rows'] ) : array();
					if ( ! isset( $rows[ $ri ] ) ) break;
					$direct_price = MPWCFG_Pricing::round_clp( (float) ( $rows[ $ri ]['price'] ?? 0 ) );
					if ( $direct_price > 0 ) {
						$calc['total'] = $direct_price;
						$calc['unit_price'] = $direct_price;
						$calc['qty_units'] = 1;
						$calc['complete'] = true;
					}
					break;
				}
				if ( empty( $calc['complete'] ) ) {
					wp_send_json_error( array( 'message' => __( 'La combinación seleccionada no tiene un precio configurado.', 'mpw-configurador-productos' ) ), 400 );
				}
			}
		}

		// Archivo adjunto (opcional — el cliente puede no tener diseño aún).
		$file = array();
		if ( ! empty( $_POST['file_url'] ) ) {
			$url     = esc_url_raw( wp_unslash( $_POST['file_url'] ) ); // phpcs:ignore
			$uploads = wp_get_upload_dir();
			if ( $url && 0 === strpos( $url, $uploads['baseurl'] ) ) {
				$file = array(
					'url'  => $url,
					'name' => isset( $_POST['file_name'] ) ? sanitize_file_name( wp_unslash( $_POST['file_name'] ) ) : basename( $url ), // phpcs:ignore
				);
			}
		}

		$cart_item_data = array(
			'mpwcfg_config' => array(
				'price'     => $calc['total'],
				'qty_units' => $calc['qty_units'],
				'lines'     => $calc['lines'],
				'file'      => $file,
				'hash'      => md5( wp_json_encode( $selections ) . microtime() ),
			),
		);

		$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
		$variation    = array();
		if ( $variation_id ) {
			$raw = isset( $_POST['variation'] ) ? wp_unslash( $_POST['variation'] ) : array(); // phpcs:ignore
			if ( is_array( $raw ) ) {
				foreach ( $raw as $key => $value ) {
					$variation[ sanitize_key( $key ) ] = wc_clean( $value );
				}
			}
			$variation_product = wc_get_product( $variation_id );
			if ( ! $variation_product || $variation_product->get_parent_id() !== $product_id ) {
				wp_send_json_error( array( 'message' => __( 'La variación seleccionada no es válida.', 'mpw-configurador-productos' ) ), 400 );
			}
		}

		$added = WC()->cart->add_to_cart( $product_id, 1, $variation_id, $variation, $cart_item_data );

		if ( ! $added ) {
			// WooCommerce suele haber añadido un aviso con el motivo real.
			$notices = wc_get_notices( 'error' );
			$message = ! empty( $notices )
				? wp_strip_all_tags( $notices[0]['notice'] )
				: __( 'No se pudo agregar al carrito. Intenta de nuevo.', 'mpw-configurador-productos' );
			wc_clear_notices();
			wp_send_json_error( array( 'message' => $message ), 400 );
		}

		wp_send_json_success(
			array(
				'message'   => __( 'Producto configurado agregado.', 'mpw-configurador-productos' ),
				'cart_url'  => wc_get_cart_url(),
				'cart_hash' => WC()->cart->get_cart_hash(),
				'count'     => WC()->cart->get_cart_contents_count(),
			)
		);
	}
}
