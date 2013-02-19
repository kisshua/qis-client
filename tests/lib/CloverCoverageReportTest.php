<?php
/**
 * Clover Coverage Report test class file
 *
 * @package Qis
 */

/**
 * @see CloverCoverageReport
 */
require_once 'CloverCoverageReport.php';

/**
 * Clover Coverage Report test class
 * 
 * @uses BaseTestCase
 * @package Qis
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class CloverCoverageReportTest extends BaseTestCase
{
    /**
     * Setup before each test
     * 
     * @return void
     */
    public function setUp()
    {
        //$this->_createXmlFile();
    }

    /**
     * Tear down after each test
     * 
     * @return void
     */
    public function tearDown()
    {
        @unlink('samplecoverage.xml');
    }

    /**
     * Test construction incorrectly
     * 
     * @expectedException PHPUnit_Framework_Error_Warning
     * @return void
     */
    public function testConstructionIncorrectly()
    {
        $this->_object = new CloverCoverageReport();
    }

    /**
     * A valid file is required
     * 
     * @expectedException CloverCoverageReportException
     * @return void
     */
    public function testConstructionFileNotExists()
    {
        $this->_object = new CloverCoverageReport('nofile.xml');
    }

    /**
     * Test report no project node
     *
     * @return void
     */
    public function testReportNoProjectNode()
    {
        $contents = '<?xml version="1.0" encoding="UTF-8"?>
    <coverage generated="1301557448" phpunit="3.4.15">
    </coverage>';
        $this->_createXmlFile($contents);

        $result = $this->_bufferOutput();

        $this->assertContains('Coverage report generated', $result);
        $this->assertContains('Total Coverage', $result);
    }

    /**
     * Test report project is present
     *
     * @return void
     */
    public function testReportProjectIsPresent()
    {
        $this->_createXmlFile();

        $result = $this->_bufferOutput();

        $this->assertContains('Coverage report generated', $result);
        $this->assertContains('107 / 119 |  90%  [*', $result);
        $this->assertContains(' 12 /  15 |  80%  [*', $result);
        $this->assertContains('Total Coverage', $result);
    }

    /**
     * Passing in a file when no coverage xml
     * 
     * @expectedException CloverCoverageReportException
     * @return void
     */
    public function testReportFileAnalysisWhenNoXml()
    {
        $this->_createXmlFile('');

        $result = $this->_bufferOutput('foobar.php');
    }

    /**
     * Passing in a file not in coverage xml throws an exception
     * 
     * @return void
     */
    public function testReportFileAnalysisTargetFileNotExisting()
    {
        $this->_createXmlFile();

        $result = $this->_bufferOutput('foobar.php');
        $this->assertContains("No coverage information available", $result);
    }

    /**
     * When a full path is not given for a target file,
     * the file is relative to the common root
     * 
     * @return void
     */
    public function testReportFileAnalysisShortTarget()
    {
        $this->_createXmlFile();

        $result = $this->_bufferOutput(
            'vendor/sumpygump/qi-console/lib/Qi/Console/ArgV.php'
        );
        $this->assertContains('    1          : <' . '?php', $result);
    }

    /**
     * Test report file analysis empty filelist
     *
     * @return void
     */
    public function testReportFileAnalysisEmptyFilelist()
    {
        $contents = '<?xml version="1.0" encoding="UTF-8"?>
    <coverage generated="1301557448" phpunit="3.4.15">
    </coverage>';
        $this->_createXmlFile($contents);
        $report = $this->_bufferOutput('Crank.php');

        $result = $this->_object->generateFileAnalysis('Crankshaft.php');
        $this->assertEquals('', $report);
        $this->assertFalse($result);
    }

    /**
     * Test report excludes test files
     *
     * @return void
     */
    public function testReportExcludesTestFiles()
    {
        $path = realpath('..') . '/tests/';
        $file = $path . 'foobar.php';
        touch($file);

        $this->_createXmlFile();
        $result = $this->_bufferOutput();

        $this->assertNotContains('tests/foobar.php', $result);

        unlink($file);
    }

    /**
     * Test find common root
     *
     * @return void
     */
    public function testFindCommonRoot()
    {
        $list = array(
            'foo/bar/baz/woof',
            'foo/bar/baz/qoof',
            'foo/bar/cackle/quux',
        );

        $commonRoot = CloverCoverageReport::findCommonRoot($list);
        $this->assertEquals('foo/bar/', $commonRoot);
    }

    /**
     * String as input causes error
     * 
     * @expectedException PHPUnit_Framework_Error
     * @return void
     */
    public function testFindCommonRootString()
    {
        $list = 'foobar/baz/';

        $commonRoot = CloverCoverageReport::findCommonRoot($list);
    }

    /**
     * Object as input causes error
     * 
     * @expectedException PHPUnit_Framework_Error
     * @return void
     */
    public function testFindCommonRootObject()
    {
        $list = new StdClass();

        $list->foo = 'bar';

        $commonRoot = CloverCoverageReport::findCommonRoot($list);
    }

    /**
     * Test find common root one item
     *
     * @return void
     */
    public function testFindCommonRootOneItem()
    {
        $list = array(
            'foo/bar/baz/quux.php',
        );
        
        $commonRoot = CloverCoverageReport::findCommonRoot($list);
        $this->assertEquals('foo/bar/baz/', $commonRoot);
    }

    /**
     * Test find common root associated array
     *
     * @return void
     */
    public function testFindCommonRootAssociatedArray()
    {
        $list = array(
            'a' => 'foo/bar/baz/',
            'b' => 'foo/bar/goo/',
            'c' => 'foo/bar/wunk/',
        );

        $commonRoot = CloverCoverageReport::findCommonRoot($list);
        $this->assertEquals('foo/bar/', $commonRoot);
    }

    /**
     * Test find common root all equal
     *
     * @return void
     */
    public function testFindCommonRootAllEqual()
    {
        $list = array(
            'foo/bar/baz/',
            'foo/bar/baz/',
            'foo/bar/baz/',
        );

        $commonRoot = CloverCoverageReport::findCommonRoot($list);
        $this->assertEquals('foo/bar/baz/', $commonRoot);
    }

    /**
     * Test find common root no commonality
     *
     * @return void
     */
    public function testFindCommonRootNoCommonality()
    {
        $list = array(
            'foo/bar/baz',
            'baz/unk/qoof',
            'immy/soo/barn/toog',
        );

        $commonRoot = CloverCoverageReport::findCommonRoot($list);
        $this->assertEquals('', $commonRoot);
    }

    /**
     * The root should be discrete paths
     *
     * If a directory or file starts with the same letters, don't
     * consider the commonality among them as the root
     * 
     * @return void
     */
    public function testFindCommonRootReturnsOnlyPaths()
    {
        $list = array(
            'foo/bar/baz/woof',
            'foo/bar/baz/qoof',
            'foo/bar/bax/quux',
        );

        $commonRoot = CloverCoverageReport::findCommonRoot($list);
        $this->assertEquals('foo/bar/', $commonRoot);
    }

    /**
     * Buffer output and capture
     * 
     * @param string $targetFile Target file name
     * @return array
     */
    protected function _bufferOutput($targetFile = null)
    {
        ob_start();
        $this->_object = new CloverCoverageReport(
            'samplecoverage.xml', $targetFile
        );

        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    /**
     * Create xml file
     *
     * @param string $contents Contents of file
     * @return void
     */
    protected function _createXmlFile($contents = null)
    {
        $filename = 'samplecoverage.xml';
        $path     = realpath('..');

        // @codingStandardsIgnoreStart
        if (null === $contents) {
            $contents = '<?xml version="1.0" encoding="UTF-8"?>
    <coverage generated="1301557448" phpunit="3.4.15">
        <project name="." timestamp="1301558264">
        <file name="' . $path . '/vendor/sumpygump/qi-console/lib/Qi/Console/ArgV.php">
            <class name="Qi_Console_ArgV" namespace="global" fullPackage="Qi.Console" package="Qi" subpackage="Console">
                <metrics methods="17" coveredmethods="12" statements="119" coveredstatements="73" elements="136" coveredelements="85"/>
            </class>
            <line num="0" type="stmt" count="14"/>
            <line num="105" type="method" name="__construct" count="59"/>
            <metrics loc="423" ncloc="220" classes="1" methods="17" coveredmethods="0" statements="119" coveredstatements="107" elements="136" coveredelements="0"/>
        </file>
        <file name="' . $path . '/vendor/sumpygump/qi-console/lib/Qi/Console/Std.php">
            <class name="Qi_Console_Std" namespace="global" fullPackage="Qi.Console" package="Qi" subpackage="Console">
                <metrics methods="8" coveredmethods="7" statements="200" coveredstatements="190" elements="110" coveredelements="70"/>
            </class>
            <line num="0" type="stmt" count="14"/>
            <line num="105" type="method" name="__construct" count="59"/>
            <metrics loc="71" ncloc="29" classes="1" methods="3" coveredmethods="0" statements="15" coveredstatements="12" elements="18" coveredelements="0"/>
        </file>
        <file name="' . $path . '/vendor/sumpygump/qi-console/lib/Qi/Console/Terminfo.php">
            <class name="Qi_Console_Terminfo" namespace="global" fullPackage="Qi.Console" package="Qi" subpackage="Console">
                <metrics methods="0" coveredmethods="0" statements="0" coveredstatements="0" elements="0" coveredelements="0"/>
            </class>
            <metrics loc="0" ncloc="0" classes="0" methods="0" coveredmethods="0" statements="0" coveredstatements="0" elements="0" coveredelements="0"/>
        </file>
        <file name="' . $path . '/Cookoo.php">
          <class name="Cookoo" namespace="global" fullPackage="Qis" package="Qis">
            <metrics methods="24" coveredmethods="0" statements="152" coveredstatements="0" elements="176" coveredelements="0"/>
          </class>
          <line num="85" type="method" name="__construct" count="0"/>
          <metrics loc="509" ncloc="299" classes="1" methods="24" coveredmethods="0" statements="152" coveredstatements="0" elements="176" coveredelements="0"/>
        </file>
        <metrics files="18" loc="8565" ncloc="6392" classes="18" methods="236" coveredmethods="118" statements="2011" coveredstatements="851" elements="2247" coveredelements="969"/>
        </project>
    </coverage>';
        }
        // @codingStandardsIgnoreEnd

        file_put_contents($filename, $contents);
    }
}
