<?php
/**
 * Plantilla del configurador (frontend) — solo la columna de opciones.
 * Cada paso se muestra como desplegable o como botones según su ajuste.
 * Las opciones no muestran su precio; el detalle se desglosa en el resumen.
 *
 * @var WC_Product $product
 * @var array      $config
 * @var array      $opts
 * @var array      $initial  Resultado de MPWCFG_Pricing::calculate() con defaults.
 *
 * @package MPW_Configurador_Productos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pid     = $product->get_id();
$step_no = 0;

$base_label     = $config['summary_base_label'] ?? __( 'Configuración base', 'mpw-configurador-productos' );
$discount_label = $config['summary_discount_label'] ?? __( 'Descuento por volumen', 'mpw-configurador-productos' );
$total_label    = $config['total_label'] ?? __( 'Total estimado', 'mpw-configurador-productos' );
$tax_note       = $config['tax_note'] ?? '';
$cta_label      = $config['cta_label'] ?? __( 'Agregar al carrito', 'mpw-configurador-productos' );
$contact_url    = $config['contact_url'] ?? '';
$contact_label  = $config['contact_label'] ?? __( 'WhatsApp', 'mpw-configurador-productos' );
$after_action   = $config['after_add_action'] ?? 'cart';
$after_label    = 'Ir al carrito para pagar';
$show_continue  = ( $config['show_continue_shopping'] ?? 'yes' ) === 'yes';
$continue_label = $config['continue_shopping_label'] ?? __( 'Seguir comprando', 'mpw-configurador-productos' );
$trust          = ! empty( $config['trust'] ) && is_array( $config['trust'] ) ? $config['trust'] : array();
$uds_word       = __( 'uds', 'mpw-configurador-productos' );

/**
 * Pinta el contenido interno de una opción (chip + etiqueta + meta).
 *
 * @param array  $opt      Opción.
 * @param string $type     swatch|quantity.
 * @param string $uds_word Palabra "uds".
 * @param bool   $for_btn  true para el botón del desplegable (mete cantidad como meta).
 */
