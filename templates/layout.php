WARNING: The v-wpsa standard uses 2 deprecated sniffs
--------------------------------------------------------------------------------
-  Generic.Functions.CallTimePassByReference
   This sniff has been deprecated since v3.12.1 and will be removed in v4.0.0.
-  Squiz.WhiteSpace.LanguageConstructSpacing
   This sniff has been deprecated since v3.3.0 and will be removed in v4.0.0.
   Use the Generic.WhiteSpace.LanguageConstructSpacing sniff instead.

Deprecated sniffs are still run, but will stop working at some point in the
future.

WARNING: The v-wpsa standard uses 2 deprecated sniffs
--------------------------------------------------------------------------------
-  Generic.Functions.CallTimePassByReference
   This sniff has been deprecated since v3.12.1 and will be removed in v4.0.0.
-  Squiz.WhiteSpace.LanguageConstructSpacing
   This sniff has been deprecated since v3.3.0 and will be removed in v4.0.0.
   Use the Generic.WhiteSpace.LanguageConstructSpacing sniff instead.

Deprecated sniffs are still run, but will stop working at some point in the
future.

WARNING: The v-wpsa standard uses 2 deprecated sniffs
--------------------------------------------------------------------------------
-  Generic.Functions.CallTimePassByReference
   This sniff has been deprecated since v3.12.1 and will be removed in v4.0.0.
-  Squiz.WhiteSpace.LanguageConstructSpacing
   This sniff has been deprecated since v3.3.0 and will be removed in v4.0.0.
   Use the Generic.WhiteSpace.LanguageConstructSpacing sniff instead.

Deprecated sniffs are still run, but will stop working at some point in the
future.

WARNING: The v-wpsa standard uses 2 deprecated sniffs
--------------------------------------------------------------------------------
-  Generic.Functions.CallTimePassByReference
   This sniff has been deprecated since v3.12.1 and will be removed in v4.0.0.
-  Squiz.WhiteSpace.LanguageConstructSpacing
   This sniff has been deprecated since v3.3.0 and will be removed in v4.0.0.
   Use the Generic.WhiteSpace.LanguageConstructSpacing sniff instead.

Deprecated sniffs are still run, but will stop working at some point in the
future.

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
			<?php echo $content; ?>
		</div>
	</div>
</div>

<div class="container mt-3">
	<div class="row">
		<div class="col">
			<?php echo V_WPSA_Config::get( 'template.footer' ); ?>
		</div>
	</div>
</div>
