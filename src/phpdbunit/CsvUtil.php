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
 * Utilities for processing CSV file.
 */
final class CsvUtil
{


    /**
     * Load csv data for providing expected data.
     *
     * @param string $file file path
     *
     * @return array, csv data
     */
    public static final function loadCsv($file)
    {
        if (($fp = fopen($file, 'r')) !== false) {
            $csv = array();
            $header = fgetcsv($fp);
            while (($data = fgetcsv($fp)) !== false) {
                $csv[] = array_combine($header, $data);
            }
            fclose($fp);
            return $csv;
        }
        throw new \Exception("[ERROR] Can not load csv file: $file");
    }

}
