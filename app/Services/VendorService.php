<?php
/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 11/02/2021
 * Time: 6:08 PM
 */

namespace App\Services;


use App\Repositories\VendorRepository;
use App\VendorAdapters\AirComputerAdapter;
use App\VendorAdapters\ARGSeguridadAdapter;
use App\VendorAdapters\BatepreciosAdapter;
use App\VendorAdapters\CevenAdapter;
use App\VendorAdapters\ElitAdapter;
use App\VendorAdapters\GrupoNucleoAdapter;
use App\VendorAdapters\MasNetAdapter;
use App\VendorAdapters\MicroGlobalAdapter;
use App\VendorAdapters\StenfarAdapter;
use App\VendorAdapters\StylusAdapter;

class VendorService
{
    public function __construct()
    {
        $this->vendorRepository = new VendorRepository();
    }

    /**
     * @param $name
     * @return ElitAdapter|GrupoNucleoAdapter|StylusAdapter|AirComputerAdapter|BatepreciosAdapter|ARGSeguridadAdapter|MasNetAdapter|CevenAdapter|StenfarAdapter
     */
    public function getVendorAdapterInstance($name)
    {
        switch ($name) {
            case 'ELIT':
                return new ElitAdapter();
            case 'Grupo Nucleo':
                return new GrupoNucleoAdapter();
            case 'Stylus':
                return new StylusAdapter();
            case 'Air Computers':
                return new AirComputerAdapter();
            case 'BatePrecios':
                return new BatepreciosAdapter();
            case 'ARG Seguridad':
                return new ARGSeguridadAdapter();
            case 'MasNet':
                return new MasNetAdapter();
            case 'Ceven':
                return new CevenAdapter();
            case 'Stenfar':
                return new StenfarAdapter();
            case 'Micro Global':
                return new MicroGlobalAdapter();
            default:
                break;
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all()
    {
        return $this->vendorRepository->all();
    }
}
