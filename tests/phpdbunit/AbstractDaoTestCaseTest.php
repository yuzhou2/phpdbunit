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

//use phpdbunit\AbstractDatabaseTestCase;

/**
 * Test the AbstractDaoTestCase class.
 */
class AbstractDatabaseTestCaseTest extends AbstractDatabaseTestCase
{
    /**
     * The table name for test CSV file.
     * @var string
     */
    private $csvTableName;
    /**
     * The table name for test array.
     * @var string
     */
    private $arrayTableName;


    /**
     * Test the getDataSet() function.
     *
     * @return void
     */
    public function testGetDataSet()
    {//{{{
        // table1
        $exValue1 = array();
        $exValue1[0]['uid'] = 100;
        $exValue1[0]['name'] = 'abc';
        $exValue1[1]['uid'] = 101;
        $exValue1[1]['name'] = 'xyz';
        $exDataSet1 = new DbUnit_ArrayDataSet(array($this->csvTableName => $exValue1));
        $exTable1 = $exDataSet1->getTable($this->csvTableName);

        $acTable1 = $this->getDataSet()->getTable($this->csvTableName);
        $this->assertTablesEqual(
            $exTable1, $acTable1, "[ERROR] Not equal to original data, table: $this->csvTableName"
        );
        $this->assertTablesEqual(
            $exTable1, $this->getTable($this->csvTableName),
            "[ERROR] Not equal to actual data, table: $this->csvTableName"
        );

        // table2
        $exValue2 = $this->sampleData[$this->arrayTableName];
        $exDataSet2 = new DbUnit_ArrayDataSet(array($this->arrayTableName => $exValue2));
        $exTable2 = $exDataSet2->getTable($this->arrayTableName);

        $acTable2 = $this->getDataSet()->getTable($this->arrayTableName);
        $this->assertTablesEqual(
            $exTable2, $acTable2, "[ERROR] Not equal to original data, table: $this->arrayTableName"
        );
        $this->assertTablesEqual(
            $exTable2, $this->getTable($this->arrayTableName),
            "[ERROR] Not equal to actual data, table: $this->arrayTableName"
        );

        // data set
        $exDataSet = new DbUnit_ArrayDataSet(array(
                                              $this->csvTableName   => $exValue1,
                                              $this->arrayTableName => $exValue2,
                                             ));
        $this->assertDataSetsEqual(
            $exDataSet, $this->getDataSet(), '[ERROR] Not equal to data set.'
        );
    }//}}}

    /**
     * Test the getRow() function.
     *
     * @return void
     */
    public function testGetRow()
    {//{{{
        $expect = array();
        $expect['uid'] = 101;
        $expect['name'] = 'xyz';

        $actual = $this->getRow($this->csvTableName, array('uid' => 101));

        $this->assertEquals($expect, $actual, '[ERROR] Fetch wrong row');
    }//}}}

    /**
     * Test the loadCsv() function.
     *
     * @return void
     */
    public function testLoadCsv()
    {//{{{
        $expect = array();
        $expect[0]['uid'] = 100;
        $expect[0]['name'] = 'abc';
        $expect[1]['uid'] = 101;
        $expect[1]['name'] = 'xyz';

        $csv = $this->loadCsv(dirname(__FILE__).'/AbstractDaoTestCaseTest.csv');
        $this->assertEquals($expect, $csv);
    }//}}}

    /**
     * Initialize $daoName, $createSql, $sampleData/$sampleCsv.
     *
     * @return void
     * @override
     */
    protected function initParam()
    {//{{{
        $csvTableName = 'abstract_dao_test_case_csv';
        $arrayTableName = 'abstract_dao_test_case_array';

        $this->csvTableName = $csvTableName;
        $this->arrayTableName = $arrayTableName;
        $this->daoName = 'AbstractDaoTestCaseDao';
        $this->createSql = <<<EOT
            CREATE TEMPORARY TABLE IF NOT EXISTS $csvTableName
            (
                uid         INT(16)     NOT NULL,
                name        VARCHAR(20) NOT NULL,
                PRIMARY KEY (uid)
            );
            CREATE TEMPORARY TABLE IF NOT EXISTS $arrayTableName
            (
                uid         INT(16)     NOT NULL,
                PRIMARY KEY (uid)
            );
EOT;

        $this->sampleCsv = array($csvTableName => dirname(__FILE__).'/AbstractDaoTestCaseTest.csv');
        $this->sampleData = array();
        $this->sampleData[$arrayTableName][] = array('uid' => 1);
    }//}}}

    /**
     * Tear down.
     *
     * @return void
     * @override
     */
    protected function tearDown()
    {//{{{
        unset($this->csvTableName);
        unset($this->arrayTableName);
        parent::tearDown();
    }//}}}

}
