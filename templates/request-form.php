<?php
/**
 * Request Form Template - WordPress Native
 *
 * Replaces protected/views/site/index.php with WordPress-safe template.
 *
 * @package V_WP_SEO_Audit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plugin_url      = esc_url( V_WP_SEO_AUDIT_PLUGIN_URL );
$app_name        = apply_filters( 'v_wp_seo_audit_app_name', 'V-WP-SEO-Audit' );
$placeholder     = apply_filters( 'v_wp_seo_audit_placeholder', 'Enter website URL...' );
$marketing_texts = apply_filters(
	'v_wp_seo_audit_marketing_texts',
	array(
		'content'  => 'Analyze your website content for SEO optimization. Get insights on text quality, keyword usage, and content structure.',
		'metatags' => 'Review all meta tags including title, description, and Open Graph tags to improve your search engine visibility.',
		'links'    => 'Extract and analyze all internal and external links. Identify broken links and optimize your link structure.',
		'speed'    => 'Test your website speed and performance. Get recommendations to improve loading times and user experience.',
		'advice'   => 'Receive professional advice on improving your website SEO. Get actionable recommendations based on best practices.',
		'review'   => 'Get a comprehensive review of your website. Identify strengths and weaknesses in your SEO strategy.',
	)
);
?>

<!-- Request Form Template -->
<div class="jumbotron">
	<h1><?php echo esc_html( $app_name ); ?></h1>
	<p class="lead mb-4">
		<?php echo esc_html( $app_name ); ?> is a free SEO tool which provides you content analysis of the website.
	</p>
	<form id="website-form">
		<div class="form-row">
			<div class="form-group col-md-6">
				<div class="input-group mb-3">
					<input type="text" name="Website[domain]" id="domain" class="form-control form-control-lg" placeholder="<?php echo esc_attr( $placeholder ); ?>">
					<div class="input-group-append">
						<button class="btn btn-primary" type="button" id="submit">
							<?php esc_html_e( 'Analyze', 'v-wp-seo-audit' ); ?>
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
		<h5 class="mb-3"><?php esc_html_e( 'Content analysis', 'v-wp-seo-audit' ); ?></h5>
		<img class="marketing-img float-left" src="<?php echo esc_url( $plugin_url . 'assets/img/content.png' ); ?>" alt="<?php esc_attr_e( 'Content analysis', 'v-wp-seo-audit' ); ?>" />
		<p>
			<?php echo esc_html( $marketing_texts['content'] ); ?>
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php esc_html_e( 'Meta Tags', 'v-wp-seo-audit' ); ?></h5>
		<img class="marketing-img float-left" src="<?php echo esc_url( $plugin_url . 'assets/img/tags.png' ); ?>" alt="<?php esc_attr_e( 'Meta Tags', 'v-wp-seo-audit' ); ?>" />
		<p>
			<?php echo esc_html( $marketing_texts['metatags'] ); ?>
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php esc_html_e( 'Link Extractor', 'v-wp-seo-audit' ); ?></h5>
		<img class="marketing-img float-left" src="<?php echo esc_url( $plugin_url . 'assets/img/link.png' ); ?>" alt="<?php esc_attr_e( 'Link Extractor', 'v-wp-seo-audit' ); ?>" />
		<p>
			<?php echo esc_html( $marketing_texts['links'] ); ?>
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php esc_html_e( 'Speed Test', 'v-wp-seo-audit' ); ?></h5>
		<img class="marketing-img float-left" src="<?php echo esc_url( $plugin_url . 'assets/img/speed.png' ); ?>" alt="<?php esc_attr_e( 'Speed Test', 'v-wp-seo-audit' ); ?>" />
		<p>
			<?php echo esc_html( $marketing_texts['speed'] ); ?>
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php esc_html_e( 'Get Advice', 'v-wp-seo-audit' ); ?></h5>
		<img class="marketing-img float-left" src="<?php echo esc_url( $plugin_url . 'assets/img/advice.png' ); ?>" alt="<?php esc_attr_e( 'Get Advice', 'v-wp-seo-audit' ); ?>" />
		<p>
			<?php echo esc_html( $marketing_texts['advice'] ); ?>
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php esc_html_e( 'Website Review', 'v-wp-seo-audit' ); ?></h5>
		<img class="marketing-img float-left" src="<?php echo esc_url( $plugin_url . 'assets/img/review.png' ); ?>" alt="<?php esc_attr_e( 'Website Review', 'v-wp-seo-audit' ); ?>" />
		<p>
			<?php echo esc_html( $marketing_texts['review'] ); ?>
		</p>
	</div>
</div>
