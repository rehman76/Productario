<?php
/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 29/07/2021
 * Time: 04:04 PM
 */

namespace App\Services;

use App\Connectors\MercadolibreConnector;
use App\Jobs\DispatchOrderToMicroGlobalJob;
use App\Publication;
use App\PublicationPremiumProduct;
use App\Sale;
use App\SaleBuyer;
use App\SaleItem;
use App\SalePayment;
use App\VendorProduct;
use Illuminate\Support\Facades\Storage;

class SaleService
{
    protected $shippingCost, $order;


    public static function saleOrder($order, $resendOrder = false)
    {
        if (isset($order['error'])) {
            return ;
        }
        $microGlobalSaleItems = [];
        $mercadolibreConnector = new MercadolibreConnector();
        $getPaymentAgainstOrder = $mercadolibreConnector->getPaymentAgainstOrder( $order['payments'][0]['id']);
        $getShippingAgainstProduct = $mercadolibreConnector->getShipmentCostAgainstOrder($order['shipping']['id']);
        $flexShippingMethods = ['Prioritário', 'Prioritario a domicilio', 'Prioritario a sucursal de correo'];
        $isFlexShippingMethod = false;
        $shipmentPrice = 0;


        if($order['shipping']['id'])
        {
            $shipment = $mercadolibreConnector->getShipmentAgainstOrder($order['shipping']['id']);
            $isFlexShippingMethod = in_array($shipment['shipping_option']['name'], $flexShippingMethods) ? true : false;
        }

        if(!array_key_exists('error',$getShippingAgainstProduct))
        {
            $shipmentPrice = $getShippingAgainstProduct['senders'][0]['save'];
        }

        if(!array_key_exists('error',$getPaymentAgainstOrder)) {

            $deductions = [
                'ml' => 0,
                'mp' => 0
            ];
                foreach ($getPaymentAgainstOrder['charges_details'] as $charges_detail) {
                    $type = $charges_detail['accounts']['to'];
                    $value = $charges_detail['amounts']['original'];

                    $deductions[$type] = isset($deductions[$type]) ? $deductions[$type] + $value : $value;
                }

                $mlCommission = $deductions['ml'];
                $tax = $deductions['mp'];
                $totalAmount = $getPaymentAgainstOrder['transaction_details']['total_paid_amount'] ?? 0;

            $saleOrder = ['order_id' => $order['id'], 'status' => $order['status'], 'total_amount' => isset($order['total_amount']) ? $order['total_amount'] : 0
                        , 'date_created' => $order['date_created']]; // here at we store sub total instead of total amount
            $vendorProductPrice = 0;
            $saleOrder = self::updateOrCreate($saleOrder, ['order_id' => $order['id']]);
            foreach ($order['order_items'] as $item) {
                $product = self::getProductDetails($item);
                $publication = self::findPublication($product['mla_id']);

                if ($publication && $publication->winner_vendor_product_id) {
                    $vendorProduct = self::findVendorProduct($publication->winner_vendor_product_id);
                    $product['is_publish'] = true;
                    $product['sale_id'] = $saleOrder->id;
                    $product['vendor_product_id'] = $vendorProduct->id;
                    $product['vendor_id'] = $vendorProduct->vendor_id;
                    $product['publication_id'] = $publication->id;
                    $product['publication_snapshot'] = ($publication);
                    $product['vendor_product_snapshot'] = ($vendorProduct);
                    $product['publication_sale_price'] = $publication->publication_sale_price;
                    $product['vendor_product_cost'] = $vendorProduct->price * $product['qty'];
                    if ($saleOrder['status'] == 'paid') {
                        $microGlobalSaleItems = self::checkAndPushIfSaleItemRelatedToMicroglobal($product, $saleOrder, $vendorProduct->sku, $microGlobalSaleItems, $resendOrder);
                        $vendorProductPrice += ($vendorProduct->price ?? 0) * ($product['qty'] ?? 0);
                    }
                } else {
                    $product['is_publish'] = false;
                }

                SaleItem::updateOrCreate(['sale_id' => $saleOrder->id, 'mla_id' => $product['mla_id']], $product);
            }

            $buyer = self::buyerRequiredField($saleOrder->id, $order['buyer']);
            SaleBuyer::updateOrCreate(['sale_id' => $saleOrder->id, 'buyer_id' => $buyer['buyer_id']], $buyer);
            self::createPayment($order['payments'], $saleOrder->id);
            if ($saleOrder['status'] == 'paid') {
                $profit = self::calculateProfitByUsingTaxes($tax, $mlCommission, $shipmentPrice, $totalAmount, $vendorProductPrice);
                $expense = $profit - $totalAmount ?? 0;
                self::updateOrCreate(['profit' => ($profit), 'ml_commissions' => $mlCommission, 'expense' => $expense, 'shipping_cost' => $shipmentPrice, 'taxes' => $tax], ['order_id' => $order['id']]);
                self::checkAndDispatchOrderToMicroglobal($microGlobalSaleItems, $order, $saleOrder, $mercadolibreConnector, $isFlexShippingMethod);
            }
        }
    }

    public static function createPayment($payments, $saleId)
    {
        if (!empty($payments)) {
            foreach ($payments as $payment) {
                SalePayment::updateOrCreate(
                    ['sale_id' => $saleId, 'payment_id' => $payment['id']],
                    ['payment_id' => $payment['id'],
                        'transaction_amount' => $payment['transaction_amount'],
                        'currency_id' => $payment['currency_id'],
                        'status' => $payment['status'],
                    ]);
            }
        }
    }