$mpwcfg_opt_inner = function ( $opt, $type, $uds_word, $context ) {
	$out = '';
	$sub = ( 'quantity' === $type ) ? __( 'unidades', 'mpw-configurador-productos' ) : ( isset( $opt['sublabel'] ) ? $opt['sublabel'] : '' );

	if ( 'qty-button' === $context ) {
		$out .= '<span class="mpwcfg-swatch__label">' . esc_html( $opt['label'] ) . '</span>';
		$out .= '<span class="mpwcfg-swatch__sub">' . esc_html( $uds_word ) . '</span>';
		return $out;
	}

	$out .= '<span class="mpwcfg-opt-main"><b>' . esc_html( $opt['label'] ) . '</b>';
	if ( $sub ) {
		$out .= '<span>' . esc_html( $sub ) . '</span>';
	}
	$out .= '</span>';

	if ( 'quantity' === $type && ! empty( $opt['badge'] ) ) {
		$out .= '<span class="mpwcfg-opt-badge">' . esc_html( $opt['badge'] ) . '</span>';
	}
	return $out;
};
?>
<div class="mpwcfg-configurator" data-product="<?php echo esc_attr( $pid ); ?>" data-product-name="<?php echo esc_attr( $product->get_name() ); ?>" data-base="<?php echo esc_attr( (float) $config['base_price'] ); ?>" data-uds="<?php echo esc_attr( $uds_word ); ?>" data-after-action="<?php echo esc_attr( $after_action ); ?>" data-after-label="<?php echo esc_attr( $after_label ); ?>" data-shop-url="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" data-checkout-url="<?php echo esc_url( wc_get_checkout_url() ); ?>" data-continue-label="<?php echo esc_attr( $continue_label ); ?>" data-show-continue="<?php echo $show_continue ? 'yes' : 'no'; ?>">

	<div class="mpwcfg-steps-front">
		<?php
		$front_steps = array_values( $config['steps'] );
		$priority_map = array( 'arte' => 0, 'formato' => 10, 'impresion' => 20, 'sustrato' => 30, 'terminacion' => 40, 'cantidad' => 90 );
		$decorated = array();
		foreach ( $front_steps as $position => $front_step ) {
			$front_key = isset( $front_step['key'] ) ? sanitize_key( $front_step['key'] ) : '';
			$front_type = isset( $front_step['type'] ) ? $front_step['type'] : 'swatch';
			$front_priority = isset( $priority_map[ $front_key ] ) ? $priority_map[ $front_key ] : ( 'upload' === $front_type ? 0 : ( 'quantity' === $front_type ? 90 : 50 ) );
			$decorated[] = array( 'step' => $front_step, 'priority' => $front_priority, 'position' => $position );
		}
		usort( $decorated, function( $a, $b ) {
			if ( $a['priority'] === $b['priority'] ) {
				return $a['position'] <=> $b['position'];
			}
			return $a['priority'] <=> $b['priority'];
		} );
		$front_steps = array();
		foreach ( $decorated as $item ) {
			$front_steps[] = $item['step'];
		}
		?>
		<?php foreach ( $front_steps as $step ) : ?>
			<?php
			$type = $step['type'] ?? 'swatch';
			$key  = $step['key'];

			/* ---------------- Subida de diseño ---------------- */
			if ( 'upload' === $type ) :
				$step_no++;
				$accepted = $config['accepted_label'] ?? 'PDF · AI · PNG';
				?>
				<section class="mpwcfg-step-front mpwcfg-step-front--upload" data-step="<?php echo esc_attr( $key ); ?>">
					<div class="mpwcfg-step-head">
						<div class="mpwcfg-step-head__left">
							<span class="mpwcfg-step-index"><?php echo esc_html( str_pad( $step_no, 2, '0', STR_PAD_LEFT ) ); ?></span>
							<span class="mpwcfg-step-label"><?php echo esc_html( $step['label'] ); ?></span>
						</div>
						<?php if ( $accepted ) : ?>
							<span class="mpwcfg-step-value mpwcfg-step-value--mono"><?php echo esc_html( $accepted ); ?></span>
						<?php endif; ?>
					</div>

					<div role="button" tabindex="0" class="mpwcfg-dropzone">
						<span class="mpwcfg-dropzone__icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4"/><path d="m6 10 6-6 6 6"/><path d="M4 20h16"/></svg>
						</span>
						<span class="mpwcfg-dropzone__text">
							<strong>Arrastra tu archivo o haz clic para seleccionar</strong>
							<small><?php echo esc_html( $accepted ); ?> · máx. 25 MB</small>
						</span>
						<span class="mpwcfg-upload-name"></span>
					</div>
					<input type="file" class="mpwcfg-upload-input" accept=".pdf,.png,.jpg,.jpeg,.ai,.eps" hidden />
					<div class="mpwcfg-upload-file" hidden>
						<span class="mpwcfg-upload-file__name"></span>
						<button type="button" class="mpwcfg-upload-file__remove">Quitar</button>
					</div>

					<?php if ( ! empty( $config['template_url'] ) ) : ?>
						<a class="mpwcfg-template-link" href="<?php echo esc_url( $config['template_url'] ); ?>" target="_blank" rel="noopener">
							<span aria-hidden="true">↧</span>
							<?php echo esc_html( $config['template_label'] ?: __( 'Descargar plantilla', 'mpw-configurador-productos' ) ); ?>
						</a>
					<?php endif; ?>
					<input type="hidden" class="mpwcfg-file-url" value="" />
					<input type="hidden" class="mpwcfg-file-name" value="" />
				</section>
				<?php
				continue;
			endif;

			/* ---------------- Pasos con opciones ---------------- */
			$step_no++;
			$display     = $step['display'] ?? ( 'quantity' === $type ? 'buttons' : 'dropdown' );
			$default_idx = MPWCFG_Pricing::default_index( $step );
			$default_opt = $step['options'][ $default_idx ] ?? array();
			$default_val = $default_opt['label'] ?? '';
			if ( 'quantity' === $type && '' !== $default_val ) {
				$default_val .= ' ' . $uds_word;
			}
			$d_price = isset( $default_opt['price'] ) ? (float) $default_opt['price'] : 0;
			$d_qty   = isset( $default_opt['qty'] ) ? (int) $default_opt['qty'] : 0;
			$d_mult  = isset( $default_opt['multiplier'] ) ? (float) $default_opt['multiplier'] : 1;
			$d_badge = isset( $default_opt['badge'] ) ? $default_opt['badge'] : '';
			?>
			<section class="mpwcfg-step-front"
				data-step="<?php echo esc_attr( $key ); ?>"
				data-type="<?php echo esc_attr( $type ); ?>"
				data-display="<?php echo esc_attr( $display ); ?>"
				data-price="<?php echo esc_attr( $d_price ); ?>"
				data-qty="<?php echo esc_attr( $d_qty ); ?>"
				data-mult="<?php echo esc_attr( $d_mult ); ?>"
				data-badge="<?php echo esc_attr( $d_badge ); ?>"
				data-label="<?php echo esc_attr( $default_opt['label'] ?? '' ); ?>"
				data-index="<?php echo esc_attr( $default_idx ); ?>">

				<div class="mpwcfg-step-head">
					<div class="mpwcfg-step-head__left">
						<span class="mpwcfg-step-index"><?php echo esc_html( str_pad( $step_no, 2, '0', STR_PAD_LEFT ) ); ?></span>
						<span class="mpwcfg-step-label"><?php echo esc_html( $step['label'] ); ?></span>
					</div>
					<span class="mpwcfg-step-value"><?php echo esc_html( $default_val ); ?></span>
				</div>

				<?php
				// Atributos de datos comunes para cada opción.
				$opt_attrs = function ( $opt, $i, $default_idx ) {
					$img = ! empty( $opt['image_id'] ) ? wp_get_attachment_image_url( (int) $opt['image_id'], 'thumbnail' ) : '';
					return ' data-index="' . esc_attr( $i ) . '"' .
						' data-label="' . esc_attr( $opt['label'] ?? '' ) . '"' .
						' data-sub="' . esc_attr( $opt['sublabel'] ?? '' ) . '"' .
						' data-price="' . esc_attr( isset( $opt['price'] ) ? (float) $opt['price'] : 0 ) . '"' .
						' data-qty="' . esc_attr( isset( $opt['qty'] ) ? (int) $opt['qty'] : 0 ) . '"' .
						' data-mult="' . esc_attr( isset( $opt['multiplier'] ) ? (float) $opt['multiplier'] : 1 ) . '"' .
						' data-badge="' . esc_attr( $opt['badge'] ?? '' ) . '"' .
						' data-chip="' . esc_attr( $opt['chip'] ?? '' ) . '"' .
						' data-image="' . esc_url( $img ) . '"';
				};
				?>

				<?php if ( 'dropdown' === $display ) : ?>
					<div class="mpwcfg-dd">
						<button type="button" class="mpwcfg-dd__btn" aria-haspopup="listbox" aria-expanded="false">
							<span class="mpwcfg-dd__face"><?php echo $mpwcfg_opt_inner( $default_opt, $type, $uds_word, 'btn' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<svg class="mpwcfg-dd__caret" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
						</button>
						<div class="mpwcfg-dd__panel" role="listbox">
							<?php foreach ( $step['options'] as $i => $opt ) : ?>
								<button type="button" class="mpwcfg-dd__opt<?php echo $i === $default_idx ? ' is-sel' : ''; ?>"<?php echo $opt_attrs( $opt, $i, $default_idx ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
									<?php echo $mpwcfg_opt_inner( $opt, $type, $uds_word, 'opt' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<svg class="mpwcfg-dd__check" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
								</button>
							<?php endforeach; ?>
						</div>
					</div>

				<?php elseif ( 'slider' === $display ) : ?>
					<?php
					$allow_custom = ! empty( $step['allow_custom'] ) && 'yes' === $step['allow_custom'];
					$custom_label = ! empty( $step['custom_label'] ) ? $step['custom_label'] : __( 'Más de 1.000 uds — cotización personalizada', 'mpw-configurador-productos' );
					$custom_note  = __( 'Para cantidades superiores al último tramo se aplica el precio unitario más bajo disponible. El total es orientativo y se confirma por cotización.', 'mpw-configurador-productos' );

					$slider_opts = array();
					foreach ( $step['options'] as $opt ) {
						$slider_opts[] = array(
							'label' => $opt['label'],
							'qty'   => isset( $opt['qty'] ) ? (int) $opt['qty'] : 0,
							'mult'  => isset( $opt['multiplier'] ) ? (float) $opt['multiplier'] : 1,
							'badge' => isset( $opt['badge'] ) ? $opt['badge'] : '',
						);
					}
					if ( $allow_custom ) {
						$slider_opts[] = array( 'label' => __( 'Más', 'mpw-configurador-productos' ), 'qty' => 0, 'mult' => 0, 'badge' => '', 'custom' => true );
					}
					$max_idx = count( $slider_opts ) - 1;
					?>
					<div class="mpwcfg-slider">
						<div class="mpwcfg-slider__thumb" aria-hidden="true"></div>
						<input type="range" class="mpwcfg-slider__range"
							min="0" max="<?php echo esc_attr( $max_idx ); ?>"
							step="1" value="<?php echo esc_attr( $default_idx ); ?>"
							data-slider-opts="<?php echo esc_attr( wp_json_encode( $slider_opts ) ); ?>"
							aria-label="<?php echo esc_attr( $step['label'] ); ?>" />
						<div class="mpwcfg-slider__labels" aria-hidden="true"></div>
					</div>
					<?php if ( $allow_custom ) : ?>
					<div class="mpwcfg-custom-qty" hidden>
						<div class="mpwcfg-custom-qty__row">
							<label class="mpwcfg-custom-qty__label" for="mpwcfg-custom-qty-<?php echo esc_attr( $step['key'] ); ?>">
								<?php echo esc_html( $custom_label ); ?>
							</label>
							<input type="number" class="mpwcfg-custom-qty__input"
								id="mpwcfg-custom-qty-<?php echo esc_attr( $step['key'] ); ?>"
								min="1" step="1" placeholder="ej. 2500"
								aria-label="<?php esc_attr_e( 'Cantidad exacta', 'mpw-configurador-productos' ); ?>" />
						</div>
						<p class="mpwcfg-custom-qty__note"><?php echo esc_html( $custom_note ); ?></p>
					</div>
					<input type="hidden" class="mpwcfg-custom-qty-val" value="" />
					<?php endif; ?>

				<?php else : ?>
					<div class="mpwcfg-swatches<?php echo 'quantity' === $type ? ' mpwcfg-swatches--qty' : ''; ?> mpwcfg-swatches--count-<?php echo esc_attr( count( $step['options'] ) ); ?>">
						<?php foreach ( $step['options'] as $i => $opt ) : ?>
							<button type="button" class="mpwcfg-swatch<?php echo $i === $default_idx ? ' is-selected' : ''; ?>"<?php echo $opt_attrs( $opt, $i, $default_idx ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-pressed="<?php echo $i === $default_idx ? 'true' : 'false'; ?>">
								<?php if ( 'quantity' === $type ) : ?>
									<?php if ( ! empty( $opt['badge'] ) ) : ?>
										<span class="mpwcfg-swatch__badge"><?php echo esc_html( $opt['badge'] ); ?></span>
									<?php endif; ?>
									<span class="mpwcfg-swatch__label"><?php echo esc_html( $opt['label'] ); ?></span>
									<span class="mpwcfg-swatch__sub"><?php echo esc_html( $uds_word ); ?></span>
								<?php else : ?>
									<span class="mpwcfg-swatch__label"><?php echo esc_html( $opt['label'] ); ?></span>
									<?php if ( ! empty( $opt['sublabel'] ) ) : ?>
										<span class="mpwcfg-swatch__sub"><?php echo esc_html( $opt['sublabel'] ); ?></span>
									<?php endif; ?>
								<?php endif; ?>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</section>
		<?php endforeach; ?>
	</div>

	<!-- Resumen del pedido -->
	<div class="mpwcfg-summary">
		<div class="mpwcfg-summary__title">Resumen de tu pedido</div>
		<div class="mpwcfg-summary__config"></div>
		<div class="mpwcfg-summary__file" hidden></div>
		<div class="mpwcfg-summary__total">
			<span class="mpwcfg-summary__total-label"><?php echo esc_html( $total_label ); ?></span>
			<div class="mpwcfg-summary__total-row">
				<span class="mpwcfg-total"><?php echo esc_html( MPWCFG_Pricing::format_clp( $initial['total'] ) ); ?></span>
			</div>
			<?php if ( $tax_note ) : ?>
				<span class="mpwcfg-summary__tax"><?php echo esc_html( $tax_note ); ?><?php if ( $initial['qty_units'] ) : ?> · <span class="mpwcfg-summary__tax-qty"><?php echo esc_html( $initial['qty_units'] ); ?></span> <?php esc_html_e( 'unidades', 'mpw-configurador-productos' ); ?><?php endif; ?></span>
			<?php endif; ?>
		</div>
		<div class="mpwcfg-actions">
			<button type="button" class="mpwcfg-add">
				<span class="mpwcfg-add__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
				</span>
				<span class="mpwcfg-add__text"><?php echo esc_html( $cta_label ); ?></span>
			</button>
			<?php if ( $contact_url ) : ?>
				<a class="mpwcfg-contact mpwcfg-contact--whatsapp" href="<?php echo esc_url( $contact_url ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Escribir por WhatsApp con el resumen del pedido', 'mpw-configurador-productos' ); ?>">
					<?php echo esc_html( $contact_label ); ?>
				</a>
			<?php endif; ?>
		</div>
		<div class="mpwcfg-after-add-links">
			<a class="mpwcfg-cart-link" href="#" hidden><?php echo esc_html( $after_label ); ?> →</a>
		</div>
		<p class="mpwcfg-feedback" role="status" aria-live="polite"></p>
	</div>

	<?php if ( ! empty( $trust ) ) : ?>
		<ul class="mpwcfg-trust">
			<?php foreach ( $trust as $t ) : ?>
				<li class="mpwcfg-trust__item">
					<span class="mpwcfg-trust__check" aria-hidden="true">
						<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
					</span>
					<span><?php echo esc_html( $t ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
