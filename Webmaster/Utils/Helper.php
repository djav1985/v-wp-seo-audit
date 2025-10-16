<?php
/**
 * File: Helper.php
 *
 * Description: Utility helper functions.
 *
 * @package v_wpsa
 * @author Vontainment
 * @license MIT
 * @license URI https://opensource.org/licenses/MIT
 */

class Helper {
	public static function striptags( $html ) {
		$html   = preg_replace( '/(<|>)\1{2}/is', '', (string) $html );
		$search = array(
			'#<style[^>]*?>.*?</style>#si', // Strip style tags properly
			'#<script[^>]*?>.*?</script>#si', // Strip out javascript
			'#<!--.*?>.*?<*?-->#si', // Strip if
			'#<[\/\!]*?[^<>]*?>#si',         // Strip out HTML tags*/
			'#<![\s\S]*?--[ \t\n\r]*>#si',  // Strip multi-line comments including CDATA
		);
		$html   = preg_replace( $search, ' ', (string) $html );
		$html   = html_entity_decode( (string) $html, ENT_QUOTES, 'UTF-8' );
		$html   = preg_replace( '/&#?[a-z0-9]{2,8};/i', '', (string) $html );
		$html   = preg_replace( '#(<\/[^>]+?>)(<[^>\/][^>]*?>)#i', '$1 $2', (string) $html );
		return $html;
	}

	public static function isEmptyArray( $array ) {
		foreach ( $array as $value ) {
			if ( is_array( $value ) ) {
				if ( self::isEmptyArray( $value ) == false ) {
					return false;
				}
			} else {
				return empty( $value );
			}
		}
		return true;
	}
}
