<?php

namespace App\Http\Controllers;

use App\Connectors\MercadolibreConnector;
use App\Connectors\TiendanubeConnector;
use Illuminate\Http\Request;

class ConnectorOauthController extends Controller
{
    /****** Tiendanube Connector ****/

    public function getTiendanubeAccessCode()
    {
        return 'https://www.tiendanube.com/apps/2929/authorize';
    }

    public function getAccessTokenTiendanube(Request $request)
    {
        return TiendanubeConnector::getAccessToken($request['code']);
    }

    /****** Mercadolibre Connector ****/
    public function getMercadolibreAccessCode()
    {
        return redirect()->to('https://auth.mercadolibre.com.ar/authorization?response_type=code&client_id='.
            env('Mercadolibre_APP_CLIENT_ID'). '&redirect_uri='. env('Mercadolibre_APP_REDIRECT_URL'));
    }

    public function getMercadolibreAccessToken(Request $request)
    {
        return (new MercadolibreConnector())->getAccessToken($request['code']);
    }


}
