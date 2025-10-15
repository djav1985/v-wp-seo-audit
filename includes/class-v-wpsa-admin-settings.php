<?php
/**
 * Admin Settings Page for v-wpsa plugin
 * Provides interface to toggle between legacy Yii and native WordPress code paths.
 *
 * @package v_wpsa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class V_WPSA_Admin_Settings
 */
class V_WPSA_Admin_Settings {

	/**
	 * Option name for feature flag.
	 */
	const OPTION_USE_NATIVE = 'v_wpsa_use_native_generator';

	/**
	 * Initialize admin settings.
	 */
	public static function init() {
		// Add admin menu.
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );

		// Register settings.
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * Add admin menu page.
	 */
	public static function add_admin_menu() {
		add_options_page(
			'SEO Audit Settings',
			'SEO Audit',
			'manage_options',
			'v-wpsa-settings',
			array( __CLASS__, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings.
	 */
	public static function register_settings() {
		register_setting(
			'v_wpsa_settings_group',
			self::OPTION_USE_NATIVE,
			array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => array( __CLASS__, 'sanitize_boolean' ),
			)
		);

		add_settings_section(
			'v_wpsa_main_section',
			'Report Generation Settings',
			array( __CLASS__, 'render_section_description' ),
			'v-wpsa-settings'
		);

		add_settings_field(
			self::OPTION_USE_NATIVE,
			'Use WordPress Native Generator',
			array( __CLASS__, 'render_use_native_field' ),
			'v-wpsa-settings',
			'v_wpsa_main_section'
		);
	}

	/**
	 * Sanitize boolean value.
	 *
	 * @param mixed $value Value to sanitize.
	 * @return bool Sanitized boolean value.
	 */
	public static function sanitize_boolean( $value ) {
		return (bool) $value;
	}

	/**
	 * Render settings section description.
	 */
	public static function render_section_description() {
		echo '<p>Configure how the SEO Audit plugin generates reports and PDFs.</p>';
	}

	/**
	 * Render the "use native" checkbox field.
	 */
	public static function render_use_native_field() {
		$use_native = get_option( self::OPTION_USE_NATIVE, false );
		?>
		<label>
			<input type="checkbox" 
				   name="<?php echo esc_attr( self::OPTION_USE_NATIVE ); ?>" 
				   value="1" 
				   <?php checked( $use_native, true ); ?> />
			Enable WordPress-native report/PDF generation (recommended)
		</label>
		<p class="description">
			When enabled, reports and PDFs are generated using WordPress-native code without the Yii framework.
			This improves performance and reduces memory usage.
			<br><strong>Note:</strong> Initial website analysis still requires the legacy framework.
		</p>
		<?php
	}

	/**
	 * Render the settings page.
	 */
	public static function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle settings update message.
		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error(
				'v_wpsa_messages',
				'v_wpsa_message',
				'Settings Saved',
				'updated'
			);
		}

		settings_errors( 'v_wpsa_messages' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<div class="notice notice-info">
				<p>
					<strong>Migration Status:</strong> This plugin is being migrated from Yii framework to WordPress-native code.
					The native generator provides better performance but is currently in beta testing.
				</p>
			</div>

			<form action="options.php" method="post">
				<?php
				settings_fields( 'v_wpsa_settings_group' );
				do_settings_sections( 'v-wpsa-settings' );
				submit_button( 'Save Settings' );
				?>
			</form>

			<hr>

			<h2>System Status</h2>
			<table class="widefat striped">
				<tbody>
					<tr>
						<td><strong>Current Mode:</strong></td>
						<td>
							<?php
							$use_native = get_option( self::OPTION_USE_NATIVE, false );
							if ( $use_native ) {
								echo '<span style="color: green;">✓ WordPress Native</span>';
							} else {
								echo '<span style="color: orange;">⚠ Legacy (Yii Framework)</span>';
							}
							?>
						</td>
					</tr>
					<tr>
						<td><strong>TCPDF Library:</strong></td>
						<td>
							<?php
							$tcpdf_path = v_wpsa_PLUGIN_DIR . 'protected/extensions/tcpdf/tcpdf/tcpdf.php';
							if ( file_exists( $tcpdf_path ) ) {
								echo '<span style="color: green;">✓ Installed</span>';
							} else {
								echo '<span style="color: red;">✗ Missing</span>';
							}
							?>
						</td>
					</tr>
					<tr>
						<td><strong>Uploads Directory:</strong></td>
						<td>
							<?php
							$upload_dir = wp_upload_dir();
							if ( is_writable( $upload_dir['basedir'] ) ) {
								echo '<span style="color: green;">✓ Writable</span>';
								echo ' (' . esc_html( $upload_dir['basedir'] ) . ')';
							} else {
								echo '<span style="color: red;">✗ Not Writable</span>';
							}
							?>
						</td>
					</tr>
					<tr>
						<td><strong>Memory Limit:</strong></td>
						<td>
							<?php
							$memory_limit = ini_get( 'memory_limit' );
							echo esc_html( $memory_limit );
							$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );
							if ( $memory_bytes < 256 * 1024 * 1024 ) {
								echo ' <span style="color: orange;">⚠ Low (256M+ recommended for PDF generation)</span>';
							}
							?>
						</td>
					</tr>
					<tr>
						<td><strong>PHP Version:</strong></td>
						<td><?php echo esc_html( PHP_VERSION ); ?></td>
					</tr>
				</tbody>
			</table>

			<hr>

			<h2>Documentation</h2>
			<p>
				For detailed information about the migration, see 
				<a href="https://github.com/djav1985/v-wp-seo-audit/blob/main/MIGRATION_STATUS.md" target="_blank">MIGRATION_STATUS.md</a>
			</p>
		</div>
		<?php
	}
}
