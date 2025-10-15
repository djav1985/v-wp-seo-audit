<?php
/**
 * File: WebsitestatController.php
 *
 * @package v_wpsa
 */

Yii::import( 'application.vendors.Webmaster.Rates.*' );
Yii::import( 'application.vendors.Webmaster.Source.*' );
Yii::import( 'application.vendors.Webmaster.Google.*' );
class WebsitestatController extends Controller {

	/**
	 * Domain name to analyze.
	 *
	 * @var string
	 */
	protected $domain;

	/**
	 * Database command object.
	 *
	 * @var V_WPSA_DB
	 */
	protected $command;

	/**
	 * Website data array.
	 *
	 * @var array
	 */
	protected $website;

	/**
	 * Website ID.
	 *
	 * @var int
	 */
	protected $wid;

	/**
	 * Time difference for cache expiration.
	 *
	 * @var int
	 */
	protected $diff;

	/**
	 * String representation of time.
	 *
	 * @var string
	 */
	protected $strtime;

	/**
	 * Thumbnail URL.
	 *
	 * @var string
	 */
	protected $thumbnail;

	/**
	 * Tag cloud data.
	 *
	 * @var array
	 */
	protected $cloud = array();

	/**
	 * Content analysis data.
	 *
	 * @var array
	 */
	protected $content = array();

	/**
	 * Document structure data.
	 *
	 * @var array
	 */
	protected $document = array();

	/**
	 * Set flags for various checks.
	 *
	 * @var array
	 */
	protected $isseter = array();

	/**
	 * Links analysis data.
	 *
	 * @var array
	 */
	protected $links = array();

	/**
	 * W3C validation data.
	 *
	 * @var array
	 */
	protected $w3c = array();

	/**
	 * Meta tags data.
	 *
	 * @var array
	 */
	protected $meta = array();

	/**
	 * Miscellaneous data.
	 *
	 * @var array
	 */
	protected $misc = array();

	/**
	 * Generated timestamps and metadata.
	 *
	 * @var array
	 */
	protected $generated = array();
	/**
	 * init function.
	 */
	public function init() {
		parent::init();

		// Use WordPress native database class.
		if ( ! class_exists( 'V_WPSA_DB' ) ) {
			throw new CHttpException( 500, 'Database class not available' );
		}

		$this->command = new V_WPSA_DB();
		$this->domain  = isset( $_GET['domain'] ) ? $_GET['domain'] : null;

		if (
		! $this->website = $this->command->get_website_by_domain( $this->domain, array( 'id', 'domain', 'modified', 'idn', 'score', 'final_url' ) )
		) {
			if ( ! Yii::app()->params['param.instant_redirect']) {
				$form         = new WebsiteForm();
				$form->domain = $this->domain;
				if ($form->validate()) {
					$this->redirect( $this->createUrl( 'websitestat/generateHTML', array( 'domain' => $this->domain ) ) );
				}

			}
			throw new CHttpException( 404, "The page you are looking for doesn't exists" );

		}
		$this->wid = $this->website['id'];
		$this->collectInfo();

	}

	/**
	 * actionGenerateHTML function.
	 *
	 * @param mixed $domain Parameter.
	 */
	public function actionGenerateHTML( $domain ) {
		$downloadForm = new DownloadPdfForm();
		if (isset( $_POST['DownloadPdfForm'] ) and is_array( $_POST['DownloadPdfForm'] )) {
			$downloadForm->attributes = $_POST['DownloadPdfForm'];
			if ($downloadForm->validate()) {
				$this->actionGeneratePDF( $this->domain );
				Yii::app()->end();
			}
		}

		$this->title = 'Website review | ' . $this->website['idn'];
		$description = 'Website review | ' . $this->website['idn'];
		$cs          = Yii::app()->clientScript;
		$cs->registerMetaTag( 'Website review | ' . $this->website['idn'], 'keywords' );
		$cs->registerMetaTag( $description, 'description' );

		$cs->registerScriptFile( Yii::app()->request->getBaseUrl( true ) . '/assets/js/jquery.flot.js' );
		$cs->registerScriptFile( Yii::app()->request->getBaseUrl( true ) . '/assets/js/jquery.flot.pie.js' );

		$url = $this->createAbsoluteUrl( 'websitestat/generateHTML', array( 'domain' => $this->domain ) );
		$cs->registerMetaTag( $this->title, null, null, array( 'property' => 'og:title' ) );
		$cs->registerMetaTag( $description, null, null, array( 'property' => 'og:description' ) );
		$cs->registerMetaTag( $url, null, null, array( 'property' => 'og:url' ) );
		$cs->registerMetaTag(
			WebsiteThumbnail::getOgImage(
				array(
					'url'  => $this->domain,
					'size' => 'm',
				)
			),
			null,
			null,
			array(
				'property' => 'og:image',
				'encode'   => false,
			)
		);
		$cs->registerCssFile( Yii::app()->request->getBaseUrl( true ) . '/assets/css/fontawesome.min.css' );

		if ( ! Yii::app()->params['param.instant_redirect']) {
			$updUrl = $this->createUrl(
				'parse/index',
				array(
					'Website'  => array(
						'domain' => $this->domain,
					),
					'redirect' => '1',
				)
			);
		} else {
			$updUrl = '#update_form';
		}

		$this->render(
			'index',
			array(
				'website'       => $this->website,
				'cloud'         => $this->cloud,
				'content'       => $this->content,
				'document'      => $this->document,
				'isseter'       => $this->isseter,
				'links'         => $this->links,
				'meta'          => $this->meta,
				'w3c'           => $this->w3c,
				'over_max'      => 6,
				'generated'     => $this->generated,
				'diff'          => $this->diff,
				'linkcount'     => count( $this->links['links'] ),
				'rateprovider'  => new RateProvider(),
				'thumbnail'     => $this->thumbnail,
				'downloadForm'  => $downloadForm,
				'isPostRequest' => Yii::app()->request->isPostRequest,
				'updUrl'        => $updUrl,
				'misc'          => $this->misc,
			)
		);
	}

