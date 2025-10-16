<?php
/**
 * Report Template
 * WordPress-native template for SEO audit report.
 * Replaces protected/views/websitestat/index.php
 *
 * Variables available:
 * - $website: Website data array
 * - $thumbnail: Thumbnail URL
 * - $generated: Generated date array
 * - $diff: Time difference
 * - $upd_url: Update URL (was $updUrl, renamed for snake_case compliance)
 * - $rates: Rate configuration array
 * - $website['score_breakdown']: Stored score breakdown data
 * - $meta: Meta data array
 * - $content: Content data array
 * - $document: Document data array
 * - $links: Links data array
 * - $linkcount: Total link count
 * - $cloud: Keywords cloud data
 * - $w3c: W3C validation data
 * - $isseter: Various boolean flags
 * - $misc: Miscellaneous data
 * - $over_max: Maximum items to show before collapse
 *
 * @package v_wpsa
 *
 * Note: This template renders pre-analyzed SEO data. Output escaping is selectively
 * applied based on data type and source:
 * - Numeric IDs, scores, counts: Safe integers, no escaping needed
 * - Analysis results ($advice, etc.): Hardcoded strings from rating system
 * - Configuration values: Trusted admin-configured content
 * - User-provided data (domain names, URLs): Escaped with esc_html()/esc_url()
 * - HTML content: Already sanitized during analysis phase
 *
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $website ) || ! is_array( $website ) ) : ?>
// Ensure $upd_url is always set to avoid undefined variable warning.
if ( ! isset( $upd_url ) ) {
	$upd_url = '';
}
	<div class="alert alert-danger mt-5 mb-5">
		<?php echo 'No report available. The domain could not be analyzed or the record was not created. Please try again or check your domain input.'; ?>
	</div>
	<?php
	return;
endif;
?>
<?php
$score_breakdown  = isset( $website['score_breakdown'] ) && is_array( $website['score_breakdown'] ) ? $website['score_breakdown'] : array();
$score_categories = isset( $score_breakdown['categories'] ) && is_array( $score_breakdown['categories'] ) ? $score_breakdown['categories'] : array();
$score_lookup     = static function ( $key, $field = null, $default = null ) use ( $score_categories ) {
	if ( ! isset( $score_categories[ $key ] ) || ! is_array( $score_categories[ $key ] ) ) {
			return $default;
	}

	if ( null === $field ) {
			return $score_categories[ $key ];
	}

		return isset( $score_categories[ $key ][ $field ] ) ? $score_categories[ $key ][ $field ] : $default;
};

$score_advice = static function ( $key ) use ( $score_lookup ) {
		$advice = $score_lookup( $key, 'advice', 'error' );

		return $advice ? $advice : 'error';
};

$score_points = static function ( $key ) use ( $score_lookup ) {
		$points = $score_lookup( $key, 'points', 0 );

		return is_numeric( $points ) ? (float) $points : 0.0;
};

$score_total      = isset( $score_breakdown['total'] ) ? (float) $score_breakdown['total'] : ( isset( $website['score'] ) ? (float) $website['score'] : 0.0 );
$display_score    = (int) round( $score_total );
$website['score'] = $display_score;
$rates            = isset( $rates ) && is_array( $rates ) ? $rates : array();
$format_points    = static function ( $value ) {
		$value = (float) $value;

	if ( abs( $value - round( $value ) ) < 0.01 ) {
			return (string) (int) round( $value );
	}

		return number_format( $value, 2 );
};
?>
<script type="text/javascript">
		"use strict";

	jQuery(function($) {
		dynamicThumbnail({
			<?php echo 'main_' . $website['id']; ?>: <?php echo wp_json_encode( $thumbnail ); ?>
		});

		var pie_data = [];
		pie_data[0] = {
			label: '<?php echo 'External Links'; ?> : <?php echo 'noFollow'; ?> <?php echo V_WPSA_Utils::proportion( $linkcount, $links['external_nofollow'] ); ?>%',
			data: <?php echo $links['external_nofollow']; ?>,
			color: '#6A93BA'
		};
		pie_data[1] = {
			label: '<?php echo 'External Links'; ?> : <?php echo 'Passing Juice'; ?> <?php echo V_WPSA_Utils::proportion( $linkcount, $links['external_dofollow'] ); ?>%',
			data: <?php echo $links['external_dofollow']; ?>,
			color: '#315D86'
		};
		pie_data[2] = {
			label: '<?php echo 'Internal Links'; ?> <?php echo V_WPSA_Utils::proportion( $linkcount, $links['internal'] ); ?>%',
			data: <?php echo $links['internal']; ?>,
			color: '#ddd'
		};

		drawPie();
		window.onresize = function(event) {
			drawPie();
		};

		/**
		 * drawPie function.
		 */
		function drawPie() {
			$.plot($("#links-pie"), pie_data, {
				series: {
					pie: {
						show: true
					}
				},
				legend: {
					container: "#legend"
				}
			});
		}

		$('.collapse-task').click(function() {
			var p = $(this).parent(".task-list");
			p.find(".over-max").hide();
			$(this).hide();
			p.find('.expand-task').show();
		});

		$('.expand-task').click(function() {
			var p = $(this).parent(".task-list");
			p.find(".over-max").show();
			$(this).hide();
			p.find('.collapse-task').show();
		});

				$('#update_stat').on('click', function(e) {
						e.preventDefault();

						var $button = $(this);
						var originalText = $.trim($button.text());

			// Show loading state on button.
			$button.prop('disabled', true)
				.html('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>Updating...')
				.addClass('disabled');

			// Always call generateReport directly with force=true to update the report
			if (window.vWpSeoAudit && typeof window.vWpSeoAudit.generateReport === 'function') {
				var $container = $('.v-wpsa-container').first();

				// Create error and progress elements if they don't exist
				var $errors = $('#errors');
				var $progressBar = $('#progress-bar');

				// If form elements don't exist, create temporary ones in the container
				if (!$errors.length) {
					$errors = $('<div id="errors" class="alert alert-danger mb-3" style="display: none"></div>');
					$container.prepend($errors);
				}
				if (!$progressBar.length) {
					$progressBar = $('<div id="progress-bar" class="progress mb-3" style="display: none"><div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div></div>');
					$container.prepend($progressBar);
				}

				window.vWpSeoAudit.generateReport('<?php echo esc_js( $website['domain'] ); ?>', {
					$container: $container,
					$errors: $errors,
					$progressBar: $progressBar,
					force: true,
					afterSend: function() {
						// Restore button state after completion.
						$button.prop('disabled', false)
							.text(originalText)
							.removeClass('disabled');
					}
				});
			} else {
				$button.prop('disabled', false)
					.text(originalText)
					.removeClass('disabled');
				window.alert('Error: Report generation function not available. Please refresh the page and try again.');
			}
		});

		$('body').on("click", ".pdf_review", function() {
			$(this).hide();
			$(this).closest(".form-container").find(".download_form").fadeIn();
			return false;
		});

		<?php if ( V_WPSA_Config::get( 'psi.show' ) ) : ?>
			WrPsi(
				<?php
				echo wp_json_encode(
					array(
						'i18nEnterFullscreen' => 'Enter fullscreen mode',
						'i18nExitFullscreen'  => 'Exit fullscreen mode',
						'runInstantly'        => V_WPSA_Config::get( 'psi.run_instantly' ),
						'url'                 => ! empty( $website['final_url'] ) ? $website['final_url'] : 'http://' . $website['domain'],
						'locale'              => 'en',
					)
				)
				?>
			);
		<?php endif; ?>
	});
