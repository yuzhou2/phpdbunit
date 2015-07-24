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

/**
 * Array DataSet. The code is from the see link.
 *
 * @see http://www.phpunit.de/manual/3.6/en/database.html#available-implementations
 */
class DbUnit_ArrayDataSet extends \PHPUnit_Extensions_Database_DataSet_AbstractDataSet
{
    /**
     * @var array
     */
    protected $tables = array();


    /**
     * Construct.
     *
     * @param array $data the data for all tables
     */
    public function __construct(array $data)
    {
        foreach ($data as $tableName => $rows) {
            $columns = array();
            if (isset($rows[0])) {
                $columns = array_keys($rows[0]);
            }

            $metaData = new \PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData(
                $tableName, $columns
            );
            $table = new \PHPUnit_Extensions_Database_DataSet_DefaultTable($metaData);

            foreach ($rows as $row) {
                $table->addRow($row);
            }
            $this->tables[$tableName] = $table;
        }
    }

    /**
     * Create an instance of iterator.
     *
     * @param boolean $reverse reverse or not
     *
     * @return PHPUnit_Extensions_Database_DataSet_ITableIterator
     */
    protected function createIterator($reverse=false)
    {
        return new \PHPUnit_Extensions_Database_DataSet_DefaultTableIterator(
            $this->tables, $reverse
        );
    }

    /**
     * Get the data by the table name.
     *
     * @param string $tableName the table name
     *
     * @return array, the table data
     */
    public function getTable($tableName)
    {
        if (!isset($this->tables[$tableName])) {
            $msg = "$tableName is not a table in the current database.";
            throw new \InvalidArgumentException($msg);
        }
        return $this->tables[$tableName];
    }

}