	/**
	 * actionGeneratePDF function.
	 *
	 * @param mixed $domain Parameter.
	 */
	public function actionGeneratePDF( $domain ) {
		$filename = $this->domain;
		try {
			$pdfFile = Utils::createPdfFolder( $filename );
		} catch ( Exception $e ) {
			// If we cannot create the deep nested folder, try to notify user gracefully.
			throw new CHttpException( 500, 'Unable to prepare PDF file: ' . $e->getMessage() );
		}
		if ( file_exists( $pdfFile ) ) {
			$this->outputPDF( $pdfFile, $this->website['idn'] );
		}

		$html = $this->renderPartial(
			'pdf',
			array(
				'website'      => $this->website,
				'cloud'        => $this->cloud,
				'content'      => $this->content,
				'document'     => $this->document,
				'isseter'      => $this->isseter,
				'links'        => $this->links,
				'meta'         => $this->meta,
				'w3c'          => $this->w3c,
				'over_max'     => 6,
				'generated'    => $this->generated,
				'diff'         => $this->diff,
				'linkcount'    => count( $this->links['links'] ),
				'rateprovider' => new RateProvider(),
				'thumbnail'    => WebsiteThumbnail::getOgImage(
					array(
						'url'  => $this->domain,
						'size' => 'm',
					)
				),
				'misc'         => $this->misc,
			),
			true
		);

		$this->createPdfFromHtml( $html, $pdfFile, $this->website['idn'] );
	}

	/**
	 * outputPDF function.
	 *
	 * @param mixed $pdfFile Parameter.
	 * @param mixed $filename Parameter.
	 */
	protected function outputPDF( $pdfFile, $filename ) {
		header( 'Content-type: application/pdf' );
		// It will be called downloaded.pdf.
		header( 'Content-Disposition: attachment; filename="' . $filename . '.pdf"' );
		// The PDF source is in original.pdf.
		readfile( $pdfFile );
		Yii::app()->end();
	}

	/**
	 * createPdfFromHtml function.
	 *
	* @param mixed $html Parameter.
	* @param mixed $pdfFile Parameter.
	* @param mixed $filename Parameter.
	* @param bool  $stream Whether to stream the PDF directly to the client (default: true).
	 */
	public function createPdfFromHtml( $html, $pdfFile, $filename, $stream = true ) {
		$pdf = Yii::createComponent( 'application.extensions.tcpdf.ETcPdf', 'P', 'cm', 'A4', true, 'UTF-8' );
		$pdf->SetCreator( PDF_CREATOR );
		$pdf->SetAuthor( 'http://website-review.php8developer.com' );
		$pdf->SetTitle( 'Website review ' . $this->website['idn'] );
		$pdf->SetSubject( 'Website review ' . $this->website['idn'] );
		$pdf->setPrintHeader( false );
		$pdf->setPrintFooter( false );
		$pdf->AddPage();
		$pdf->SetFont( 'dejavusans', '', 10, '', false );

		// $pdf->writeHTML($html, true, false, true, false, '');
		@$pdf->writeHTML( $html, 2 );
		// Save PDF to disk. Convert PHP warnings (e.g., fopen failures) into exceptions
		// so they can be handled by the caller and returned as JSON errors.
		$prev_handler = set_error_handler( function( $errno, $errstr, $errfile, $errline ) {
			throw new ErrorException( $errstr, 0, $errno, $errfile, $errline );
		} );
		try {
			$pdf->Output( $pdfFile, 'F' );
		} finally {
			// Restore previous error handler even if Output() threw.
			if ( $prev_handler !== null ) {
				set_error_handler( $prev_handler );
			} else {
				restore_error_handler();
			}
		}
		// Ensure the file was written successfully.
		if ( ! file_exists( $pdfFile ) ) {
			throw new Exception( 'PDF engine failed to create file' );
		}
		// If requested, stream the PDF directly to the client and end execution.
		if ( $stream ) {
			$this->outputPDF( $pdfFile, $filename );
		}
	}

