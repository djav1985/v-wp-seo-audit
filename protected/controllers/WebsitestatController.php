<?php
Yii::import('application.vendors.Webmaster.Rates.*');
Yii::import('application.vendors.Webmaster.Source.*');
Yii::import('application.vendors.Webmaster.Google.*');

class WebsitestatController extends Controller
{
	protected $domain, $command, $website, $wid, $diff, $strtime, $thumbnail,
		$cloud = array(),
		$content = array(),
		$document = array(),
		$isseter = array(),
		$links = array(),
		$w3c = array(),
		$meta = array(),
		$misc = array(),
		$generated = array();

	public function init()
	{
		parent::init();
		$this->command = Yii::app()->db->createCommand();
		$this->domain = isset($_GET['domain']) ? $_GET['domain'] : null;
		
		// Query for website record
		$this->website = $this->command
			->select("id, domain, modified, idn, score, final_url")
			->from("{{website}}")
			->where('md5domain=:md5', array(':md5' => md5($this->domain)))
			->queryRow();
		
		// If website doesn't exist, handle appropriately
		if (!$this->website) {
			if (!Yii::app()->params["param.instant_redirect"]) {
				$form = new WebsiteForm();
				$form->domain = $this->domain;
				if ($form->validate()) {
					$this->redirect($this->createUrl("websitestat/generateHTML", array("domain" => $this->domain)));
					Yii::app()->end();
				}
			}
			throw new CHttpException(404, Yii::t("app", "The page you are looking for doesn't exists"));
		}
		
		$this->command->reset();
		$this->wid = $this->website['id'];
		$this->collectInfo();
	}

	public function actionGenerateHTML($domain)
	{
		$downloadForm = new DownloadPdfForm;
		if (isset($_POST['DownloadPdfForm']) and is_array($_POST['DownloadPdfForm'])) {
			$downloadForm->attributes = $_POST['DownloadPdfForm'];
			if ($downloadForm->validate()) {
				$this->actionGeneratePDF($this->domain);
				Yii::app()->end();
			}
		}

		$this->title = Yii::t("meta", "Webstat page title", array('{Domain}' => $this->website['idn']));
		$description = Yii::t("meta", "Webstat page description", array('{Domain}' => $this->website['idn']));
		$cs = Yii::app()->clientScript;
		$cs->registerMetaTag(Yii::t("meta", "Webstat page keywords", array('{Domain}' => $this->website['idn'])), "keywords");
		$cs->registerMetaTag($description, "description");

		$cs->registerScriptFile(Yii::app()->request->getBaseUrl(true) . '/js/jquery.flot.js');
		$cs->registerScriptFile(Yii::app()->request->getBaseUrl(true) . '/js/jquery.flot.pie.js');

		$url = $this->createAbsoluteUrl("websitestat/generateHTML", array("domain" => $this->domain));
		$cs->registerMetaTag($this->title, null, null, array('property' => 'og:title'));
		$cs->registerMetaTag($description, null, null, array('property' => 'og:description'));
		$cs->registerMetaTag($url, null, null, array('property' => 'og:url'));
		$cs->registerMetaTag(WebsiteThumbnail::getOgImage(array(
			'url' => $this->domain,
			'size' => 'm',
		)), null, null, array('property' => 'og:image', 'encode' => false));
		$cs->registerCssFile(Yii::app()->request->getBaseUrl(true) . '/css/fontawesome.min.css');

		if (!Yii::app()->params["param.instant_redirect"]) {
			$updUrl = $this->createUrl('parse/index', array(
				"Website" => array(
					"domain" => $this->domain
				),
				"redirect" => "1"
			));
		} else {
			$updUrl = "#update_form";
		}

		$this->render('index', array(
			'website' => $this->website,
			'cloud' => $this->cloud,
			'content' => $this->content,
			'document' => $this->document,
			'isseter' => $this->isseter,
			'links' => $this->links,
			'meta' => $this->meta,
			'w3c' => $this->w3c,
			'over_max' => 6,
			'generated' => $this->generated,
			'diff' => $this->diff,
			'linkcount' => count($this->links['links']),
			'rateprovider' => new RateProvider(),
			'thumbnail' => $this->thumbnail,
			'downloadForm' => $downloadForm,
			'isPostRequest' => Yii::app()->request->isPostRequest,
				'updUrl' => $updUrl,
			"misc" => $this->misc,
		));
	}

	public function actionGeneratePDF($domain)
	{
		$filename = $this->domain;
		$pdfFile = Utils::createPdfFolder($filename);
		if (file_exists($pdfFile)) {
			$this->outputPDF($pdfFile, $this->website['idn']);
		}

		$html = $this->renderPartial('pdf', array(
			'website' => $this->website,
			'cloud' => $this->cloud,
			'content' => $this->content,
			'document' => $this->document,
			'isseter' => $this->isseter,
			'links' => $this->links,
			'meta' => $this->meta,
			'w3c' => $this->w3c,
			'over_max' => 6,
			'generated' => $this->generated,
			'diff' => $this->diff,
			'linkcount' => count($this->links['links']),
			'rateprovider' => new RateProvider(),
			'thumbnail' => WebsiteThumbnail::getOgImage(array(
				'url' => $this->domain,
				'size' => 'm',
			)),
			"misc" => $this->misc,
		), true);

		$this->createPdfFromHtml($html, $pdfFile, $this->website['idn']);
	}

