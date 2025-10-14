<?php
/**
 * File: command.php
 * Yii framework command-line executable finder utility.
 *
 * @package V_WP_SEO_Audit
 */

/**
 * ExecutableFinder class - finds executable files.
 */
class ExecutableFinder {

	/**
	 * Array of file suffixes to check for executables.
	 *
	 * @var array
	 */
	private $suffixes = array( '.exe', '.bat', '.cmd', '.com' );
	/**
	 * Replaces default suffixes of executable.
	 *
	 * @param array $suffixes Suffixes array.
	 */
	public function setSuffixes( array $suffixes) {

		$this->suffixes = $suffixes;

	}
	/**
	 * Adds new possible suffix to check for executable.
	 *
	 * @param string $suffix Suffix to add.
	 */
	public function addSuffix( $suffix) {

		$this->suffixes[] = $suffix;

	}
	/**
	 * Finds an executable by name.
	 *
	 * @param string $name      The executable name (without the extension).
	 * @param string $default   The default to return if no executable is found.
	 * @param array  $extraDirs Additional dirs to check into.
	 *
	 * @return string The executable path or default value.
	 */
	public function find( $name, $default = null, array $extraDirs = array()) {

		if (ini_get( 'open_basedir' )) {
			 $searchPath = explode( PATH_SEPARATOR, ini_get( 'open_basedir' ) );
			$dirs        = array();
			foreach ($searchPath as $path) {
				// Silencing against https://bugs.php.net/69240.
				// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				if (@is_dir( $path )) {
					$dirs[] = $path;

				} else {
					if (basename( $path ) === $name && is_executable( $path )) {
						return $path;

					}

				}

			}

		} else {
				$dirs = array_merge(
					// phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
					explode( PATH_SEPARATOR, getenv( 'PATH' ) ?: getenv( 'Path' ) ),
					$extraDirs
				);

		}
		$suffixes = array( '' );
		if ('\\' === DIRECTORY_SEPARATOR) {
			$pathExt = getenv( 'PATHEXT' );
			// phpcs:ignore Universal.Operators.DisallowShortTernary.Found
			$suffixes = $pathExt ? explode( PATH_SEPARATOR, $pathExt ) : $this->suffixes;

		}
		foreach ($suffixes as $suffix) {
			foreach ($dirs as $dir) {
				// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
				if (is_file( $file = $dir . DIRECTORY_SEPARATOR . $name . $suffix ) && ( '\\' === DIRECTORY_SEPARATOR || is_executable( $file ) )) {
					 return $file;

				}

			}

		}
		return $default;

	}
}
/**
 * Class PhpExecutableFinder
 */
class PhpExecutableFinder {

	/**
	 * ExecutableFinder instance.
	 *
	 * @var ExecutableFinder
	 */
	private $executableFinder;
	/**
	 * __construct function.
	 */
	public function __construct() {

		 $this->executableFinder = new ExecutableFinder();
	}
	/**
	 * Finds The PHP executable.
	 *
	 * @param bool $includeArgs Whether or not include command arguments.
	 *
	 * @return string|false The PHP executable path or false if it cannot be found.
	 */
	public function find( $includeArgs = true) {

		$args = $this->findArguments();
		// phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
		$args = $includeArgs && $args ? ' ' . implode( ' ', $args ) : '';
		// HHVM support.
		if (defined( 'HHVM_VERSION' )) {
			// phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			return ( getenv( 'PHP_BINARY' ) ?: PHP_BINARY ) . $args;

		}
		// PHP_BINARY return the current sapi executable.
		if (PHP_BINARY && in_array( PHP_SAPI, array( 'cli', 'cli-server', 'phpdbg' ), true ) && is_file( PHP_BINARY )) {
			return PHP_BINARY . $args;

		}
		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
		if ($php = getenv( 'PHP_PATH' )) {
			if ( ! is_executable( $php )) {
				return false;

			}
			return $php;

		}
		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
		if ($php = getenv( 'PHP_PEAR_PHP_BIN' )) {
			if (is_executable( $php )) {
				return $php;

			}

		}
		$dirs = array( PHP_BINDIR );
		if ('\\' === DIRECTORY_SEPARATOR) {
			$dirs[] = 'C:\xampp\php\\';

		}
		return $this->executableFinder->find( 'php', false, $dirs );

	}
	/**
	 * Finds the PHP executable arguments.
	 *
	 * @return array The PHP executable arguments
	 */
	public function findArguments() {

		$arguments = array();
		if (defined( 'HHVM_VERSION' )) {
			 $arguments[] = '--php';

		} elseif ('phpdbg' === PHP_SAPI) {
				 $arguments[] = '-qrr';

		}
		return $arguments;
	}
}

$phpFinder    = new PhpExecutableFinder();
$phpPath      = $phpFinder->find();
$scriptPath   = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR;
$cron_jobs    = array(
	array(
		'period' => '0 0 * * *',
		'script' => 'yiic.php sitemap',
	),
	array(
		'period' => '1 0 1 * *',
		'script' => 'yiic.php clear pdf',
	),
);
$null         = '>/dev/null 2>&1';
$isWin        = ( strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN' );
$defaultWin   = 'php.exe';
$defaultLinux = '/usr/bin/php';
$found        = true;
if ( ! $phpPath) {
	$phpPath = $isWin ? $defaultWin : $defaultLinux;
	$found   = false;
}
?>
<html lang="en">
<head>
<title>Website Review | Command Builder</title>
</head>
<body>
<p>
	Below you can find a list of cron jobs you need to setup.
	<a href="https://docs.php8developer.com/website-review/#/installation?id=cron-jobs" target="_blank">Read here</a> how you can do it.
</p>
<ul>
 <?php
	foreach ($cron_jobs as $cron_job) :
		?>
   <li><?php echo $isWin ? sprintf( '%s %s %s', $cron_job['period'], $phpPath, $scriptPath . $cron_job['script'] ) : sprintf( '%s %s %s %s', $cron_job['period'], $phpPath, $scriptPath . $cron_job['script'], $null ); ?></li>
		<?php
	endforeach;
	?>
</ul>
<?php
if ( ! $found) :
	?>
	<div style="border: 1px solid red; background-color: #ff8469; padding: 20px; color: #fff;">
		We did not find PHP binary path. You need to ask your hosting provider whether following PHP binary path is correct: <strong><?php echo $phpPath; ?></strong>
	</div>
	<?php
endif;
?>
</body>
</html>
