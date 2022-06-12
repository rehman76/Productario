<?php

namespace App\Nova\Filters;

use App\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Filters\Filter;

class PublicationByVendors extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        $vendorProductsPublications = DB::table('vendor_products')->where('vendor_id','=', $value)
            ->join('publication_vendor_product', 'vendor_products.id','=','publication_vendor_product.vendor_product_id')
            ->pluck('publication_vendor_product.publication_id');

        return $query->whereIn('id',$vendorProductsPublications);

    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        $vendors = Vendor::all();
        $vendorsArray = collect();

        foreach ($vendors as $vendor)
        {
            $vendorsArray->put($vendor->name, $vendor->id);
        }

        return $vendorsArray;
    }
}
