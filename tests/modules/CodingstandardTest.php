<?php
/**
 * Test codingstandard module
 *
 * @package Qis
 */

/**
 * @see Codingstandard
 */
require_once 'modules/Codingstandard.php';

/**
 * Mock Qis Module coding standard
 *
 * @uses Qis_Module_Codingstandard
 * @package Qis
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class MockQisModuleCodingstandard extends Qis_Module_Codingstandard
{
    /**
     * Get standard
     *
     * @return string
     */
    public function getStandard()
    {
        return $this->_standard;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Check version
     *
     * @return bool
     */
    public function checkVersion()
    {
        return $this->_checkVersion();
    }

    /**
     * Check php sloc
     *
     * @return bool
     */
    public function checkPhpSloc()
    {
        return $this->_checkPhpSloc();
    }
}

/**
 * MockQisModuleCodingstandardErrorLevel
 *
 * @uses MockQisModuleCodingstandard
 * @package Qis
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class MockQisModuleCodingstandardErrorLevel extends MockQisModuleCodingstandard
{
    /**
     * Get Project Summary
     *
     * @return array
     */
    public function getProjectSummary()
    {
        return array('error_level' => 4);
    }
}

/**
 * Codingstandard Module Test class
 * 
 * @uses BaseTestCase
 * @package Qis
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class Qis_Module_CodingstandardTest extends BaseTestCase
{
    /**
     * Setup before each test
     * 
     * @return void
     */
    public function setUp()
    {
        $path = realpath('.') . DIRECTORY_SEPARATOR . '.qis';
        mkdir($path);

        $this->_createObject();
    }

    /**
     * Tear down after each test
     * 
     * @return void
     */
    public function tearDown()
    {
        $path = realpath('.') . DIRECTORY_SEPARATOR . '.qis';
        if (file_exists($path)) {
            passthru("rm -rf $path");
        }
    }

    /**
     * Test constructor with no arguments
     *
     * @expectedException PHPUnit_Framework_Error
     * @return void
     */
    public function testConstructorWithNoArguments()
    {
        $this->_object = new Qis_Module_Codingstandard();
    }

    /**
     * testConstructorWithoutSecondArgument
     *
     * @expectedException PHPUnit_Framework_Error
     * @return void
     */
    public function testConstructorWithoutSecondArgument()
    {
        $this->_object = new Qis_Module_Codingstandard(
            $this->_getDefaultQisObject()
        );
    }

    /**
     * Test constructor
     *
     * @return void
     */
    public function testConstructor()
    {
        $settings = array();

        $this->_object = new Qis_Module_Codingstandard(
            $this->_getDefaultQisObject(), $settings
        );

        $this->assertInstanceOf('Qis_Module_Codingstandard', $this->_object);
    }

    /**
     * Test constructor set defaults
     *
     * @return void
     */
    public function testConstructorSetDefaults()
    {
        $settings = array(
            'standard' => 'Foox',
            'path'     => 'vvvvv',
        );

        $this->_object = new MockQisModuleCodingstandard(
            $this->_getDefaultQisObject(), $settings
        );

        $this->assertEquals('Foox', $this->_object->getStandard());
        $this->assertEquals('vvvvv', $this->_object->getPath());
    }

    /**
     * Test initialize
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->_createObject(false);

        $this->_object->initialize();

        $path = realpath('.') . DIRECTORY_SEPARATOR . '.qis/codingstandard/';
        // This should have created files in the directory.
        // assert they exist
        $this->assertTrue(file_exists($path));
        $this->assertTrue(file_exists($path . 'cs.db3'));
        $this->assertTrue(file_exists($path . 'db.log'));
    }

    /**
     * testExecuteNoArguments
     *
     * @expectedException PHPUnit_Framework_Error
     * @return void
     */
    public function testExecuteNoArguments()
    {
        $this->_object->execute();
    }

    /**
     * Test execute
     *
     * @return void
     */
    public function testExecute()
    {
        $args = new Qi_Console_ArgV(array());

        ob_start();
        $this->_object->execute($args);
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertContains('Running Codingstandard module', $result);
        $this->assertContains(
            'Sniffing code with \'Apricot\' standard...', $result
        );
        $this->assertContains('Writing results to db...', $result);
        $this->assertContains('Codingstandard results:', $result);
    }

    /**
     * testCheckVersion
     *
     * @expectedException Qis_Module_CodingStandardException
     * @return void
     */
    public function testCheckVersion()
    {
        $this->_object->setOption('phpcsbin', 'ffffffff');
        $this->_object->checkVersion();
    }

    /**
     * Test check version cannot detect version
     *
     * @return void
     */
    public function testCheckVersionCannotDetectVersion()
    {
        // The : command will output nothing and return status 0
        // This means no version output will be found so checkVersion will 
        // return false
        $this->_object->setOption('phpcsbin', ':');
        $result = $this->_object->checkVersion();

        $this->assertFalse($result);
    }

    /**
     * Test check version not found match
     *
     * @return void
     */
    public function testCheckVersionNotFoundMatch()
    {
        // The ls command doesn't output the version in the same format as 
        // phpcs
        $this->_object->setOption('phpcsbin', 'ls');
        $result = $this->_object->checkVersion();

        $this->assertFalse($result);
    }

    /**
     * testCheckPhpSlocNotInstalled
     *
     * @expectedException Qis_Module_CodingStandardException
     * @return void
     */
    public function testCheckPhpSlocNotInstalled()
    {
        $this->_object->setOption('phpslocbin', 'foofffff');
        $this->_object->checkPhpSloc();
    }

    /**
     * Test check php sloc no response
     *
     * @return void
     */
    public function testCheckPhpSlocNoResponse()
    {
        // The : command returns no output, so this tests that the object will 
        // return false if there cmd returns no output
        $this->_object->setOption('phpslocbin', ':');
        $result = $this->_object->checkPhpSloc();

        $this->assertFalse($result);
    }

    /**
     * Test execute with path not found
     *
     * @return void
     */
    public function testExecuteWithPathNotFound()
    {
        $args = array(
            'cs',
            'foo',
            'margarine',
        );
        $args = new Qi_Console_ArgV($args);

        ob_start();
        $this->_object->execute($args);
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertContains("Path `margarine' not found", $result);
    }

    /**
     * Test execute with valid path
     *
     * @return void
     */
    public function testExecuteWithValidPath()
    {
        $args = array(
            'cs',
            'foo',
            'commands/AllTest.php',
        );
        $args = new Qi_Console_ArgV($args);

        ob_start();
        $this->_object->execute($args);
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertContains("Sniffing code with", $result);
        $this->assertContains("Codingstandard results:", $result);
    }

    /**
     * Test execute with multiple paths
     *
     * @return void
     */
    public function testExecuteWithMultiplePaths()
    {
        $args = array(
            'cs',
            'foo',
            'grab,bag,hag',
        );
        $args = new Qi_Console_ArgV($args);

        ob_start();
        $this->_object->execute($args);
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertContains("Path `grab,bag,hag' not found", $result);
    }

    /**
     * Test execute with list command
     *
     * @return void
     */
    public function testExecuteWithListCommand()
    {
        $args = array(
            'cs',
            '--list',
        );

        $args = new Qi_Console_ArgV($args);

        ob_start();
        $this->_object->execute($args);
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertNotContains("PHP CODE SNIFFER REPORT SUMMARY", $result);
        $this->assertContains("Running Codingstandard module task...", $result);
    }

    /**
     * Test get help message
     *
     * @return void
     */
    public function testGetHelpMessage()
    {
        $message = $this->_object->getHelpMessage();
        $this->assertContains('Run coding standard validation', $message);
    }

    /**
     * Test get extended help message
     *
     * @return void
     */
    public function testGetExtendedHelpMessage()
    {
        $message = $this->_object->getExtendedHelpMessage();
        $this->assertContains('Usage: cs', $message);
        $this->assertContains('Valid Options:', $message);
    }

    /**
     * Test get summary
     *
     * @return void
     */
    public function testGetSummary()
    {
        $summary = $this->_object->getSummary();

        $this->assertNotContains('Codingstandard error level', $summary);
    }

    /**
     * Test get summary short
     *
     * @return void
     */
    public function testGetSummaryShort()
    {
        $summary = $this->_object->getSummary(true);

        $this->assertContains('Codingstandard error level', $summary);
    }

    /**
     * Test get default ini
     *
     * @return void
     */
    public function testGetDefaultIni()
    {
        $defaultIni = $this->_object->getDefaultIni();

        $this->assertContains('; Module to run codesniffs', $defaultIni);
        $this->assertContains('codingstandard.standard=', $defaultIni);
    }

    /**
     * Test get status
     *
     * @return void
     */
    public function testGetStatus()
    {
        $status = $this->_object->getStatus();

        $this->assertTrue($status);
    }

    /**
     * Test get status error
     *
     * @return void
     */
    public function testGetStatusError()
    {
        $settings = array(
            'standard' => 'Apricot',
            'path'     => '.',
        );

        $this->_object = new MockQisModuleCodingstandardErrorLevel(
            $this->_getDefaultQisObject(array()), $settings
        );

        $status = $this->_object->getStatus();

        $this->assertFalse($status);
    }

    /**
     * Create object
     *
     * @param bool $initialize Whether to initialize
     * @param Qis_Console_ArgV $args Arguments to pass to object
     * @return Qis_Module_Codingstandard
     */
    protected function _createObject($initialize = true, $args = array())
    {
        $settings = array(
            'standard' => 'Apricot',
            'path'     => '.',
        );

        $this->_object = new MockQisModuleCodingstandard(
            $this->_getDefaultQisObject($args), $settings
        );

        if ($initialize) {
            $this->_object->initialize();
        }
    }

    /**
     * Get default qis object
     *
     * @param Qis_Console_ArgV $args Arguments
     * @return void
     */
    protected function _getDefaultQisObject($args = array())
    {
        $args     = new Qi_Console_ArgV($args);
        $terminal = new Qi_Console_Terminal();

        Qis::$exit = false;
        return new Qis($args, $terminal);
    }
}
