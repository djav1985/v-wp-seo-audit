<?php
Yii::import( 'application.vendors.Webmaster.Source.*' );
Yii::import( 'application.vendors.Webmaster.TagCloud.*' );
Yii::import( 'application.vendors.Webmaster.Utils.*' );
Yii::import( 'application.vendors.Webmaster.Matrix.*' );
Yii::import( 'application.vendors.Webmaster.Rates.*' );
Yii::import( 'application.vendors.Webmaster.Utils.*' );

/**
Error codes:
 101. Server temporary unvailable
 102. Couldn't grab html from website
 103. Website contains badwords
 201. Error while inserting data
 202. Error while updating data
 */
class ParseCommand extends CConsoleCommand {
	private $idn;
	private $domain;
	private $ip;
	private $score;
	private $html;
	private $errorcode;
	private $document;
	private $favicon;
	private $content;
	private $image;
	private $links;
	private $metatags;
	private $seoanalyse;
	private $validation;
	private $tagcloud;
	private $matrix;
	private $misc;
	private $url;

	/**
	 * @var MetaTags
	 */
	private $Metatags;

	private function _init( $domain, $idn, $ip) {
		$this->domain = $domain;
		$this->idn    = $idn;
		$this->ip     = $ip;
	}

	public function actionUpdate( $domain, $idn, $ip, $wid) {
		$this->_init( $domain, $idn, $ip );
		// Check if were errors during html grabbing.
		if ( ! $this->grabHtml()) {
			return $this->errorcode;
		}

		if ( ! $this->checkForBadwords()) {
			return $this->errorcode;
		}

		$this->parseWebsite();

		// Begin transaction
		$transaction = Yii::app()->db->beginTransaction();
		try {

			// Get db command
			$command = Yii::app()->db->createCommand();

			// Update base domain information
			$now = date( 'Y-m-d H:i:s' );
			$command->update(
				'{{website}}',
				array(
					'modified'  => $now,
					'score'     => $this->score,
					'idn'       => $this->idn,
					'final_url' => $this->url,
				),
				'id=:id',
				array( ':id' => $wid )
			);

			// Update cloud and matrix
			$command->update(
				'{{cloud}}',
				array(
					'words'  => @json_encode( $this->tagcloud ),
					'matrix' => @json_encode( $this->matrix ),
				),
				'wid=:wid',
				array( ':wid' => $wid )
			);

			// Update deprecated tags, images, headings
			$command->update(
				'{{content}}',
				array(
					'deprecated'     => @json_encode( $this->document['deprecatedTags'] ),
					'headings'       => @json_encode( $this->content['headings'] ),
					'isset_headings' => (int) (bool) $this->content['issetHeadings'],
					'total_img'      => (int) $this->image['totalCount'],
					'total_alt'      => (int) $this->image['totalAlt'],
				),
				'wid=:wid',
				array( ':wid' => $wid )
			);

			// Update document data
			$command->update(
				'{{document}}',
				array(
					'doctype'   => mb_substr( (string) $this->document['doctype'], 0, 100 ),
					'lang'      => mb_substr( (string) $this->document['langID'], 0, 2 ),
					'charset'   => mb_substr( (string) $this->metatags['charset'], 0, 20 ),
					'css'       => (int) $this->document['cssCount'],
					'js'        => (int) $this->document['jsCount'],
					'htmlratio' => $this->seoanalyse['htmlratio'],
					'favicon'   => $this->favicon,
				),
				'wid=:wid',
				array( ':wid' => $wid )
			);

			// Update BOOLEAN data
			$command->update(
				'{{issetobject}}',
				array(
					'flash'        => (int) (bool) $this->content['flash'],
					'iframe'       => (int) (bool) $this->content['iframe'],
					'nestedtables' => (int) (bool) $this->content['nestedTables'],
					'inlinecss'    => (int) (bool) $this->content['inlinceCss'],
					'email'        => (int) $this->content['email'],
					'viewport'     => (int) (bool) $this->metatags['viewport'],
					'dublincore'   => (int) ! empty( $this->metatags['dublincore'] ),
					'printable'    => (int) (bool) $this->document['isPrintable'],
					'appleicons'   => (int) (bool) $this->document['appleIcon'],
					'robotstxt'    => (int) $this->misc['robotstxt'],
					'gzip'         => (int) $this->misc['gzip'],
				),
				'wid=:wid',
				array( ':wid' => $wid )
			);

			// Update Links
			$command->update(
				'{{links}}',
				array(
					'links'             => @json_encode( $this->links['links'] ),
					'internal'          => (int) $this->links['internal'],
					'external_dofollow' => (int) $this->links['externalDofollow'],
					'external_nofollow' => (int) $this->links['externalNofollow'],
					'isset_underscore'  => (int) (bool) $this->links['issetUnderscore'],
					'files_count'       => (int) $this->links['files'],
					'friendly'          => (int) (bool) $this->links['friendly'],
				),
				'wid=:wid',
				array( ':wid' => $wid )
			);

			// Update metatags
			$command->update(
				'{{metatags}}',
				array(
					'title'        => $this->metatags['title'],
					'keyword'      => $this->metatags['keywords'],
					'description'  => $this->metatags['description'],
					'ogproperties' => @json_encode( $this->metatags['ogproperties'] ),
				),
				'wid=:wid',
				array( ':wid' => $wid )
			);

			// Update w3c
			$command->update(
				'{{w3c}}',
				array(
					'validator' => 'html',
					'valid'     => (int) (bool) $this->validation['w3c']['status'],
					'errors'    => (int) $this->validation['w3c']['errors'],
					'warnings'  => (int) $this->validation['w3c']['warnings'],
				),
				'wid=:wid',
				array( ':wid' => $wid )
			);

			$command->reset();

			$command->update(
				'{{misc}}',
				array(
					'sitemap'   => @json_encode( $this->misc['sitemap'] ),
					'analytics' => @json_encode( $this->misc['analytics'] ),
				),
				'wid=:wid',
				array( ':wid' => $wid )
			);

			$command->reset();

			// All is ok. Commit the result
			$transaction->commit();

		} catch (Exception $e) {
			Yii::log( $e, CLogger::LEVEL_ERROR );
			$transaction->rollback();
			return 202;
		}
		return 0;
	}