	protected function outputPDF($pdfFile, $filename)
	{
		header('Content-type: application/pdf');
		// It will be called downloaded.pdf
		header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
		// The PDF source is in original.pdf
		readfile($pdfFile);
		Yii::app()->end();
	}

	protected function createPdfFromHtml($html, $pdfFile, $filename)
	{
		$pdf = Yii::createComponent('application.extensions.tcpdf.ETcPdf', 'P', 'cm', 'A4', true, 'UTF-8');
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor("http://website-review.php8developer.com");
		$pdf->SetTitle(Yii::t("app", "Analyse of {Domain}", array("{Domain}" => $this->website['idn'])));
		$pdf->SetSubject(Yii::t("app", "Analyse of {Domain}", array("{Domain}" => $this->website['idn'])));
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->AddPage();
		$pdf->SetFont('dejavusans', '', 10, '', false);

		//$pdf->writeHTML($html, true, false, true, false, '');
		@$pdf->writeHTML($html, 2);
		$pdf->Output($pdfFile, "F");
		$this->outputPDF($pdfFile, $filename);
	}

	protected function collectInfo()
	{
		// Safety check: ensure website exists
		if (!$this->website || !isset($this->website['modified'])) {
			throw new CHttpException(500, 'Website data is not available');
		}
		
		// Set thumbnail
		$this->thumbnail = WebsiteThumbnail::getThumbData(array(
			'url' => $this->domain,
			'size' => 'l',
		));

		$this->cloud = 		$this->command->select('*')->from("{{cloud}}")->where('wid=:wid', array(':wid' => $this->wid))->queryRow();
		$this->command->reset();
		$this->content = 	$this->command->select('*')->from("{{content}}")->where('wid=:wid', array(':wid' => $this->wid))->queryRow();
		$this->command->reset();
		$this->document = $this->command->select('*')->from("{{document}}")->where('wid=:wid', array(':wid' => $this->wid))->queryRow();
		$this->command->reset();
		$this->isseter = 	$this->command->select('*')->from("{{issetobject}}")->where('wid=:wid', array(':wid' => $this->wid))->queryRow();
		$this->command->reset();
		$this->links = 		$this->command->select('*')->from("{{links}}")->where('wid=:wid', array(':wid' => $this->wid))->queryRow();
		$this->command->reset();
		$this->meta = 		$this->command->select('*')->from("{{metatags}}")->where('wid=:wid', array(':wid' => $this->wid))->queryRow();
		$this->command->reset();
		$this->w3c =		$this->command->select('*')->from("{{w3c}}")->where('wid=:wid', array(':wid' => $this->wid))->queryRow();
		$this->command->reset();
		$this->misc =		$this->command->select('*')->from("{{misc}}")->where('wid=:wid', array(':wid' => $this->wid))->queryRow();
		$this->command->reset();

		// Initialize as empty arrays if query returned false/null
		if (!$this->cloud) $this->cloud = array('words' => '[]', 'matrix' => '[]');
		if (!$this->content) $this->content = array('headings' => '[]', 'deprecated' => '[]', 'total_img' => 0, 'total_alt' => 0);
		if (!$this->document) $this->document = array('doctype' => '', 'lang' => '', 'htmlratio' => 0, 'charset' => '');
		if (!$this->isseter) $this->isseter = array('robotstxt' => 0, 'nestedtables' => 0, 'inlinecss' => 0);
		if (!$this->links) $this->links = array('links' => '[]', 'external_nofollow' => 0, 'external_dofollow' => 0, 'internal' => 0);
		if (!$this->meta) $this->meta = array('ogproperties' => '[]', 'title' => '', 'description' => '');
		if (!$this->w3c) $this->w3c = array();
		if (!$this->misc) $this->misc = array('sitemap' => '[]', 'analytics' => '[]');

		$this->content['headings'] = @(array)json_decode($this->content['headings'], true);
		$this->links['links'] = @(array)json_decode($this->links['links'], true);
		$this->cloud['words'] = Utils::shuffle_assoc(@(array) json_decode($this->cloud['words'], true));
		$this->cloud['matrix'] = @(array)json_decode($this->cloud['matrix'], true);
		$this->meta['ogproperties'] = @(array)json_decode($this->meta['ogproperties'], true);
		$this->content['deprecated'] = @(array)json_decode($this->content['deprecated'], true);

		if ($this->misc) {
			$this->misc['sitemap'] = @(array)json_decode($this->misc['sitemap'], true);
			$this->misc['analytics'] = @(array)json_decode($this->misc['analytics'], true);
		}

		$this->strtime = strtotime($this->website['modified']);
		$this->generated['A'] = date("A", $this->strtime);
		$this->generated['Y'] = date("Y", $this->strtime);
		$this->generated['M'] = date("M", $this->strtime);
		$this->generated['d'] = date("d", $this->strtime);
		$this->generated['H'] = date("H", $this->strtime);
		$this->generated['i'] = date("i", $this->strtime);
		$this->diff = time() - $this->strtime;
	}
}
