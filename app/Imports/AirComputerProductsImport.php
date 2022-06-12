<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;


/**
 * Stylus Publication data Importer.
 *
 * @author Theodore Yaosin <theodoreyaosin@outlook.com>
 */
class AirComputerProductsImport implements ToArray, WithHeadingRow {
    /**
     * Return array.
     *
     * @param  array  $array
     * @return array
     */
    public function array(array $row) {
        return $row;
    }

    /**
     * Count of heading rows.
     *
     * @return int
     */
    public function headingRow(): int {
        return 1;
    }
}
