<?php
/**
 * This file is part of phpdbunit.
 *
 * (c) yuzhou2
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace phpdbunit;

use phpdbunit\CsvUtil;

/**
 * Test the CsvUtilTest class.
 */
class CsvUtilTest extends \PHPUnit_Framework_TestCase
{


    /**
     * Test loadCsv() function with simple case.
     *
     * @return void
     */
    public function testLoadCsvSimple()
    {//{{{
        $expect = array();
        $expect[0]['col1'] = 'Aa';
        $expect[0]['col2'] = 'Bb';
        $expect[1]['col1'] = 'Cc';
        $expect[1]['col2'] = 'Dd';

        $csv = CsvUtil::loadCsv(__DIR__.'/CsvUtilTest.csv');
        $this->assertEquals($expect, $csv);
    }//}}}

}
