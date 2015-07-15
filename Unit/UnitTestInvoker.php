<?php
namespace Annexus\TestSuite\Unit;

require_once ( __DIR__ . '/../Config.php' );
require_once ( __DIR__ . '/TestCaseMethods.php' );

use Annexus\TestSuite\Config;
use Annexus\TestSuite\Bootstrap;

class UnitTestInvoker
{
    private $testCases = array ( );
    private $rootFolder;
    protected $testCaseResults = array ( );
    
    public function __construct ( )
    {
        if (php_sapi_name() != "cli")
		{
			$this->rootFolder = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . Config::$testFolders[0];
		}
		else
		{
			$this->rootFolder = Config::$testFolders[0];
		}
    }
    
    /**
     * Creates test case object
     * 
     * If $testMethods is left empty then it will resolve all test methods automatically
     */
    protected function createTestCase ( $testCasePath, $testMethods = null )
    {
        require_once ( $testCasePath );
        $testCase = basename ( $testCasePath, '.php' );
        $instance = new $testCase;
        
        if ( $testMethods === null )
        {
            $testMethods = preg_grep ( '/^test/i', get_class_methods ( $instance ) );
        }
        
        $this->testCases[] = new TestCaseMethods ( $testCasePath, $instance, $testMethods );
    }
    
    public function addTestCase ( TestCaseMethods $testCase )
    {
        $this->testCases[] = $testCase;
    }
    
    public function addTestCases ( array $testCases )
    {
        $this->testCases = array_merge ( $this->testCases, $testCases );
    }
    
    protected function clearTestCases ( )
    {
        $this->testCases = array ( );
    }
    
    protected function getAllTestCases ( )
    {
        $testCases = array ( );

        if ( !is_dir ( $this->rootFolder ) ) {
            throw new \InvalidArgumentException("\r\n
Invalid root test folder specified\r\n
You said: " . $this->rootFolder . "\r\n\r\n
The root folder is a folder which should have all you test files.\r\n
For example, this could be a root folder: '/var/www/myproject/tests'. You should then place all your tests inside that 'root' folder. Those tests may be placed in subdirectories as well.\r\n\r\n
Also make sure the given path is correct. If you are on Windows, try to put double quotes around the path name: \r\n
Will NOT work: /> php.exe TestSuite.phar C:\\web development\\htdocs\\myproject\\tests \r\n
Correct way: /> php.exe TestSuite.phar \"C:\\web development\\htdocs\\myproject\\tests\" \r\n\r\n
Please try again\r\n\r\n");
        }
        
        $directory = new \RecursiveDirectoryIterator ( $this->rootFolder );
        $iterator = new \RecursiveIteratorIterator ( $directory );
        $iterator->setFlags ( \RecursiveDirectoryIterator::SKIP_DOTS );
        $regexIterator = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        foreach ( $regexIterator as $filepath => $object )
        {
            if ( preg_match ( '#^(.*?)CF([a-zA-Z0-9]+).php$#', $filepath ) )
                continue;

            require_once ( $filepath );
            $testCase = basename ( $filepath, '.php' );

            $instance = new $testCase;
            $testMethods = preg_grep ( '/^Test_/i', get_class_methods ( $instance ) );
            
            $testCases[] = new TestCaseMethods ( $filepath, $instance, $testMethods );
        }
        
        return $testCases;
    }
    
    protected function executeTestMethods ( )
    {
        foreach ( $this->testCases as $testCaseMethods )
        {
            $testCaseMethods->testCase->setUpBeforeClass ( );
            foreach ( $testCaseMethods->testMethods as $testMethod )
            {
                $testCaseMethods->testCase->setUp ( );
                $testCaseMethods->testCase->$testMethod ( );
                $testCaseMethods->testCase->tearDown ( );
            }
            $testCaseMethods->testCase->tearDownAfterClass ( );
        }
        
    }
}

?>