	public function actionInsert( $domain, $idn, $ip) {
		$this->_init( $domain, $idn, $ip );
		if ( ! $this->grabHtml()) {
			return $this->errorcode;
		}

		if ( ! $this->checkForBadwords()) {
			return $this->errorcode;
		}

		// Parse website and set variables
		$this->parseWebsite();

		// Begin transaction
		$transaction = Yii::app()->db->beginTransaction();
		try {

			// Get db command
			$command = Yii::app()->db->createCommand();

			// Insert base domain information
			$now = date( 'Y-m-d H:i:s' );
			$command->insert(
				'{{website}}',
				array(
					'domain'    => $this->domain,
					'md5domain' => md5( $this->domain ),
					'idn'       => $this->idn,
					'added'     => $now,
					'modified'  => $now,
					'final_url' => $this->url,
					'score'     => $this->score,
				)
			);

			// Get website's ID
			$wid = Yii::app()->db->getLastInsertID();

			// Insert cloud and matrix
			$command->insert(
				'{{cloud}}',
				array(
					'wid'    => $wid,
					'words'  => @json_encode( $this->tagcloud ),
					'matrix' => @json_encode( $this->matrix ),
				)
			);

			// Insert deprecated tags, headings, images
			$command->insert(
				'{{content}}',
				array(
					'wid'            => $wid,
					'deprecated'     => @json_encode( $this->document['deprecatedTags'] ),
					'headings'       => @json_encode( $this->content['headings'] ),
					'isset_headings' => (int) (bool) $this->content['issetHeadings'],
					'total_img'      => (int) $this->image['totalCount'],
					'total_alt'      => (int) $this->image['totalAlt'],
				)
			);

			// Insert document data
			$command->insert(
				'{{document}}',
				array(
					'wid'       => $wid,
					'doctype'   => mb_substr( (string) $this->document['doctype'], 0, 100 ),
					'lang'      => mb_substr( (string) $this->document['langID'], 0, 2 ),
					'charset'   => mb_substr( (string) $this->metatags['charset'], 0, 20 ),
					'css'       => (int) $this->document['cssCount'],
					'js'        => (int) $this->document['jsCount'],
					'htmlratio' => $this->seoanalyse['htmlratio'],
					'favicon'   => $this->favicon,
				)
			);

			// Insert BOOLEAN data
			$command->insert(
				'{{issetobject}}',
				array(
					'wid'          => $wid,
					'flash'        => (int) $this->content['flash'],
					'iframe'       => (int) (bool) $this->content['iframe'],
					'nestedtables' => (int) (bool) $this->content['nestedTables'],
					'inlinecss'    => (int) (bool) $this->content['inlinceCss'],
					'email'        => (int) (bool) $this->content['email'],
					'viewport'     => (int) (bool) $this->metatags['viewport'],
					'dublincore'   => (int) ! empty( $this->metatags['dublincore'] ),
					'printable'    => (int) (bool) $this->document['isPrintable'],
					'appleicons'   => (int) (bool) $this->document['appleIcon'],
					'robotstxt'    => (int) (bool) $this->misc['robotstxt'],
					'gzip'         => (int) (bool) $this->misc['gzip'],
				)
			);

			// Insert Links
			$command->insert(
				'{{links}}',
				array(
					'wid'               => $wid,
					'links'             => @json_encode( $this->links['links'] ),
					'internal'          => (int) $this->links['internal'],
					'external_dofollow' => (int) $this->links['externalDofollow'],
					'external_nofollow' => (int) $this->links['externalNofollow'],
					'isset_underscore'  => (int) (bool) $this->links['issetUnderscore'],
					'files_count'       => (int) $this->links['files'],
					'friendly'          => (int) (bool) $this->links['friendly'],
				)
			);

			// Insert metatags
			$command->insert(
				'{{metatags}}',
				array(
					'wid'          => $wid,
					'title'        => $this->metatags['title'],
					'keyword'      => $this->metatags['keywords'],
					'description'  => $this->metatags['description'],
					'ogproperties' => @json_encode( $this->metatags['ogproperties'] ),
				)
			);

			// Insert w3c
			$command->insert(
				'{{w3c}}',
				array(
					'wid'       => $wid,
					'validator' => 'html',
					'valid'     => (int) (bool) $this->validation['w3c']['status'],
					'errors'    => (int) $this->validation['w3c']['errors'],
					'warnings'  => (int) $this->validation['w3c']['warnings'],
				)
			);

			// Insert misc
			$command->insert(
				'{{misc}}',
				array(
					'wid'       => $wid,
					'sitemap'   => @json_encode( $this->misc['sitemap'] ),
					'analytics' => @json_encode( $this->misc['analytics'] ),
				)
			);

			// All is ok. Commit the result
			$transaction->commit();

		} catch (Exception $e) {
			Yii::log( $e, CLogger::LEVEL_ERROR );
			$transaction->rollback();
			return 201;
		}

		return 0;
	}

