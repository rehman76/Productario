<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;

class VendorProductFileExportController extends Controller
{
    public function exportFile($fileName, $key)
    {
        if ($key==env('VENDOR_PRODUCT_EXPORT_KEY'))
        {
            $filePath = public_path('export/vendor_products/'.$fileName);

            if (file_exists($filePath))
            {
                // Send Download
                return Response::download($filePath, $fileName, [
                    'Content-Type: csv',
                    'Content-Disposition: attachment; filename='.$fileName,
                ]);
            }
            else
            {// Error
                exit('Requested file does not exist on our server!');
            }
        } else {
            exit('Access Denied');
        }

    }
}
