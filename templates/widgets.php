<?php
/**
 * File: widgets.php
 *
 * Description: Widget templates for the plugin.
 *
 * @package v_wpsa
 * @author Vontainment
 * @license MIT
 * @license URI https://opensource.org/licenses/MIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render website list widget.
 * Displays a paginated list of analyzed websites with thumbnails and scores.
 *
 * @param array $args Widget arguments.
 *                    - 'order' (string): Order by clause (default: 'modified DESC').
 *                    - 'page' (int): Current page number (default: 1).
 *                    - 'per_page' (int): Number of items per page (default: 12).
 *
 * @return string Widget HTML output.
 */
function v_wpsa_render_website_list( $args = array() ) {
	// Default arguments.
	$defaults = array(
		'order'    => 'modified DESC',
		'page'     => 1,
		'per_page' => V_WPSA_Config::get( 'param.rating_per_page', 12 ),
	);

	$args = wp_parse_args( $args, $defaults );

	// Get database instance.
	$db = new V_WPSA_DB();

	// Calculate offset.
	$offset = ( $args['page'] - 1 ) * $args['per_page'];

	// Get websites from database.
	$websites = $db->get_websites(
		array(
			'order'   => $args['order'],
			'limit'   => $args['per_page'],
			'offset'  => $offset,
			'columns' => array( 'id', 'domain', 'idn', 'score', 'added', 'modified' ),
		)
	);

	if ( empty( $websites ) ) {
		return '';
	}

	// Get total count for pagination.
	$total = $db->count_websites();

	// Prepare thumbnail stack.
	$thumbnail_stack = array();
	foreach ( $websites as $website ) {
		$thumbnail_key = (string) $website['id'];
		// Ensure frontend receives an object with a `thumb` property to be
		// compatible with the dynamicThumbnail() helper in assets/js/base.js.
		$thumbnail_stack[ $thumbnail_key ] = array(
			'thumb' => v_wpsa_get_website_thumbnail_url(
				array(
					'url'  => $website['domain'],
					'size' => 'l',
				)
			),
		);
	}

	// Start output buffering.
	ob_start();

	// Render widget template.
	v_wpsa_render_website_list_template( $websites, $thumbnail_stack, $args, $total );

	return ob_get_clean();
}

/**
 * Render website list template HTML.
 *
 * @param array $websites Websites data array.
 * @param array $thumbnail_stack Thumbnail URLs keyed by website ID (e.g., '12').
 * @param array $args Widget arguments.
 * @param int   $total Total number of websites.
 */
function v_wpsa_render_website_list_template( $websites, $thumbnail_stack, $args, $total ) {
	$base_url = V_WPSA_Config::get_base_url( true );
	?>
	<script type="text/javascript">
		"use strict";
		jQuery(function($){
			var urls = <?php echo wp_json_encode( ! empty( $thumbnail_stack ) ? $thumbnail_stack : new stdClass() ); ?>;
			if (typeof dynamicThumbnail === 'function') {
				dynamicThumbnail(urls);
			}
		});
	</script>

	<div class="row">
		<?php foreach ( $websites as $website ) : ?>
			<?php
			// Generate view report URL with hash fragment for deep linking.
			$domain      = $website['domain'];
			$hash_domain = str_replace( '.', '-', $domain );
			$url         = home_url( '/#' . $hash_domain );
			?>
			<div class="col col-12 col-md-6 col-lg-4 mb-4">
				<div class="card mb-3">
					<h5 class="card-header"><?php echo esc_html( v_wpsa_crop_domain( $website['idn'] ) ); ?></h5>
					<a class="v-wpsa-view-report" href="<?php echo esc_url( $url ); ?>" data-domain="<?php echo esc_attr( $domain ); ?>">
						<img class="card-img-top" id="thumb_<?php echo absint( $website['id'] ); ?>" src="<?php echo esc_url( $base_url . '/assets/img/loader.gif' ); ?>" alt="<?php echo esc_attr( $website['idn'] ); ?>" />
					</a>
					<ul class="list-group list-group-flush">
						<li class="list-group-item">
							<p class="card-text">
								<?php
								/* translators: %d: score out of 100 */
								echo esc_html( sprintf( __( 'The score is %d/100', 'v-wpsa' ), absint( $website['score'] ) ) );
								?>
							</p>
							<a class="v-wpsa-view-report" href="<?php echo esc_url( $url ); ?>" data-domain="<?php echo esc_attr( $domain ); ?>">
								<div class="progress mb-3">
									<div class="progress-bar progress-bar-striped bg-info" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo absint( $website['score'] ); ?>%;"></div>
								</div>
							</a>
						</li>
					</ul>

					<div class="card-body">
						<a class="btn btn-primary v-wpsa-view-report" href="<?php echo esc_url( $url ); ?>" data-domain="<?php echo esc_attr( $domain ); ?>" role="button">
							<?php esc_html_e( 'Review', 'v-wpsa' ); ?>
						</a>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<?php
	// Render pagination.
	if ( $total > $args['per_page'] ) {
		v_wpsa_render_pagination( $args['page'], $args['per_page'], $total );
	}
	?>

	<div class="clearfix"></div>
	<?php
}

