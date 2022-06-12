<?php


namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Publication;


class PublicationExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return ['Id','Name', 'SKU', 'EAN', 'Description' , 'Markup', 'Discount','Price', 'Minimum Price',
            'Price Variation %', 'Sale Price', 'Quantity', 'Quantity Variation %','TN ID','TN Product URL' ,'MLA ID',  'Is Bundle',
             'Created At'];

    }

    public function collection()
    {
        return Publication::all(['id', 'name', 'sku', 'ean', 'description', 'markup', 'discount', 'price',
                'minimum_price','price_variation', 'sale_price', 'quantity', 'quantity_variation',
                        'tiendanube_id', 'tiendanube_product_url', 'mla', 'is_bundle', 'created_at']);
    }

}