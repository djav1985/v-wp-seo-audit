<?php
/**
 * File: layout.php
 *
 * Description: Main layout template for plugin pages.
 *
 * @package v_wpsa
 * @author Vontainment
 * @license MIT
 * @license URI https://opensource.org/licenses/MIT
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
