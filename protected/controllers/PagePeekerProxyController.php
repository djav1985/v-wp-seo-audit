<?php
/**
 * File: PagePeekerProxyController.php
 *
 * @package V_WP_SEO_Audit
 */

class PagePeekerProxyController extends Controller {

	/**
	 * actionIndex function.
	 */
	public function actionIndex() {
		if ( ! Yii::app()->params['thumbnail.proxy']) {
			 throw new CHttpException( 404, "The page you are looking for doesn't exists" );

		}
		$method = 'exec' . Yii::app()->request->getQuery( 'method' );
		if ( ! method_exists( $this, $method )) {
			throw new CHttpException( 404, "The page you are looking for doesn't exists" );

		}
		return $this->$method();

	}

	/**
	 * execPoll function.
	 */
	private function execPoll() {
		$url      = WebsiteThumbnail::getPollUrl(
			array(
				'url'  => Yii::app()->request->getQuery( 'url' ),
				'size' => Yii::app()->request->getQuery( 'size' ),
			)
		);
		$response = Utils::curl( $url );
		  $this->jsonResponse( @json_decode( $response, true ) );

	}

	/**
	 * execReset function.
	 */
	private function execReset() {
		$url = WebsiteThumbnail::getResetUrl(
			array(
				'url'  => Yii::app()->request->getQuery( 'url' ),
				'size' => Yii::app()->request->getQuery( 'size' ),
			)
		);
		Utils::curl( $url );
		$this->jsonResponse(
			array(
				'ok' => 1,
			)
		);
	}
}
