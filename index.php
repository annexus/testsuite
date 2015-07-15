<?php
$version = '1.0.1';

ini_set('display_errors', '1');
error_reporting(E_ALL | E_STRICT);

require_once ( __DIR__ . '/Mock/Mock.php' );
require_once ( __DIR__ . '/Unit/Runners/Cli/CliRunner.php' );
use Annexus\TestSuite\Config;
use Annexus\TestSuite\Unit\CliRunner;

function run ( $tests )
{
	Config::$testFolders = array ( $tests );
	$now = microtime(true);
	$run = new CliRunner ( );
	$now = microtime(true) - $now;
	echo "This test was run in $now seconds";
}

if ( !isset ( $argv[1] ) )
{
	echo "\r\nNo test folder or file specified. Type --help for more info.\r\n";
	exit(1);
}

$bootstrap = null;
switch ( $argv[1] )
{
	case '--help':
	{
		echo PHP_EOL . PHP_EOL;
		echo 'There are two ways to run tests:' . PHP_EOL . PHP_EOL;
		echo '1. TestSuite.phar "Path/to/testFolder/"' . PHP_EOL;
		echo '2. TestSuite.phar --bootstrap "location/to/your/bootstrap/file.php" "Path/to/testFolder/"' . PHP_EOL . PHP_EOL;
		echo 'Notice that "--bootstrap" MUST come as the first argument. That command could be useful for example to load the "autoload.php" file that comes with composer. So you have access to all you classes in your Unit Tests.' . PHP_EOL . PHP_EOL;
		echo '(**) If you make any changes to the Annexus\TestSuite code, then run the "php build.php" command to build a new PHAR file';
		echo PHP_EOL . PHP_EOL;
		
		break;
	}
	case '--version':
		echo "\r\n$version - Annexus Testsuite\r\n";
		break;
	case '--bootstrap':
	{	
		$bootstrap = $argv[2];
		if ( !is_file ( $bootstrap ) )
		{
			echo "\r\nInvalid bootstrap file location specified: $bootstrap \r\n";
			break;
		}

		require_once ( $bootstrap );
		run ( $argv[3] );
		break;
	}
	default:
	{
		try
		{
			run ( $argv[1] );
		}
		catch(InvalidArgumentException $ex)
		{
			echo "\r\n".$ex->getMessage()."\r\n";
		}
		catch(\Exception $ex)
		{
			echo "\r\n".$ex->getMessage()."\r\n";
		}
		break;
	}
}




?>