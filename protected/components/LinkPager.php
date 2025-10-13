<?php
/**
 * File: LinkPager.php
 *
 * @package V_WP_SEO_Audit
 */

class LinkPager extends CLinkPager {

	protected function createPageButton( $label, $page, $class, $hidden, $selected) {
		$btn = parent::createPageButton( $label, $page, $class, $hidden, $selected );
		return preg_replace( '#<a #is', '<a class="page-link" ', $btn );
	}
}
