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
 * - $updUrl: Update URL
 * - $rateprovider: Rate provider object
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
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $website ) || ! is_array( $website ) ) : ?>
	<div class="alert alert-danger mt-5 mb-5">
		<?php echo 'No report available. The domain could not be analyzed or the record was not created. Please try again or check your domain input.'; ?>
	</div>
	<?php
	return;
endif;
?>
<script type="text/javascript">
	"use strict";

	jQuery(function($) {
		dynamicThumbnail({
			<?php echo 'main_' . $website['id']; ?>: <?php echo json_encode( $thumbnail ); ?>
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
			var href = $(this).attr("href");
			// If href points to external location, follow it.
			if (href.indexOf("#") < 0) {
				return true;
			}
			e.preventDefault();
			// Fill domain input and trigger the same flow as the Analyze button.
			var $domain = $("#domain");
			if ($domain.length) {
				$domain.val('<?php echo esc_js( $website['domain'] ); ?>');
				// Trigger the same validation -> generateReport flow wired to #submit
				$('#submit').trigger('click');
			} else {
				// Fallback: call generateReport directly if form is not present.
				if (window.vWpSeoAudit && typeof window.vWpSeoAudit.generateReport === 'function') {
					window.vWpSeoAudit.generateReport('<?php echo esc_js( $website['domain'] ); ?>', {
						$container: $('.v-wpsa-container').first(),
						$errors: $('#errors'),
						$progressBar: $('#progress-bar')
					});
				}
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
				$monthNames = array(
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
				$month      = isset( $monthNames[ $generated['M'] ] ) ? $monthNames[ $generated['M'] ] : $generated['M'];
				echo $month . ' ' . $generated['d'] . ' ' . $generated['Y'] . ' ' . $generated['H'] . ':' . $generated['i'] . ' ' . $generated['A'];
				?>
				</small>
			</p>


			<p>
				<?php
				// Always show UPDATE button; clicking fills the domain input and allows re-analysis.
				echo 'Old data? <a href="' . $updUrl . '" class="btn btn-success" id="update_stat">UPDATE</a> !';
				?>
			</p>

			<?php echo V_WPSA_Config::get( 'param.addthis' ); ?>


			<p class="mt-3">
				<strong><?php echo 'The score is ' . (int) $website['score'] . '/100'; ?></strong>
			</p>
			<div class="progress-score progress mb-3">
				<div class="progress-bar progress-bar-striped bg-info" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $website['score']; ?>%;"></div>
			</div>

			<a href="#" class="btn btn-primary v-wpsa-download-pdf" data-domain="<?php echo esc_html( $website['domain'] ); ?>">
				<?php echo 'Download PDF Version'; ?>
			</a>

		</div>
	</div>
</div>


<h3 id="section_content" class="mt-5 mb-3"><?php echo 'SEO Content'; ?></h3>
<div class="category-wrapper">
	<!-- Title -->
	<?php $advice = $rateprovider->addCompareArray( 'title', mb_strlen( V_WPSA_Utils::html_decode( $meta['title'] ) ) ); ?>
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
				if ( $advice === 'success' ) {
					echo 'Great! Your title tag has an optimal length (' . $title_length . ' characters).';
				} elseif ( $advice === 'warning' ) {
					echo 'Your title tag length (' . $title_length . ' characters) could be improved. Aim for 10-70 characters.';
				} else {
					echo 'Your title tag needs attention. Current length is ' . $title_length . ' characters. Optimal length is 10-70 characters.';
				}
				?>
			</p>
		</div>
	</div>

	<!-- Description -->
	<?php $advice = $rateprovider->addCompareArray( 'description', mb_strlen( V_WPSA_Utils::html_decode( $meta['description'] ) ) ); ?>
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
				if ( $advice === 'success' ) {
					echo 'Perfect! Your meta description has an optimal length (' . $desc_length . ' characters).';
				} elseif ( $advice === 'warning' ) {
					echo 'Your meta description length (' . $desc_length . ' characters) could be improved. Aim for 70-160 characters.';
				} else {
					echo 'Your meta description needs attention. Current length is ' . $desc_length . ' characters. Optimal length is 70-160 characters.';
				}
				?>
			</p>
		</div>
	</div>

	<!-- Og properties -->
	<?php $advice = $rateprovider->addCompare( 'ogmetaproperties', ! empty( $meta['ogproperties'] ) ); ?>
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
				if ( $advice === 'success' ) {
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
	<?php $advice = $rateprovider->addCompare( 'imgHasAlt', $content['total_img'] === $content['total_alt'] ); ?>
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
				if ( $advice === 'success' ) {
					echo 'Excellent! All images have alt attributes, which is great for SEO and accessibility.';
				} else {
					echo 'Some images are missing alt attributes. Alt text is important for SEO and accessibility. ' . (int) $content['total_alt'] . ' out of ' . (int) $content['total_img'] . ' images have alt attributes.';
				}
				?>
			</p>
		</div>
	</div>

	<!-- Text/HTML Ratio -->
	<?php $advice = $rateprovider->addCompareArray( 'htmlratio', $document['htmlratio'] ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo 'Text/HTML Ratio'; ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php echo 'Ratio'; ?> : <strong><?php echo $document['htmlratio']; ?>%</strong>
			</p>
			<p>
				<?php
				if ( $advice === 'success' ) {
					echo 'Good! Your page has a healthy text to HTML ratio (' . $document['htmlratio'] . '%). This means your page has a good balance of content to code.';
				} elseif ( $advice === 'warning' ) {
					echo 'Your text/HTML ratio (' . $document['htmlratio'] . '%) could be improved. Aim for a ratio between 10-25% for better SEO.';
				} else {
					echo 'Your text/HTML ratio (' . $document['htmlratio'] . '%) is too low. Consider adding more content or reducing HTML markup for better SEO.';
				}
				?>
			</p>
		</div>
	</div>

	<!-- Flash -->
	<?php $advice = $rateprovider->addCompare( 'noFlash', ! $isseter['flash'] ); ?>
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
				if ( $advice === 'success' ) {
					echo 'Great! Your page does not use Flash, which is good for SEO and modern web standards.';
				} else {
					echo 'Your page uses Flash content. Flash is obsolete and not supported by most modern browsers and devices. Consider using HTML5 alternatives.';
				}
				?>
			</p>
		</div>
	</div>

	<!-- Iframe -->
	<?php $advice = $rateprovider->addCompare( 'noIframe', ! $isseter['iframe'] ); ?>
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
				if ( $advice === 'success' ) {
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
	<?php $advice = $rateprovider->addCompare( 'isFriendlyUrl', $links['friendly'] ); ?>
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
				if ( $advice === 'success' ) {
					echo 'Perfect! Your URLs are SEO-friendly and do not contain query strings or dynamic parameters.';
				} else {
					echo 'Your URLs contain query strings or dynamic parameters. Consider using URL rewriting to create cleaner, more SEO-friendly URLs.';
				}
				?>
			</p>
		</div>
	</div>

	<!-- Underscore -->
	<?php $advice = $rateprovider->addCompare( 'noUnderScore', ! $links['isset_underscore'] ); ?>
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
				if ( $advice === 'success' ) {
					echo 'Great! Your URLs do not contain underscores, which is better for SEO. Search engines prefer hyphens over underscores.';
				} else {
					echo 'Your URLs contain underscores. Consider using hyphens instead of underscores in URLs for better SEO, as search engines treat hyphens as word separators.';
				}
				?>
			</p>
		</div>
	</div>

	<!-- In-page links -->
	<?php $advice = $rateprovider->addCompare( 'issetInternalLinks', $links['internal'] > 0 ); ?>
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
				foreach ( $links['links'] as $link ) {
					if ( ! empty( $link['Link'] ) && preg_match( '/\.(pdf|doc|docx|xls|xlsx|ppt|pptx|zip|rar|txt|csv)$/i', $link['Link'] ) ) {
						$file_links++;
					}
				}
				echo 'We found a total of ' . $linkcount . ' links including ' . $file_links . ' link(s) to files';
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
						foreach ( $links['links'] as $link ) :
							$i++;
							?>
							<tr <?php echo $i > $over_max ? 'class="over-max"' : null; ?>>
								<td class="text-break">
									<a href="<?php echo $link['Link']; ?>" target="_blank" rel="nofollow">
										<?php echo ! empty( $link['Name'] ) ? esc_html( V_WPSA_Utils::html_decode( $link['Name'] ) ) : '-'; ?>
									</a>
								</td>
								<td><?php echo ( $link['Type'] === 'internal' ? 'Internal' : 'External' ); ?></td>
								<td><?php echo ( $link['Juice'] === 'nofollow' ? 'noFollow' : 'Passing Juice' ); ?></td>
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
	<?php $advice = $rateprovider->addCompare( 'issetFavicon', ! empty( $document['favicon'] ) ); ?>
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
				if ( $advice === 'success' ) {
					echo 'Excellent! Your website has a favicon, which helps with branding and user experience.';
				} else {
					echo 'Your website is missing a favicon. A favicon helps with branding and makes your site more recognizable in browser tabs and bookmarks.';
				}
				?>
			</p>
		</div>
	</div>

	<!-- Language -->
	<?php $advice = $rateprovider->addCompare( 'lang', $document['lang'] ); ?>
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
				if ( $advice === 'success' ) {
					echo 'Great! Your page has a language attribute declared, which helps search engines understand your content.';
				} else {
					echo 'Your page is missing a language attribute. Adding a language attribute to your HTML tag helps search engines and screen readers.';
				}
				?>
			</p>
		</div>
	</div>

	<!-- Dublin Core -->
	<?php $advice = $rateprovider->addCompare( 'lang', $isseter['dublincore'] ); ?>
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
				if ( $advice === 'success' ) {
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
	<?php $advice = $rateprovider->addCompare( 'doctype', $document['doctype'] ); ?>
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
	<?php $advice = $rateprovider->addCompare( 'charset', $document['charset'] ); ?>
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
				if ( $advice === 'success' ) {
					echo 'Perfect! Your page specifies a character encoding, which is essential for proper text display.';
				} else {
					echo 'Your page is missing a character encoding declaration. This can lead to display issues with special characters. Add a charset meta tag.';
				}
				?>
			</p>
		</div>
	</div>

	<!-- W3C Validity -->
	<?php $advice = $rateprovider->addCompare( 'w3c', $w3c['valid'] ); ?>
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
		</div>
	</div>

	<!-- Deprecated -->
	<?php $advice = $rateprovider->addCompare( 'noDeprecated', empty( $content['deprecated'] ) ); ?>
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
							<?php foreach ( $content['deprecated'] as $tag => $count ) : ?>
								<tr>
									<td><?php echo htmlspecialchars( '<' . $tag . '>' ); ?></td>
									<td><?php echo $count; ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>

			<p>
				<?php
				if ( $advice === 'success' ) {
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
							<?php $advice = $rateprovider->addCompare( 'noNestedtables', ! $isseter['nestedtables'] ); ?>
							<td width="50px"><img src="<?php echo V_WPSA_Config::get_base_url( true ); ?>/assets/img/isset_<?php echo (int) ! $isseter['nestedtables']; ?>.png" /></td>
							<td>
								<?php
								if ( $advice === 'success' ) {
									echo 'Good! No nested tables found. Nested tables can slow down page rendering.';
								} else {
									echo 'Your page uses nested tables, which can slow down page rendering. Consider using CSS for layout instead.';
								}
								?>
							</td>
						</tr>

						<tr>
							<?php $advice = $rateprovider->addCompare( 'noInlineCSS', ! $isseter['inlinecss'] ); ?>
							<td><img src="<?php echo V_WPSA_Config::get_base_url( true ); ?>/assets/img/isset_<?php echo (int) ! $isseter['inlinecss']; ?>.png" /></td>
							<td>
								<?php
								if ( $advice === 'success' ) {
									echo 'Perfect! No inline CSS found. External stylesheets are better for performance and maintainability.';
								} else {
									echo 'Your page uses inline CSS. Consider moving styles to external stylesheets for better performance and caching.';
								}
								?>
							</td>
						</tr>

						<tr>
							<?php $advice = $rateprovider->addCompareArray( 'cssCount', $document['css'] ); ?>
							<td><img src="<?php echo V_WPSA_Config::get_base_url( true ); ?>/assets/img/isset_<?php echo $advice === 'success' ? '1' : '0'; ?>.png" /></td>
							<td>
								<?php
								$css_count = (int) $document['css'];
								if ( $advice === 'success' ) {
									echo 'Great! Your page has an optimal number of CSS files (' . $css_count . '). Keep stylesheets minimal for better performance.';
								} else {
									echo 'Your page has ' . $css_count . ' CSS files. Too many CSS files can slow down page load. Consider combining them.';
								}
								?>
							</td>
						</tr>

						<tr>
							<?php $advice = $rateprovider->addCompareArray( 'jsCount', $document['js'] ); ?>
							<td><img src="<?php echo V_WPSA_Config::get_base_url( true ); ?>/assets/img/isset_<?php echo $advice === 'success' ? '1' : '0'; ?>.png" /></td>
							<td>
								<?php
								$js_count = (int) $document['js'];
								if ( $advice === 'success' ) {
									echo 'Excellent! Your page has an optimal number of JavaScript files (' . $js_count . '). Keep scripts minimal for better performance.';
								} else {
									echo 'Your page has ' . $js_count . ' JavaScript files. Too many JS files can slow down page load. Consider combining them.';
								}
								?>
							</td>
						</tr>

						<tr>
							<?php $advice = $rateprovider->addCompare( 'hasGzip', $isseter['gzip'] ); ?>
							<td><img src="<?php echo V_WPSA_Config::get_base_url( true ); ?>/assets/img/isset_<?php echo $advice === 'success' ? '1' : '0'; ?>.png" /></td>
							<td>
								<?php
								echo 'Gzip Compression';
								if ( $advice === 'success' ) {
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
	<?php $advice = $rateprovider->addCompare( 'hasSitemap', ! empty( $misc['sitemap'] ) ); ?>
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
	<?php $advice = $rateprovider->addCompare( 'hasRobotsTxt', $isseter['robotstxt'] ); ?>
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
	<?php $advice = $rateprovider->addCompare( 'hasAnalytics', ! empty( $misc['analytics'] ) ); ?>
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
							<input type="text"  name="Website[domain]" id="domain" class="form-control form-control-lg" placeholder="<?php echo V_WPSA_Config::get( 'param.placeholder' ); ?>">
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
