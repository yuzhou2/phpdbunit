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

use phpdbunit\DbUnit_ArrayDataSet;

/**
 * Abstract class for testing DBUnit.
 * @see     http://www.phpunit.de/manual/3.6/en/database.html#tip:-use-your-own-abstract-database-testcase
 */
abstract class AbstractDatabaseTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    /**
     * only instantiate pdo once for test clean-up/fixture load
     * @var \PDO
     */
    protected static $pdo;
    /**
     * only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
     * @var PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    private $conn;

    /**
     * The sql string for create table.
     * Sometimes we might need to create other table to help test, use the
     * syntax <code>CREATE ...; CREATE ...</code> to create tables.
     *
     * @var string
     */
    protected $createSql;
    /**
     * The sample data for test, write as a CSV file and the file should
     * contain column header.  The field format is an array as
     * (table_name, file_path).  The file_path should be an absolute path.
     * For example,
     * <pre>
     * array('table1' => dirname(__FILE__).'/Tester.csv')
     * </pre>
     * Note that you can use this attribute to provide sample data, or use
     * $sampleData.  But the sample data in one table cannot exist in both
     * two places.
     *
     * @var array
     * @see http://www.phpunit.de/manual/3.6/en/database.html#understanding-datasets-and-datatables
     */
    protected $sampleCsv;
    /**
     * The sample data for test, and it should be a table array, such as
     * (table_name, data_array).  For example,
     * <pre>
     * array('table1' => array(array('col1' => 'Aa1', 'col2' => 'Bb2'),
     *                         array('col1' => 'Cc3', 'col2' => 'Dd4')))
     * </pre>
     * Note that you can use this attribute to provide sample data, or use
     * $sampleCsv.  But the sample data in one table cannot exist in both
     * two places.
     *
     * @var array
     * @see http://www.phpunit.de/manual/3.6/en/database.html#understanding-datasets-and-datatables
     */
    protected $sampleData;


    /**
     * Initialize $createSql, $sampleData/$sampleCsv, ... etc.
     * This function will be called in {@link #setUp()}, and then
     * create a DAO instance for test.
     *
     * @return void
     */
    abstract protected function initParam();

    /**
     * Creates a new ArrayDataSet with the given $arrayFile.  For example,
     * <pre>
     * array('table1' => array(array('col1' => 'Aa1', 'col2' => 'Bb2'),
     *                         array('col1' => 'Cc3', 'col2' => 'Dd4')))
     * </pre>
     *
     * @param array $arrayFile array of sample
     *
     * @return DbUnit_ArrayDataSet
     */
    protected final function createArrayDataSet($arrayFile)
    {//{{{
        return new DbUnit_ArrayDataSet($arrayFile);
    }//}}}

    /**
     * Creates a new CsvDataSet with the given $csvFile.  For example,
     * <pre>
     * array('table1' => dirname(__FILE__).'/Tester.csv')
     * </pre>
     *
     * @param array $csvFile array of csv file
     *
     * @return PHPUnit_Extensions_Database_DataSet_CsvDataSet
     */
    protected final function createCsvDataSet($csvFile)
    {//{{{
        $dataSet = new \PHPUnit_Extensions_Database_DataSet_CsvDataSet();
        foreach ($csvFile as $tblName => $path) {
            $dataSet->addTable($tblName, $path);
        }
        return $dataSet;
    }//}}}

    /**
     * Implement to return the database connection that will be checked for
     * expected data sets and tables.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     * @override
     * @see http://www.phpunit.de/manual/3.4/en/database.html
     */
    protected function getConnection()
    {//{{{
        if ($this->conn === null) {
            if (self::$pdo === null) {
                self::$pdo = new \PDO('sqlite::memory:');
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, ':memory:');
        }
        return $this->conn;
    }//}}}

    /**
     * Implement to return the data set that will be used in in database set up
     * and tear down operations.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     * @override
     * @see http://www.phpunit.de/manual/3.4/en/database.html
     */
    protected function getDataSet()
    {//{{{
        $result = new \PHPUnit_Extensions_Database_DataSet_CompositeDataSet(array());
        if ($this->sampleData) {
            $dataSet = $this->createArrayDataSet($this->sampleData);
            $result->addDataSet($dataSet);
        }
        if ($this->sampleCsv) {
            $dataSet = $this->createCsvDataSet($this->sampleCsv);
            $result->addDataSet($dataSet);
        }
        return $result;
    }//}}}

    /**
     * Override to return a specific operation that should be performed on the
     * test database at the beginning of each test.
     *
     * @return PHPUnit_Extensions_Database_Operation_DatabaseOperation
     * @override
     * @see http://www.phpunit.de/manual/3.4/en/database.html
     */
    protected function getSetUpOperation()
    {//{{{
        $this->createTable();
        return parent::getSetUpOperation();
    }//}}}

    /**
     * Get a row from the specific table by the primary key.
     *
     * @param string $tableName  the table name
     * @param array  $conditions the where conditions
     *
     * @return array, a row
     */
    protected final function getRow($tableName, array $conditions)
    {//{{{
        //[ferret ignore][false alarm] test code
        $sql = 'SELECT * FROM '.$tableName.' WHERE ';
        $row = -1;
        while (list($field, $value) = each($conditions)) {
            $row++ || $sql .= ' AND ';
            $sql .= "$field = '$value'";
        }
        $table = $this->getConnection()->createQueryTable($tableName, $sql);
        return $table->getRow(0);
    }//}}}

    /**
     * Get table with data.
     *
     * @param string $tableName the table name
     *
     * @return PHPUnit_Extensions_Database_DataSet_ITable
     */
    protected final function getTable($tableName)
    {//{{{
        //[ferret ignore][false alarm] test code
        $sql = 'SELECT * FROM '.$tableName;
        return $this->getConnection()->createQueryTable($tableName, $sql);
    }//}}}

    /**
     * Load csv data for providing expected data.
     *
     * @param string $file file path
     *
     * @return array, the csv data
     */
    protected final function loadCsv($file)
    {//{{{
        return CsvUtil::loadCsv($file);
    }//}}}

    /**
     * Set up.
     *
     * @return void
     * @override
     */
    protected function setUp()
    {//{{{
        $this->initParam();
        parent::setUp();
    }//}}}

    /**
     * Tear down.
     *
     * @return void
     * @override
     */
    protected function tearDown()
    {//{{{
        parent::tearDown();
        unset($this->conn);
        unset($this->createSql);
        unset($this->sampleData);
        unset($this->sampleCsv);
    }//}}}

    /**
     * Create database table.
     *
     * @return void
     */
    private function createTable()
    {//{{{
        if ($this->createSql) {
            foreach (explode(';', $this->createSql) as $sql) {
                $sth = self::$pdo->prepare($sql);
                $sth->execute();
            }
        }
    }//}}}

}
