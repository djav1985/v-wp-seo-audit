<?php
/**
 * File: Website.php
 *
 * @package V_WP_SEO_Audit
 */

Yii::import( 'application.vendors.Webmaster.Utils.IDN' );

class Website extends CActiveRecord {

	public static function model( $className = __CLASS__) {
		return parent::model( $className );

	}

	/**
	 * tableName function.
	 */
	public function tableName() {
		return Yii::app()->db->tablePrefix . 'website';

	}

	/**
	 * total function.
	 */
	public function total() {
		return $this->cache( 60 * 60 * 5 )->count();
	}

	/**
	 * Remove website by domain using WordPress native database.
	 *
	 * @param string $domain Domain name.
	 * @return bool True on success, false on failure.
	 */
	public static function removeByDomain( $domain) {
		// Use WordPress native database class.
		if ( ! class_exists( 'V_WP_SEO_Audit_DB' ) ) {
			return false;
		}

		$idn    = new IDN();
		$domain = $idn->encode( $domain );
		$db     = new V_WP_SEO_Audit_DB();

		// Get website by domain.
		$website = $db->get_website_by_domain( $domain );
		if ( ! $website ) {
			return false;
		}

		$website_id = $website['id'];

		// Delete website and all related records.
		return $db->delete_website( $website_id );
	}
}
