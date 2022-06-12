<?php

use App\Notifications\ErrorNotification;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PublicationExport;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/dash');
})->middleware('auth');


Route::get('/test_notfication', function () {
   $response =  \Illuminate\Support\Facades\Http::withHeaders([
            "SOAPAction" => "http://tempuri.org/SendAltaPedido"
        ])->withBody('<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <SendAltaPedido xmlns="http://tempuri.org/">
      <cliente>928286</cliente>
      <usuario></usuario>
      <password>YFK665</password>
      <oPedido xmlns="">
        <IDs>
          <Origen>TIENDA</Origen>
          <Tienda>17</Tienda>
          <Cliente>928286</Cliente>
          <ID>123456323232131223132224324</ID>
        </IDs>
        <TipoEnvio>ENVIA</TipoEnvio>
        <Sucursal>BATEPRECIOS</Sucursal>
        <UFI>
          <Nombre>Ali Raza</Nombre>
          <Empresa>Softpers SYSTEMS</Empresa>
          <Documento>20183438</Documento>
        </UFI>
        <Moneda>PES</Moneda>
        <Observacion>ESTO ES UNA PRUEBA TECNICA, NO ENVIAR test</Observacion>
        <ObservacionDespacho>ESTO ES UNA PRUEBA TECNICA, NO ENVIAR testing</ObservacionDespacho>
        <Productos>
          <Producto>
            <PartNumber>PE-ET1000R</PartNumber>
            <Cantidad>1</Cantidad>
          </Producto>
          <Producto>
            <PartNumber>GV-R68XTAORUS M-16GD</PartNumber>
            <Cantidad>1</Cantidad>
          </Producto>
        </Productos>
      </oPedido>
    </SendAltaPedido>
  </soap:Body>
</soap:Envelope>', 'text/xml;charset=utf-8')
            ->post('https://ws.microglobal.com.ar/WSMGAltaNdp_test/WSMGAltaNdp.asmx');


    $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response->body());
    $xml = new SimpleXMLElement($response);
    $body = $xml->xpath('//soapBody')[0];
    $sendAltaPedidoResponse = json_decode(json_encode((array)$body), TRUE);

    dd($sendAltaPedidoResponse['SendAltaPedidoResponse']['SendAltaPedidoResult']['result']);
})->middleware('auth');

Route::get('login', function () {
    return redirect()->route('dash');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('feeds/{fileName}/{key}', 'VendorProductFileExportController@exportFile');

Route::group(['middleware' => 'auth'], function () {
    /**** Connectors Oauth ***/
    // Mercadolibre Connector
    Route::get('mercadolibre/get_account_access_code', 'ConnectorOauthController@getMercadolibreAccessCode');
    Route::get('mercadolibre/auth', 'ConnectorOauthController@getMercadolibreAccessToken');
    // Tiendanube Connector
    Route::get('tiendanube/get_account_access_code', 'ConnectorOauthController@getTiendanubeAccessCode');
    Route::get('tiendanube/auth', 'ConnectorOauthController@getAccessTokenTiendanube');


    Route::get('woo/import/categories', 'WooController@importProductCategories');

    Route::get('/excel-download', function (){
        return Excel::download(new PublicationExport, 'publications.xlsx');
    });
});
