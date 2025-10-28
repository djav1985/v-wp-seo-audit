<?php
/**
 * File: pdf.php
 *
 * Description: PDF report template.
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
<style>
	table {
		background-color: #ffffff;
	}

	.table {
		width: 546px !important;
	}

	.table tr {
		border-bottom: 5px solid #fff !important;
	}

	.table-inner {
		width: 350px !important;
	}

	table td {
		padding: 5px;
		margin: 5px;
	}

	a {
		color: #315D86;
		text-decoration: underline;
	}

	.even {
		background-color: #fff;
	}

	.odd {
		background-color: #f9f9f9;
	}

	.header {
		font-size: 14px;
		font-weight: bold;
	}

	.suh-header {
		font-size: 12px;
		font-weight: bold;
	}

	.td-icon {
		width: 40px;
	}

	.td-compare {
		width: 120px;
	}

	.td-result {
		width: 370px;
	}

	.adv-icon {
		width: 32px;
		height: 32px;
		padding: 5px !important;
	}

	.grade-1 {
		font-weight: 300;
		font-size: 12px;
		color: #191a1b;
	}

	.grade-2 {
		font-weight: 300;
		font-size: 14px;
		color: #141415;
	}

	.grade-3 {
		font-size: 18px;
		color: #0f0f10;
	}

	.grade-4 {
		font-size: 20px;
		color: #315d86;
	}

	.grade-5 {
		font-weight: 600;
		font-size: 24px;
		color: #315d86;
	}

	.success {
		background-color: #dff0d8;
	}

	.error {
		background-color: #f2dede;
	}

	.warning {
		background-color: #fcf8e3;
	}

	.icon-time {
		font-size: 8px;
	}

	.progress {
		background-color: #f7f7f7;
	}

	.bar {
		background-color: #149bdf;
	}
</style>

<table class="table table-fluid">
	<tr class="no-top-line">
		<td>
			<img class="thumbnail" id="thumb_<?php echo esc_attr( $website['id'] ); ?>" src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( $website['idn'] ); ?>" />	
		</td>
		<td>
			<h1 class="h-review"><?php echo 'Website review for ' . esc_html( $website['idn'] ); ?></h1>
			<i class="icon-time"></i>&nbsp;<small><?php echo 'Generated on'; ?>
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
				echo esc_html( $month . ' ' . $generated['d'] . ' ' . $generated['Y'] . ' ' . $generated['H'] . ':' . $generated['i'] . ' ' . $generated['A'] );
				?>
			</small><br /><br />

			<strong><?php echo 'The score is ' . (int) $website['score'] . '/100'; ?></strong>
			<br /><br />

			<table width="180px" cellspacing="0" cellpadding="0">
				<tr>
					<td width="<?php echo esc_attr( $website['score'] ); ?>%" class="bar"></td>
					<td class="progress"></td>
				</tr>
			</table>

		</td>
	</tr>
</table>

<br />

<!-- SEO Content -->
<table class="table table-striped table-fluid" cellspacing="3" cellpadding="5">
	<thead>
		<tr>
			<th colspan="3" align="center">
				<h4 class="header"><?php echo 'SEO Content'; ?></h4><br /><br />
			</th>
		</tr>
	</thead>
	<tbody>

		<!-- Title -->
			<?php $advice = $score_advice( 'title' ); ?>
		<?php list($img_advice,) = explode( ' ', $advice ); ?>
		<tr class="<?php echo esc_attr( $advice ); ?>">
			<td class="td-icon">
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/' . $img_advice . '.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="td-compare" align="center" valign="middle">
				<?php echo 'Title'; ?>
			</td>
			<td class="td-result">
				<?php echo esc_html( $meta['title'] ); ?>
				<br /><br />
				<strong><?php echo 'Length'; ?> : <?php echo esc_html( mb_strlen( (string) $meta['title'] ) ); ?></strong>
				<br /><br />
				<?php
				$title_length = mb_strlen( (string) $meta['title'] );
				if ( 'success' === $advice ) {
					echo esc_html( 'Great! Your title tag has an optimal length (' . $title_length . ' characters).' );
				} elseif ( 'warning' === $advice ) {
					echo esc_html( 'Your title tag length (' . $title_length . ' characters) could be improved. Aim for 10-70 characters.' );
				} else {
					echo esc_html( 'Your title tag needs attention. Current length is ' . $title_length . ' characters. Optimal length is 10-70 characters.' );
				}
				?>
			</td>
		</tr>

		<!-- Description -->
			<?php $advice = $score_advice( 'description' ); ?>
		<?php list($img_advice,) = explode( ' ', $advice ); ?>
		<tr class="<?php echo esc_attr( $advice ); ?>">
			<td>
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/' . $img_advice . '.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="td-compare" align="center" valign="middle">
				<?php echo 'Description'; ?>
			</td>
			<td>
				<?php echo esc_html( $meta['description'] ); ?>
				<br /><br />
				<strong><?php echo 'Length'; ?> : <?php echo esc_html( mb_strlen( (string) $meta['description'] ) ); ?></strong>
				<br /><br />
				<?php
				$desc_length = mb_strlen( (string) $meta['description'] );
				if ( 'success' === $advice ) {
					echo esc_html( 'Perfect! Your meta description has an optimal length (' . $desc_length . ' characters).' );
				} elseif ( 'warning' === $advice ) {
					echo esc_html( 'Your meta description length (' . $desc_length . ' characters) could be improved. Aim for 70-160 characters.' );
				} else {
					echo esc_html( 'Your meta description needs attention. Current length is ' . $desc_length . ' characters. Optimal length is 70-160 characters.' );
				}
				?>
			</td>
		</tr>

		<!-- Og properties -->
			<?php $advice = $score_advice( 'ogmetaproperties' ); ?>
		<tr class="<?php echo esc_attr( $advice ); ?>">

			<td>
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/' . $advice . '.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>

			<td class="td-compare" align="center" valign="middle">
				<?php echo 'Og Meta Properties'; ?>
			</td>

			<td>
				<?php
				if ( 'success' === $advice ) {
					echo esc_html( 'Great! Your page has Open Graph meta properties for better social media sharing.' );
				} else {
					echo esc_html( 'Your page is missing Open Graph meta properties. Adding these tags helps control how your content appears when shared on social media.' );
				}
				?>
				<br /><br />
				<?php if ( ! empty( $meta['ogproperties'] ) ) : ?>
					<table class="table table-striped table-fluid table-inner" cellpadding="5">
						<tr nobr="true" class="odd">
							<td width="100px"><span class="suh-header"><?php echo 'Property'; ?></span><br /><br /></td>
							<td width="250px"><span class="suh-header"><?php echo 'Content'; ?></span><br /><br /></td>
						</tr>
						<?php
						$i = 0;
						foreach ( $meta['ogproperties'] as $property => $c ) :
							$even = 0 === $i % 2;
							?>
							<tr nobr="true" class="<?php echo $even ? 'even' : 'odd'; ?>">
								<td><?php echo esc_html( $property ); ?></td>
								<td><?php echo esc_html( $c ); ?></td>
							</tr>
							<?php
							$i++;
						endforeach;
						?>
					</table>
				<?php endif; ?>
			</td>

		</tr>

		<!-- Headings -->
		<tr class="odd">
			<td>
				<br /><br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/neutral.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="td-compare" align="center" valign="middle">
				<?php echo 'Headings'; ?>
			</td>
			<td>

				<table class="table table-inner table-striped table-fluid">
					<tbody>
						<tr class="no-top-line">
							<?php foreach ( $content['headings'] as $heading => $headings ) : ?>
								<td><strong><?php echo esc_html( strtoupper( $heading ) ); ?></strong></td>
							<?php endforeach; ?>
						</tr>
						<tr>
							<?php foreach ( $content['headings'] as $headings ) : ?>
								<td><?php echo (int) count( $headings ); ?></td>
							<?php endforeach; ?>
						</tr>
					</tbody>
				</table>

				<?php
				if ( $content['isset_headings'] ) :
					$i = 0;
					?>
					<ul id="headings">
						<?php
						foreach ( $content['headings'] as $heading => $headings ) :
							if ( ! empty( $headings ) ) :
								foreach ( $headings as $h ) :
									$i++;
									?>
									<li>[<?php echo esc_html( mb_strtoupper( (string) $heading ) ); ?>] <?php echo esc_html( $h ); ?></li>
									<?php
								endforeach;
							endif;
						endforeach;
						?>
					</ul>
				<?php endif; ?>

			</td>
		</tr>

		<!-- Images -->
			<?php $advice = $score_advice( 'imgHasAlt' ); ?>
		<tr class="<?php echo esc_attr( $advice ); ?>">
			<td>
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/' . $advice . '.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="td-compare" align="center" valign="middle">
				<?php echo 'Images'; ?>
			</td>
			<td>
				<?php echo esc_html( 'We found ' . (int) $content['total_img'] . ' images on this web page.' ); ?>
				<br />
				<br />
				<?php
				if ( 'success' === $advice ) {
					echo esc_html( 'Excellent! All images have alt attributes, which is great for SEO and accessibility.' );
				} else {
					echo esc_html( 'Some images are missing alt attributes. Alt text is important for SEO and accessibility. ' . (int) $content['total_alt'] . ' out of ' . (int) $content['total_img'] . ' images have alt attributes.' );
				}
				?>

				<?php if ( 'success' !== $advice && ! empty( $content['images_missing_alt'] ) && is_array( $content['images_missing_alt'] ) ) : ?>
					<br />
					<br />
					<strong><?php echo 'Images Missing Alt Text:'; ?></strong>
					<br />
					<table class="table-inner" cellspacing="0" cellpadding="3" style="margin-top:5px;">
						<thead>
							<tr style="background-color:#f0f0f0;">
								<th style="font-weight:bold;padding:5px;"><?php echo 'Image Source'; ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$max_display = 20; // Limit display in PDF to avoid bloat.
							$i           = 0;
							foreach ( $content['images_missing_alt'] as $img_src ) :
								$i++;
								if ( $i > $max_display ) {
									break;
								}
								// Extract filename from URL for display.
								$parsed   = wp_parse_url( $img_src );
								$filename = isset( $parsed['path'] ) ? basename( $parsed['path'] ) : '';
								if ( empty( $filename ) ) {
									$filename = $img_src;
								}
								// Truncate long filenames for PDF display.
								if ( strlen( $filename ) > 60 ) {
									$filename = substr( $filename, 0, 57 ) . '...';
								}
								$row_class = ( 0 === $i % 2 ) ? 'even' : 'odd';
								?>
								<tr class="<?php echo esc_attr( $row_class ); ?>">
									<td style="padding:5px;word-wrap:break-word;" title="<?php echo esc_attr( $img_src ); ?>">
										<?php echo esc_html( $filename ); ?>
									</td>
								</tr>
							<?php endforeach; ?>
							<?php if ( count( $content['images_missing_alt'] ) > $max_display ) : ?>
								<tr>
									<td style="padding:5px;font-style:italic;">
										<?php echo esc_html( '... and ' . ( count( $content['images_missing_alt'] ) - $max_display ) . ' more images' ); ?>
									</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				<?php endif; ?>

			</td>
		</tr>

		<!-- Text/HTML Ratio -->
			<?php $advice = $score_advice( 'htmlratio' ); ?>
		<?php list($img_advice,) = explode( ' ', $advice ); ?>
		<tr class="<?php echo esc_attr( $advice ); ?>">
			<td>
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/' . $img_advice . '.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="td-compare" align="center" valign="middle">
				<?php echo 'Text/HTML Ratio'; ?>
			</td>
			<td>
				<?php
				$text_percent = (int) $document['htmlratio'];
				$html_percent = 100 - $text_percent;
				echo 'Ratio';
				?>
				: <strong><?php echo esc_html( $text_percent . '% text vs ' . $html_percent . '% HTML' ); ?></strong>
				<br />
				<br />
				<?php
				if ( 'error less_than' === $advice ) {
					echo esc_html( 'Your text/HTML ratio (' . $text_percent . '% text, ' . $html_percent . '% HTML) is very low. Add more readable content for better SEO.' );
				} elseif ( 'success' === $advice ) {
					echo esc_html( 'Your text/HTML ratio (' . $text_percent . '% text, ' . $html_percent . '% HTML) is acceptable, but could be stronger. Aim for 40%-70% text for best SEO.' );
				} elseif ( 'success ideal_ratio' === $advice ) {
					echo esc_html( 'Excellent! Your page has an ideal text to HTML ratio (' . $text_percent . '% text, ' . $html_percent . '% HTML). This is optimal for SEO and readability.' );
				} elseif ( 'warning' === $advice ) {
					echo esc_html( 'Your text/HTML ratio (' . $text_percent . '% text, ' . $html_percent . '% HTML) is a bit too text-heavy. Consider balancing content and markup for best results.' );
				}
				?>
			</td>
		</tr>

		<!-- Flash -->
			<?php $advice = $score_advice( 'noFlash' ); ?>
		<tr class="<?php echo esc_attr( $advice ); ?>">
			<td>
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/' . $advice . '.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="td-compare" align="center" valign="middle">
				<?php echo 'Flash'; ?>
			</td>
			<td>
				<?php
				if ( 'success' === $advice ) {
					echo esc_html( 'Great! Your page does not use Flash, which is good for SEO and modern web standards.' );
				} else {
					echo esc_html( 'Your page uses Flash content. Flash is obsolete and not supported by most modern browsers and devices. Consider using HTML5 alternatives.' );
				}
				?>
			</td>
		</tr>

		<!-- Iframe -->
			<?php $advice = $score_advice( 'noIframe' ); ?>
		<tr class="<?php echo esc_attr( $advice ); ?>">
			<td>
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/' . $advice . '.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="td-compare" align="center" valign="middle">
				<?php echo 'Iframe'; ?>
			</td>
			<td>
				<?php
				if ( 'success' === $advice ) {
					echo esc_html( 'Good! Your page does not use iframes, which is better for SEO and page performance.' );
				} else {
					echo esc_html( 'Your page uses iframes. While sometimes necessary, iframes can negatively impact SEO and page load times.' );
				}
				?>
			</td>
		</tr>

	</tbody>
</table>

<br /><br /><br />


<!-- SEO Links -->
<table class="table table-striped table-fluid" cellspacing="3" cellpadding="5">
	<thead>
		<tr>
			<th colspan="3" align="center">
				<h4 class="header"><?php echo 'SEO Links'; ?></h4>
			</th>
		</tr>
	</thead>
	<tbody>
		<!-- Friendly url -->
			<?php $advice = $score_advice( 'isFriendlyUrl' ); ?>
		<tr class="<?php echo esc_attr( $advice ); ?>">
			<td class="td-icon">
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/' . $advice . '.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="td-compare">
				<?php echo 'URL Rewrite'; ?>
			</td>
			<td class="td-result">
				<?php
				if ( 'success' === $advice ) {
					echo esc_html( 'Perfect! Your URLs are SEO-friendly and do not contain query strings or dynamic parameters.' );
				} else {
					echo esc_html( 'Your URLs contain query strings or dynamic parameters. Consider using URL rewriting to create cleaner, more SEO-friendly URLs.' );
				}
				?>
			</td>
		</tr>

		<!-- Underscore -->
			<?php $advice = $score_advice( 'noUnderScore' ); ?>
		<tr class="<?php echo esc_attr( $advice ); ?>">
			<td>
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/' . $advice . '.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="compare">
				<?php echo 'Underscores in the URLs'; ?>
			</td>
			<td>
				<?php
				if ( 'success' === $advice ) {
					echo esc_html( 'Great! Your URLs do not contain underscores, which is better for SEO. Search engines prefer hyphens over underscores.' );
				} else {
					echo esc_html( 'Your URLs contain underscores. Consider using hyphens instead of underscores in URLs for better SEO, as search engines treat hyphens as word separators.' );
				}
				?>
			</td>
		</tr>

		<!-- In-page links -->
			<?php $advice = $score_advice( 'issetInternalLinks' ); ?>
		<tr class="<?php echo esc_attr( $advice ); ?>">
			<td>
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/' . $advice . '.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="compare">
				<?php echo 'In-page links'; ?>
			</td>
			<td>
				<?php
				$file_links = 0;
				foreach ( $links['links'] as $link_item ) {
					if ( ! empty( $link_item['Link'] ) && preg_match( '/\.(pdf|doc|docx|xls|xlsx|ppt|pptx|zip|rar|txt|csv)$/i', $link_item['Link'] ) ) {
						$file_links++;
					}
				}
				$total_links = count( $links['links'] );
				echo esc_html( 'We found a total of ' . $total_links . ' link(s) including ' . $file_links . ' link(s) to files' );
				?>

			</td>
		</tr>

		<!-- Statistic -->
		<tr class="odd">
			<td>
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/neutral.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="compare">
				<?php echo 'Statistics'; ?>
			</td>
			<td>
				<?php echo 'External Links'; ?> : <?php echo 'noFollow'; ?> <?php echo esc_html( V_WPSA_Utils::proportion( $linkcount, $links['external_nofollow'] ) . '%' ); ?><br /><br />
				<?php echo 'External Links'; ?> : <?php echo 'Passing Juice'; ?> <?php echo esc_html( V_WPSA_Utils::proportion( $linkcount, $links['external_dofollow'] ) . '%' ); ?><br /><br />
				<?php echo 'Internal Links'; ?> <?php echo esc_html( V_WPSA_Utils::proportion( $linkcount, $links['internal'] ) . '%' ); ?>
			</td>
		</tr>
	</tbody>
</table>

<br /><br /><br />

<table class="table table-striped table-fluid" cellspacing="3" cellpadding="5">
	<thead>
		<tr>
			<th colspan="3" align="center">
				<h4 class="header"><?php echo 'In-page links'; ?></h4>
			</th>
		</tr>
	</thead>
	<tbody>

		<tr class="odd">
			<td width="60%"><span class="suh-header"><?php echo 'Anchor'; ?></span></td>
			<td width="20%"><span class="suh-header"><?php echo 'Type'; ?></span></td>
			<td width="20%"><span class="suh-header"><?php echo 'Juice'; ?></span></td>
		</tr>
		<?php
		$i = 0;
		foreach ( $links['links'] as $link_item ) :
			$even = 0 === $i % 2;
			?>
			<tr class="<?php echo $even ? 'even' : 'odd'; ?>">
				<td>
					<a href="<?php echo esc_url( $link_item['Link'] ); ?>" target="_blank">
						<?php echo ! empty( $link_item['Name'] ) ? esc_html( $link_item['Name'] ) : '-'; ?>
					</a>
				</td>
				<td><?php echo esc_html( 'internal' === $link_item['Type'] ? 'Internal' : 'External' ); ?></td>
				<td><?php echo esc_html( 'nofollow' === $link_item['Juice'] ? 'noFollow' : 'Passing Juice' ); ?></td>
			</tr>
			<?php
			$i++;
		endforeach;
		?>
	</tbody>
</table>

<br><br><br>

<!-- SEO Keywords -->
<table class="table table-striped table-fluid" cellspacing="3" cellpadding="5">
	<thead>
		<tr>
			<th colspan="3" align="center">
				<h4 class="header"><?php echo 'SEO Keywords'; ?></h4>
			</th>
		</tr>
	</thead>
	<tbody>
		<!-- Tag cloud -->
		<tr class="odd">
			<td class="td-icon">
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/neutral.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="td-compare">
				<?php echo 'Keywords Cloud'; ?>
			</td>
			<td class="cloud-container td-result">
				<?php foreach ( $cloud['words'] as $word => $stat ) : ?>
					<span class="grade-<?php echo (int) $stat['grade']; ?>"><?php echo esc_html( $word ); ?></span>
				<?php endforeach; ?>
			</td>
		</tr>
	</tbody>
</table>

<br /><br /><br />

<!-- SEO Keywords -->
<table class="table table-striped table-fluid" cellspacing="3" cellpadding="5">
	<thead>
		<tr>
			<th colspan="6" align="center">
				<h4 class="header"><?php echo 'Keywords Consistency'; ?></h4>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr class="odd">
			<td width="20%"><span class="suh-header"><?php echo 'Keyword'; ?></span></td>
			<td width="15%"><span class="suh-header"><?php echo 'Content'; ?></span></td>
			<td width="15%"><span class="suh-header"><?php echo 'Title'; ?></span></td>
			<td width="15%"><span class="suh-header"><?php echo 'Keywords'; ?></span></td>
			<td width="15%"><span class="suh-header"><?php echo 'Description'; ?></span></td>
			<td width="15%"><span class="suh-header"><?php echo 'Headings'; ?></span></td>
		</tr>
		<?php
		$i = 0;
		foreach ( $cloud['matrix'] as $word => $object ) :
			$even = 0 === $i % 2;
			?>
			<tr class="<?php echo $even ? 'even' : 'odd'; ?>">
				<td><?php echo esc_html( $word ); ?></td>
				<td><?php echo (int) $cloud['words'][ $word ]['count']; ?></td>
				<td><img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/isset_' . ( isset( $object['title'] ) ? (int) $object['title'] : 0 ) . '.png' ); ?>" /></td>
				<td><img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/isset_' . ( isset( $object['keywords'] ) ? (int) $object['keywords'] : 0 ) . '.png' ); ?>" /></td>
				<td><img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/isset_' . ( isset( $object['description'] ) ? (int) $object['description'] : 0 ) . '.png' ); ?>" /></td>
				<td><img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/isset_' . ( isset( $object['headings'] ) ? (int) $object['headings'] : 0 ) . '.png' ); ?>" /></td>
			</tr>
			<?php
			$i++;
		endforeach;
		?>
	</tbody>
</table>

<br /><br /><br />

<!-- USability -->
<table class="table table-striped table-fluid" cellspacing="3" cellpadding="5">
	<thead>
		<tr>
			<th colspan="3" align="center">
				<h4 class="header"><?php echo 'Usability'; ?></h4>
			</th>
		</tr>
	</thead>
	<tbody>

		<!-- Url -->
		<tr class="odd">
			<td class="td-icon">
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/neutral.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="td-compare">
				<?php echo 'Url'; ?>
			</td>
			<td class="td-result">
				<?php echo 'Domain'; ?> : <?php echo esc_html( $website['idn'] ); ?>
				<br />
				<?php echo 'Length'; ?> : <?php echo esc_html( mb_strlen( $website['idn'] ) ); ?>
			</td>
		</tr>

		<!-- Favicon -->
			<?php $advice = $score_advice( 'issetFavicon' ); ?>
		<tr class="<?php echo esc_attr( $advice ); ?>">
			<td>
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/' . $advice . '.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="compare">
				<?php echo 'Favicon'; ?>
			</td>
			<td>
				<?php
				if ( 'success' === $advice ) {
					echo esc_html( 'Excellent! Your website has a favicon, which helps with branding and user experience.' );
				} else {
					echo esc_html( 'Your website is missing a favicon. A favicon helps with branding and makes your site more recognizable in browser tabs and bookmarks.' );
				}
				?>
			</td>
		</tr>

		<!-- Language -->
			<?php $advice = $score_advice( 'lang' ); ?>
		<tr class="<?php echo esc_attr( $advice ); ?>">
			<td>
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/' . $advice . '.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="compare">
				<?php echo 'Language'; ?>
			</td>
			<td>
				<?php
				if ( 'success' === $advice ) {
					echo esc_html( 'Great! Your page has a language attribute declared, which helps search engines understand your content.' );
				} else {
					echo esc_html( 'Your page is missing a language attribute. Adding a language attribute to your HTML tag helps search engines and screen readers.' );
				}
				?>
			</td>
		</tr>

		<!-- Dublin Core -->
			<?php $advice = $score_advice( 'dublincore' ); ?>
		<tr class="<?php echo esc_attr( $advice ); ?>">
			<td>
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/' . $advice . '.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="compare">
				<?php echo 'Dublin Core'; ?>
			</td>
			<td>
				<?php
				if ( 'success' === $advice ) {
					echo esc_html( 'Good! Your page uses Dublin Core metadata, which can help with content categorization and discovery.' );
				} else {
					echo esc_html( 'Your page does not use Dublin Core metadata. While not essential, Dublin Core can help with content categorization in digital libraries and archives.' );
				}
				?>
			</td>
		</tr>

	</tbody>
</table>

<br /><br /><br />

<!-- Document -->
<table class="table table-striped table-fluid" cellspacing="3" cellpadding="5">
	<thead>
		<tr>
			<th colspan="3" align="center">
				<h4 class="header"><?php echo 'Document'; ?></h4>
			</th>
		</tr>
	</thead>
	<tbody>

		<!-- Doctype -->
			<?php $advice = $score_advice( 'doctype' ); ?>
		<tr class="<?php echo esc_attr( $advice ); ?>">
			<td class="td-icon">
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/' . $advice . '.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="td-compare">
				<?php echo 'Doctype'; ?>
			</td>
			<td class="td-result">
				<?php
				if ( $document['doctype'] ) :
					echo esc_html( $document['doctype'] );
				else :
					echo 'Missing doctype';
				endif;
				?>
			</td>
		</tr>

		<!-- Encoding -->
			<?php $advice = $score_advice( 'charset' ); ?>
		<tr class="<?php echo esc_attr( $advice ); ?>">
			<td>
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/' . $advice . '.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="compare">
				<?php echo 'Encoding'; ?>
			</td>
			<td>
				<?php
				if ( 'success' === $advice ) {
					echo esc_html( 'Perfect! Your page specifies a character encoding, which is essential for proper text display.' );
				} else {
					echo esc_html( 'Your page is missing a character encoding declaration. This can lead to display issues with special characters. Add a charset meta tag.' );
				}
				?>
			</td>
		</tr>

		<!-- W3C Validity -->
			<?php $advice = $score_advice( 'w3c' ); ?>
		<tr class="<?php echo esc_attr( $advice ); ?>">
			<td>
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/' . $advice . '.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="compare">
				<?php echo 'W3C Validity'; ?>
			</td>
			<td>
				<?php echo 'Errors'; ?> : <?php echo (int) $w3c['errors']; ?>
				<br />
				<?php echo 'Warnings'; ?> : <?php echo (int) $w3c['warnings']; ?>
				<br /><br />
				<?php if ( ! empty( $w3c['messages'] ) && is_array( $w3c['messages'] ) ) : ?>
					<table class="table table-striped table-fluid table-inner" cellpadding="5">
						<tr nobr="true" class="odd">
							<td width="60px"><span class="suh-header"><?php echo 'Type'; ?></span></td>
							<td width="40px"><span class="suh-header"><?php echo 'Line'; ?></span></td>
							<td width="250px"><span class="suh-header"><?php echo 'Message'; ?></span></td>
						</tr>
						<?php
						$i = 0;
						foreach ( $w3c['messages'] as $msg ) :
							$even     = 0 === $i % 2;
							$msg_type = isset( $msg['type'] ) ? $msg['type'] : 'unknown';
							$msg_line = isset( $msg['line'] ) ? $msg['line'] : '-';
							$msg_text = isset( $msg['message'] ) ? $msg['message'] : '';
							?>
							<tr nobr="true" class="<?php echo $even ? 'even' : 'odd'; ?>">
								<td><?php echo esc_html( ucfirst( $msg_type ) ); ?></td>
								<td><?php echo esc_html( $msg_line ); ?></td>
								<td><?php echo esc_html( $msg_text ); ?></td>
							</tr>
							<?php
							$i++;
						endforeach;
						?>
					</table>
				<?php endif; ?>
			</td>
		</tr>

		<!-- Deprecated -->
			<?php $advice = $score_advice( 'noDeprecated' ); ?>
		<tr class="<?php echo esc_attr( $advice ); ?>">
			<td>
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/' . $advice . '.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="compare">
				<?php echo 'Deprecated HTML'; ?>
			</td>
			<td width="70%">
				<?php if ( ! empty( $content['deprecated'] ) ) : ?>
					<table class="table table-striped table-fluid table-inner" cellpadding="5">
						<tr class="odd">
							<td align="center"><span class="suh-header"><?php echo 'Deprecated tags'; ?></span></td>
							<td align="center"><span class="suh-header"><?php echo 'Occurrences'; ?></span></td>
						</tr>
						<?php
						$i = 0;
						foreach ( $content['deprecated'] as $tag_name => $count ) :
							$even = 0 === $i % 2;
							?>
							<tr class="<?php echo $even ? 'even' : 'odd'; ?>">
								<td align="center"><?php echo esc_html( '<' . $tag_name . '>' ); ?></td>
								<td align="center"><?php echo (int) $count; ?></td>
							</tr>
							<?php
							$i++;
						endforeach;
						?>
					</table>
				<?php endif; ?>
				<?php
				if ( 'success' === $advice ) {
					echo esc_html( 'Excellent! Your page does not use deprecated HTML tags.' );
				} else {
					echo esc_html( 'Your page uses deprecated HTML tags. Consider updating to modern HTML5 elements for better compatibility and standards compliance.' );
				}
				?>
			</td>
		</tr>

		<!-- Speed Tips -->
		<tr class="odd">
			<td>
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/neutral.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="compare">
				<?php echo 'Speed Tips'; ?>
			</td>
			<td>

				<table cellspacing="3" cellpadding="5">
					<tbody>

						<tr class="no-top-line even">
													<?php $advice = $score_advice( 'noNestedtables' ); ?>
							<td width="20px"><img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/isset_' . (int) ! $isseter['nestedtables'] . '.png' ); ?>" /></td>
							<td width="330px">
								<?php
								if ( 'success' === $advice ) {
									echo esc_html( 'Good! No nested tables found. Nested tables can slow down page rendering.' );
								} else {
									echo esc_html( 'Your page uses nested tables, which can slow down page rendering. Consider using CSS for layout instead.' );
								}
								?>
							</td>
						</tr>

						<tr class="odd">
													<?php $advice = $score_advice( 'noInlineCSS' ); ?>
							<td><img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/isset_' . (int) ! $isseter['inlinecss'] . '.png' ); ?>" /></td>
							<td>
								<?php
								if ( 'success' === $advice ) {
									echo esc_html( 'Perfect! No inline CSS found. External stylesheets are better for performance and maintainability.' );
								} else {
									echo esc_html( 'Your page uses inline CSS. Consider moving styles to external stylesheets for better performance and caching.' );
								}
								?>
							</td>
						</tr>

						<tr class="even">
													<?php $advice = $score_advice( 'cssCount' ); ?>
							<?php list($img_advice,) = explode( ' ', $advice ); ?>
							<td><img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/isset_' . ( 'success' === $img_advice ? '1' : '0' ) . '.png' ); ?>" /></td>
							<td>
								<?php
								$css_count = is_array( $document['css'] ) ? count( $document['css'] ) : 0;
								if ( 'success' === $advice ) {
									echo esc_html( 'Great! Your page has an optimal number of CSS files (' . $css_count . '). Keep stylesheets minimal for better performance.' );
								} else {
									echo esc_html( 'Your page has ' . $css_count . ' CSS files. Too many CSS files can slow down page load. Consider combining them.' );
								}
								?>
							</td>
						</tr>

						<tr class="odd">
													<?php $advice = $score_advice( 'jsCount' ); ?>
							<?php list($img_advice,) = explode( ' ', $advice ); ?>
							<td><img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/isset_' . ( 'success' === $img_advice ? '1' : '0' ) . '.png' ); ?>" /></td>
							<td>
								<?php
								$js_count = is_array( $document['js'] ) ? count( $document['js'] ) : 0;
								if ( 'success' === $advice ) {
									echo esc_html( 'Excellent! Your page has an optimal number of JavaScript files (' . $js_count . '). Keep scripts minimal for better performance.' );
								} else {
									echo esc_html( 'Your page has ' . $js_count . ' JavaScript files. Too many JS files can slow down page load. Consider combining them.' );
								}
								?>
							</td>
						</tr>

						<tr class="even">
													<?php $advice = $score_advice( 'hasGzip' ); ?>
							<?php list($img_advice,) = explode( ' ', $advice ); ?>
							<td><img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/isset_' . ( 'success' === $img_advice ? '1' : '0' ) . '.png' ); ?>" /></td>
							<td>
								<?php
								echo 'Gzip Compression';
								if ( 'success' === $advice ) {
									echo esc_html( ' - Enabled! Your server is using Gzip compression to reduce file sizes and improve page load times.' );
								} else {
									echo esc_html( ' - Not detected. Enable Gzip compression to reduce file sizes and improve page load speed.' );
								}
								?>
							</td>
						</tr>

					</tbody>
				</table>

			</td>
		</tr>

	</tbody>
</table>

<br /><br /><br />


<!-- Mobile Optimization -->
<!-- Document -->
<table class="table table-striped table-fluid" cellspacing="3" cellpadding="5">
	<thead>
		<tr>
			<th colspan="3" align="center">
				<h4 class="header"><?php echo 'Mobile'; ?></h4>
			</th>
		</tr>
	</thead>
	<tbody>
		<!-- Mobile Optimization -->
		<tr class="odd">
			<td class="td-icon">
				<br />
				<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/neutral.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
			</td>
			<td class="td-compare">
				<?php echo 'Mobile Optimization'; ?>
			</td>
			<td class="td-result">

				<table cellspacing="3" cellpadding="5">
					<tbody>

						<tr class="even">
							<td width="20px"><img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/isset_' . (int) $isseter['appleicons'] . '.png' ); ?>" /></td>
							<td width="330px"><?php echo 'Apple Icon'; ?></td>
						</tr>

						<tr class="odd">
							<td><img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/isset_' . (int) $isseter['viewport'] . '.png' ); ?>" /></td>
							<td><?php echo 'Meta Viewport Tag'; ?></td>
						</tr>

						<tr class="even">
							<td><img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/isset_' . (int) ! $isseter['flash'] . '.png' ); ?>" /></td>
							<td><?php echo 'Flash content'; ?></td>
						</tr>

					</tbody>
				</table>

			</td>
		</tr>

	</tbody>
</table>

<?php if ( $misc ) : ?>
	<br /><br /><br />

	<!-- Optimization -->
	<table class="table table-striped table-fluid" cellspacing="3" cellpadding="5">
		<thead>
			<tr>
				<th colspan="3" align="center">
					<h4 class="header"><?php echo 'Optimization'; ?></h4>
				</th>
			</tr>
		</thead>
		<tbody>

			<!-- Sitemap -->
	<?php $advice = $score_advice( 'hasSitemap' ); ?>
			<tr class="<?php echo esc_attr( $advice ); ?>">
				<td class="td-icon">
					<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/' . $advice . '.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
				</td>
				<td class="td-compare">
					<?php echo 'XML Sitemap'; ?>
				</td>
				<td class="td-result">
					<?php if ( ! empty( $misc['sitemap'] ) ) : ?>
						<?php
						echo esc_html( 'Great! We found an XML sitemap on your website. Sitemaps help search engines discover and index your pages more efficiently.' );
						?>
						<br><br>

						<table class="table table-striped table-fluid table-inner" cellpadding="5">
							<?php
							$i = 0;
							foreach ( $misc['sitemap'] as $sitemap ) :
								$even = 0 === $i % 2;
								?>
								<tr class="<?php echo $even ? 'even' : 'odd'; ?>">
									<td><?php echo esc_html( $sitemap ); ?></td>
								</tr>
								<?php
								$i++;
							endforeach;
							?>
						</table>
					<?php else : ?>
						<?php echo 'Missing'; ?>
						<br><br>
						<?php echo esc_html( 'Your website does not have an XML sitemap. Creating and submitting a sitemap helps search engines discover and index all your important pages.' ); ?>
					<?php endif; ?>
				</td>
			</tr>

			<!-- Robots -->
	<?php $advice = $score_advice( 'hasRobotsTxt' ); ?>
			<tr class="<?php echo esc_attr( $advice ); ?>">
				<td class="td-icon">
					<br />
					<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/' . $advice . '.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
				</td>
				<td class="compare">
					<?php echo 'Robots.txt'; ?>
				</td>
				<td class="td-result">
					<?php if ( $isseter['robotstxt'] ) : ?>
						<?php echo esc_html( 'http://' . $website['domain'] . '/robots.txt' ); ?>
						<br><br>
						<?php
						echo esc_html( 'Great! Your website has a robots.txt file. This helps search engines understand which pages to crawl and index.' );
						?>
					<?php else : ?>
						<?php echo 'Missing'; ?>
						<br><br>
						<?php echo esc_html( 'Your website does not have a robots.txt file. While not always required, a robots.txt file helps control how search engines access your site.' ); ?>
					<?php endif; ?>
				</td>
			</tr>

			<!-- Analytics support -->
	<?php $advice = $score_advice( 'hasAnalytics' ); ?>
			<tr class="<?php echo esc_attr( $advice ); ?>">
				<td class="td-icon">
					<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/' . $advice . '.png' ); ?>" width="32px" height="32px" class="adv-icon" align="middle" />
				</td>
				<td class="compare">
					<?php echo 'Analytics'; ?>
				</td>
				<td class="td-result">
					<?php if ( ! empty( $misc['analytics'] ) ) : ?>
						<?php
						echo esc_html( 'Great! We detected analytics tracking on your website. Analytics help you understand visitor behavior and improve your site.' );
						?>
						<br><br>
						<table class="table table-striped table-fluid table-inner" cellpadding="5">
							<?php
							$i = 0;
							foreach ( $misc['analytics'] as $analytics ) :
								$even = 0 === $i % 2;
								?>
								<tr class="<?php echo $even ? 'even' : 'odd'; ?>">
									<td>
										<img src="<?php echo esc_url( V_WPSA_Config::get_base_url( true ) . '/assets/img/analytics/' . $analytics . '.png' ); ?>" />
										&nbsp;&nbsp;
										<?php echo esc_html( AnalyticsFinder::getProviderName( $analytics ) ); ?>
									</td>
								</tr>
								<?php
								$i++;
							endforeach;
							?>
						</table>
					<?php else : ?>
						<?php echo 'Missing'; ?>
						<br><br>
						<?php echo esc_html( 'Your website does not have analytics tracking installed. Consider adding analytics to gain insights into your visitors and improve your site performance.' ); ?>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>
<?php endif; ?>
