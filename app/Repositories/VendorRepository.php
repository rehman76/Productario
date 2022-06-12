<?php
/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 11/02/2021
 * Time: 6:08 PM
 */

namespace App\Repositories;


use App\Vendor;

class VendorRepository
{
    public function __construct()
    {
        $this->vendor = new Vendor();
    }

    public function all()
    {
        return $this->vendor->all();
    }
}