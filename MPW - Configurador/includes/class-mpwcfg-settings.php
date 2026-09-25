<?php
/**
 * Panel de apariencia: colores, tipografía y forma del configurador,
 * editables desde el dashboard sin tocar Elementor.
 * Se guardan como opción y se inyectan como variables CSS globales; un
 * widget de Elementor con sus propios controles de estilo sigue teniendo
 * prioridad (su selector es más específico).
 *
 * @package MPW_Configurador_Productos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MPWCFG_Settings {

	const OPTION = 'mpwcfg_theme_settings';
	const PAGE   = 'mpwcfg-settings';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'maybe_save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	}

	/**
	 * Valores por defecto (calzan con los del widget de Elementor / CSS base).
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'bg'           => '#190b1f',
			'surface'      => '#251331',
			'surface_2'    => '#2e1840',
			'border'       => '#3a2348',
			'accent'       => '#ff2e7e',
			'accent_ink'   => '#2a0f1c',
			'mint'         => '#36d6cf',
			'success'      => '#4bd99a',
			'whatsapp'     => '#25d366',
			'text'         => '#f7f0f4',
			'text_muted'   => '#c3b0cf',
			'text_dim'     => '#8b779a',
			'font_display' => 'Space Grotesk',
			'font_mono'    => 'JetBrains Mono',
			'radius'       => 16,
			'padding'      => 0,
		);
	}

	/**
	 * Configuración guardada, con fallback a los valores por defecto.
	 *
	 * @return array
	 */
	public static function get() {
		$saved = get_option( self::OPTION, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
	}

	/**
	 * Página en el menú del admin.
	 */
	public function menu() {
		add_menu_page(
			__( 'MPW · Configurador', 'mpw-configurador-productos' ),
			__( 'MPW · Configurador', 'mpw-configurador-productos' ),
			'manage_woocommerce',
			self::PAGE,
			array( $this, 'render' ),
			'dashicons-admin-appearance',
			56
		);
	}

	/**
	 * Assets solo en la pantalla de este panel.
	 */
	public function assets( $hook ) {
		if ( 'toplevel_page_' . self::PAGE !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'mpwcfg-settings', MPWCFG_URL . 'assets/js/settings.js', array( 'jquery', 'wp-color-picker' ), MPWCFG_VERSION, true );
	}

	/**
	 * Guarda o restablece el formulario. Redirige para evitar reenvíos del POST.
	 */
	public function maybe_save() {
		if ( ! isset( $_POST['mpwcfg_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mpwcfg_settings_nonce'] ) ), 'mpwcfg_save_settings' ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( isset( $_POST['mpwcfg_reset'] ) ) {
			delete_option( self::OPTION );
			$this->redirect_back();
		}

		$raw      = isset( $_POST['cc_theme'] ) && is_array( $_POST['cc_theme'] ) ? wp_unslash( $_POST['cc_theme'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$defaults = self::defaults();
		$clean    = array();

		foreach ( $defaults as $key => $default ) {
			if ( ! isset( $raw[ $key ] ) ) {
				$clean[ $key ] = $default;
				continue;
			}
			if ( in_array( $key, array( 'radius', 'padding' ), true ) ) {
				$clean[ $key ] = max( 0, min( 200, (int) $raw[ $key ] ) );
			} elseif ( in_array( $key, array( 'font_display', 'font_mono' ), true ) ) {
				// Solo letras, números, espacios y guiones: evita romper el bloque CSS.
				$clean[ $key ] = preg_replace( '/[^A-Za-z0-9 \-]/', '', (string) $raw[ $key ] );
				if ( '' === $clean[ $key ] ) {
					$clean[ $key ] = $default;
				}
			} else {
				$hex           = sanitize_hex_color( $raw[ $key ] );
				$clean[ $key ] = $hex ? $hex : $default;
			}
		}

		update_option( self::OPTION, $clean );
		$this->redirect_back();
	}

	/**
	 * Redirige de vuelta al panel tras guardar (evita reenvío del formulario).
	 */
	private function redirect_back() {
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'       => self::PAGE,
					'mpwcfg_updated' => 1,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Renderiza el formulario del panel.
	 */
	public function render() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$s = self::get();
		?>
		<div class="wrap mpwcfg-settings-page">
			<h1><?php esc_html_e( 'MPW · Apariencia del configurador', 'mpw-configurador-productos' ); ?></h1>

			<?php if ( isset( $_GET['mpwcfg_updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Cambios guardados.', 'mpw-configurador-productos' ); ?></p></div>
			<?php endif; ?>

			<p class="description" style="max-width:720px;margin-bottom:18px;">
				<?php esc_html_e( 'Estos valores son el diseño por defecto del configurador en todo el sitio (colores, tipografía, bordes y relleno). Si un producto usa el widget de Elementor con sus propios colores, ese estilo particular seguirá teniendo prioridad sobre este panel.', 'mpw-configurador-productos' ); ?>
			</p>

			<form method="post">
				<?php wp_nonce_field( 'mpwcfg_save_settings', 'mpwcfg_settings_nonce' ); ?>

				<h2 class="title"><?php esc_html_e( 'Colores', 'mpw-configurador-productos' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					$color_fields = array(
						'bg'         => __( 'Fondo', 'mpw-configurador-productos' ),
						'surface'    => __( 'Superficie (botones)', 'mpw-configurador-productos' ),
						'surface_2'  => __( 'Superficie al pasar el mouse', 'mpw-configurador-productos' ),
						'border'     => __( 'Borde', 'mpw-configurador-productos' ),
						'accent'     => __( 'Acento (fucsia)', 'mpw-configurador-productos' ),
						'accent_ink' => __( 'Texto sobre el acento', 'mpw-configurador-productos' ),
						'mint'       => __( 'Secundario / descuentos', 'mpw-configurador-productos' ),
						'success'    => __( 'Éxito (agregado al carrito)', 'mpw-configurador-productos' ),
						'whatsapp'   => __( 'Botón de WhatsApp', 'mpw-configurador-productos' ),
						'text'       => __( 'Texto principal', 'mpw-configurador-productos' ),
						'text_muted' => __( 'Texto secundario', 'mpw-configurador-productos' ),
						'text_dim'   => __( 'Texto tenue / etiquetas', 'mpw-configurador-productos' ),
					);
					foreach ( $color_fields as $key => $label ) :
						?>
						<tr>
							<th scope="row"><label for="cc-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
							<td>
								<input type="text"
									id="cc-<?php echo esc_attr( $key ); ?>"
									class="mpwcfg-color-field"
									name="cc_theme[<?php echo esc_attr( $key ); ?>]"
									value="<?php echo esc_attr( $s[ $key ] ); ?>" />
							</td>
						</tr>
					<?php endforeach; ?>
				</table>

				<h2 class="title"><?php esc_html_e( 'Tipografía', 'mpw-configurador-productos' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="mpwcfg-font_display"><?php esc_html_e( 'Fuente principal (Google Fonts)', 'mpw-configurador-productos' ); ?></label></th>
						<td>
							<input type="text" id="mpwcfg-font_display" name="cc_theme[font_display]" value="<?php echo esc_attr( $s['font_display'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'Nombre exacto de la fuente en Google Fonts, ej. "Space Grotesk".', 'mpw-configurador-productos' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mpwcfg-font_mono"><?php esc_html_e( 'Fuente mono / etiquetas', 'mpw-configurador-productos' ); ?></label></th>
						<td><input type="text" id="mpwcfg-font_mono" name="cc_theme[font_mono]" value="<?php echo esc_attr( $s['font_mono'] ); ?>" class="regular-text" /></td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Forma', 'mpw-configurador-productos' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="mpwcfg-radius"><?php esc_html_e( 'Radio de bordes (px)', 'mpw-configurador-productos' ); ?></label></th>
						<td><input type="number" min="0" max="48" id="mpwcfg-radius" name="cc_theme[radius]" value="<?php echo esc_attr( $s['radius'] ); ?>" class="small-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="mpwcfg-padding"><?php esc_html_e( 'Relleno interior extra (px)', 'mpw-configurador-productos' ); ?></label></th>
						<td>
							<input type="number" min="0" max="80" id="mpwcfg-padding" name="cc_theme[padding]" value="<?php echo esc_attr( $s['padding'] ); ?>" class="small-text" />
							<p class="description"><?php esc_html_e( 'Se suma solo si el widget de Elementor no define su propio relleno.', 'mpw-configurador-productos' ); ?></p>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Guardar cambios', 'mpw-configurador-productos' ); ?></button>
					<button type="submit" name="mpwcfg_reset" value="1" class="button" onclick="return confirm('<?php echo esc_js( __( '¿Restablecer el diseño por defecto? Se perderán estos ajustes.', 'mpw-configurador-productos' ) ); ?>');">
						<?php esc_html_e( 'Restablecer por defecto', 'mpw-configurador-productos' ); ?>
					</button>
				</p>
			</form>
		</div>
		<?php
	}
}
