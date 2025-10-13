<?php

class ManageController extends Controller {

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

	public function actionRemove( $domain) {
		Website::removeByDomain( (string) $domain );
		echo 'removed';
	}

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
