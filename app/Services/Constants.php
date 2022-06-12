<?php
/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 18/03/2021
 * Time: 8:03 PM
 */

namespace App\Services;


class Constants
{
    /*** Woo Sync Action Statuses ***/
    const WooActionRunningStatus = 'running';
    const WooActionFinishStatus = 'finished';

    /*** Connectors ***/
    const ConnectorMercadolibre = 1;
    const ConnectorTiendanube = 2;
    const ConnectorWooCommerce = 3;

    /** Categories  */
    const OfertaCategoryId = 847;

    /*** Vendor Ids ***/
    const BatepreciosVendorId = 5;
    const VendorMicroglobalId = 10;
    const VendorArgSeguridadId = 6;

    /*** Vendor Product Statuses ****/

    const deActivateVendorProduct = 0;
    const activateVendorProduct = 1;
}
