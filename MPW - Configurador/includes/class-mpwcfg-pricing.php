<?php
/**
 * Motor de precios y configuración por defecto.
 *
 * Modelo:
 * subtotal = precio_base + recargos
 * total    = subtotal x multiplicador de cantidad
 *
 *
 * @package MPW_Configurador_Productos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MPWCFG_Pricing {


	/* ============================================================
	   CONFIGURACIÓN POR DEFECTO
	   ============================================================ */

	public static function default_config() {

		return array(

			'enabled'                 => 'yes',
			'mode'                    => 'traditional',
			'combinations'            => self::default_combinations(),
			'base_price'              => 14990,
			'cta_label'               => 'Agregar al carrito',
			'accepted_label'          => 'PDF · AI · PNG',
			'template_label'          => 'Descargar plantilla con sangrado y márgenes',
			'template_url'            => '',
			'tax_note'                => 'IVA incluido',
			'total_label'             => 'Total estimado',
			'summary_base_label'      => 'Configuración base',
			'summary_surcharge_label' => 'Adicionales',
			'summary_discount_label'  => 'Descuento por volumen',
			'contact_url'             => '',
			'contact_label'           => 'WhatsApp',
			'after_add_action'        => 'cart',
			'after_add_label'         => 'Ir al carrito',
			'show_continue_shopping' => 'yes',
			'continue_shopping_label' => 'Seguir comprando',

			'trust' => array(
				'Prueba digital sin costo',
				'Despacho 3–5 días hábiles',
				'Reimpresión garantizada',
			),

			'steps' => array(

				array(
					'key'     => 'formato',
					'type'    => 'swatch',
					'label'   => 'Formato',
					'help'    => '',
					'display' => 'dropdown',

					'options' => array(

						array(
							'label'    => '9 × 5 cm',
							'sublabel' => 'Estándar',
							'price'    => 0,
							'chip'     => '',
							'default'  => 'no',
						),

						array(
							'label'    => '8,5 × 5,5 cm',
							'sublabel' => 'Europeo',
							'price'    => 0,
							'chip'     => '',
							'default'  => 'yes',
						),

						array(
							'label'    => '5,5 × 5,5 cm',
							'sublabel' => 'Cuadrada',
							'price'    => 1500,
							'chip'     => '',
							'default'  => 'no',
						),

					),
				),


				array(
					'key'     => 'sustrato',
					'type'    => 'swatch',
					'label'   => 'Sustrato',
					'help'    => '',
					'display' => 'dropdown',

					'options' => array(

						array(
							'label'    => 'Couché 300 g',
							'sublabel' => 'Satinado',
							'price'    => 0,
							'chip'     => '#f1ece3',
							'default'  => 'yes',
						),

						array(
							'label'    => 'Couché 350 g',
							'sublabel' => 'Más cuerpo',
							'price'    => 1200,
							'chip'     => '#f7f2e9',
							'default'  => 'no',
						),

						array(
							'label'    => 'Reciclado 300 g',
							'sublabel' => 'Eco kraft',
							'price'    => 2000,
							'chip'     => '#cbb189',
							'default'  => 'no',
						),

						array(
							'label'    => 'Algodón premium',
							'sublabel' => 'Textura mate',
							'price'    => 4500,
							'chip'     => '#ece5d9',
							'default'  => 'no',
						),

					),
				),


				array(
					'key'     => 'terminacion',
					'type'    => 'swatch',
					'label'   => 'Terminación',
					'help'    => '',
					'display' => 'dropdown',

					'options' => array(

						array(
							'label'    => 'Mate',
							'sublabel' => 'Sin brillo',
							'price'    => 0,
							'chip'     => '#cfc9c5',
							'default'  => 'yes',
						),

						array(
							'label'    => 'Brillante',
							'sublabel' => 'Laminado',
							'price'    => 0,
							'chip'     => '#e8edf3',
							'default'  => 'no',
						),

						array(
							'label'    => 'Soft-touch',
							'sublabel' => 'Aterciopelado',
							'price'    => 3500,
							'chip'     => '#c2b9b3',
							'default'  => 'no',
						),

						array(
							'label'    => 'Spot UV',
							'sublabel' => 'Realce puntual',
							'price'    => 5900,
							'chip'     => '#ff2e7e',
							'default'  => 'no',
						),

					),
				),


				array(
					'key'     => 'impresion',
					'type'    => 'swatch',
					'label'   => 'Impresión',
					'help'    => '',
					'display' => 'dropdown',

					'options' => array(

						array(
							'label'    => '1 cara',
							'sublabel' => 'Tiro',
							'price'    => 0,
							'chip'     => '',
							'default'  => 'no',
						),

						array(
							'label'    => '2 caras',
							'sublabel' => 'Tiro / retiro',
							'price'    => 6000,
							'chip'     => '',
							'default'  => 'yes',
						),

					),
				),


				array(
					'key'          => 'cantidad',
					'type'         => 'quantity',
					'label'        => 'Cantidad',
					'help'         => '',
					'display'      => 'slider',
					'allow_custom' => 'yes',
					'custom_label' => 'Más — cotización personalizada',

					'options' => array(

						array(
							'label'      => '100',
							'qty'        => 100,
							'multiplier' => 1,
							'badge'      => '',
							'default'    => 'no',
						),

						array(
							'label'      => '250',
							'qty'        => 250,
							'multiplier' => 2.1,
							'badge'      => '',
							'default'    => 'no',
						),

						array(
							'label'      => '500',
							'qty'        => 500,
							'multiplier' => 3.5,
							'badge'      => '',
							'default'    => 'yes',
						),

						array(
							'label'      => '1000',
							'qty'        => 1000,
							'multiplier' => 5.5,
							'badge'      => '',
							'default'    => 'no',
						),

					),
				),


				array(
					'key'          => 'arte',
					'type'         => 'upload',
					'label'        => 'Sube tu diseño',
					'toggle_label' => 'Sube tu diseño o arrástralo aquí',
					'help'         => 'Máx. 25 MB · CMYK 300 dpi · con sangrado de 3 mm',
				),

			),

		);
	}



	/**
	 * Datos de ejemplo para el modo de combinaciones. Se guardan por producto;
	 * no existe lógica asociada a un ID concreto.
	 */
	public static function default_combinations() {
		return array(
			'levels' => array(
				array(
					'key' => 'formato',
					'label' => 'Formato',
					'options' => array(
						array( 'label' => '7,5 × 7,5 cm', 'sku' => '' ),
						array( 'label' => '7,5 × 10 cm', 'sku' => '' ),
						array( 'label' => '10 × 15 cm', 'sku' => '' ),
						array( 'label' => '15 × 21 cm', 'sku' => '' ),
					),
				),
				array(
					'key' => 'hojas',
					'label' => 'Cantidad de hojas',
					'options' => array(),
				),
				array(
					'key' => 'cantidad',
					'label' => 'Unidades',
					'options' => array(),
				),
			),
			'profiles' => array(
				array(
					'format_index' => 0,
					'tiers' => array(
						array( 'label' => '50 hojas', 'rows' => array(
							array( 'quantity' => '8 un.', 'price' => 40001 ),
							array( 'quantity' => '16 un.', 'price' => 73001 ),
							array( 'quantity' => '32 un.', 'price' => 133001 ),
							array( 'quantity' => '64 un.', 'price' => 240001 ),
						) ),
						array( 'label' => '100 hojas', 'rows' => array(
							array( 'quantity' => '8 un.', 'price' => 47000 ),
							array( 'quantity' => '16 un.', 'price' => 85000 ),
							array( 'quantity' => '32 un.', 'price' => 155000 ),
							array( 'quantity' => '64 un.', 'price' => 280000 ),
						) ),
					),
				),
				array(
					'format_index' => 1,
					'tiers' => array(
						array( 'label' => '50 hojas', 'rows' => array(
							array( 'quantity' => '8 un.', 'price' => 45001 ),
							array( 'quantity' => '16 un.', 'price' => 82001 ),
							array( 'quantity' => '32 un.', 'price' => 150001 ),
							array( 'quantity' => '64 un.', 'price' => 270001 ),
						) ),
						array( 'label' => '100 hojas', 'rows' => array(
							array( 'quantity' => '8 un.', 'price' => 56000 ),
							array( 'quantity' => '16 un.', 'price' => 94000 ),
							array( 'quantity' => '32 un.', 'price' => 175000 ),
							array( 'quantity' => '64 un.', 'price' => 310000 ),
						) ),
					),
				),
				array(
					'format_index' => 2,
					'tiers' => array(
						array( 'label' => '50 hojas', 'rows' => array(
							array( 'quantity' => '9 un.', 'price' => 80001 ),
							array( 'quantity' => '18 un.', 'price' => 109001 ),
							array( 'quantity' => '36 un.', 'price' => 200001 ),
							array( 'quantity' => '54 un.', 'price' => 285001 ),
						) ),
						array( 'label' => '100 hojas', 'rows' => array(
							array( 'quantity' => '9 un.', 'price' => 75000 ),
							array( 'quantity' => '18 un.', 'price' => 125000 ),
							array( 'quantity' => '36 un.', 'price' => 215000 ),
							array( 'quantity' => '54 un.', 'price' => 300000 ),
						) ),
					),
				),
				array(
					'format_index' => 3,
					'tiers' => array(
						array( 'label' => '50 hojas', 'rows' => array(
							array( 'quantity' => '2 un.', 'price' => 22001 ),
							array( 'quantity' => '10 un.', 'price' => 94001 ),
							array( 'quantity' => '30 un.', 'price' => 250001 ),
							array( 'quantity' => '50 un.', 'price' => 400001 ),
						) ),
						array( 'label' => '100 hojas', 'rows' => array(
							array( 'quantity' => '2 un.', 'price' => 26000 ),
							array( 'quantity' => '10 un.', 'price' => 109000 ),
							array( 'quantity' => '30 un.', 'price' => 285000 ),
							array( 'quantity' => '50 un.', 'price' => 460000 ),
						) ),
					),
				),
			),
			'matrix' => array(),
		);
	}

	/* ============================================================
	   OBTENER CONFIGURACIÓN
	   ============================================================ */

	public static function get_config( $product_id ) {

		$saved = get_post_meta(
			$product_id,
			MPWCFG_META_KEY,
			true
		);

		if (
			empty( $saved ) ||
			! is_array( $saved )
		) {

			$config = self::default_config();

		} else {

			$config = wp_parse_args(
				$saved,
				self::default_config()
			);
		}

		$config['mode'] = isset( $config['mode'] ) && in_array( $config['mode'], array( 'traditional', 'combinations' ), true ) ? $config['mode'] : 'traditional';
		if ( empty( $config['combinations'] ) || ! is_array( $config['combinations'] ) ) {
			$config['combinations'] = self::default_combinations();
		}
		// Migra automáticamente configuraciones anteriores basadas en una matriz
		// cartesiana al modelo por formato, sin perder los precios existentes.
		if ( empty( $config['combinations']['profiles'] ) && ! empty( $config['combinations']['matrix'] ) ) {
			$config['combinations']['profiles'] = self::profiles_from_legacy_matrix( $config['combinations'] );
		}
		// Configuraciones guardadas antes del modelo por formato pueden no tener
		// perfiles. Creamos uno vacío por cada formato existente para que el admin
		// pueda definir sus propias opciones sin imponer cantidades ni precios.
		// En el modelo de combinaciones, el tercer nivel representa las unidades comprables.
		// Conserva nombres personalizados; solo migra el nombre histórico por defecto.
		if ( isset( $config['combinations']['levels'][2]['key'], $config['combinations']['levels'][2]['label'] )
			&& 'cantidad' === $config['combinations']['levels'][2]['key']
			&& 'Cantidad' === $config['combinations']['levels'][2]['label'] ) {
			$config['combinations']['levels'][2]['label'] = 'Unidades';
		}

		if ( empty( $config['combinations']['profiles'] ) && ! empty( $config['combinations']['levels'][0]['options'] ) ) {
			$config['combinations']['profiles'] = array();
			foreach ( $config['combinations']['levels'][0]['options'] as $fi => $format ) {
				$config['combinations']['profiles'][] = array(
					'format_index' => (int) $fi,
					'tiers' => array(),
				);
			}
		}

		return $config;
	}

	/**
	 * Convierte la matriz anterior Formato x Hojas x Cantidad en perfiles por formato.
	 */
	private static function profiles_from_legacy_matrix( $combo ) {
		$profiles = array();
		$levels = isset( $combo['levels'] ) && is_array( $combo['levels'] ) ? array_values( $combo['levels'] ) : array();
		$matrix = isset( $combo['matrix'] ) && is_array( $combo['matrix'] ) ? $combo['matrix'] : array();
		if ( count( $levels ) < 3 || empty( $levels[0]['options'] ) ) {
			return $profiles;
		}
		foreach ( $levels[0]['options'] as $fi => $format ) {
			$profile = array( 'format_index' => (int) $fi, 'tiers' => array() );
			if ( ! empty( $levels[1]['options'] ) ) {
				foreach ( $levels[1]['options'] as $hi => $sheet ) {
					$tier = array( 'label' => $sheet['label'] ?? '', 'rows' => array() );
					foreach ( $levels[2]['options'] as $qi => $qty ) {
						$price = null;
						foreach ( $matrix as $row ) {
							$values = isset( $row['values'] ) && is_array( $row['values'] ) ? array_map( 'intval', $row['values'] ) : array();
							if ( $values === array( (int) $fi, (int) $hi, (int) $qi ) ) {
								$price = self::round_clp( isset( $row['price'] ) ? (float) $row['price'] : 0 );
								break;
							}
						}
						if ( null !== $price ) {
							$tier['rows'][] = array( 'quantity' => $qty['label'] ?? '', 'price' => $price );
						}
					}
					if ( ! empty( $tier['rows'] ) ) $profile['tiers'][] = $tier;
				}
			}
			$profiles[] = $profile;
		}
		return $profiles;
	}



	/**
	 * Precio de una combinación. La matriz es la fuente de verdad del precio.
	 * @param array $config Configuración completa.
	 * @param array $selections Selecciones combo_level_N => índice.
	 * @return array
	 */
	public static function calculate_combinations( $config, $selections ) {
		$combo = isset( $config['combinations'] ) && is_array( $config['combinations'] ) ? $config['combinations'] : self::default_combinations();
		$levels = isset( $combo['levels'] ) && is_array( $combo['levels'] ) ? array_values( $combo['levels'] ) : array();
		$profiles = isset( $combo['profiles'] ) && is_array( $combo['profiles'] ) ? $combo['profiles'] : array();
		$wanted = array();
		$complete = true;
		$lines = array();

		if ( empty( $levels ) || ! isset( $selections['combo_level_0'] ) ) {
			return array( 'total' => 0, 'unit_price' => 0, 'qty_units' => 1, 'lines' => array(), 'complete' => false );
		}

		$format_idx = (int) $selections['combo_level_0'];
		if ( empty( $levels[0]['options'][ $format_idx ] ) ) {
			return array( 'total' => 0, 'unit_price' => 0, 'qty_units' => 1, 'lines' => array(), 'complete' => false );
		}
		$format_label = $levels[0]['options'][ $format_idx ]['label'];
		$lines[] = array( 'label' => $levels[0]['label'], 'value' => $format_label, 'price' => null );
		$wanted[] = $format_idx;

		// Nuevo modelo por formato: cada formato posee sus propios tramos y cantidades.
		if ( count( $levels ) >= 3 && ! empty( $profiles ) ) {
			$profile = null;
			foreach ( $profiles as $candidate ) {
				if ( (int) ( $candidate['format_index'] ?? -1 ) === $format_idx ) {
					$profile = $candidate;
					break;
				}
			}
			if ( ! $profile ) {
				// Si existe una matriz guardada de una versión anterior, úsala como
				// respaldo. Esto evita que una migración parcial bloquee el carrito.
				return self::calculate_legacy_matrix_fallback( $combo, $levels, $selections, $lines, $format_idx );
			}

			$tier_idx = isset( $selections['combo_level_1'] ) ? (int) $selections['combo_level_1'] : null;
			$tiers = isset( $profile['tiers'] ) && is_array( $profile['tiers'] ) ? array_values( $profile['tiers'] ) : array();
			if ( null === $tier_idx || empty( $tiers[ $tier_idx ] ) ) {
				return self::calculate_legacy_matrix_fallback( $combo, $levels, $selections, $lines, $format_idx );
			}
			$tier = $tiers[ $tier_idx ];
			$lines[] = array( 'label' => $levels[1]['label'] ?? 'Cantidad de hojas', 'value' => $tier['label'], 'price' => null );

			$row_idx = isset( $selections['combo_level_2'] ) ? (int) $selections['combo_level_2'] : null;
			$rows = isset( $tier['rows'] ) && is_array( $tier['rows'] ) ? array_values( $tier['rows'] ) : array();
			if ( null === $row_idx || empty( $rows[ $row_idx ] ) ) {
				return self::calculate_legacy_matrix_fallback( $combo, $levels, $selections, $lines, $format_idx );
			}
			$row = $rows[ $row_idx ];
			$lines[] = array( 'label' => $levels[2]['label'] ?? 'Unidades', 'value' => $row['quantity'], 'price' => null );
			$price = self::round_clp( (float) ( $row['price'] ?? 0 ) );
			return array(
				'total' => $price,
				'unit_price' => $price,
				'qty_units' => 1,
				'lines' => $lines,
				'complete' => true,
			);
		}

		// Compatibilidad con configuraciones antiguas basadas en matriz.
		foreach ( $levels as $i => $level ) {
			if ( 0 === $i ) continue;
			$key = 'combo_level_' . $i;
			if ( ! isset( $selections[ $key ] ) ) {
				$complete = false;
				break;
			}
			$idx = (int) $selections[ $key ];
			if ( empty( $level['options'][ $idx ] ) ) {
				$complete = false;
				break;
			}
			$wanted[] = $idx;
			$lines[] = array( 'label' => $level['label'], 'value' => $level['options'][ $idx ]['label'], 'price' => null );
		}
		$price = 0;
		$matched = false;
		if ( $complete ) {
			foreach ( (array) $combo['matrix'] as $row ) {
				$values = isset( $row['values'] ) && is_array( $row['values'] ) ? array_map( 'intval', $row['values'] ) : array();
				if ( $values === $wanted ) {
					$price = self::round_clp( isset( $row['price'] ) ? (float) $row['price'] : 0 );
					$matched = true;
					break;
				}
			}
		}
		return array(
			'total' => $price,
			'unit_price' => $price,
			'qty_units' => 1,
			'lines' => $lines,
			'complete' => $complete && $matched,
		);
	}


	/**
	 * Respaldo para matrices guardadas antes del modelo por perfiles.
	 * Se usa solamente si la configuración por formato no resuelve la selección.
	 */
	private static function calculate_legacy_matrix_fallback( $combo, $levels, $selections, $lines, $format_idx ) {
		if ( count( $levels ) < 3 ) {
			return array( 'total' => 0, 'unit_price' => 0, 'qty_units' => 1, 'lines' => $lines, 'complete' => false );
		}

		if ( isset( $selections['combo_level_1'] ) ) {
			$idx = (int) $selections['combo_level_1'];
			if ( isset( $levels[1]['options'][ $idx ] ) ) {
				$lines[] = array( 'label' => $levels[1]['label'], 'value' => $levels[1]['options'][ $idx ]['label'], 'price' => null );
			}
		}
		if ( isset( $selections['combo_level_2'] ) ) {
			$idx = (int) $selections['combo_level_2'];
			if ( isset( $levels[2]['options'][ $idx ] ) ) {
				$lines[] = array( 'label' => $levels[2]['label'], 'value' => $levels[2]['options'][ $idx ]['label'], 'price' => null );
			}
		}

		if ( ! isset( $selections['combo_level_1'], $selections['combo_level_2'] ) || empty( $combo['matrix'] ) ) {
			return array( 'total' => 0, 'unit_price' => 0, 'qty_units' => 1, 'lines' => $lines, 'complete' => false );
		}

		$wanted = array( $format_idx, (int) $selections['combo_level_1'], (int) $selections['combo_level_2'] );
		foreach ( (array) $combo['matrix'] as $row ) {
			$values = isset( $row['values'] ) && is_array( $row['values'] ) ? array_map( 'intval', $row['values'] ) : array();
			if ( $values === $wanted ) {
				$price = self::round_clp( isset( $row['price'] ) ? (float) $row['price'] : 0 );
				return array( 'total' => $price, 'unit_price' => $price, 'qty_units' => 1, 'lines' => $lines, 'complete' => $price > 0 );
			}
		}
		return array( 'total' => 0, 'unit_price' => 0, 'qty_units' => 1, 'lines' => $lines, 'complete' => false );
	}


	/* ============================================================
	   OPCIÓN POR DEFECTO
	   ============================================================ */

	public static function default_index( $step ) {

		if ( empty( $step['options'] ) ) {
			return 0;
		}

		foreach ( $step['options'] as $i => $opt ) {

			if (
				isset( $opt['default'] ) &&
				'yes' === $opt['default']
			) {
				return (int) $i;
			}
		}

		return 0;
	}



	/* ============================================================
	   CÁLCULO DE PRECIO
	   ============================================================ */

	public static function calculate(
		$config,
		$selections
	) {

		$base =
			isset(
				$config['base_price']
			)

				? (float)
					$config['base_price']

				: 0;


		$surcharge =
			0.0;


		$multiplier =
			1.0;


		$qty_units =
			1;


		$discount_badge =
			'';


		$lines =
			array();



		foreach (
			(array) $config['steps']
			as $step
		) {

			$key =
				isset( $step['key'] )
					? $step['key']
					: '';


			$type =
				isset(
					$step['type']
				)

					? $step['type']

					: 'swatch';



			/* =====================================================
			   OPCIONES NORMALES
			   ===================================================== */

			if (
				'swatch' ===
				$type
			) {

				$idx =
					isset(
						$selections[ $key ]
					)

						? (int)
							$selections[ $key ]

						: self::default_index(
							$step
						);


				if (
					! isset(
						$step['options'][ $idx ]
					)
				) {

					$idx =
						self::default_index(
							$step
						);
				}


				$opt =
					$step['options'][ $idx ];


				$price =
					isset(
						$opt['price']
					)

						? (float)
							$opt['price']

						: 0;


				$surcharge +=
					$price;


				$lines[] =
					array(

						'label' =>
							isset(
								$step['label']
							)
								? $step['label']
								: '',

						'value' =>
							isset(
								$opt['label']
							)
								? $opt['label']
								: '',

						'price' =>
							$price,

					);



			/* =====================================================
			   CANTIDAD
			   ===================================================== */

			} elseif (
				'quantity' ===
				$type
			) {



				$is_custom =
					isset(
						$selections[ $key ]
					) &&
					'custom' ===
						(string)
						$selections[ $key ];



				/* =================================================
				   CANTIDAD PERSONALIZADA
				   ================================================= */

				if (
					$is_custom &&
					! empty(
						$step['allow_custom']
					) &&
					'yes' ===
						$step['allow_custom']
				) {


					$last_opt =
						end(
							$step['options']
						);


					$last_mult =
						isset(
							$last_opt['multiplier']
						)

							? (float)
								$last_opt['multiplier']

							: 1;


					$last_qty =
						isset(
							$last_opt['qty']
						)

							? (int)
								$last_opt['qty']

							: 1;


					$custom_qty =
						isset(
							$selections[
								$key .
								'_custom'
							]
						)

							? max(
								1,
								(int)
								$selections[
									$key .
									'_custom'
								]
							)

							: $last_qty;


					$unit_at_last =
						$last_qty > 0

							? self::round_clp(

								(
									(
										$base +
										$surcharge
									) *
									$last_mult
								) /
								$last_qty

							)

							: 0;


					$multiplier =
						1;


					$qty_units =
						$custom_qty;


					$discount_badge =
						'';


					$lines[] =
						array(

							'label' =>
								isset(
									$step['label']
								)
									? $step['label']
									: 'Cantidad',

							'value' =>
								number_format(
									$custom_qty,
									0,
									',',
									'.'
								) .
								' uds (personalizado)',

							'price' =>
								null,

							'custom_unit_price' =>
								$unit_at_last,

						);


					if (
						(
							$base +
							$surcharge
						) > 0
					) {

						$multiplier =
							(
								$unit_at_last *
								$custom_qty
							) /
							(
								$base +
								$surcharge
							);
					}



				/* =================================================
				   CANTIDAD NORMAL
				   ================================================= */

				} else {


					$idx =
						isset(
							$selections[ $key ]
						)

							? (int)
								$selections[ $key ]

							: self::default_index(
								$step
							);


					if (
						! isset(
							$step['options'][ $idx ]
						)
					) {

						$idx =
							self::default_index(
								$step
							);
					}


					$opt =
						$step['options'][ $idx ];


					$multiplier =
						isset(
							$opt['multiplier']
						)

							? (float)
								$opt['multiplier']

							: 1;


					$qty_units =
						isset(
							$opt['qty']
						)

							? (int)
								$opt['qty']

							: 1;


					$discount_badge =
						isset(
							$opt['badge']
						)

							? (string)
								$opt['badge']

							: '';


					$lines[] =
						array(

							'label' =>
								isset(
									$step['label']
								)
									? $step['label']
									: 'Cantidad',

							'value' =>
								isset(
									$opt['label']
								)
									? $opt['label']
									: $qty_units,

							'price' =>
								null,

						);
				}
			}
		}



		/* ============================================================
		   TOTAL
		   ============================================================ */

		$subtotal =
			$base +
			$surcharge;


		$total =
			self::round_clp(
				$subtotal *
				$multiplier
			);


		$unit_price =
			$qty_units > 0

				? self::round_clp(
					$total /
					$qty_units
				)

				: $total;



		return array(

			'base' =>
				self::round_clp(
					$base
				),

			'surcharge' =>
				self::round_clp(
					$surcharge
				),

			'total' =>
				$total,

			'qty_units' =>
				$qty_units,

			'unit_price' =>
				$unit_price,

			'discount_badge' =>
				$discount_badge,

			'lines' =>
				$lines,

		);
	}



	/* ============================================================
	   REDONDEO CLP
	   ============================================================ */

	public static function round_clp( $value ) {

		return (int) (
			round(
				$value /
				10
			) *
			10
		);
	}



	/* ============================================================
	   FORMATO CLP
	   ============================================================ */

	public static function format_clp( $value ) {

		return '$ ' .
			number_format(
				(float) $value,
				0,
				',',
				'.'
			);
	}

}