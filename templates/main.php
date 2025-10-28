<?php
/**
 * File: main.php
 *
 * Description: Main template for the SEO audit form.
 *
 * @package v_wpsa
 * @author Vontainment
 * @license MIT
 * @license URI https://opensource.org/licenses/MIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get plugin configuration.
$plugin_name = apply_filters( 'v_wpsa_plugin_name', 'SEO Audit by Vontainment' );
$placeholder = apply_filters( 'v_wpsa_placeholder', 'example.com' );
$base_url    = V_WPSA_PLUGIN_URL;
?>

<div class="jumbotron">
	<h2 class="h1"><?php echo esc_html( $plugin_name ); ?></h2>
	<p class="lead mb-4">
		<?php echo esc_html( $plugin_name ); ?> <?php esc_html_e( 'is a free SEO tool which provides you content analysis of the website.', 'v-wpsa' ); ?>
	</p>
	<form id="website-form">
		<div class="form-row">
			<div class="form-group col-md-6">
				<div class="input-group mb-3">
					<input type="text" name="Website[domain]" id="domain" class="form-control form-control-lg" placeholder="<?php echo esc_attr( $placeholder ); ?>">
					<div class="input-group-append">
						<button class="btn btn-primary" type="button" id="submit">
							<?php esc_html_e( 'Analyze', 'v-wpsa' ); ?>
						</button>
					</div>
				</div>

				<div class="alert alert-danger mb-0" id="errors" style="display: none"></div>

				<div class="clearfix"></div>

				<div id="progress-bar" class="progress" style="display: none">
					<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
				</div>
			</div>
		</div>
	</form>
</div>

<div class="row">
	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php esc_html_e( 'Content analysis', 'v-wpsa' ); ?></h5>
		<img class="marketing-img float-left" src="<?php echo esc_url( $base_url . 'assets/img/content.png' ); ?>" alt="<?php esc_attr_e( 'Content analysis', 'v-wpsa' ); ?>" />
		<p>
			<?php esc_html_e( 'Get detailed analysis of your website content including headings, images, and text structure.', 'v-wpsa' ); ?>
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php esc_html_e( 'Meta Tags', 'v-wpsa' ); ?></h5>
		<img class="marketing-img float-left" src="<?php echo esc_url( $base_url . 'assets/img/tags.png' ); ?>" alt="<?php esc_attr_e( 'Meta Tags', 'v-wpsa' ); ?>" />
		<p>
			<?php esc_html_e( 'Analyze your meta tags including title, description, and Open Graph properties.', 'v-wpsa' ); ?>
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php esc_html_e( 'Link Extractor', 'v-wpsa' ); ?></h5>
		<img class="marketing-img float-left" src="<?php echo esc_url( $base_url . 'assets/img/link.png' ); ?>" alt="<?php esc_attr_e( 'Link Extractor', 'v-wpsa' ); ?>" />
		<p>
			<?php esc_html_e( 'Extract and analyze all internal and external links on your website.', 'v-wpsa' ); ?>
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php esc_html_e( 'Speed Test', 'v-wpsa' ); ?></h5>
		<img class="marketing-img float-left" src="<?php echo esc_url( $base_url . 'assets/img/speed.png' ); ?>" alt="<?php esc_attr_e( 'Speed Test', 'v-wpsa' ); ?>" />
		<p>
			<?php esc_html_e( 'Check your website speed and get recommendations for improvement.', 'v-wpsa' ); ?>
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php esc_html_e( 'Get Advice', 'v-wpsa' ); ?></h5>
		<img class="marketing-img float-left" src="<?php echo esc_url( $base_url . 'assets/img/advice.png' ); ?>" alt="<?php esc_attr_e( 'Get Advice', 'v-wpsa' ); ?>" />
		<p>
			<?php esc_html_e( 'Receive actionable recommendations to improve your SEO performance.', 'v-wpsa' ); ?>
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php esc_html_e( 'Website Review', 'v-wpsa' ); ?></h5>
		<img class="marketing-img float-left" src="<?php echo esc_url( $base_url . 'assets/img/review.png' ); ?>" alt="<?php esc_attr_e( 'Website Review', 'v-wpsa' ); ?>" />
		<p>
			<?php esc_html_e( 'Get a comprehensive review of your website SEO health and performance.', 'v-wpsa' ); ?>
		</p>
	</div>
</div>

<div id="v-wpsa-latest-reviews-container"></div>
<script type="text/javascript">
	// Load latest reviews dynamically to prevent widget caching issues
	(function() {
		'use strict';
		// jQuery is enqueued by WordPress, so it should be available
		jQuery(document).ready(function($) {
			$.ajax({
				url: <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
				type: 'POST',
				data: {
					action: 'v_wpsa_load_latest_reviews',
					nonce: <?php echo wp_json_encode( wp_create_nonce( 'v_wpsa_nonce' ) ); ?>,
					_cache_bust: new Date().getTime()
				},
				dataType: 'json',
				success: function(response) {
					if (response && response.success && response.data && response.data.html) {
						$('#v-wpsa-latest-reviews-container').html(response.data.html);
					}
				},
				error: function() {
					// Silent fail - widget is optional, no need to alert users
					console.log('v-wpsa: Failed to load latest reviews widget');
				}
			});
		});
	})();
</script>
