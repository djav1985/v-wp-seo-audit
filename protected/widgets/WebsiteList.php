<?php

/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 2015.11.22
 * Time: 15:55
 *
 * @package V_WP_SEO_Audit
 */
class WebsiteList extends CWidget {

	/**
	 * Configuration array for the widget.
	 *
	 * @var array
	 */
	public $config   = array();

	/**
	 * Template name for rendering the widget.
	 *
	 * @var string
	 */
	public $template = 'website_list';

	/**
	 * init function.
	 */
	public function init() {
		$config       = array(
			'criteria'      => array(
				'order' => 't.added DESC',
			),
			'countCriteria' => array(),
			'pagination'    => array(
				'pageVar'  => 'page',
				'pageSize' => Yii::app()->params['param.rating_per_page'],
			),
		);
		$this->config = CMap::mergeArray( $config, $this->config );
	}

	/**
	 * run function.
	 */
	public function run() {
		$dataProvider = new CActiveDataProvider( 'Website', $this->config );
		$data         = $dataProvider->getData();
		if (empty( $data )) {
			return null;
		}
		$thumbnailStack = WebsiteThumbnail::thumbnailStack( $data, array( 'size' => 'l' ) );
		$this->render(
			$this->template,
			array(
				'dataProvider'   => $dataProvider,
				'thumbnailStack' => $thumbnailStack,
				'data'           => $data,
			)
		);
	}
}