/**
 * Render pagination for website list.
 *
 * @param int $current_page Current page number.
 * @param int $per_page Items per page.
 * @param int $total Total number of items.
 */
function v_wpsa_render_pagination( $current_page, $per_page, $total ) {
	$total_pages = ceil( $total / $per_page );

	if ( $total_pages <= 1 ) {
		return;
	}

	echo '<nav aria-label="' . esc_attr__( 'Website list pagination', 'v-wpsa' ) . '">';
	echo '<ul class="pagination flex-wrap">';

	// Previous page link.
	if ( $current_page > 1 ) {
		$prev_page = $current_page - 1;
		echo '<li class="page-item">';
		echo '<a class="page-link" href="' . esc_url( add_query_arg( 'page', $prev_page ) ) . '" aria-label="' . esc_attr__( 'Previous', 'v-wpsa' ) . '">';
		echo '<span aria-hidden="true">&laquo;</span>';
		echo '</a>';
		echo '</li>';
	} else {
		echo '<li class="page-item disabled">';
		echo '<span class="page-link"><span aria-hidden="true">&laquo;</span></span>';
		echo '</li>';
	}

	// Page number links.
	for ( $i = 1; $i <= $total_pages; $i++ ) {
		$active_class = ( $i === $current_page ) ? ' active' : '';
		echo '<li class="page-item' . esc_attr( $active_class ) . '">';
		if ( $i === $current_page ) {
			echo '<span class="page-link">' . absint( $i ) . '</span>';
		} else {
			echo '<a class="page-link" href="' . esc_url( add_query_arg( 'page', $i ) ) . '">' . absint( $i ) . '</a>';
		}
		echo '</li>';
	}

	// Next page link.
	if ( $current_page < $total_pages ) {
		$next_page = $current_page + 1;
		echo '<li class="page-item">';
		echo '<a class="page-link" href="' . esc_url( add_query_arg( 'page', $next_page ) ) . '" aria-label="' . esc_attr__( 'Next', 'v-wpsa' ) . '">';
		echo '<span aria-hidden="true">&raquo;</span>';
		echo '</a>';
		echo '</li>';
	} else {
		echo '<li class="page-item disabled">';
		echo '<span class="page-link"><span aria-hidden="true">&raquo;</span></span>';
		echo '</li>';
	}

	echo '</ul>';
	echo '</nav>';
}

/**
 * Helper function to get website thumbnail URL.
 *
 * @param array $args Arguments for thumbnail.
 *                    - 'url' (string): Website URL.
 *                    - 'size' (string): Thumbnail size (s, m, l).
 *
 * @return string Thumbnail URL.
 */
function v_wpsa_get_website_thumbnail_url( $args = array() ) {
	$defaults = array(
		'url'  => '',
		'size' => 'm',
	);

	$args = wp_parse_args( $args, $defaults );

	if ( class_exists( 'V_WPSA_Thumbnail', false ) ) {
		return V_WPSA_Thumbnail::get_og_image( $args );
	}

	// Try to return a cached thumbnail from the WordPress uploads directory.
	if ( function_exists( 'wp_upload_dir' ) ) {
		$upload_dir  = wp_upload_dir();
		$filename    = md5( $args['url'] ) . '.jpg';
		$cached_path = rtrim( $upload_dir['basedir'], '\/' ) . '/seo-audit/thumbnails/' . $filename;
		$cached_url  = rtrim( $upload_dir['baseurl'], '\/' ) . '/seo-audit/thumbnails/' . $filename;

		if ( file_exists( $cached_path ) ) {
			// Add cache-busting parameter based on file modification time.
			$cache_bust = filemtime( $cached_path );
			return add_query_arg( 'v', $cache_bust, $cached_url );
		}
	}

	// Fallback to direct thum.io URL if no cached thumbnail is available.
	$width = '350';
	return "https://image.thum.io/get/maxAge/350/width/{$width}/https://" . $args['url'];
}

/**
 * Helper function to crop domain name for display.
 *
 * @param string $domain Domain name to crop.
 * @param int    $max_length Maximum length (default: 25).
 *
 * @return string Cropped domain name.
 */
function v_wpsa_crop_domain( $domain, $max_length = 25 ) {
	// If Utils class exists, use it.
	if ( class_exists( 'Utils', false ) && method_exists( 'Utils', 'cropDomain' ) ) {
		return V_WPSA_Utils::crop_domain( $domain );
	}

	// Fallback to simple truncation.
	if ( strlen( $domain ) > $max_length ) {
		return substr( $domain, 0, $max_length - 3 ) . '...';
	}

	return $domain;
}
