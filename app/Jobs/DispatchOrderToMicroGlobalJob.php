<?php

namespace App\Jobs;

use App\SaleLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class DispatchOrderToMicroGlobalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected  $microGlobalSaleItems = [], $order, $saleOrder, $shippingMethod, $isFlexShippingMethod;

    public function __construct($microGlobalSaleItems, $order, $saleOrder, $shippingMethod, $isFlexShippingMethod)
    {
        $this->onQueue('sale');
        $this->microGlobalSaleItems = $microGlobalSaleItems;
        $this->order = $order;
        $this->saleOrder = $saleOrder;
        $this->shippingMethod = $shippingMethod;
        $this->isFlexShippingMethod = $isFlexShippingMethod;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $message = '';
        $shippingDispatchNotes = 'Sin Notas';
        $shippingNotes = 'RETIRA';
        $products = '';

        foreach ($this->microGlobalSaleItems as $microGlobalSaleItem)
        {
            $products.= '<Producto>
                            <PartNumber>'. $microGlobalSaleItem['part_number'] .'</PartNumber>
                            <Cantidad>'. $microGlobalSaleItem['qty'] .'</Cantidad>
                         </Producto>';
        }

        if($this->shippingMethod == 'ENVIA')
        {
            $shippingDispatchNotes = $this->saleOrder->sale_label;
            $shippingNotes = 'ENVIA';
        }

        $shippingNotes.= $this->isFlexShippingMethod ? '-FLEX' : '';


        $requestXml = '<?xml version="1.0" encoding="utf-8"?>
                    <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                      <soap:Body>
                        <SendAltaPedido xmlns="http://tempuri.org/">
                          <cliente>'. env('MICROGLOBAL_SERVICE_CLIENT_ID') .'</cliente>
                          <password>'. env('MICROGLOBAL_SERVICE_PASSWORD') .'</password>
                          <oPedido xmlns="">
                            <IDs>
                              <Origen>TIENDA</Origen>
                              <Tienda>17</Tienda>
                              <Cliente>'. env('MICROGLOBAL_SERVICE_CLIENT_ID') .'</Cliente>
                              <ID>'. $this->order['id'] .'</ID>
                            </IDs>
                            <TipoEnvio>'.$this->shippingMethod.'</TipoEnvio>
                            <Sucursal>BATEPRECIOS</Sucursal>
                            <UFI>
                              <Nombre>'.$this->order['buyer']['first_name'].' '. $this->order['buyer']['last_name'] .'</Nombre>
                              <Empresa>'. $this->order['buyer']['nickname'] .'</Empresa>
                              <Documento>20183438</Documento>
                            </UFI>
                            <Moneda>PES</Moneda>
                            <Observacion>'.$shippingNotes.'</Observacion>
                            <ObservacionDespacho>'.$shippingDispatchNotes.'</ObservacionDespacho>
                            <Productos>'. $products .'</Productos>
                          </oPedido>
                        </SendAltaPedido>
                      </soap:Body>
                    </soap:Envelope>';

        $response =  Http::withHeaders([
            "SOAPAction" => "http://tempuri.org/SendAltaPedido"
        ])->withBody($requestXml, 'text/xml;charset=utf-8')
            ->post(env('MICROGLOBAL_SERVICE_URL'));

        $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response->body());
        $reponseXml = new SimpleXMLElement($response);
        $body = $reponseXml->xpath('//soapBody')[0];
        $sendAltaPedidoResponse = json_decode(json_encode((array)$body), TRUE);

        $response =  $sendAltaPedidoResponse['SendAltaPedidoResponse']['SendAltaPedidoResult'];

        if ($response['result'] != "0")
        {
            $message = $response['message'];
        } else {
            $this->saleOrder->attributes = [
              'Microglobal Sale Id' =>   $response['pedidoResponse']['NumNdp']
            ];
            $this->saleOrder->save();

            $message = 'Miscroglobal Sales Generated with order number#'. $response['pedidoResponse']['NumNdp'];
        }

        SaleLog::create([
            'message' => $message . '.  Find the request here ---------------> '. $requestXml,
            'sale_id' => $this->saleOrder->id,
        ]);
    }
}
