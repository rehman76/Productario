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
class MasnetProductsImport implements ToArray, WithHeadingRow, WithCustomCsvSettings {
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

    /**
     * Custom CSV settings.
     *
     * @return array
     */
    public function getCsvSettings(): array {
        return [
            'delimiter' => ';',
        ];
    }
}