	/**
	 * collectInfo function.
	 */
	protected function collectInfo() {
		// Set thumbnail.
		$this->thumbnail = WebsiteThumbnail::getThumbData(
			array(
				'url'  => $this->domain,
				'size' => 'l',
			)
		);

		// Get all report data using WordPress native database.
		$data = $this->command->get_website_report_data( $this->wid );

		$this->cloud    = $data['cloud'];
		$this->content  = $data['content'];
		$this->document = $data['document'];
		$this->isseter  = $data['issetobject'];
		$this->links    = $data['links'];
		$this->meta     = $data['metatags'];
		$this->w3c      = $data['w3c'];
		$this->misc     = $data['misc'];

		// Initialize as empty arrays if query returned false/null.
		if ( ! $this->cloud) {
			$this->cloud = array(
				'words'  => '[]',
				'matrix' => '[]',
			);
		}
		if ( ! $this->content) {
			$this->content = array(
				'headings'       => '[]',
				'deprecated'     => '[]',
				'total_img'      => 0,
				'total_alt'      => 0,
				'isset_headings' => 0,
			);
		}
		if ( ! $this->document) {
			$this->document = array(
				'doctype'   => '',
				'lang'      => '',
				'htmlratio' => 0,
				'charset'   => '',
			);
		}
		if ( ! $this->isseter) {
			$this->isseter = array(
				'robotstxt'    => 0,
				'nestedtables' => 0,
				'inlinecss'    => 0,
				'flash'        => 0,
				'iframe'       => 0,
			);
		}
		if ( ! $this->links) {
			$this->links = array(
				'links'             => '[]',
				'external_nofollow' => 0,
				'external_dofollow' => 0,
				'internal'          => 0,
				'friendly'          => 0,
				'isset_underscore'  => 0,
				'files_count'       => 0,
			);
		}
		if ( ! $this->meta) {
			$this->meta = array(
				'ogproperties' => '[]',
				'title'        => '',
				'description'  => '',
				'keyword'      => '',
			);
		}
		if ( ! $this->w3c) {
			$this->w3c = array();
		}
		if ( ! $this->misc) {
			$this->misc = array(
				'sitemap'   => '[]',
				'analytics' => '[]',
			);
		}

		// Ensure fields exist and are not null before JSON decoding.
		// If field is null or doesn't exist, default to empty JSON array string.
		if ( ! isset( $this->content['headings'] ) || $this->content['headings'] === null) {
			$this->content['headings'] = '[]';
		}
		if ( ! isset( $this->content['deprecated'] ) || $this->content['deprecated'] === null) {
			$this->content['deprecated'] = '[]';
		}
		if ( ! isset( $this->links['links'] ) || $this->links['links'] === null) {
			$this->links['links'] = '[]';
		}
		if ( ! isset( $this->cloud['words'] ) || $this->cloud['words'] === null) {
			$this->cloud['words'] = '[]';
		}
		if ( ! isset( $this->cloud['matrix'] ) || $this->cloud['matrix'] === null) {
			$this->cloud['matrix'] = '[]';
		}
		if ( ! isset( $this->meta['ogproperties'] ) || $this->meta['ogproperties'] === null) {
			$this->meta['ogproperties'] = '[]';
		}
		if ( ! isset( $this->misc['sitemap'] ) || $this->misc['sitemap'] === null) {
			$this->misc['sitemap'] = '[]';
		}
		if ( ! isset( $this->misc['analytics'] ) || $this->misc['analytics'] === null) {
			$this->misc['analytics'] = '[]';
		}

		// Decode JSON fields to arrays.
		$this->content['headings']   = (array) json_decode( $this->content['headings'], true );
		$this->links['links']        = (array) json_decode( $this->links['links'], true );
		$this->cloud['words']        = Utils::shuffle_assoc( (array) json_decode( $this->cloud['words'], true ) );
		$this->cloud['matrix']       = (array) json_decode( $this->cloud['matrix'], true );
		$this->meta['ogproperties']  = (array) json_decode( $this->meta['ogproperties'], true );
		$this->content['deprecated'] = (array) json_decode( $this->content['deprecated'], true );
		$this->misc['sitemap']       = (array) json_decode( $this->misc['sitemap'], true );
		$this->misc['analytics']     = (array) json_decode( $this->misc['analytics'], true );

		$this->strtime        = strtotime( $this->website['modified'] );
		$this->generated['A'] = date( 'A', $this->strtime );
		$this->generated['Y'] = date( 'Y', $this->strtime );
		$this->generated['M'] = date( 'M', $this->strtime );
		$this->generated['d'] = date( 'd', $this->strtime );
		$this->generated['H'] = date( 'H', $this->strtime );
		$this->generated['i'] = date( 'i', $this->strtime );
		$this->diff           = time() - $this->strtime;
	}
}
