<?php
/**
 * LinkPager component for v_wpsa plugin.
 *
 * Extends CLinkPager to add Bootstrap-compatible page-link classes.
 *
 * @package v_wpsa
 */

/**
 * File: LinkPager.php
 *
 * @package v_wpsa
 */
class LinkPager extends CLinkPager {

	/**
	 * CreatePageButton function.
	 *
	 * @param mixed $label Parameter.
	 * @param mixed $page Parameter.
	 * @param mixed $class Parameter.
	 * @param mixed $hidden Parameter.
	 * @param mixed $selected Parameter.
	 */
	protected function createPageButton( $label, $page, $class, $hidden, $selected) {
		$btn = parent::createPageButton( $label, $page, $class, $hidden, $selected );
		return preg_replace( '#<a #is', '<a class="page-link" ', $btn );
	}
}
