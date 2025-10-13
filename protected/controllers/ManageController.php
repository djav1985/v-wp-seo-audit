<?php
/**
 * File: ManageController.php
 *
 * @package V_WP_SEO_Audit
 */

class ManageController extends Controller {

	/**
	 * init function.
	 */
	public function init() {
		parent::init();
		$app_key = (string) Yii::app()->params['app.manage_key'];
		$get_key = (string) Yii::app()->request->getQuery( 'key' );
		if ($app_key === '') {
			throw new CHttpException( 400, 'Bad request' );
		}

		if (strcmp( $app_key, $get_key ) !== 0) {
			throw new CHttpException( 400, 'Bad request' );
		}

		@ini_set( 'max_execution_time', 0 );
		@ini_set( 'max_input_time', -1 );
	}

	/**
	 * actionRemove function.
	 *
	 * @param mixed $domain Parameter.
	 */
	public function actionRemove( $domain) {
		Website::removeByDomain( (string) $domain );
		echo 'removed';
	}

	/**
	 * actionClear function.
	 */
	public function actionClear() {
		$this->runCommand(
			array(
				'yiic',
				'clear',
				'pdf',
			)
		);
		echo 'ok';
	}

	/**
	 * runCommand function.
	 *
	 * @param mixed $args Parameter.
	 */
	protected function runCommand( $args) {
		// Get command path.
		$commandPath = Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'commands';

		// Create new console command runner.
		$runner = new CConsoleCommandRunner();

		// Adding commands.
		$runner->addCommands( $commandPath );

		// If something goes wrong return error.
		$runner->run( $args );
	}
}