    public static function getProductDetails(array $data): array
    {
        $object['mla_id'] = $data['item']['id'];
        $object['title'] = $data['item']['title'];
        $object['qty'] = $data['quantity'] ?? 0;
        $object['sale_fee'] = $data['sale_fee'] ?? 0;;
        $object['unit_price'] = $data['unit_price'] ?? 0;;
        $object['full_unit_price'] = $data['full_unit_price'] ?? 0;;
        return $object;

    }

    public static function findPublication($mlaId)
    {
        if ($publication = self::getMLAProduct($mlaId))
        {
            return $publication;
        }

        /*** Check if MLA is related to catalog product ****/
        $mercadolibreConnector = new MercadolibreConnector();
        $mlaProduct = $mercadolibreConnector->getProduct($mlaId);

        if ($mlaProduct['catalog_listing'] && $mlaProduct['item_relations'])
        {
            if ($publication = self::getMLAProduct($mlaProduct['item_relations'][0]['id']))
            {
                return $publication;
            }
        }

        return [];
    }

    public static function findVendorProduct($id)
    {
        return VendorProduct::where('id', $id)->first(['id', 'vendor_id', 'sku', 'name', 'status', 'description', 'ean',
            'currency', 'link', 'quantity', 'notes', 'vendor_price', 'min_quantity', 'price', 'calculated_retail_price',
            'sale_price', 'discount', 'iva', 'other_taxes', 'weight']) ?? [];
    }

    public static function buyerRequiredField($sale_id, array $data)
    {
        $object = [];
        $object['buyer_id'] = $data['id'];
        $object['first_name'] = $data['first_name'] ?? '';

        $object['last_name'] = $data['last_name'] ?? '';
        $object['nick_name'] = $data['nickname'] ?? '';
        $object['email'] = $data['email'] ?? '';
        $object['sale_id'] = $sale_id;
        return $object;
    }


    public static function updateOrCreate(array $data, array $condition = null)
    {
        return $condition ? Sale::updateOrCreate($condition, $data) : Sale::updateOrCreate($data);
    }

    public static function calculateProfitByUsingTaxes($tax = 0, $mlCommission = 0, $shipmentPrice = 0, $total_amount = 0, $vendorProductPrice = 0)
    {

            return ($total_amount + $shipmentPrice - $mlCommission - $tax)-$vendorProductPrice;
    }


    public static function checkAndPushIfSaleItemRelatedToMicroglobal($saleItem, $saleOrder, $vendorProductSku, $microGlobalSaleItems, $resendOrder)
    {

        $isSaleItemExists = SaleItem::where('sale_id','=',$saleOrder->id)->where('mla_id','=',$saleItem['mla_id'])->exists();

        if ($saleItem['vendor_id'] == Constants::VendorMicroglobalId  && (!$isSaleItemExists || $resendOrder)) {
            array_push($microGlobalSaleItems, [
                'qty' => $saleItem['qty'],
                'part_number' => $vendorProductSku
            ]);
        }

        return $microGlobalSaleItems;
    }

    /*
     *  There are three cases for shipping label and shipping method
     *  1.  if the shipping method is pickup (RETIRA) then no need of shipping label , if shipping id is null that means shipping is pickup.
     *  2.  if the shipping method is ME(ENVIA) then we have to check all items must be for microglobal if they are then we will print the label and send that method
     *  3.  if shipping type flex then we do generate the label but didn't send, and method we will send pickup (RETIRA) to microglobal
     *
     */
    public static function checkAndDispatchOrderToMicroglobal($microGlobalSaleItems, $order, $saleOrder, $mercadolibreConnector, $isFlexShippingMethod)
    {
        if ($microGlobalSaleItems)
        {
            $shippingMethod = self::getShippingMethodOfOrder($microGlobalSaleItems, count($order['order_items']), $isFlexShippingMethod, $order);

            if(!$saleOrder->sale_label && $order['shipping']['id']
                        && ($shippingMethod=='ENVIA' || ($shippingMethod=='RETIRA' && $isFlexShippingMethod)))
            {
                $shippingLabel = $mercadolibreConnector->getShipmentLabel($order['shipping']['id']);
                if($shippingLabel->successful())
                {
                    $path= 'orders/'.$order['id'].'/'.uniqid().'.pdf';
                    Storage::disk('s3-disk')->put($path, $shippingLabel);
                    $saleOrder->sale_label = Storage::disk('s3-disk')->url($path);;
                    $saleOrder->save();
                }
            }
            DispatchOrderToMicroGlobalJob::dispatch($microGlobalSaleItems, $order, $saleOrder, $shippingMethod, $isFlexShippingMethod);
        }
    }

    public static function getShippingMethodOfOrder($microGlobalSaleItems, $orderItemsCount, $isFlexShippingMethod, $order)
    {
        if(count($microGlobalSaleItems) == $orderItemsCount && !$isFlexShippingMethod && $order['shipping']['id'])
        {
           return 'ENVIA';
        }

        return 'RETIRA';
    }

    public static function getMLAProduct($mlaId)
    {
        $columns =  ['id', 'winner_vendor_product_id', 'Name', 'Description', 'SKU', 'MarkUp', 'discount', 'Mla',
            'attributes', 'quantity', 'notes', 'sale_price', 'price', 'iva', 'other_taxes', 'status', 'min_quantity', 'ean'];

        $publication =  Publication::where('mla', $mlaId)->first($columns);
        /*** If MLA related to classic Publication ***/
        if ($publication)
        {
            return $publication;
        }

        $premiumPublication  =  PublicationPremiumProduct::where('mla_id', $mlaId)->first();

        /*** If MLA related to premium publication ***/
        if ($premiumPublication)
        {
            return Publication::where('id', $premiumPublication->publication_id)->first($columns);
        }

        return [];
    }



}
