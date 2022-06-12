<?php


namespace App\Services;

use App\VendorAdapters\AirComputerAdapter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Util\Exception;
use App\Jobs\ImportAirComputerProductsJob;


class AirComputerProductsService
{
    protected $vendorInstanceId;

    protected $airComputerAdapter;

    protected $vendorProductService;

    public function __construct(AirComputerAdapter $airComputerAdapter, VendorProductService $vendorProductService)
    {
            $this->airComputerAdapter = $airComputerAdapter;
            $this->vendorProductService = $vendorProductService;
    }

    public function getProducts()
    {
        return $this->fetchData([]);
    }

    public function saveProductsFromAllGroups($vendorInstanceId)
    {
        $token = $this->getToken();

       $groups =  $this->fetchData([
            'q' => 'grupos',
            'token' => $token
        ]);

        $totalCount =  count($groups);

        $groups->each(function ($group, $key) use ($token, $vendorInstanceId, $totalCount) {
            $isLast = false;
            if ($totalCount == ++$key)
            {
                $isLast = true;
            }

            ImportAirComputerProductsJob::dispatch($group['grupo'],$vendorInstanceId,$token, $isLast);
        });
    }

    public function saveGroupProducts($groupId, $vendorInstanceId, $token, $isLast)
    {
        $groupProducts = $this->fetchData([
            'grupo_id' => $groupId,
            'token' => $token
        ]);

        $this->airComputerAdapter->importToDataBase($vendorInstanceId, $groupProducts);

        if ($isLast && Storage::exists('air_computer_skus.txt'))
        {
            $recentlyUpdatedSkus = explode("\n",Storage::get('air_computer_skus.txt'));
            $zombieProductsSkus = $this->vendorProductService->
                            getActiveProductsSkuAgainstVendorWhereSkuNotMatch($vendorInstanceId, $recentlyUpdatedSkus);
            $this->vendorProductService->updateMissingProductStatus($zombieProductsSkus, $vendorInstanceId);
            Storage::delete('air_computer_skus.txt');
        }
    }

    private function fetchData($queryParams = [])
    {
        try {
            $responseData = Http::get('https://api.air-intra.com/v1', $queryParams)->throw()
                ->json();

            if (array_key_exists('error_id', $responseData) && $responseData['error_id'] >= 500) {
                throw new Exception('Token is expired');
            }

            return collect($responseData);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function getToken()
    {
        $response = Http::get('https://api.air-intra.com/v1/login.php', [
            'user' => env('VENDOR_AIR_COMPUTER_ID'),
            'pass' => env('VENDOR_AIR_COMPUTER_PASSWORD'),
        ])->object();

        return $response->token;
    }
}