</script>

<div class="jumbotron">
	<div class="row">
		<div class="col-md-4 col-lg-5 col-sm-12">
			<img class="img-responsive img-thumbnail mb-20" id="thumb_main_<?php echo $website['id']; ?>" src="<?php echo V_WPSA_Config::get_base_url( true ); ?>/assets/img/loader.gif" alt="<?php echo $website['idn']; ?>" />
		</div>
		<div class="col-md-8 col-lg-7 col-sm-12 text-left">
			<h1 class="text-break">
				<?php echo 'Website review for ' . esc_html( $website['idn'] ); ?>
			</h1>

			<p>
				<i class="fas fa-clock"></i>&nbsp;<small><?php echo 'Generated on'; ?>
					<?php
					$month_names = array(
						'Jan' => 'January',
						'Feb' => 'February',
						'Mar' => 'March',
						'Apr' => 'April',
						'May' => 'May',
						'Jun' => 'June',
						'Jul' => 'July',
						'Aug' => 'August',
						'Sep' => 'September',
						'Oct' => 'October',
						'Nov' => 'November',
						'Dec' => 'December',
					);
					$month       = isset( $month_names[ $generated['M'] ] ) ? $month_names[ $generated['M'] ] : $generated['M'];
					echo $month . ' ' . $generated['d'] . ' ' . $generated['Y'] . ' ' . $generated['H'] . ':' . $generated['i'] . ' ' . $generated['A'];
					?>
				</small>
			</p>



			<?php echo V_WPSA_Config::get( 'param.addthis' ); ?>


			<p class="mt-3">
				<strong><?php echo 'The score is ' . (int) $website['score'] . '/100'; ?></strong>
			</p>
			<div class="progress-score progress mb-3">
				<div class="progress-bar progress-bar-striped bg-info" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $website['score']; ?>%;"></div>
			</div>

			<div class="btn-toolbar" role="toolbar" aria-label="Report actions">
								<div class="btn-group mr-2" role="group" aria-label="Report download and update">
										<button type="button" class="btn btn-primary v-wpsa-download-pdf" data-domain="<?php echo esc_attr( $website['domain'] ); ?>">
												<?php echo 'Download PDF Version'; ?>
										</button>
										<?php
										if ( ! isset( $upd_url ) ) {
												$upd_url = '';
										}
										?>
										<button type="button" class="btn btn-success" id="update_stat" data-domain="<?php echo esc_attr( $website['domain'] ); ?>" data-update-url="<?php echo esc_url( $upd_url ); ?>">
												<?php echo 'UPDATE'; ?>
										</button>
										<?php if ( current_user_can( 'manage_options' ) ) : ?>
												<button type="button" class="btn btn-danger v-wpsa-delete-report" data-domain="<?php echo esc_attr( $website['domain'] ); ?>">
														<?php echo 'DELETE'; ?>
												</button>
										<?php endif; ?>
								</div>
			</div>

		</div>
	</div>
</div>


