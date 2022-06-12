<?php

namespace App\Console\Commands;

use App\Publication;
use App\SaleItem;
use App\SaleItemSnapshot;
use App\VendorProduct;
use Illuminate\Console\Command;

class MoveSaleItemsSnapShotsToSaleItemsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'move:sale_item_snapshots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command move sale item snapshot properties to sale items';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
//        SaleItemSnapshot::all()->map(function ($saleItemSnapshot){
//            $product = [];
//            if (isset($saleItemSnapshot->vendor['sku']))
//            {
//                $vendorProduct = VendorProduct::where('sku', $saleItemSnapshot->vendor['sku'])->first();
//                if ($vendorProduct)
//                {
//                    $product['vendor_product_id'] = $vendorProduct->id;
//                    $product['vendor_id'] = $vendorProduct->vendor_id;
//                }
//            }
//
//            if (isset($saleItemSnapshot->publication['id']))
//            {
//                $publication = Publication::find($saleItemSnapshot->publication['id']);
//                $product['publication_id'] =$publication ? $publication->id : null;
//            }
//
//            $product['publication_snapshot'] = ($saleItemSnapshot->publication ?? null);
//            $product['vendor_product_snapshot'] = ($saleItemSnapshot->vendor ?? null);
//            $product['publication_sale_price'] = $saleItemSnapshot->publication['price'] ?? null;
//            $product['vendor_product_cost'] = $saleItemSnapshot->vendor['price'] ?? null;
//
//            SaleItem::updateOrCreate(['sale_item_snapshot_id' => $saleItemSnapshot->id], $product);
//
//        });
//        SaleItem::whereNotNull('vendor_product_cost')->get()->map(function ($saleItem) {
//            $saleItem->
//        });

    }
}
