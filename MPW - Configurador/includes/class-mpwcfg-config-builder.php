<?php
/**
 * Constructor de configuración compartido.
 * Renderiza los campos del builder (globales + pasos + opciones) y sanitiza el
 * $_POST['mpwcfg']. Lo usan TANTO la pestaña del producto como el panel por categoría,
 * para que la lógica sea idéntica en ambos sitios.
 *
 * @package MPW_Configurador_Productos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MPWCFG_Config_Builder {

	/**
	 * Campos globales del configurador (precio base, textos del resumen, confianza…).
	 *
	 * @param array  $config  Configuración actual.
	 * @param string $context Reservado (compatibilidad de firma).
	 */
	public static function render_globals( $config, $context = 'product' ) {
		$g = wp_parse_args(
			$config,
			array(
				'enabled'                 => 'yes',
				'base_price'              => 0,
				'cta_label'               => __( 'Agregar al carrito', 'mpw-configurador-productos' ),
				'accepted_label'          => 'PDF · AI · PNG',
				'template_label'          => __( 'Descargar plantilla con sangrado y márgenes', 'mpw-configurador-productos' ),
				'template_url'            => '',
				'tax_note'                => __( 'IVA incluido', 'mpw-configurador-productos' ),
				'total_label'             => __( 'Total estimado', 'mpw-configurador-productos' ),
				'summary_base_label'      => __( 'Configuración base', 'mpw-configurador-productos' ),
				'summary_surcharge_label' => __( 'Terminación & sustrato', 'mpw-configurador-productos' ),
				'summary_discount_label'  => __( 'Descuento por volumen', 'mpw-configurador-productos' ),
				'contact_url'             => '',
				'contact_label'           => __( 'WhatsApp', 'mpw-configurador-productos' ),
				'after_add_action'        => 'cart',
				'after_add_label'         => __( 'Ir al carrito', 'mpw-configurador-productos' ),
				'show_continue_shopping' => 'yes',
				'continue_shopping_label' => __( 'Seguir comprando', 'mpw-configurador-productos' ),
				'trust'                   => array(),
			)
		);
		$trust = self::trust_defaults( $g['trust'] );
		?>
		<div class="mpwcfg-globals">
			<p class="mpwcfg-field">
				<label class="mpwcfg-check">
					<input type="checkbox" name="mpwcfg[enabled]" value="yes" <?php checked( 'yes', $g['enabled'] ); ?> />
					<span><?php esc_html_e( 'Mostrar el configurador en este producto', 'mpw-configurador-productos' ); ?></span>
				</label>
			</p>

			<div class="mpwcfg-grid2">
				<label class="mpwcfg-field">
					<span><?php esc_html_e( 'Precio base (CLP)', 'mpw-configurador-productos' ); ?></span>
					<input type="number" step="10" min="0" name="mpwcfg[base_price]" value="<?php echo esc_attr( $g['base_price'] ); ?>" />
				</label>
				<label class="mpwcfg-field">
					<span><?php esc_html_e( 'Texto del botón', 'mpw-configurador-productos' ); ?></span>
					<input type="text" name="mpwcfg[cta_label]" value="<?php echo esc_attr( $g['cta_label'] ); ?>" />
				</label>
			</div>



			<div class="mpwcfg-grid2">
				<label class="mpwcfg-field">
					<span><?php esc_html_e( 'Formatos aceptados (rótulo del paso de diseño)', 'mpw-configurador-productos' ); ?></span>
					<input type="text" name="mpwcfg[accepted_label]" value="<?php echo esc_attr( $g['accepted_label'] ); ?>" placeholder="PDF · AI · PNG" />
				</label>
				<label class="mpwcfg-field">
					<span><?php esc_html_e( 'Botón de WhatsApp (URL, opcional)', 'mpw-configurador-productos' ); ?></span>
					<input type="url" name="mpwcfg[contact_url]" value="<?php echo esc_attr( $g['contact_url'] ); ?>" placeholder="https://wa.me/569…" />
				</label>
			</div>

			<label class="mpwcfg-field">
				<span><?php esc_html_e( 'Texto del botón de WhatsApp', 'mpw-configurador-productos' ); ?></span>
				<input type="text" name="mpwcfg[contact_label]" value="<?php echo esc_attr( $g['contact_label'] ); ?>" placeholder="WhatsApp" />
			</label>


			<fieldset class="mpwcfg-subgroup">
				<legend><?php esc_html_e( 'Resumen de precio', 'mpw-configurador-productos' ); ?></legend>
				<div class="mpwcfg-grid2">
					<label class="mpwcfg-field">
						<span><?php esc_html_e( 'Rótulo «configuración base»', 'mpw-configurador-productos' ); ?></span>
						<input type="text" name="mpwcfg[summary_base_label]" value="<?php echo esc_attr( $g['summary_base_label'] ); ?>" />
					</label>
					<label class="mpwcfg-field">
						<span><?php esc_html_e( 'Rótulo «recargos»', 'mpw-configurador-productos' ); ?></span>
						<input type="text" name="mpwcfg[summary_surcharge_label]" value="<?php echo esc_attr( $g['summary_surcharge_label'] ); ?>" />
					</label>
				</div>
				<div class="mpwcfg-grid2">
					<label class="mpwcfg-field">
						<span><?php esc_html_e( 'Rótulo «descuento por volumen»', 'mpw-configurador-productos' ); ?></span>
						<input type="text" name="mpwcfg[summary_discount_label]" value="<?php echo esc_attr( $g['summary_discount_label'] ); ?>" />
					</label>
					<label class="mpwcfg-field">
						<span><?php esc_html_e( 'Rótulo «total»', 'mpw-configurador-productos' ); ?></span>
						<input type="text" name="mpwcfg[total_label]" value="<?php echo esc_attr( $g['total_label'] ); ?>" />
					</label>
				</div>
				<label class="mpwcfg-field">
					<span><?php esc_html_e( 'Nota bajo el total (ej. IVA incluido)', 'mpw-configurador-productos' ); ?></span>
					<input type="text" name="mpwcfg[tax_note]" value="<?php echo esc_attr( $g['tax_note'] ); ?>" />
				</label>
			</fieldset>

			<fieldset class="mpwcfg-subgroup">
				<legend><?php esc_html_e( 'Sellos de confianza (bajo el botón)', 'mpw-configurador-productos' ); ?></legend>
				<?php foreach ( $trust as $t ) : ?>
					<input type="text" class="mpwcfg-trust-input" name="mpwcfg[trust][]" value="<?php echo esc_attr( $t ); ?>" placeholder="<?php esc_attr_e( 'Ej. Prueba digital sin costo', 'mpw-configurador-productos' ); ?>" />
				<?php endforeach; ?>
			</fieldset>
		</div>
		<?php
	}

	/**
	 * Rótulos de confianza por defecto, completando hasta 3 ranuras.
	 */
	private static function trust_defaults( $trust ) {
		$defaults = array(
			__( 'Prueba digital sin costo', 'mpw-configurador-productos' ),
			__( 'Despacho 3–5 días hábiles', 'mpw-configurador-productos' ),
			__( 'Reimpresión garantizada', 'mpw-configurador-productos' ),
		);
		$trust = is_array( $trust ) ? array_values( array_filter( array_map( 'strval', $trust ), 'strlen' ) ) : array();
		if ( empty( $trust ) ) {
			$trust = $defaults;
		}
		// Garantiza hasta 4 ranuras editables; las ranuras vacías son opcionales.
		while ( count( $trust ) < 4 ) {
			$trust[] = '';
		}
		return array_slice( $trust, 0, 4 );
	}


	/**
	 * Constructor del modo de combinaciones. El producto puede usar este modo
	 * en lugar del configurador tradicional.
	 */
	public static function render_combinations( $config ) {
		$combo = isset( $config['combinations'] ) && is_array( $config['combinations'] ) ? $config['combinations'] : MPWCFG_Pricing::default_combinations();
		$levels = isset( $combo['levels'] ) && is_array( $combo['levels'] ) ? array_values( $combo['levels'] ) : array();
		$profiles = isset( $combo['profiles'] ) && is_array( $combo['profiles'] ) ? array_values( $combo['profiles'] ) : array();
		?>
		<div class="mpwcfg-combinations" id="mpwcfg-combinations">
			<div class="mpwcfg-combinations__intro">
				<div><strong><?php esc_html_e( 'Modo por combinaciones', 'mpw-configurador-productos' ); ?></strong><span><?php esc_html_e( 'Cada opción puede tener sus propias alternativas disponibles y precios.', 'mpw-configurador-productos' ); ?></span></div>
			</div>

			<div class="mpwcfg-combo-level-names">
				<div class="mpwcfg-combo-section-title"><?php esc_html_e( 'Estructura de selección', 'mpw-configurador-productos' ); ?></div>
				<div class="mpwcfg-combo-level-names__grid">
					<label><span>01</span><input type="text" name="mpwcfg[combinations][levels][0][label]" value="<?php echo esc_attr( $levels[0]['label'] ?? 'Formato' ); ?>" /></label>
					<label><span>02</span><input type="text" name="mpwcfg[combinations][levels][1][label]" value="<?php echo esc_attr( $levels[1]['label'] ?? 'Cantidad de hojas' ); ?>" /></label>
					<label><span>03</span><input type="text" name="mpwcfg[combinations][levels][2][label]" value="<?php echo esc_attr( $levels[2]['label'] ?? 'Unidades' ); ?>" /></label>
				</div>
			</div>

			<div class="mpwcfg-combo-section-title"><?php echo esc_html( '1. ' . ( $levels[0]['label'] ?? 'Formato' ) ); ?></div>
			<p class="mpwcfg-combo-help"><?php echo esc_html( 'Define las opciones disponibles para el producto. Cada ' . strtolower( $levels[0]['label'] ?? 'formato' ) . ' tendrá sus propias combinaciones.' ); ?></p>
			<div class="mpwcfg-combo-levels" id="mpwcfg-combo-levels">
				<?php if ( ! empty( $levels[0] ) ) : ?>
					<?php self::render_combo_level( 0, $levels[0] ); ?>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $levels[0]['options'] ) ) : ?>
				<div class="mpwcfg-combo-section-title"><?php echo esc_html( '2. Combinaciones por ' . strtolower( $levels[0]['label'] ?? 'formato' ) ); ?></div>
				<p class="mpwcfg-combo-help"><?php echo esc_html( 'Define las opciones disponibles y las unidades que realmente se pueden comprar para cada ' . strtolower( $levels[0]['label'] ?? 'formato' ) . '.' ); ?></p>
				<div class="mpwcfg-combo-profiles" id="mpwcfg-combo-profiles">
					<?php foreach ( $levels[0]['options'] as $format_index => $format ) :
						$profile = array();
						foreach ( $profiles as $candidate ) {
							if ( (int) ( $candidate['format_index'] ?? -1 ) === (int) $format_index ) { $profile = $candidate; break; }
						}
						self::render_combo_profile( $format_index, $format, $profile, $levels );
					endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	public static function render_combo_profile( $format_index, $format, $profile, $levels = array() ) {
		$tiers = isset( $profile['tiers'] ) && is_array( $profile['tiers'] ) ? array_values( $profile['tiers'] ) : array();
		$base = 'mpwcfg[combinations][profiles][' . (int) $format_index . ']';
		?>
		<div class="mpwcfg-combo-profile" data-format-index="<?php echo esc_attr( $format_index ); ?>">
			<div class="mpwcfg-combo-profile__head">
				<span class="mpwcfg-combo-letter"><?php echo esc_html( chr( 65 + (int) $format_index ) ); ?></span>
				<strong><?php echo esc_html( $format['label'] ?? '' ); ?></strong>
				<span class="mpwcfg-combo-profile__chevron">⌃</span>
			</div>
			<input type="hidden" name="<?php echo esc_attr( $base ); ?>[format_index]" value="<?php echo esc_attr( $format_index ); ?>" />
			<div class="mpwcfg-combo-profile__body">
				<div class="mpwcfg-combo-profile__label"><?php echo esc_html( sprintf( 'Opciones de %s para este %s', $levels[1]['label'] ?? 'Cantidad de hojas', strtolower( $levels[0]['label'] ?? 'formato' ) ) ); ?></div>
				<div class="mpwcfg-combo-tier-chips">
					<?php foreach ( $tiers as $ti => $tier ) : ?>
						<span class="mpwcfg-combo-tier-chip">
							<input type="text" name="<?php echo esc_attr( $base . '[tiers][' . $ti . '][label]' ); ?>" value="<?php echo esc_attr( $tier['label'] ?? '' ); ?>" />
							<button type="button" class="mpwcfg-combo-remove-tier">×</button>
						</span>
					<?php endforeach; ?>
					<button type="button" class="button button-secondary mpwcfg-combo-add-tier">+ <?php esc_html_e( 'agregar tramo', 'mpw-configurador-productos' ); ?></button>
				</div>
				<div class="mpwcfg-combo-profile__label"><?php esc_html_e( 'Cantidades y precio', 'mpw-configurador-productos' ); ?></div>
				<div class="mpwcfg-combo-profile__tables">
					<?php foreach ( $tiers as $ti => $tier ) :
						$rows = isset( $tier['rows'] ) && is_array( $tier['rows'] ) ? array_values( $tier['rows'] ) : array(); ?>
						<div class="mpwcfg-combo-tier-table" data-tier-index="<?php echo esc_attr( $ti ); ?>">
							<div class="mpwcfg-combo-tier-table__title"><?php echo esc_html( $tier['label'] ?? '' ); ?></div>
							<div class="mpwcfg-combo-profile-grid">
								<div class="mpwcfg-combo-grid-head"><span><?php echo esc_html( $levels[1]['label'] ?? 'Cantidad de hojas' ); ?></span><span><?php echo esc_html( $levels[2]['label'] ?? 'Unidades' ); ?></span><span><?php esc_html_e( 'Precio (CLP)', 'mpw-configurador-productos' ); ?></span><span></span></div>
								<?php foreach ( $rows as $ri => $row ) : ?>
									<div class="mpwcfg-combo-grid-row">
										<input type="text" value="<?php echo esc_attr( $tier['label'] ?? '' ); ?>" readonly aria-label="<?php echo esc_attr( $levels[1]['label'] ?? 'Cantidad de hojas' ); ?>" />
										<input type="text" name="<?php echo esc_attr( $base . '[tiers][' . $ti . '][rows][' . $ri . '][quantity]' ); ?>" value="<?php echo esc_attr( $row['quantity'] ?? '' ); ?>" placeholder="8 un." />
										<input type="number" min="0" step="1" name="<?php echo esc_attr( $base . '[tiers][' . $ti . '][rows][' . $ri . '][price]' ); ?>" value="<?php echo esc_attr( $row['price'] ?? 0 ); ?>" />
										<button type="button" class="mpwcfg-combo-remove-row">×</button>
									</div>
								<?php endforeach; ?>
							</div>
							<button type="button" class="button button-secondary mpwcfg-combo-add-row">+ <?php esc_html_e( 'agregar cantidad', 'mpw-configurador-productos' ); ?></button>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}

	public static function render_combo_level( $index, $level ) {
		$label = isset( $level['label'] ) ? $level['label'] : '';
		$options = isset( $level['options'] ) && is_array( $level['options'] ) ? array_values( $level['options'] ) : array();
		?>
		<div class="mpwcfg-combo-level" data-level-index="<?php echo esc_attr( $index ); ?>">
			<div class="mpwcfg-combo-level__head">
				<span class="mpwcfg-combo-drag dashicons dashicons-menu"></span>
				<span class="mpwcfg-combo-number"><?php echo esc_html( str_pad( $index + 1, 2, '0', STR_PAD_LEFT ) ); ?></span>
				<input type="text" class="mpwcfg-combo-level__label" name="mpwcfg[combinations][levels][<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $label ); ?>" placeholder="<?php esc_attr_e( 'Nombre del nivel', 'mpw-configurador-productos' ); ?>" />
				<button type="button" class="button mpwcfg-combo-remove-level">×</button>
			</div>
			<div class="mpwcfg-combo-options" data-level-index="<?php echo esc_attr( $index ); ?>">
				<?php foreach ( $options as $oi => $opt ) : ?>
					<div class="mpwcfg-combo-option">
						<span class="dashicons dashicons-menu"></span>
						<input type="text" name="mpwcfg[combinations][levels][<?php echo esc_attr( $index ); ?>][options][<?php echo esc_attr( $oi ); ?>][label]" value="<?php echo esc_attr( $opt['label'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Opción', 'mpw-configurador-productos' ); ?>" />
						<input type="text" name="mpwcfg[combinations][levels][<?php echo esc_attr( $index ); ?>][options][<?php echo esc_attr( $oi ); ?>][sku]" value="<?php echo esc_attr( $opt['sku'] ?? '' ); ?>" placeholder="SKU (opcional)" />
						<button type="button" class="button-link-delete mpwcfg-combo-remove-option">×</button>
					</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="button button-secondary mpwcfg-combo-add-option">+ <?php esc_html_e( 'Agregar opción', 'mpw-configurador-productos' ); ?></button>
		</div>
		<?php
	}

	/**
	 * Contenedor de pasos + botones para añadir.
	 */
	public static function render_steps( $config ) {
		$steps = isset( $config['steps'] ) && is_array( $config['steps'] ) ? array_values( $config['steps'] ) : array();
		$priority_map = array(
			'arte'       => 0,
			'formato'    => 10,
			'impresion'  => 20,
			'sustrato'   => 30,
			'terminacion'=> 40,
			'cantidad'   => 90,
		);
		$decorated = array();
		foreach ( $steps as $position => $step ) {
			$key = isset( $step['key'] ) ? sanitize_key( $step['key'] ) : '';
			$type = isset( $step['type'] ) ? $step['type'] : 'swatch';
			$priority = isset( $priority_map[ $key ] ) ? $priority_map[ $key ] : ( 'upload' === $type ? 0 : ( 'quantity' === $type ? 90 : 50 ) );
			$decorated[] = array( 'step' => $step, 'priority' => $priority, 'position' => $position );
		}
	usort( $decorated, function( $a, $b ) {
			if ( $a['priority'] === $b['priority'] ) {
				return $a['position'] <=> $b['position'];
			}
			return $a['priority'] <=> $b['priority'];
		} );
		$steps = array();
		foreach ( $decorated as $item ) {
			$steps[] = $item['step'];
		}
		?>
		<div class="mpwcfg-steps-wrap">
			<div class="mpwcfg-steps" id="mpwcfg-steps">
				<?php foreach ( $steps as $i => $step ) : ?>
					<?php self::render_step( $i, $step ); ?>
				<?php endforeach; ?>
			</div>

			<p class="mpwcfg-steps__actions">
				<button type="button" class="button button-secondary mpwcfg-add-step" data-type="swatch"><?php esc_html_e( '+ Paso de opciones', 'mpw-configurador-productos' ); ?></button>
				<button type="button" class="button button-secondary mpwcfg-add-step" data-type="quantity"><?php esc_html_e( '+ Paso de cantidad', 'mpw-configurador-productos' ); ?></button>
				<button type="button" class="button button-secondary mpwcfg-add-step" data-type="upload"><?php esc_html_e( '+ Subida de diseño', 'mpw-configurador-productos' ); ?></button>
			</p>

			<?php self::render_templates(); ?>
		</div>
		<?php
	}

	/**
	 * Un paso completo.
	 */
	public static function render_step( $index, $step ) {
		$type    = isset( $step['type'] ) ? $step['type'] : 'swatch';
		$label   = isset( $step['label'] ) ? $step['label'] : '';
		$key     = isset( $step['key'] ) ? $step['key'] : '';
		$help    = isset( $step['help'] ) ? $step['help'] : '';
		$toggle  = isset( $step['toggle_label'] ) ? $step['toggle_label'] : '';
		$display = isset( $step['display'] ) ? $step['display'] : ( 'quantity' === $type ? 'buttons' : 'dropdown' );
		?>
		<div class="mpwcfg-step" data-type="<?php echo esc_attr( $type ); ?>">
			<div class="mpwcfg-step__bar">
				<span class="mpwcfg-step__drag dashicons dashicons-menu" title="<?php esc_attr_e( 'Arrastrar para reordenar', 'mpw-configurador-productos' ); ?>"></span>
				<span class="mpwcfg-step__badge mpwcfg-step__badge--<?php echo esc_attr( $type ); ?>">
					<?php
					echo esc_html(
						'swatch' === $type ? __( 'Opciones', 'mpw-configurador-productos' ) : ( 'quantity' === $type ? __( 'Cantidad', 'mpw-configurador-productos' ) : __( 'Diseño', 'mpw-configurador-productos' ) )
					);
					?>
				</span>
				<input type="text" class="mpwcfg-step__label" name="mpwcfg[steps][<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $label ); ?>" placeholder="<?php esc_attr_e( 'Rótulo del paso (ej. Formato)', 'mpw-configurador-productos' ); ?>" />
				<input type="hidden" class="mpwcfg-step__type" name="mpwcfg[steps][<?php echo esc_attr( $index ); ?>][type]" value="<?php echo esc_attr( $type ); ?>" />
				<input type="hidden" class="mpwcfg-step__key" name="mpwcfg[steps][<?php echo esc_attr( $index ); ?>][key]" value="<?php echo esc_attr( $key ); ?>" />
				<button type="button" class="mpwcfg-step__duplicate dashicons dashicons-admin-page" title="<?php esc_attr_e( 'Duplicar paso', 'mpw-configurador-productos' ); ?>"></button>
				<button type="button" class="mpwcfg-step__remove dashicons dashicons-trash" title="<?php esc_attr_e( 'Eliminar paso', 'mpw-configurador-productos' ); ?>"></button>
			</div>

			<div class="mpwcfg-step__body">
				<?php if ( 'upload' === $type ) : ?>
					<label class="mpwcfg-field">
						<span><?php esc_html_e( 'Texto del interruptor', 'mpw-configurador-productos' ); ?></span>
						<input type="text" name="mpwcfg[steps][<?php echo esc_attr( $index ); ?>][toggle_label]" value="<?php echo esc_attr( $toggle ); ?>" placeholder="<?php esc_attr_e( 'Ya tengo mi diseño listo', 'mpw-configurador-productos' ); ?>" />
					</label>
					<label class="mpwcfg-field">
						<span><?php esc_html_e( 'Ayuda / requisitos', 'mpw-configurador-productos' ); ?></span>
						<textarea name="mpwcfg[steps][<?php echo esc_attr( $index ); ?>][help]" rows="2"><?php echo esc_textarea( $help ); ?></textarea>
					</label>
				<?php else : ?>
					<label class="mpwcfg-field mpwcfg-field--display">
						<span><?php esc_html_e( 'Mostrar como', 'mpw-configurador-productos' ); ?></span>
						<select name="mpwcfg[steps][<?php echo esc_attr( $index ); ?>][display]" class="mpwcfg-step__display">
							<option value="dropdown" <?php selected( 'dropdown', $display ); ?>><?php esc_html_e( 'Desplegable', 'mpw-configurador-productos' ); ?></option>
							<option value="buttons"  <?php selected( 'buttons',  $display ); ?>><?php esc_html_e( 'Botones',     'mpw-configurador-productos' ); ?></option>
							<option value="slider"   <?php selected( 'slider',   $display ); ?>><?php esc_html_e( 'Slider',      'mpw-configurador-productos' ); ?></option>
						</select>
					</label>
					<?php if ( 'quantity' === $type ) : ?>
					<div class="mpwcfg-subgroup mpwcfg-custom-qty-fields">
						<label class="mpwcfg-field">
							<span><?php esc_html_e( 'Texto del campo personalizado', 'mpw-configurador-productos' ); ?></span>
							<input type="text"
								name="mpwcfg[steps][<?php echo esc_attr( $index ); ?>][custom_label]"
								value="<?php echo esc_attr( isset( $step['custom_label'] ) ? $step['custom_label'] : '' ); ?>"
								placeholder="<?php esc_attr_e( 'Más de 1.000 uds — cotización personalizada', 'mpw-configurador-productos' ); ?>" />
						</label>
					</div>
					<?php endif; ?>
					<div class="mpwcfg-options">
						<?php
						if ( ! empty( $step['options'] ) ) {
							foreach ( $step['options'] as $j => $opt ) {
								self::render_option( $index, $j, $type, $opt );
							}
						}
						?>
					</div>
					<button type="button" class="button button-small mpwcfg-add-option"><?php esc_html_e( '+ Opción', 'mpw-configurador-productos' ); ?></button>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Una fila de opción (swatch o cantidad).
	 */
	public static function render_option( $step_index, $opt_index, $type, $opt ) {
		$label    = isset( $opt['label'] ) ? $opt['label'] : '';
		$sublabel = isset( $opt['sublabel'] ) ? $opt['sublabel'] : '';
		$price    = isset( $opt['price'] ) ? $opt['price'] : 0;
		$qty      = isset( $opt['qty'] ) ? $opt['qty'] : '';
		$mult     = isset( $opt['multiplier'] ) ? $opt['multiplier'] : 1;
		$badge    = isset( $opt['badge'] ) ? $opt['badge'] : '';
		$chip     = isset( $opt['chip'] ) ? $opt['chip'] : '';
		$image_id = isset( $opt['image_id'] ) ? (int) $opt['image_id'] : 0;
		$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
		$default  = isset( $opt['default'] ) && 'yes' === $opt['default'];
		$base     = 'mpwcfg[steps][' . $step_index . '][options][' . $opt_index . ']';
		?>
		<div class="mpwcfg-option" data-type="<?php echo esc_attr( $type ); ?>">
			<span class="mpwcfg-option__drag dashicons dashicons-menu"></span>
			<?php if ( 'quantity' !== $type ) : ?>
				<?php /* Se conservan estos valores en la configuración para compatibilidad, pero ya no se muestran como columnas en el editor. */ ?>
				<input type="hidden" class="mpwcfg-option__chip" name="<?php echo esc_attr( $base ); ?>[chip]" value="<?php echo esc_attr( $chip ); ?>" />
				<input type="hidden" class="mpwcfg-option__image" name="<?php echo esc_attr( $base ); ?>[image_id]" value="<?php echo esc_attr( $image_id ); ?>" />
			<?php endif; ?>
			<input type="text" class="mpwcfg-option__label" name="<?php echo esc_attr( $base ); ?>[label]" value="<?php echo esc_attr( $label ); ?>" placeholder="<?php esc_attr_e( 'Etiqueta', 'mpw-configurador-productos' ); ?>" />

			<?php if ( 'quantity' === $type ) : ?>
				<input type="number" class="mpwcfg-option__qty" name="<?php echo esc_attr( $base ); ?>[qty]" value="<?php echo esc_attr( $qty ); ?>" step="1" min="1" placeholder="<?php esc_attr_e( 'Uds.', 'mpw-configurador-productos' ); ?>" title="<?php esc_attr_e( 'Unidades de este tramo', 'mpw-configurador-productos' ); ?>" />
				<input type="number" class="mpwcfg-option__mult" name="<?php echo esc_attr( $base ); ?>[multiplier]" value="<?php echo esc_attr( $mult ); ?>" step="0.1" min="0" placeholder="×" title="<?php esc_attr_e( 'Multiplicador de precio', 'mpw-configurador-productos' ); ?>" />
				<input type="text" class="mpwcfg-option__badge" name="<?php echo esc_attr( $base ); ?>[badge]" value="<?php echo esc_attr( $badge ); ?>" placeholder="<?php esc_attr_e( '-30%', 'mpw-configurador-productos' ); ?>" title="<?php esc_attr_e( 'Sello de descuento (texto libre)', 'mpw-configurador-productos' ); ?>" />
			<?php else : ?>
				<input type="text" class="mpwcfg-option__sub" name="<?php echo esc_attr( $base ); ?>[sublabel]" value="<?php echo esc_attr( $sublabel ); ?>" placeholder="<?php esc_attr_e( 'Subtítulo (opcional)', 'mpw-configurador-productos' ); ?>" />
				<input type="number" class="mpwcfg-option__price" name="<?php echo esc_attr( $base ); ?>[price]" value="<?php echo esc_attr( $price ); ?>" step="10" placeholder="<?php esc_attr_e( 'Recargo $', 'mpw-configurador-productos' ); ?>" title="<?php esc_attr_e( 'Recargo en CLP', 'mpw-configurador-productos' ); ?>" />
			<?php endif; ?>

			<label class="mpwcfg-option__default" title="<?php esc_attr_e( 'Marcar como seleccionada por defecto', 'mpw-configurador-productos' ); ?>">
				<input type="radio" name="<?php echo esc_attr( 'mpwcfg[steps][' . $step_index . '][_default]' ); ?>" value="<?php echo esc_attr( $opt_index ); ?>" <?php checked( $default ); ?> />
				<span class="dashicons dashicons-star-filled"></span>
			</label>
			<button type="button" class="mpwcfg-option__remove dashicons dashicons-no-alt"></button>
		</div>
		<?php
	}

	/**
	 * Plantillas ocultas que admin.js clona para crear pasos/opciones.
	 */
	public static function render_templates() {
		?>
		<script type="text/template" id="mpwcfg-tpl-step-swatch">
			<?php self::render_step( '__I__', array( 'type' => 'swatch', 'options' => array() ) ); ?>
		</script>
		<script type="text/template" id="mpwcfg-tpl-step-quantity">
			<?php self::render_step( '__I__', array( 'type' => 'quantity', 'options' => array() ) ); ?>
		</script>
		<script type="text/template" id="mpwcfg-tpl-step-upload">
			<?php self::render_step( '__I__', array( 'type' => 'upload' ) ); ?>
		</script>
		<script type="text/template" id="mpwcfg-tpl-option-swatch">
			<?php self::render_option( '__I__', '__J__', 'swatch', array() ); ?>
		</script>
		<script type="text/template" id="mpwcfg-tpl-option-quantity">
			<?php self::render_option( '__I__', '__J__', 'quantity', array( 'multiplier' => 1 ) ); ?>
		</script>
		<?php
	}

	/**
	 * Sanitiza $_POST['mpwcfg'] en una configuración limpia. Compartido por producto
	 * y categoría. No incluye 'source' (eso lo gestiona el producto).
	 *
	 * @param array $raw Datos crudos (ya unslasheados).
	 * @return array
	 */
	public static function sanitize( $raw ) {
		$config = array(
			'enabled'                 => isset( $raw['enabled'] ) ? 'yes' : 'no',
			'mode'                    => isset( $raw['mode'] ) && 'combinations' === sanitize_key( $raw['mode'] ) ? 'combinations' : 'traditional',
			'base_price'              => isset( $raw['base_price'] ) ? MPWCFG_Pricing::round_clp( (float) $raw['base_price'] ) : 0,
			'cta_label'               => self::txt( $raw, 'cta_label', __( 'Agregar al carrito', 'mpw-configurador-productos' ) ),
			'accepted_label'          => self::txt( $raw, 'accepted_label', 'PDF · AI · PNG' ),
			'template_label'          => self::txt( $raw, 'template_label', '' ),
			'template_url'            => isset( $raw['template_url'] ) ? esc_url_raw( $raw['template_url'] ) : '',
			'tax_note'                => self::txt( $raw, 'tax_note', '' ),
			'total_label'             => self::txt( $raw, 'total_label', __( 'Total estimado', 'mpw-configurador-productos' ) ),
			'summary_base_label'      => self::txt( $raw, 'summary_base_label', '' ),
			'summary_surcharge_label' => self::txt( $raw, 'summary_surcharge_label', '' ),
			'summary_discount_label'  => self::txt( $raw, 'summary_discount_label', '' ),
			'contact_url'             => isset( $raw['contact_url'] ) ? esc_url_raw( $raw['contact_url'] ) : '',
			'contact_label'           => self::txt( $raw, 'contact_label', 'WhatsApp' ),
			'after_add_action'        => in_array( isset( $raw['after_add_action'] ) ? sanitize_key( $raw['after_add_action'] ) : 'cart', array( 'cart', 'checkout', 'shop', 'stay' ), true ) ? sanitize_key( $raw['after_add_action'] ) : 'cart',
			'after_add_label'         => self::txt( $raw, 'after_add_label', 'Ir al carrito' ),
			'show_continue_shopping' => ( isset( $raw['show_continue_shopping'] ) && 'yes' === $raw['show_continue_shopping'] ) ? 'yes' : 'no',
			'continue_shopping_label' => self::txt( $raw, 'continue_shopping_label', 'Seguir comprando' ),
			'trust'                   => array(),
			'combinations'            => array( 'levels' => array(), 'profiles' => array(), 'matrix' => array() ),
			'steps'                   => array(),
		);

		if ( ! empty( $raw['trust'] ) && is_array( $raw['trust'] ) ) {
			foreach ( $raw['trust'] as $t ) {
				$t = sanitize_text_field( $t );
				if ( '' !== $t ) {
					$config['trust'][] = $t;
				}
			}
		}


		if ( ! empty( $raw['combinations']['levels'] ) && is_array( $raw['combinations']['levels'] ) ) {
			foreach ( $raw['combinations']['levels'] as $level ) {
				$label = isset( $level['label'] ) ? sanitize_text_field( $level['label'] ) : '';
				if ( '' === $label ) {
					continue;
				}
				$clean_level = array(
					'key' => sanitize_key( sanitize_title( $label ) ) ?: 'nivel_' . count( $config['combinations']['levels'] ),
					'label' => $label,
					'options' => array(),
				);
				if ( ! empty( $level['options'] ) && is_array( $level['options'] ) ) {
					foreach ( $level['options'] as $opt ) {
						$olabel = isset( $opt['label'] ) ? sanitize_text_field( $opt['label'] ) : '';
						if ( '' === $olabel ) continue;
						$clean_level['options'][] = array(
							'label' => $olabel,
							'sku'   => isset( $opt['sku'] ) ? sanitize_text_field( $opt['sku'] ) : '',
						);
					}
				}
				$config['combinations']['levels'][] = $clean_level;
			}
		}
		if ( ! empty( $raw['combinations']['profiles'] ) && is_array( $raw['combinations']['profiles'] ) ) {
			foreach ( $raw['combinations']['profiles'] as $profile ) {
				$format_index = isset( $profile['format_index'] ) ? absint( $profile['format_index'] ) : 0;
				$clean_profile = array( 'format_index' => $format_index, 'tiers' => array() );
				if ( ! empty( $profile['tiers'] ) && is_array( $profile['tiers'] ) ) {
					foreach ( $profile['tiers'] as $tier ) {
						$tier_label = isset( $tier['label'] ) ? sanitize_text_field( $tier['label'] ) : '';
						if ( '' === $tier_label ) {
							continue;
						}
						$clean_tier = array( 'label' => $tier_label, 'rows' => array() );
						if ( ! empty( $tier['rows'] ) && is_array( $tier['rows'] ) ) {
							foreach ( $tier['rows'] as $row ) {
								$quantity = isset( $row['quantity'] ) ? sanitize_text_field( $row['quantity'] ) : '';
								if ( '' === $quantity ) {
									continue;
								}
								$clean_tier['rows'][] = array(
									'quantity' => $quantity,
									'price'    => MPWCFG_Pricing::round_clp( isset( $row['price'] ) ? (float) $row['price'] : 0 ),
								);
							}
						}
						$clean_profile['tiers'][] = $clean_tier;
					}
				}
				$config['combinations']['profiles'][] = $clean_profile;
			}
		}

		if ( ! empty( $raw['combinations']['matrix'] ) && is_array( $raw['combinations']['matrix'] ) ) {
			foreach ( $raw['combinations']['matrix'] as $row ) {
				$values = isset( $row['values'] ) && is_array( $row['values'] ) ? array_map( 'intval', $row['values'] ) : array();
				if ( count( $values ) !== count( $config['combinations']['levels'] ) ) continue;
				$config['combinations']['matrix'][] = array(
					'values' => $values,
					'price'  => MPWCFG_Pricing::round_clp( isset( $row['price'] ) ? (float) $row['price'] : 0 ),
				);
			}
		}

		if ( ! empty( $raw['steps'] ) && is_array( $raw['steps'] ) ) {
			foreach ( $raw['steps'] as $step ) {
				$type  = isset( $step['type'] ) ? sanitize_key( $step['type'] ) : 'swatch';
				$label = isset( $step['label'] ) ? sanitize_text_field( $step['label'] ) : '';

				if ( '' === $label ) {
					continue; // Ignora pasos sin rótulo.
				}

				$clean = array(
					'type'  => in_array( $type, array( 'swatch', 'quantity', 'upload' ), true ) ? $type : 'swatch',
					'label' => $label,
					'key'   => self::make_key( $step, $label ),
				);

				if ( 'upload' === $type ) {
					$clean['toggle_label'] = isset( $step['toggle_label'] ) ? sanitize_text_field( $step['toggle_label'] ) : '';
					$clean['help']         = isset( $step['help'] ) ? sanitize_textarea_field( $step['help'] ) : '';
				} else {
					$disp             = isset( $step['display'] ) ? sanitize_key( $step['display'] ) : '';
					$clean['display'] = in_array( $disp, array( 'dropdown', 'buttons', 'slider' ), true ) ? $disp : ( 'quantity' === $type ? 'slider' : 'dropdown' );
					if ( 'quantity' === $type ) {
						$clean['allow_custom'] = ( isset( $step['allow_custom'] ) && 'yes' === $step['allow_custom'] ) ? 'yes' : 'no';
						$clean['custom_label'] = isset( $step['custom_label'] ) ? sanitize_text_field( $step['custom_label'] ) : '';
					}
					$default_idx      = isset( $step['_default'] ) ? (int) $step['_default'] : 0;
					$clean['options'] = array();
					if ( ! empty( $step['options'] ) && is_array( $step['options'] ) ) {
						foreach ( $step['options'] as $idx => $opt ) {
							$olabel = isset( $opt['label'] ) ? sanitize_text_field( $opt['label'] ) : '';
							if ( '' === $olabel ) {
								continue;
							}
							$row = array(
								'label'   => $olabel,
								'default' => ( (int) $idx === $default_idx ) ? 'yes' : 'no',
							);
							if ( 'quantity' === $type ) {
								$row['qty']        = isset( $opt['qty'] ) ? max( 1, (int) $opt['qty'] ) : 1;
								$row['multiplier'] = isset( $opt['multiplier'] ) ? (float) $opt['multiplier'] : 1;
								$row['badge']      = isset( $opt['badge'] ) ? sanitize_text_field( $opt['badge'] ) : '';
							} else {
								$row['sublabel'] = isset( $opt['sublabel'] ) ? sanitize_text_field( $opt['sublabel'] ) : '';
								$row['price']    = isset( $opt['price'] ) ? (float) $opt['price'] : 0;
								$row['chip']     = isset( $opt['chip'] ) ? self::sanitize_chip( $opt['chip'] ) : '';
								$row['image_id'] = isset( $opt['image_id'] ) ? absint( $opt['image_id'] ) : 0;
							}
							$clean['options'][] = $row;
						}
					}
				}

				$config['steps'][] = $clean;
			}
		}

		return $config;
	}

	/**
	 * Helper de texto saneado con fallback.
	 */
	private static function txt( $raw, $key, $fallback ) {
		return isset( $raw[ $key ] ) ? sanitize_text_field( $raw[ $key ] ) : $fallback;
	}

	/**
	 * Sanitiza el color de muestra: hex (#rgb / #rrggbb) o vacío.
	 */
	private static function sanitize_chip( $value ) {
		$value = trim( (string) $value );
		if ( '' === $value ) {
			return '';
		}
		$hex = sanitize_hex_color( $value );
		return $hex ? $hex : '';
	}

	/**
	 * Genera/conserva una key estable a partir de la etiqueta.
	 */
	private static function make_key( $step, $label ) {
		if ( ! empty( $step['key'] ) ) {
			return sanitize_key( $step['key'] );
		}
		$key = sanitize_key( sanitize_title( $label ) );
		return $key ? $key : 'paso_' . wp_rand( 100, 999 );
	}
}
