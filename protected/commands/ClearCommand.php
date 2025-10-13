<?php
/**
 * File: ClearCommand.php
 *
 * @package V_WP_SEO_Audit
 */

class ClearCommand extends CConsoleCommand {

	/**
	 * actionPdf function.
	 *
	 * @param mixed $older_than Parameter.
	 */
	public function actionPdf( $older_than = '-15 days') {
		$rootDir   = Yii::app()->getBasePath() . '/../pdf';
		$directory = new RecursiveDirectoryIterator( $rootDir );
		 $directory->setFlags( FilesystemIterator::SKIP_DOTS );
		$iterator = new RecursiveIteratorIterator( $directory );

		foreach ($iterator as $file) {
			/**
	* Description.
			 *
			   * @var $file SplFileInfo
			   */
			if ($file->isFile() and ! Utils::starts_with( $file->getFilename(), '.' )) {
				if ($file->getMTime() < strtotime( $older_than )) {
					unlink( $file->getRealPath() );
				}
			}
		}
	}
}
