<?php

namespace App\Exports;

use App\VendorProduct;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VendorProductsExport implements FromCollection, WithHeadings
{
    protected $vendorId;

    public function __construct($vendorId)
    {
          $this->vendorId = $vendorId;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return VendorProduct::where('vendor_id', $this->vendorId)->get();
    }

    public function headings() : array
    {
        return $this->collection()->first() ? array_keys($this->collection()->first()->toArray()): [];
    }
}