	private function checkForBadwords() {
		if ( ! Yii::app()->params['param.bad_words_validation']) {
			return true;
		}
		$strip = trim( $this->Metatags->getTitle() . ' ' . $this->Metatags->getDescription() . ' ' . $this->Metatags->getKeywords() . ' ' . Helper::striptags( $this->html ) );

		$badwords_cfg  = Utils::getLocalConfigIfExists( 'badwords' );
		$badwords      = implode( '|', $badwords_cfg );
		$existsBadWord = preg_match_all( "/\b(" . $badwords . ")\b/iu", $strip, $matches );
		if ($existsBadWord) {
			$this->errorcode = 103;
			return false;
		}
		return true;
	}

	private function parseWebsite() {
		$document                         = new Document( $this->html );
		$this->document['doctype']        = $document->getDoctype();
		$this->document['isPrintable']    = $document->isPrintable();
		$this->document['appleIcon']      = $document->issetAppleIcon();
		$this->document['deprecatedTags'] = $document->getDeprecatedTags();
		$this->document['langID']         = $document->getLanguageID();
		$this->document['cssCount']       = $document->getCssFilesCount();
		$this->document['jsCount']        = $document->getJsFilesCount();

		$favicon       = new Favicon( $this->html, $this->domain );
		$this->favicon = $favicon->getFavicon();

		$content                        = new Content( $this->html );
		$this->content['flash']         = $content->issetFlash();
		$this->content['iframe']        = $content->issetIframe();
		$this->content['headings']      = $content->getHeadings();
		$this->content['issetHeadings'] = ! Helper::isEmptyArray( $this->content['headings'] );
		$this->content['nestedTables']  = $content->issetNestedTables();
		$this->content['inlinceCss']    = $content->issetInlineCss();
		$this->content['email']         = $content->issetEmail();

		$image                     = new Image( $this->html );
		$this->image['totalCount'] = $image->getTotal();
		$this->image['totalAlt']   = $image->getAltCount();

		$links                           = new Links( $this->html, $this->domain, $this->idn );
		$this->links['links']            = $links->getLinks();
		$this->links['internal']         = $links->getInternalCount();
		$this->links['externalNofollow'] = $links->getExternalNofollowCount();
		$this->links['externalDofollow'] = $links->getExternalDofollowCount();
		$this->links['files']            = $links->getFilesCount();
		$this->links['issetUnderscore']  = $links->issetUnderscore();
		$this->links['friendly']         = $links->isAllLinksAreFriendly();

		$this->metatags['title']        = $this->Metatags->getTitle();
		$this->metatags['description']  = $this->Metatags->getDescription();
		$this->metatags['keywords']     = $this->Metatags->getKeywords();
		$this->metatags['charset']      = $this->Metatags->getCharset();
		$this->metatags['viewport']     = $this->Metatags->getViewPort() !== null ? 1 : 0;
		$this->metatags['dublincore']   = $this->Metatags->getDublinCore();
		$this->metatags['ogproperties'] = $this->Metatags->getOgMetaProperties();

		$seoanalyse                    = new SeoAnalyse( $this->html );
		$this->seoanalyse['htmlratio'] = $seoanalyse->getHtmlRatio();

		$validation              = new Validation( $this->domain );
		$this->validation['w3c'] = $validation->w3cHTML();

		$tagcloud       = new TagCloud( $this->html, $this->document['langID'] );
		$this->tagcloud = $tagcloud->generate( Yii::app()->params['analyzer.tag_cloud'] );

		$matrix = new SearchMatrix();
		$matrix->addWords( array_slice( array_keys( $this->tagcloud ), 0, Yii::app()->params['analyzer.consistency_count'] ) );
		$matrix->addSearchInString( 'title', $this->metatags['title'] );
		$matrix->addSearchInString( 'description', $this->metatags['description'] );
		$matrix->addSearchInString( 'keywords', $this->metatags['keywords'] );
		$matrix->addSearchInArrayRecursive( 'headings', $this->content['headings'] );
		$this->matrix = $matrix->generate();

		$optimization            = new Optimization( $this->domain, $this->url );
		$analytics               = new AnalyticsFinder( $this->html );
		$this->misc['sitemap']   = $optimization->getSitemap();
		$this->misc['robotstxt'] = $optimization->hasRobotsTxt();
		$this->misc['gzip']      = $optimization->hasGzipSupport();
		$this->misc['analytics'] = $analytics->findAll();

		$this->score = $this->getScore();
	}

