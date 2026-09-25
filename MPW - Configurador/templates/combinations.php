<?php
/**
 * Frontend del modo por combinaciones.
 * El cliente selecciona en cascada, puede corregir cualquier selección,
 * subir su diseño y revisar el resumen completo antes de agregar al carrito.
 *
 * @var WC_Product $product
 * @var array      $config
 * @var int        $pid
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$combo = isset( $config['combinations'] ) && is_array( $config['combinations'] ) ? $config['combinations'] : MPWCFG_Pricing::default_combinations();
$levels = isset( $combo['levels'] ) && is_array( $combo['levels'] ) ? array_values( $combo['levels'] ) : array();
$matrix = isset( $combo['matrix'] ) && is_array( $combo['matrix'] ) ? array_values( $combo['matrix'] ) : array();
$trust = ! empty( $config['trust'] ) && is_array( $config['trust'] ) ? $config['trust'] : array();
$contact_url = $config['contact_url'] ?? '';
$contact_label = $config['contact_label'] ?? 'WhatsApp';
$cta_label = $config['cta_label'] ?? 'Agregar al carrito';
$after_action = $config['after_add_action'] ?? 'cart';
$after_label = 'Ir al carrito para pagar';
$show_continue = ( $config['show_continue_shopping'] ?? 'yes' ) === 'yes';
$continue_label = $config['continue_shopping_label'] ?? 'Seguir comprando';
$tax_note = $config['tax_note'] ?? 'IVA incluido';
$accepted = $config['accepted_label'] ?? 'PDF · AI · PNG';
$template_url = $config['template_url'] ?? '';
$template_label = $config['template_label'] ?? 'Descargar plantilla con sangrado y márgenes';
$matrix_json = wp_json_encode( $matrix );
$profiles_json = wp_json_encode( isset( $combo['profiles'] ) && is_array( $combo['profiles'] ) ? $combo['profiles'] : array() );
?>
<div class="mpwcfg-configurator mpwcfg-configurator--combinations"
	data-product="<?php echo esc_attr( $pid ); ?>"
	data-product-name="<?php echo esc_attr( $product->get_name() ); ?>"
	data-after-action="<?php echo esc_attr( $after_action ); ?>"
	data-after-label="<?php echo esc_attr( $after_label ); ?>"
	data-shop-url="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"
	data-checkout-url="<?php echo esc_url( wc_get_checkout_url() ); ?>"
	data-cart-url="<?php echo esc_url( wc_get_cart_url() ); ?>"
	data-tax-note="<?php echo esc_attr( $tax_note ); ?>" data-level-1-label="<?php echo esc_attr( $levels[1]['label'] ?? 'Cantidad de hojas' ); ?>" data-level-2-label="<?php echo esc_attr( $levels[2]['label'] ?? 'Unidades' ); ?>">

	<div class="mpwcfg-combo-hero">
		<div>
			<p>Selecciona las opciones paso a paso y revisa tu pedido antes de agregarlo al carrito.</p>
		</div>
	</div>

	<section class="mpwcfg-combo-upload" aria-label="Subir diseño">
		<div class="mpwcfg-combo-upload__head">
			<strong>Sube tu diseño</strong>
			<small>Si ya tienes tu diseño, súbelo ahora. También puedes enviarlo después del pedido.</small>
		</div>
		<div role="button" tabindex="0" class="mpwcfg-dropzone mpwcfg-combo-dropzone">
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
		<?php if ( $template_url ) : ?>
			<a class="mpwcfg-template-link" href="<?php echo esc_url( $template_url ); ?>" target="_blank" rel="noopener"><span aria-hidden="true">↧</span><?php echo esc_html( $template_label ); ?></a>
		<?php endif; ?>
		<input type="hidden" class="mpwcfg-file-url" value="" />
		<input type="hidden" class="mpwcfg-file-name" value="" />
	</section>

	<div class="mpwcfg-combo-progress" aria-hidden="true">
		<?php foreach ( $levels as $i => $level ) : ?><span data-progress="<?php echo esc_attr( $i ); ?>"></span><?php endforeach; ?>
	</div>

	<div class="mpwcfg-combo-levels-front">
	<?php foreach ( $levels as $i => $level ) : $opts = isset( $level['options'] ) && is_array( $level['options'] ) ? array_values( $level['options'] ) : array(); ?>
		<section class="mpwcfg-combo-step" data-level="<?php echo esc_attr( $i ); ?>" aria-label="<?php echo esc_attr( $level['label'] ?? '' ); ?>">
			<div class="mpwcfg-combo-step-head">
				<div><span><?php echo esc_html( str_pad( $i + 1, 2, '0', STR_PAD_LEFT ) ); ?></span><h3><?php echo esc_html( $level['label'] ?? '' ); ?></h3></div>
				<small class="mpwcfg-combo-step-current"></small>
			</div>
			<div class="mpwcfg-combo-options">
			<?php foreach ( $opts as $oi => $opt ) : ?><button type="button" class="mpwcfg-combo-option-btn" data-index="<?php echo esc_attr( $oi ); ?>"><span><?php echo esc_html( $opt['label'] ?? '' ); ?></span><?php if ( ! empty( $opt['sku'] ) ) : ?><small><?php echo esc_html( $opt['sku'] ); ?></small><?php endif; ?></button><?php endforeach; ?>
			</div>
		</section>
	<?php endforeach; ?>
	</div>

	<div class="mpwcfg-summary mpwcfg-combo-summary">
		<div class="mpwcfg-summary__title">Resumen de tu pedido</div>
		<div class="mpwcfg-summary__config mpwcfg-combo-summary-lines"></div>
		<div class="mpwcfg-summary__file mpwcfg-combo-summary-file" hidden></div>
		<div class="mpwcfg-summary__total mpwcfg-combo-total">
			<span class="mpwcfg-summary__total-label">Total estimado</span>
			<div class="mpwcfg-summary__total-row"><span class="mpwcfg-total mpwcfg-combo-total-value">$ 0</span></div>
			<?php if ( $tax_note ) : ?><span class="mpwcfg-summary__tax mpwcfg-combo-tax"><?php echo esc_html( $tax_note ); ?></span><?php endif; ?>
		</div>
		<div class="mpwcfg-actions">
			<button type="button" class="mpwcfg-add mpwcfg-combo-add" disabled>
				<span class="mpwcfg-add__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
				</span>
				<span class="mpwcfg-add__text"><?php echo esc_html( $cta_label ); ?></span>
			</button>
			<?php if ( $contact_url ) : ?>
				<a class="mpwcfg-contact mpwcfg-contact--whatsapp mpwcfg-combo-contact" href="<?php echo esc_url( $contact_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $contact_label ); ?></a>
			<?php endif; ?>
		</div>
		<div class="mpwcfg-after-add-links">
			<a class="mpwcfg-cart-link mpwcfg-combo-cart-link" href="#" hidden><?php echo esc_html( $after_label ); ?> →</a>
		</div>
		<p class="mpwcfg-feedback mpwcfg-combo-feedback" role="status" aria-live="polite"></p>
	</div>

	<?php if ( ! empty( $trust ) ) : ?><ul class="mpwcfg-trust"><?php foreach ( $trust as $t ) : ?><li class="mpwcfg-trust__item"><span class="mpwcfg-trust__check">✓</span><span><?php echo esc_html( $t ); ?></span></li><?php endforeach; ?></ul><?php endif; ?>
	<script type="application/json" class="mpwcfg-combo-data"><?php echo $matrix_json; ?></script>
	<script type="application/json" class="mpwcfg-combo-profiles-data"><?php echo $profiles_json; ?></script>
</div>
