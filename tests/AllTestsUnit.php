<?php
/**
 * This file contains the script required to run all PE edition unit tests in unit dir on Cruise Control.
 * This file is supposed to be executed over PHPUnit framework
 * It is called something like this:
 * phpunit <Test dir>_AllTests
 *
 * @link      http://www.oxid-esales.com
 * @package   tests
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version   SVN: $Id: $
 */

require_once 'PHPUnit/Framework/TestSuite.php';

echo "=========\nrunning php version ".phpversion()."\n\n============\n";

/**
 * PHPUnit_Framework_TestCase implemetnation for adding and testing all unit tests from unit dir
 */
class AllTestsUnit extends PHPUnit_Framework_TestCase
{

    /**
     * Default test suites
     *
     * @var array
     */
    protected static $_aTestSuites = array( 'unit', 'integration' );

    /**
     * Returns test files filter
     *
     * @return string
     */
    public static function getTestFileFilter()
    {
        $sTestFileNameEnd = '.*[^8]Test.php';
        if ( defined('OXID_TEST_UTF8') && OXID_TEST_UTF8 ) {
            $sTestFileNameEnd = '.*utf8Test.php';
            }

        return "#$sTestFileNameEnd#";
        }

    /**
     * Forms test suite
     *
     * @return object
     */
    public static function suite()
    {
        $oInterator = self::_getTestDirectories();
        $oRecursiveInterator = new RecursiveArrayIteratorself($oInterator);

        $oSuite = new PHPUnit_Framework_TestSuite( 'PHPUnit' );

        var_dump($oRecursiveInterator);
        echo "\n\n";
        $pattern = self::getTestFileFilter();
        foreach ( $oRecursiveInterator as $sDirectory ) {
            $aTestFiles = self::_fileSearch(__DIR__ . "/$sDirectory", $pattern);

            if ( empty( $aTestFiles ) ) {
                continue;
            }

            printf("Adding %s unit tests from %s filtert by (%s)\n",
                   count($aTestFiles),
                   $sDirectory,
                   $pattern);

            $oSuite = self::_addFilesToSuite( $oSuite, $aTestFiles );
                }

        return $oSuite;
                    }

    /**
     * Adds files to test suite
     *
     * @param $oSuite
     * @param $aTestFiles
     * @throws Exception
     */
    protected static function _addFilesToSuite( $oSuite, $aTestFiles )
    {
        echo "\nstart:\n";


        foreach ( $aTestFiles as $sFilename ) {
            echo "\n" . $sFilename . "\n";
            $sFilter = defined('PREG_FILTER') ? PREG_FILTER : false;
            if ( !$sFilter || preg_match("&$sFilter&i", $sFilename) ) {

                include_once $sFilename;

                $sClassName = str_replace( __DIR__ . "/", "", $sFilename );
                $sClassName = str_replace( array( "/", ".php" ), array( "_", "" ), $sClassName );

                if ( class_exists( $sClassName ) ) {
                    $oSuite->addTestSuite( $sClassName );
                } else {
                    if ( !isset( $blThrowException ) || $blThrowException ) {
                        echo "\n\nFile with wrong class name found!: $sClassName in $sFilename";
                        exit();
                    }
                }
            }
        }

        echo "\nend:\n\n\n";
        return $oSuite;
      }

    /**
     * Returns array of directories, which should be tested
     *
     * @return array
     */
    protected static function _getTestDirectories()
    {
        $aTestDirectories = self::$_aTestSuites;
        $oInterator = null;

        if ( defined('TEST_DIRS') && TEST_DIRS ) {
            $aTestDirectories = explode(',', TEST_DIRS );
            $oInterator = self::_getSuiteDirectories( $aTestDirectories );
        } else {
            var_dump($aTestDirectories);
            $oInterator = array(self::_getDirectoryTree( $aTestDirectories ));
        }

        return $oInterator;
    }

    /**
     * Returns test suite directories
     *
     * @param $sTestSuiteParts
     * @return array
     */
    protected static function _getSuiteDirectories( $sTestSuiteParts )
    {
        $aDirectories = array();

        list( $sSuiteKey, $sSuiteTests ) = explode(':', $sTestSuiteParts);
        if ( !empty( $sSuiteTests ) ) {
            foreach ( explode('%', $sSuiteTests) as $sSubDirectory ) {
                $sSubDirectory = ( $sSubDirectory == "_root_")? "" : '/'.$sSubDirectory;                $aDirectories[] = "${sSuiteKey}${sSubDirectory}";
                $aDirectories[] = self::_getDirectoryTree("${sSuiteKey}${sSubDirectory}");
            }
        } else {
            $aDirectories[] = self::_getDirectoryTree($sSuiteKey);
        }

        return new RecursiveArrayIterator($aDirectories);
    }

    /**
     * Scans given tests directories and returns formed directory tree
     *
     * @param array $aDirectories
     * @return array
     */
    protected static function _getDirectoryTree( $aDirectories )
    {
        return self::_getInteratorForDirectory($aDirectoryies);
    }

    /**
     * found on http://thephpeffect.com/recursive-glob-vs-recursive-directory-iterator/
     */
    protected static function _getInteratorForDirectory($directory) {
        $dir = new RecursiveDirectoryIterator($directory);

        return $dir;
    }

    /**
     * found on http://thephpeffect.com/recursive-glob-vs-recursive-directory-iterator/
     */
    protected static function _rsearch($folder, $pattern) {
        $ite = new RecursiveIteratorIterator(self::_getInteratorForDirectory($folder));

        return self::_fileSearch($ite, $pattern);
    }

    /**
     * found on http://thephpeffect.com/recursive-glob-vs-recursive-directory-iterator/
     */
    protected static function _fileSearch($directoryInterator, $pattern) {
        if (! $directoryInterator instanceof \Iterator ) {
            $directoryInterator = new DirectoryIterator($directoryInterator);
        }

        $files = new RegexIterator($directoryInterator, $pattern, RegexIterator::GET_MATCH);
        $fileList = array();
        foreach($files as $file) {
            $fileList = array_merge($fileList, $file);
        }
        return $fileList;
    }
}