	private function getScore() {
		$rateprovider = new RateProvider();
		$rateprovider->addCompareArray( 'htmlratio', $this->seoanalyse['htmlratio'] );
		$rateprovider->addCompareArray( 'title', mb_strlen( (string) $this->metatags['title'] ) );
		$rateprovider->addCompareArray( 'description', mb_strlen( (string) $this->metatags['description'] ) );
		$rateprovider->addCompareArray( 'jsCount', $this->document['jsCount'] );
		$rateprovider->addCompareArray( 'cssCount', $this->document['cssCount'] );
		$rateprovider->addCompare( 'noFlash', ! $this->content['flash'] );
		$rateprovider->addCompare( 'noIframe', ! $this->content['iframe'] );
		$rateprovider->addCompare( 'issetHeadings', $this->content['issetHeadings'] );
		$rateprovider->addCompare( 'noNestedtables', ! $this->content['nestedTables'] );
		$rateprovider->addCompare( 'noInlineCSS', ! $this->content['inlinceCss'] );
		$rateprovider->addCompare( 'noEmail', ! $this->content['email'] );
		$rateprovider->addCompare( 'issetFavicon', ! empty( $this->favicon ) );
		$rateprovider->addCompare( 'imgHasAlt', $this->image['totalCount'] == $this->image['totalAlt'] );
		$rateprovider->addCompare( 'noUnderScore', ! $this->links['issetUnderscore'] );
		$rateprovider->addCompare( 'issetInternalLinks', $this->links['internal'] > 0 );
		$rateprovider->addCompare( 'isFriendlyUrl', $this->links['friendly'] );
		$rateprovider->addCompare( 'keywords', ! empty( $this->metatags['keywords'] ) );
		$rateprovider->addCompare( 'charset', ! empty( $this->metatags['charset'] ) );
		$rateprovider->addCompare( 'viewport', ! empty( $this->metatags['viewport'] ) );
		$rateprovider->addCompare( 'dublincore', ! empty( $this->metatags['dublincore'] ) );
		$rateprovider->addCompare( 'ogmetaproperties', ! empty( $this->metatags['ogproperties'] ) );
		$rateprovider->addCompare( 'w3c', $this->validation['w3c']['status'] );
		$rateprovider->addCompare( 'isPrintable', $this->document['isPrintable'] );
		$rateprovider->addCompare( 'issetAppleIcons', $this->document['appleIcon'] );
		$rateprovider->addCompare( 'noDeprecated', empty( $this->document['deprecatedTags'] ) );
		$rateprovider->addCompare( 'lang', ! empty( $this->document['langID'] ) );
		$rateprovider->addCompare( 'doctype', ! empty( $this->document['doctype'] ) );
		$rateprovider->addCompare( 'hasRobotsTxt', $this->misc['robotstxt'] );
		$rateprovider->addCompare( 'hasSitemap', ! empty( $this->misc['sitemap'] ) );
		$rateprovider->addCompare( 'hasGzip', $this->misc['gzip'] );
		$rateprovider->addCompare( 'hasAnalytics', ! empty( $this->misc['analytics'] ) );
		$rateprovider->addCompareMatrix( $this->matrix );
		return $rateprovider->getScore();
	}

	private function grabHtml() {
		$url = 'http://' . $this->domain;
		$ch  = Utils::ch( curl_init( $url ) );
		if (false === $ch) {
			$this->errorcode = 102;
			return false;
		}

		$this->html = curl_exec( $ch );
		if (curl_errno( $ch )) {
			$this->errorcode = 102;
			return false;
		}

		$info      = curl_getinfo( $ch );
		$this->url = Utils::url_get_scheme_host( Utils::curl_get_final_url( $info, $url ), $url );

		$this->Metatags = new MetaTags( $this->html );
		$charset        = $this->Metatags->getCharset();
		if ( ! empty( $charset ) and strtolower( $charset ) != 'utf-8') {
			$this->html     = @iconv( $charset, 'utf-8//IGNORE', $this->html );
			$this->Metatags = new MetaTags( $this->html );
		}

		return true;
	}
}
