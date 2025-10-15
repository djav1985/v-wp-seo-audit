<?php
/**
 * Layout Template
 * WordPress-native layout wrapper for reports.
 * Replaces protected/views/layouts/main.php
 *
 * Variables available:
 * - $content: Main content to display
 *
 * @package V_WP_SEO_Audit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- WordPress Plugin Layout - No <head> section needed, assets are enqueued via plugin file -->
<div class="container mt-3">
	<div class="row">
		<div class="col">
			<?php echo $content; ?>
		</div>
	</div>
</div>

<div class="container mt-3">
	<div class="row">
		<div class="col">
			<?php echo V_WPSA_Config::get('template.footer'); ?>
			<?php echo V_WPSA_Config::get('pagepeeker.verify'); ?>
		</div>
	</div>
</div>
