<?php

Yii::import( 'application.vendors.Webmaster.Utils.IDN' );
set_time_limit( 0 );
class ImportCommand extends CConsoleCommand {

	public function actionIndex() {

		 $file_path = Yii::getPathOfAlias( 'application.data.domains' ) . '.txt';
		if ( ! file_exists( $file_path )) {
			echo "File {$file_path} doesn't exist\r\n";
			return 1;

		}

		$file           = new SplFileObject( $file_path );
		$total_imported = 0;
		$errors         = array();

		while ( ! $file->eof()) {
			$file_domain = trim( $file->fgets() );
			// Skip empty lines.
			if (empty( $file_domain )) {
				continue;

			}
			$exists = Website::model()->count(
				'md5domain=:md5domain',
				array(
					':md5domain' => md5( $file_domain ),
				)
			);
			// Skip existing domain.
			if ($exists) {
				continue;

			}

			try {
				 $idnConverter = new IDN();
				$domain        = $idnConverter->encode( $file_domain );
				$idn           = $file_domain;
				 $ip           = gethostbyname( $domain );
				 $long         = ip2long( $ip );
				if (false === $domain) {
					throw new Exception( 'Unable to convert to ascii' );

				}
				if (false === $idn) {
					throw new Exception( 'Unable to convert to utf8' );

				}
				if (false === $long) {
					throw new Exception( 'Unable to get IP address' );

				}
				$args = array(
					'yiic',
					'parse',
					'insert',
					"--domain={$file_domain}",
					"--idn={$file_domain}",
					"--ip={$ip}",
				);
				$code = $this->getCommandRunner()->run( $args );
				if (0 !== $code) {
					throw new Exception( "[$file_domain]: command error code: [{$code}]" );

				}

			} catch (Exception $exception) {
						   $errors[] = "[$file_domain]: " . $exception->getMessage();
				continue;

			}

			$total_imported++;

		}

		$error_summary = '';
		foreach ($errors as $error) {
			$error_summary .= $error . "\r\n";

		}

		$summary  = "Total number of imported domains: $total_imported\r\n";
		$summary .= 'Total number of failed imports: ' . count( $errors ) . "\r\n";
		$summary .= "Error summary:\r\n";
		$summary .= $error_summary;

		echo $summary;
		return 0;
	}
}
