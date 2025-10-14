<?php
/**
 * File: WebsiteForm.php
 *
 * @package V_WP_SEO_Audit
 */

Yii::import( 'application.vendors.Webmaster.Utils.IDN' );

class WebsiteForm extends CFormModel {

	/**
	 * Domain name to analyze (ASCII encoded).
	 *
	 * @var string
	 */
	public $domain;

	/**
	 * Internationalized Domain Name (IDN) in Unicode format.
	 *
	 * @var string
	 */
	public $idn;

	/**
	 * IP address of the domain.
	 *
	 * @var string
	 */
	public $ip;

	/**
	 * rules function.
	 */
	public function rules() {
		return array(
			array(
				'domain',
				'filter',
				'filter' => array( $this, 'trimDomain' ),
			),
			array(
				'domain',
				'filter',
				'filter' => array( $this, 'punycode' ),
			),
			array( 'domain', 'required' ),
			array(
				'domain',
				'match',
				'pattern'     => '#^[a-z\d-]{1,62}\.[a-z\d-]{1,62}(.[a-z\d-]{1,62})*$#i',
				'skipOnError' => true,
			),
			array( 'domain', 'bannedWebsites' ),
			array( 'domain', 'isReachable' ),
			array( 'domain', 'tryToAnalyse' ),
		);

	}

	/**
	 * attributeLabels function.
	 */
	public function attributeLabels() {

		return array( 'domain' => 'Domain' );

	}

	/**
	 * punycode function.
	 *
	 * @param mixed $domain Parameter.
	 */
	public function punycode( $domain ) {

		$idn          = new IDN();
		$this->domain = $idn->encode( $domain );
		$this->idn    = $domain;
		return $this->domain;
	}

	/**
	 * bannedWebsites function.
	 */
	public function bannedWebsites() {

		if ( ! $this->hasErrors()) {
			// Use WordPress-native config function instead of Utils::getLocalConfigIfExists().
			$banned = function_exists( 'v_wp_seo_audit_get_config' ) ? v_wp_seo_audit_get_config( 'domain_restriction' ) : array();
			foreach ($banned as $pattern) {
				if (preg_match( "#{$pattern}#i", $this->idn )) {
					$this->addError( 'domain', 'Website contains bad words' );

				}

			}

		}

	}

	/**
	 * trimDomain function.
	 *
	 * @param mixed $domain Parameter.
	 */
	public function trimDomain( $domain ) {

		$domain = trim( $domain );
		$domain = trim( $domain, '/' );
		$domain = mb_strtolower( $domain );
		$domain = preg_replace( '#^(https?://)#i', '', $domain );
		$domain = preg_replace( '#^www\.#i', '', $domain );
		return $domain;

	}

	/**
	 * isReachable function.
	 */
	public function isReachable() {

		if ( ! $this->hasErrors()) {
			$this->ip = gethostbyname( $this->domain );
			$long     = ip2long( $this->ip );
			if ( -1 === $long || false === $long ) {
				$this->addError( 'domain', 'Could not reach host: ' . $this->domain );

			}

		}

	}

	/**
	 * tryToAnalyse function.
	 *
	 * WordPress-native implementation: performs website analysis inline
	 * instead of calling removed CLI commands.
	 */
	public function tryToAnalyse() {

		if ( ! $this->hasErrors()) {
			// Remove "www" from domain.
			$this->domain = str_replace( 'www.', '', $this->domain );

			// Use WordPress native database class.
			if ( ! class_exists( 'V_WP_SEO_Audit_DB' ) ) {
				$this->addError( 'domain', 'Database error' );
				return false;
			}

			$db = new V_WP_SEO_Audit_DB();
			// Check if website already exists in the database.
			$website = $db->get_website_by_domain( $this->domain, array( 'modified', 'id' ) );

			// If website exists and we do not need to update data then exit from method.
			$notUpd = false;
			// Get cache time - use WordPress filter with default of 24 hours.
			$cache_time = apply_filters( 'v_wp_seo_audit_cache_time', DAY_IN_SECONDS );
			global $v_wp_seo_audit_app;
			if ( null !== $v_wp_seo_audit_app && isset( $v_wp_seo_audit_app->params['analyzer.cache_time'] ) ) {
				$cache_time = $v_wp_seo_audit_app->params['analyzer.cache_time'];
			}

			if ( $website && ( strtotime( $website['modified'] ) + $cache_time > time() ) ) {
				$notUpd = true;
				return true;
			}

			// If website exists but needs update, delete old PDFs.
			if ( $website && ! $notUpd ) {
				// Use WordPress-native delete function instead of Utils::deletePdf().
				if ( function_exists( 'v_wp_seo_audit_delete_pdf' ) ) {
					v_wp_seo_audit_delete_pdf( $this->domain );
					v_wp_seo_audit_delete_pdf( $this->domain . '_pagespeed' );
				}
				$wid = $website['id'];
			} else {
				$wid = null;
			}

			// Call WordPress-native analysis function.
			if ( ! function_exists( 'v_wp_seo_audit_analyze_website' ) ) {
				$this->addError( 'domain', 'Analysis function not available' );
				return false;
			}

			$result = v_wp_seo_audit_analyze_website( $this->domain, $this->idn, $this->ip, $wid );

			if ( is_wp_error( $result ) ) {
				$this->addError( 'domain', $result->get_error_message() );
				return false;
			}

			// After analysis, check if DB record exists.
			$websiteCheck = $db->get_website_by_domain( $this->domain, array( 'id' ) );
			if ( ! $websiteCheck) {
				$this->addError( 'domain', 'Analysis failed: domain record not created. Please try again or check your domain input.' );
				return false;
			}
			return true;
		}

	}
}
