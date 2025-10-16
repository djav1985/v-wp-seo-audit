<?php
/**
 * Layout Template
 * WordPress-native layout wrapper for reports.
 * Replaces protected/views/layouts/main.php
 *
 * Variables available:
 * - $content: Main content to display
 *
 * @package v_wpsa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- WordPress Plugin Layout - No <head> section needed, assets are enqueued via plugin file -->
<div class="container mt-3">
	<div class="row">
		<div class="col">
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $content is pre-sanitized HTML from report template
			echo $content;
			?>
		</div>
	</div>
</div>

<div class="container mt-3">
	<div class="row">
		<div class="col">
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Configuration value is trusted and may contain HTML
			echo V_WPSA_Config::get( 'template.footer' );
			?>
		</div>
	</div>
</div>