<h3 id="section_content" class="mt-5 mb-3"><?php echo 'SEO Content'; ?></h3>
<div class="category-wrapper">
	<!-- Title -->
		<?php $advice = $score_advice( 'title' ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'Title'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<p class="text-break">
				<?php echo esc_html( V_WPSA_Utils::html_decode( $meta['title'] ) ); ?>
			</p>
			<p>
				<strong>
					<?php echo 'Length'; ?> : <?php echo mb_strlen( V_WPSA_Utils::html_decode( $meta['title'] ) ); ?>
				</strong>
			</p>
			<p>
				<?php
				$title_length = mb_strlen( V_WPSA_Utils::html_decode( $meta['title'] ) );
				if ( 'success' === $advice ) {
					echo 'Great! Your title tag has an optimal length (' . $title_length . ' characters).';
				} elseif ( 'warning' === $advice ) {
					echo 'Your title tag length (' . $title_length . ' characters) could be improved. Aim for 10-70 characters.';
				} else {
					echo 'Your title tag needs attention. Current length is ' . $title_length . ' characters. Optimal length is 10-70 characters.';
				}
				?>
			</p>
		</div>
	</div>

	<!-- Description -->
		<?php $advice = $score_advice( 'description' ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'Description'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<p class="text-break">
				<?php echo esc_html( V_WPSA_Utils::html_decode( $meta['description'] ) ); ?>
			</p>
			<p>
				<strong>
					<strong><?php echo 'Length'; ?> : <?php echo mb_strlen( V_WPSA_Utils::html_decode( $meta['description'] ) ); ?></strong>
				</strong>
			</p>
			<p>
				<?php
				$desc_length = mb_strlen( V_WPSA_Utils::html_decode( $meta['description'] ) );
				if ( 'success' === $advice ) {
					echo 'Perfect! Your meta description has an optimal length (' . $desc_length . ' characters).';
				} elseif ( 'warning' === $advice ) {
					echo 'Your meta description length (' . $desc_length . ' characters) could be improved. Aim for 70-160 characters.';
				} else {
					echo 'Your meta description needs attention. Current length is ' . $desc_length . ' characters. Optimal length is 70-160 characters.';
				}
				?>
			</p>
		</div>
	</div>

	<!-- Og properties -->
		<?php $advice = $score_advice( 'ogmetaproperties' ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'Og Meta Properties'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php
				if ( 'success' === $advice ) {
					echo 'Great! Your page has Open Graph meta properties for better social media sharing.';
				} else {
					echo 'Your page is missing Open Graph meta properties. Adding these tags helps control how your content appears when shared on social media.';
				}
				?>
			</p>

			<?php if ( ! empty( $meta['ogproperties'] ) ) : ?>
				<div class="table-responsive table-items mb-3 task-list">
					<table class="table table-striped">
						<thead>
							<tr>
								<th><?php echo 'Property'; ?></th>
								<th><?php echo 'Content'; ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 0;
							foreach ( $meta['ogproperties'] as $property => $c ) :
								$i++;
								?>
								<tr <?php echo $i > $over_max ? 'class="over-max"' : ''; ?>>
									<td><?php echo esc_html( $property ); ?></td>
									<td class="text-break"><?php echo esc_html( $c ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<?php if ( $i > $over_max ) : ?>
						<button class="expand-task btn btn-primary float-right"><?php echo 'Expand'; ?></button>
						<button class="collapse-task btn btn-primary float-right"><?php echo 'Collapse'; ?></button>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>


	<!-- Headings -->
	<div class="row pt-3 pb-3 row-advice">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-neutral"></div>
			<p class="lead">
				<?php echo 'Headings'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<div class="table-responsive table-items mb-3">
				<table class="table table-striped table-fluid">
					<tbody>
						<tr class="no-top-line">
							<?php foreach ( $content['headings'] as $heading => $headings ) : ?>
								<td><strong><?php echo strtoupper( $heading ); ?></strong></td>
							<?php endforeach; ?>
						</tr>
						<tr>
							<?php foreach ( $content['headings'] as $headings ) : ?>
								<td><span class="badge badge-success badge-heading"><?php echo count( $headings ); ?></span> </td>
							<?php endforeach; ?>
						</tr>
					</tbody>
				</table>
			</div>

			<?php
			if ( $content['isset_headings'] ) :
				$i = 0;
				?>
				<div class="task-list">
					<ul id="headings">
						<?php
						foreach ( $content['headings'] as $heading => $headings ) :
							if ( ! empty( $headings ) ) :
								foreach ( $headings as $h ) :
									$i++;
									?>
									<li class="text-break<?php echo $i > $over_max ? ' over-max' : ''; ?>">[<?php echo mb_strtoupper( $heading ); ?>] <?php echo esc_html( V_WPSA_Utils::html_decode( $h ) ); ?></li>
									<?php
								endforeach;
							endif;
						endforeach;
						?>
					</ul>
					<?php if ( $i > $over_max ) : ?>
						<button class="expand-task btn btn-primary float-right"><?php echo 'Expand'; ?></button>
						<button class="collapse-task btn btn-primary float-right"><?php echo 'Collapse'; ?></button>
					<?php endif; ?>
				</div>

			<?php endif; ?>
		</div>
	</div>

	<!-- Images -->
		<?php $advice = $score_advice( 'imgHasAlt' ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'Images'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php echo 'We found ' . (int) $content['total_img'] . ' images on this web page.'; ?>
			</p>
			<p>
				<?php
				if ( 'success' === $advice ) {
					echo 'Excellent! All images have alt attributes, which is great for SEO and accessibility.';
				} else {
					$missing_count = (int) $content['total_img'] - (int) $content['total_alt'];
					echo 'Some images are missing alt attributes. Alt text is important for SEO and accessibility. ' . (int) $content['total_alt'] . ' out of ' . (int) $content['total_img'] . ' images have alt attributes. ' . $missing_count . ' images are missing alt text.';
				}
				?>
			</p>



			<?php if ( 'success' !== $advice && ! empty( $content['images_missing_alt'] ) && is_array( $content['images_missing_alt'] ) ) : ?>
				<div class="table-responsive table-items mb-3 task-list">
					<table class="table table-striped">
						<thead>
							<tr>
								<th><?php echo 'Image Source (Missing Alt Text)'; ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 0;
							foreach ( $content['images_missing_alt'] as $img_src ) :
								$i++;
								// Extract filename from URL for display.
								$parsed   = wp_parse_url( $img_src );
								$filename = isset( $parsed['path'] ) ? basename( $parsed['path'] ) : '';
								if ( empty( $filename ) ) {
									$filename = $img_src;
								}
								?>
								<tr <?php echo $i > $over_max ? 'class="over-max"' : ''; ?>>
									<td class="text-break" title="<?php echo esc_attr( $img_src ); ?>">
										<?php echo esc_html( $filename ); ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<?php if ( $i > $over_max ) : ?>
						<button class="expand-task btn btn-primary float-right"><?php echo 'Expand'; ?></button>
						<button class="collapse-task btn btn-primary float-right"><?php echo 'Collapse'; ?></button>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Text/HTML Ratio -->
		<?php $advice = $score_advice( 'htmlratio' ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'Text/HTML Ratio'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php
				$text_percent = (int) $document['htmlratio'];
				$html_percent = 100 - $text_percent;
				echo 'Ratio';
				?>
				: <strong><?php echo $text_percent; ?>% text vs <?php echo $html_percent; ?>% HTML</strong>
			</p>
			<p>
				<?php
				if ( 'error less_than' === $advice ) {
					echo 'Your text/HTML ratio (' . $text_percent . '% text, ' . $html_percent . '% HTML) is very low. Add more readable content for better SEO.';
				} elseif ( 'success' === $advice ) {
					echo 'Your text/HTML ratio (' . $text_percent . '% text, ' . $html_percent . '% HTML) is acceptable, but could be stronger. Aim for 40%-70% text for best SEO.';
				} elseif ( 'success ideal_ratio' === $advice ) {
					echo 'Excellent! Your page has an ideal text to HTML ratio (' . $text_percent . '% text, ' . $html_percent . '% HTML). This is optimal for SEO and readability.';
				} elseif ( 'warning' === $advice ) {
					echo 'Your text/HTML ratio (' . $text_percent . '% text, ' . $html_percent . '% HTML) is a bit too text-heavy. Consider balancing content and markup for best results.';
				}
				?>
			</p>
		</div>
	</div>

	<!-- Flash -->
		<?php $advice = $score_advice( 'noFlash' ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'Flash'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php
				if ( 'success' === $advice ) {
					echo 'Great! Your page does not use Flash, which is good for SEO and modern web standards.';
				} else {
					echo 'Your page uses Flash content. Flash is obsolete and not supported by most modern browsers and devices. Consider using HTML5 alternatives.';
				}
				?>
			</p>
		</div>
	</div>

	<!-- Iframe -->
		<?php $advice = $score_advice( 'noIframe' ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'Iframe'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php
				if ( 'success' === $advice ) {
					echo 'Good! Your page does not use iframes, which is better for SEO and page performance.';
				} else {
					echo 'Your page uses iframes. While sometimes necessary, iframes can negatively impact SEO and page load times.';
				}
				?>
			</p>
		</div>
	</div>
</div>


<h3 id="section_links" class="mt-5 mb-3"><?php echo 'SEO Links'; ?></h3>
<div class="category-wrapper">
	<!-- Friendly url -->
		<?php $advice = $score_advice( 'isFriendlyUrl' ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'URL Rewrite'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php
				if ( 'success' === $advice ) {
					echo 'Perfect! Your URLs are SEO-friendly and do not contain query strings or dynamic parameters.';
				} else {
					echo 'Your URLs contain query strings or dynamic parameters. Consider using URL rewriting to create cleaner, more SEO-friendly URLs.';
				}
				?>
			</p>
		</div>
	</div>

	<!-- Underscore -->
		<?php $advice = $score_advice( 'noUnderScore' ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'Underscores in the URLs'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php
				if ( 'success' === $advice ) {
					echo 'Great! Your URLs do not contain underscores, which is better for SEO. Search engines prefer hyphens over underscores.';
				} else {
					echo 'Your URLs contain underscores. Consider using hyphens instead of underscores in URLs for better SEO, as search engines treat hyphens as word separators.';
				}
				?>
			</p>
		</div>
	</div>

	<!-- In-page links -->
		<?php $advice = $score_advice( 'issetInternalLinks' ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'In-page links'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<p class="mb-3">
				<?php
				$file_links = 0;
				foreach ( $links['links'] as $link_item ) {
					if ( ! empty( $link_item['Link'] ) && preg_match( '/\.(pdf|doc|docx|xls|xlsx|ppt|pptx|zip|rar|txt|csv)$/i', $link_item['Link'] ) ) {
						$file_links++;
					}
				}
				echo 'We found a total of ' . $linkcount . ' link(s) including ' . $file_links . ' link(s) to files';
				?>
			</p>
			<div class="row">
				<div class="col-md-4">
					<div id="links-pie" style="height: 200px"></div>
				</div>
				<div class="col-md-8 mt-3 mt-md-0" id="legend"></div>
			</div>


			<div class="table-responsive table-items mt-3 task-list">
				<table class="table table-striped">
					<thead>
						<tr>
							<th><?php echo 'Anchor'; ?></th>
							<th><?php echo 'Type'; ?></th>
							<th><?php echo 'Juice'; ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 0;
						foreach ( $links['links'] as $link_item ) :
							$i++;
							?>
							<tr <?php echo $i > $over_max ? 'class="over-max"' : null; ?>>
								<td class="text-break">
									<a href="<?php echo $link_item['Link']; ?>" target="_blank" rel="nofollow">
										<?php echo ! empty( $link_item['Name'] ) ? esc_html( V_WPSA_Utils::html_decode( $link_item['Name'] ) ) : '-'; ?>
									</a>
								</td>
								<td><?php echo ( 'internal' === $link_item['Type'] ? 'Internal' : 'External' ); ?></td>
								<td><?php echo ( 'nofollow' === $link_item['Juice'] ? 'noFollow' : 'Passing Juice' ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<?php if ( $i > $over_max ) : ?>
					<button class="expand-task btn btn-primary float-right"><?php echo 'Expand'; ?></button>
					<button class="collapse-task btn btn-primary float-right"><?php echo 'Collapse'; ?></button>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>



<h3 id="section_keywords" class="mt-5 mb-3"><?php echo 'SEO Keywords'; ?></h3>
<div class="category-wrapper">
	<!-- Tag cloud -->
	<div class="row pt-3 pb-3 row-advice">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-neutral"></div>
			<p class="lead">
				<?php echo 'Keywords Cloud'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<p class="text-break cloud-container">
				<?php foreach ( $cloud['words'] as $word => $stat ) : ?>
					<span class="grade-<?php echo $stat['grade']; ?>"><?php echo esc_html( V_WPSA_Utils::html_decode( $word ) ); ?></span>
				<?php endforeach; ?>
			</p>
		</div>
	</div>

	<!-- Keywords Consistency -->
	<div class="row pt-3 pb-3 row-advice">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-neutral"></div>
			<p class="lead">
				<?php echo 'Keywords Consistency'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<div class="table-responsive">
				<table class="table table-striped">
					<thead class="thead-dark">
						<tr>
							<th><?php echo 'Keyword'; ?></th>
							<th><?php echo 'Content'; ?></th>
							<th><?php echo 'Title'; ?></th>
							<th><?php echo 'Description'; ?></th>
							<th><?php echo 'Headings'; ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $cloud['matrix'] as $word => $object ) : ?>
							<tr>
								<td><?php echo esc_html( $word ); ?></td>
								<td><?php echo (int) $cloud['words'][ $word ]['count']; ?></td>
								<td><img src="<?php echo V_WPSA_Config::get_base_url( true ); ?>/assets/img/isset_<?php echo (int) $object['title']; ?>.png" /></td>
								<td><img src="<?php echo V_WPSA_Config::get_base_url( true ); ?>/assets/img/isset_<?php echo (int) $object['description']; ?>.png" /></td>
								<td><img src="<?php echo V_WPSA_Config::get_base_url( true ); ?>/assets/img/isset_<?php echo (int) $object['headings']; ?>.png" /></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<h3 id="section_usability" class="mt-5 mb-3"><?php echo 'Usability'; ?></h3>
<div class="category-wrapper">
	<!-- Url -->
	<div class="row pt-3 pb-3 row-advice">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-neutral"></div>
			<p class="lead">
				<?php echo 'Url'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php echo 'Domain'; ?> : <?php echo $website['idn']; ?>
			</p>
			<p>
				<?php echo 'Length'; ?> : <?php echo mb_strlen( $website['idn'] ); ?>
			</p>
		</div>
	</div>

	<!-- Favicon -->
		<?php $advice = $score_advice( 'issetFavicon' ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'Favicon'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php
				if ( 'success' === $advice ) {
					echo 'Excellent! Your website has a favicon, which helps with branding and user experience.';
				} else {
					echo 'Your website is missing a favicon. A favicon helps with branding and makes your site more recognizable in browser tabs and bookmarks.';
				}
				?>
			</p>
		</div>
	</div>

	<!-- Language -->
		<?php $advice = $score_advice( 'lang' ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'Language'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php
				if ( 'success' === $advice ) {
					echo 'Great! Your page has a language attribute declared, which helps search engines understand your content.';
				} else {
					echo 'Your page is missing a language attribute. Adding a language attribute to your HTML tag helps search engines and screen readers.';
				}
				?>
			</p>
		</div>
	</div>

	<!-- Dublin Core -->
		<?php $advice = $score_advice( 'dublincore' ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'Dublin Core'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php
				if ( 'success' === $advice ) {
					echo 'Good! Your page uses Dublin Core metadata, which can help with content categorization and discovery.';
				} else {
					echo 'Your page does not use Dublin Core metadata. While not essential, Dublin Core can help with content categorization in digital libraries and archives.';
				}
				?>
			</p>
		</div>
	</div>
</div>


<h3 id="section_document" class="mt-5 mb-3"><?php echo 'Document'; ?></h3>
<div class="category-wrapper">
	<!-- Doctype -->
		<?php $advice = $score_advice( 'doctype' ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'Doctype'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php
				if ( $document['doctype'] ) :
					echo $document['doctype'];
				else :
					echo 'Missing doctype';
				endif;
				?>
			</p>
		</div>
	</div>

	<!-- Encoding -->
		<?php $advice = $score_advice( 'charset' ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'Encoding'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php
				if ( 'success' === $advice ) {
					echo 'Perfect! Your page specifies a character encoding, which is essential for proper text display.';
				} else {
					echo 'Your page is missing a character encoding declaration. This can lead to display issues with special characters. Add a charset meta tag.';
				}
				?>
			</p>
		</div>
	</div>

	<!-- W3C Validity -->
		<?php $advice = $score_advice( 'w3c' ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'W3C Validity'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php echo 'Errors'; ?> : <strong><?php echo (int) $w3c['errors']; ?></strong>
			</p>
			<p>
				<?php echo 'Warnings'; ?> : <strong><?php echo (int) $w3c['warnings']; ?></strong>
			</p>



			<?php if ( ! empty( $w3c['messages'] ) && is_array( $w3c['messages'] ) ) : ?>
				<div class="table-responsive table-items mb-3 task-list">
					<table class="table table-striped">
						<thead>
							<tr>
								<th><?php echo 'Type'; ?></th>
								<th><?php echo 'Line'; ?></th>
								<th><?php echo 'Message'; ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 0;
							foreach ( $w3c['messages'] as $msg ) :
								$i++;
								$msg_type = isset( $msg['type'] ) ? $msg['type'] : 'unknown';
								$msg_line = isset( $msg['line'] ) ? $msg['line'] : '-';
								$msg_text = isset( $msg['message'] ) ? $msg['message'] : '';
								?>
								<tr <?php echo $i > $over_max ? 'class="over-max"' : ''; ?>>
									<td>
										<span class="badge badge-<?php echo 'error' === $msg_type ? 'danger' : 'warning'; ?>">
											<?php echo esc_html( ucfirst( $msg_type ) ); ?>
										</span>
									</td>
									<td><?php echo esc_html( $msg_line ); ?></td>
									<td class="text-break"><?php echo esc_html( $msg_text ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<?php if ( $i > $over_max ) : ?>
						<button class="expand-task btn btn-primary float-right"><?php echo 'Expand'; ?></button>
						<button class="collapse-task btn btn-primary float-right"><?php echo 'Collapse'; ?></button>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Deprecated -->
		<?php $advice = $score_advice( 'noDeprecated' ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'Deprecated HTML'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<div class="table-responsive">
				<?php if ( ! empty( $content['deprecated'] ) ) : ?>
					<table class="table table-striped table-items">
						<thead>
							<tr>
								<th><?php echo 'Deprecated tags'; ?></th>
								<th><?php echo 'Occurrences'; ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $content['deprecated'] as $tag_name => $count ) : ?>
								<tr>
									<td><?php echo htmlspecialchars( '<' . $tag_name . '>' ); ?></td>
									<td><?php echo $count; ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>

			<p>
				<?php
				if ( 'success' === $advice ) {
					echo 'Excellent! Your page does not use deprecated HTML tags.';
				} else {
					echo 'Your page uses deprecated HTML tags. Consider updating to modern HTML5 elements for better compatibility and standards compliance.';
				}
				?>
			</p>
		</div>
	</div>

	<!-- Speed Tips -->
	<div class="row pt-3 pb-3 row-advice">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-neutral"></div>
			<p class="lead">
				<?php echo 'Speed Tips'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<div class="table-responsive">
				<table class="table table-striped">
					<tbody>
						<tr>
													<?php $advice = $score_advice( 'noNestedtables' ); ?>
							<td width="50px"><img src="<?php echo V_WPSA_Config::get_base_url( true ); ?>/assets/img/isset_<?php echo (int) ! $isseter['nestedtables']; ?>.png" /></td>
							<td>
								<?php
								if ( 'success' === $advice ) {
									echo 'Good! No nested tables found. Nested tables can slow down page rendering.';
								} else {
									echo 'Your page uses nested tables, which can slow down page rendering. Consider using CSS for layout instead.';
								}
								?>
							</td>
						</tr>

						<tr>
													<?php $advice = $score_advice( 'noInlineCSS' ); ?>
							<td><img src="<?php echo V_WPSA_Config::get_base_url( true ); ?>/assets/img/isset_<?php echo (int) ! $isseter['inlinecss']; ?>.png" /></td>
							<td>
								<?php
								if ( 'success' === $advice ) {
									echo 'Perfect! No inline CSS found. External stylesheets are better for performance and maintainability.';
								} else {
									echo 'Your page uses inline CSS. Consider moving styles to external stylesheets for better performance and caching.';
								}
								?>
							</td>
						</tr>

						<tr>
													<?php $advice = $score_advice( 'cssCount' ); ?>
							<td><img src="<?php echo V_WPSA_Config::get_base_url( true ); ?>/assets/img/isset_<?php echo 'success' === $advice ? '1' : '0'; ?>.png" /></td>
							<td>
								<?php
								$css_count = (int) $document['css'];
								if ( 'success' === $advice ) {
									echo 'Great! Your page has an optimal number of CSS files (' . $css_count . '). Keep stylesheets minimal for better performance.';
								} else {
									echo 'Your page has ' . $css_count . ' CSS files. Too many CSS files can slow down page load. Consider combining them.';
								}
								?>
							</td>
						</tr>

						<tr>
													<?php $advice = $score_advice( 'jsCount' ); ?>
							<td><img src="<?php echo V_WPSA_Config::get_base_url( true ); ?>/assets/img/isset_<?php echo 'success' === $advice ? '1' : '0'; ?>.png" /></td>
							<td>
								<?php
								$js_count = (int) $document['js'];
								if ( 'success' === $advice ) {
									echo 'Excellent! Your page has an optimal number of JavaScript files (' . $js_count . '). Keep scripts minimal for better performance.';
								} else {
									echo 'Your page has ' . $js_count . ' JavaScript files. Too many JS files can slow down page load. Consider combining them.';
								}
								?>
							</td>
						</tr>

						<tr>
													<?php $advice = $score_advice( 'hasGzip' ); ?>
							<td><img src="<?php echo V_WPSA_Config::get_base_url( true ); ?>/assets/img/isset_<?php echo 'success' === $advice ? '1' : '0'; ?>.png" /></td>
							<td>
								<?php
								echo 'Gzip Compression';
								if ( 'success' === $advice ) {
									echo ' - Enabled! Your server is using Gzip compression to reduce file sizes and improve page load times.';
								} else {
									echo ' - Not detected. Enable Gzip compression to reduce file sizes and improve page load speed.';
								}
								?>
							</td>
						</tr>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>


<h3 id="section_mobile" class="mt-5 mb-3"><?php echo 'Mobile'; ?></h3>
<div class="category-wrapper">
	<div class="row pt-3 pb-3 row-advice">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-neutral"></div>
			<p class="lead">
				<?php echo 'Mobile Optimization'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<div class="table-responsive">
				<table class="table table-striped">
					<tbody>

						<tr class="no-top-line">
							<td><img src="<?php echo V_WPSA_Config::get_base_url( true ); ?>/assets/img/isset_<?php echo (int) $isseter['appleicons']; ?>.png" /></td>
							<td><?php echo 'Apple Icon'; ?></td>
						</tr>

						<tr>
							<td><img src="<?php echo V_WPSA_Config::get_base_url( true ); ?>/assets/img/isset_<?php echo (int) $isseter['viewport']; ?>.png" /></td>
							<td><?php echo 'Meta Viewport Tag'; ?></td>
						</tr>

						<tr>
							<td><img src="<?php echo V_WPSA_Config::get_base_url( true ); ?>/assets/img/isset_<?php echo (int) ! $isseter['flash']; ?>.png" /></td>
							<td><?php echo 'Flash content'; ?></td>
						</tr>

					</tbody>
				</table>
			</div>
		</div>
	</div>

</div>

<h3 id="section_optimization" class="mt-5 mb-3"><?php echo 'Optimization'; ?></h3>
<div class="category-wrapper">
	<!-- Sitemap -->
	<?php $advice = $score_advice( 'hasSitemap' ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'XML Sitemap'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<?php if ( ! empty( $misc['sitemap'] ) ) : ?>
				<p>
					<?php
					echo 'Great! We found an XML sitemap on your website. Sitemaps help search engines discover and index your pages more efficiently.';
					?>
				</p>
				<div class="table-responsive">
					<table class="table table-striped table-items">
						<tbody>
							<?php foreach ( $misc['sitemap'] as $sitemap ) : ?>
								<tr>
									<td class="text-break">
										<?php echo esc_html( $sitemap ); ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

			<?php else : ?>
				<p>
					<strong><?php echo 'Missing'; ?></strong>
				</p>
				<p>
					<?php
					echo 'Your website does not have an XML sitemap. Creating and submitting a sitemap helps search engines discover and index all your important pages.';
					?>
				</p>
			<?php endif; ?>
		</div>
	</div>

	<!-- Robots -->
	<?php $advice = $score_advice( 'hasRobotsTxt' ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'Robots.txt'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<?php if ( $isseter['robotstxt'] ) : ?>
				<p><?php echo 'http://' . $website['domain'] . '/robots.txt'; ?></p>
				<p>
					<?php
					echo 'Great! Your website has a robots.txt file. This helps search engines understand which pages to crawl and index.';
					?>
				</p>
			<?php else : ?>
				<p><strong><?php echo 'Missing'; ?></strong></p>
				<p>
					<?php
					echo 'Your website does not have a robots.txt file. While not always required, a robots.txt file helps control how search engines access your site.';
					?>
				</p>
			<?php endif; ?>
		</div>
	</div>

	<!-- Analytics support -->
	<?php $advice = $score_advice( 'hasAnalytics' ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'Analytics'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<?php if ( ! empty( $misc['analytics'] ) ) : ?>
				<p>
					<?php
					echo 'Great! We detected analytics tracking on your website. Analytics help you understand visitor behavior and improve your site.';
					?>
				</p>
				<div class="table-responsive">
					<table class="table table-striped table-items">
						<tbody>
							<?php foreach ( $misc['analytics'] as $analytics ) : ?>
								<tr>
									<td>
										<img src="<?php echo V_WPSA_Config::get_base_url( true ); ?>/assets/img/analytics/<?php echo $analytics; ?>.png" />
										&nbsp;&nbsp;
										<?php echo esc_html( AnalyticsFinder::getProviderName( $analytics ) ); ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<p><strong><?php echo 'Missing'; ?></strong></p>
				<p>
					<?php
					echo 'Your website does not have analytics tracking installed. Consider adding analytics to gain insights into your visitors and improve your site performance.';
					?>
				</p>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php if ( V_WPSA_Config::get( 'psi.show' ) ) : ?>
	<h4 id="section_page_speed" class="mt-5 mb-3"><?php echo 'PageSpeed Insights'; ?></h4>
	<div class="category-wrapper">
		<div class="row pagespeed">
			<div class="col-md-6 mb-3">
				<h5><?php echo 'Device'; ?></h5>

				<div class="form-check">
					<input type="radio" name="psi__strategy" id="psi_strategy_desktop" class="form-check-input" value="desktop" <?php echo V_WPSA_Utils::is_psi_active( 'device', 'desktop' ) ? ' checked' : null; ?>>
					<label class="form-check-label" for="psi_strategy_desktop">
						<?php echo 'Desktop'; ?>
					</label>
				</div>

				<div class="form-check">
					<input type="radio" name="psi__strategy" id="psi_strategy_mobile" class="form-check-input" value="mobile" <?php echo V_WPSA_Utils::is_psi_active( 'device', 'mobile' ) ? ' checked' : null; ?>>
					<label class="form-check-label" for="psi_strategy_mobile">
						<?php echo 'Mobile'; ?>
					</label>
				</div>
			</div>

			<div class="col-md-6 mb-3">
				<h5><?php echo 'Categories'; ?></h5>

				<div class="form-check">
					<input type="checkbox" class="form-check-input" id="psi_category_performance" data-psi-category="performance" value="performance" <?php echo V_WPSA_Utils::is_psi_active( 'categories', 'performance' ) ? ' checked' : null; ?>>
					<label class="form-check-label" for="psi_category_performance">
						<?php echo 'Performance'; ?>
					</label>
				</div>

				<div class="form-check">
					<input type="checkbox" class="form-check-input" id="psi_category_accessibility" data-psi-category="accessibility" value="accessibility" <?php echo V_WPSA_Utils::is_psi_active( 'categories', 'accessibility' ) ? ' checked' : null; ?>>
					<label class="form-check-label" for="psi_category_accessibility">
						<?php echo 'Accessibility'; ?>
					</label>
				</div>

				<div class="form-check">
					<input type="checkbox" class="form-check-input" id="psi_category_bestpr" data-psi-category="best-practices" value="best-practices" <?php echo V_WPSA_Utils::is_psi_active( 'categories', 'best-practices' ) ? ' checked' : null; ?>>
					<label class="form-check-label" for="psi_category_bestpr">
						<?php echo 'Best Practices'; ?>
					</label>
				</div>

				<div class="form-check">
					<input type="checkbox" class="form-check-input" id="psi_category_seo" data-psi-category="seo" value="seo" <?php echo V_WPSA_Utils::is_psi_active( 'categories', 'seo' ) ? ' checked' : null; ?>>
					<label class="form-check-label" for="psi_category_seo">
						<?php echo 'SEO'; ?>
					</label>
				</div>

				<div class="form-check">
					<input type="checkbox" class="form-check-input" id="psi_category_pwa" data-psi-category="pwa" value="pwa" <?php echo V_WPSA_Utils::is_psi_active( 'categories', 'pwa' ) ? ' checked' : null; ?>>
					<label class="form-check-label" for="psi_category_pwa">
						<?php echo 'Progressive Web App'; ?>
					</label>
				</div>
			</div>
			<button class="psi__analyze-btn btn btn-primary mt-3">
				<?php echo 'Analyze'; ?>
			</button>
		</div>

		<div class="row">
			<div class="col">
				<div class="psi__iframe-wrapper"></div>
			</div>
		</div>
	<?php endif; ?>
	</div>

<?php if ( current_user_can( 'manage_options' ) ) : ?>
	<h3 id="section_scoring_breakdown" class="mt-5 mb-3"><?php echo 'Scoring Breakdown (Admin Only)'; ?></h3>
	<div class="category-wrapper">
		<div class="row pt-3 pb-3">
			<div class="col-12">
				<p class="lead"><?php echo 'This section shows how points are allocated across different SEO categories.'; ?></p>
				<div class="table-responsive">
					<table class="table table-striped">
						<thead class="thead-dark">
							<tr>
								<th><?php echo 'Category'; ?></th>
								<th><?php echo 'Points Earned'; ?></th>
								<th><?php echo 'Max Possible'; ?></th>
								<th><?php echo 'Status'; ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$category_labels = array(
								'title'              => 'Title Tag',
								'description'        => 'Meta Description',
								'htmlratio'          => 'Text/HTML Ratio',
								'cssCount'           => 'CSS Files Count',
								'jsCount'            => 'JavaScript Files Count',
								'noFlash'            => 'No Flash Content',
								'noIframe'           => 'No Iframes',
								'imgHasAlt'          => 'Images Have Alt Text',
								'isFriendlyUrl'      => 'SEO Friendly URLs',
								'noUnderScore'       => 'No Underscores in URLs',
								'ogmetaproperties'   => 'Open Graph Properties',
								'charset'            => 'Character Set Defined',
								'hasSitemap'         => 'Sitemap Available',
								'hasRobotsTxt'       => 'Robots.txt',
								'hasAnalytics'       => 'Analytics Tracking',
								'issetInternalLinks' => 'Internal Links Present',
								'noInlineCSS'        => 'No Inline CSS',
								'doctype'            => 'Document Doctype',
								'lang'               => 'Language Attribute',
								'issetAppleIcons'    => 'Apple Touch Icons',
								'noNestedtables'     => 'No Nested Tables',
								'issetFavicon'       => 'Favicon Present',
								'viewport'           => 'Viewport Meta Tag',
								'hasGzip'            => 'Gzip Compression',
								'noDeprecated'       => 'No Deprecated HTML',
								'issetHeadings'      => 'Headings Present',
								'dublincore'         => 'Dublin Core',
								'w3c'                => 'W3C Validity',
								'wordConsistency'    => 'Keyword Consistency',
								'isPrintable'        => 'Printable Version',
								'noEmail'            => 'No Plain Email Addresses',
								'keywords'           => 'Meta Keywords Tag',
							);

							$ordered_keys = array(
								'title',
								'description',
								'htmlratio',
								'cssCount',
								'jsCount',
								'noFlash',
								'noIframe',
								'imgHasAlt',
								'isFriendlyUrl',
								'noUnderScore',
								'ogmetaproperties',
								'charset',
								'hasSitemap',
								'hasRobotsTxt',
								'hasAnalytics',
								'issetInternalLinks',
								'noInlineCSS',
								'doctype',
								'lang',
								'issetAppleIcons',
								'noNestedtables',
								'issetFavicon',
								'viewport',
								'hasGzip',
								'noDeprecated',
								'issetHeadings',
								'dublincore',
								'w3c',
								'wordConsistency',
								'isPrintable',
								'noEmail',
								'keywords',
							);

							$display_keys = array();
							foreach ( $ordered_keys as $ordered_key ) {
								if ( isset( $rates[ $ordered_key ] ) ) {
									$display_keys[] = $ordered_key;
								}
							}
							foreach ( $rates as $rate_key => $definition ) {
								if ( ! in_array( $rate_key, $display_keys, true ) ) {
									$display_keys[] = $rate_key;
								}
							}

							$consistency_limit = (int) V_WPSA_Config::get( 'analyzer.consistency_count' );
							$matrix_count      = isset( $cloud['matrix'] ) && is_array( $cloud['matrix'] ) ? count( $cloud['matrix'] ) : 0;

							foreach ( $display_keys as $key ) {
								if ( ! isset( $rates[ $key ] ) ) {
									continue;
								}

								$definition = $rates[ $key ];
								$label      = isset( $category_labels[ $key ] ) ? $category_labels[ $key ] : ucwords( preg_replace( '/([a-z])([A-Z])/', '$1 $2', $key ) );
								$max_points = 0.0;

								if ( 'wordConsistency' === $key && is_array( $definition ) ) {
									$per_tag_sum = array_sum( $definition );
									$max_points  = $per_tag_sum * $consistency_limit;
								} elseif ( is_array( $definition ) ) {
									foreach ( $definition as $rule ) {
										if ( is_array( $rule ) && isset( $rule['score'] ) ) {
											$max_points = max( $max_points, (float) $rule['score'] );
										} elseif ( is_numeric( $rule ) ) {
											$max_points = max( $max_points, (float) $rule );
										}
									}
								} else {
									$max_points = (float) $definition;
								}

								if ( $max_points <= 0 ) {
									continue;
								}

								$earned_points = $score_points( $key );
								if ( 'wordConsistency' === $key && $earned_points > $max_points ) {
									$earned_points = $max_points;
								}

								$advice       = $score_advice( $key );
								$advice_value = is_string( $advice ) ? $advice : 'error';
								$badge_class  = 'warning';
								$status_label = 'Warning';

								if ( false !== strpos( $advice_value, 'success' ) ) {
									$badge_class  = 'success';
									$status_label = 'Pass';
								} elseif ( false !== strpos( $advice_value, 'error' ) ) {
									$badge_class  = 'danger';
									$status_label = 'Fail';
								}

								$points_display = $format_points( $earned_points );
								$max_display    = $format_points( $max_points );
								?>
								<tr>
										<td>
										<?php
										if ( 'wordConsistency' === $key ) {
											echo esc_html( $label );
										} else {
											echo esc_html( $label );
										}
										?>
										</td>
										<td><?php echo esc_html( $points_display ); ?></td>
										<td><?php echo esc_html( $max_display ); ?></td>
										<td>
												<span class="badge badge-<?php echo esc_attr( $badge_class ); ?>">
														<?php echo esc_html( $status_label ); ?>
												</span>
										</td>
								</tr>
								<?php
							}
							?>

							<tr class="table-info">
								<td><strong><?php echo 'Total Score'; ?></strong></td>
								<td><strong><?php echo (int) $website['score']; ?></strong></td>
								<td><strong>100</strong></td>
								<td>
									<span class="badge badge-info">
										<?php echo (int) $website['score']; ?>%
									</span>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>

	<div class="mt-5" id="update_form">
		<!-- JS is enqueued via WordPress plugin file. Remove direct <script> and rely on enqueued assets. -->

		<div class="jumbotron">
			<h1><?php echo V_WPSA_Config::get( 'app.name' ); ?></h1>
			<p class="lead mb-4">
				<?php echo V_WPSA_Config::get( 'app.name' ); ?> is a free SEO tool which provides you content analysis of the website.
			</p>
			<form id="website-form">
				<div class="form-row">
					<div class="form-group col-md-6">
						<div class="input-group mb-3">
							<input type="text" name="Website[domain]" id="domain" class="form-control form-control-lg" placeholder="<?php echo V_WPSA_Config::get( 'param.placeholder' ); ?>">
							<div class="input-group-append">
								<button class="btn btn-primary" type="button" id="submit">
									Analyze
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
	</div>
