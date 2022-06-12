<?php

namespace App\Jobs;

use App\Services\Constants;
use App\Services\VendorProductService;
use Goutte\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScrapDataFromArgseguridadWebsite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $client, $pageNumber;
    public function __construct($pageNumber = null)
    {
        $this->onQueue('scrapping');
        $this->client = new Client();
        $this->pageNumber = $pageNumber;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->login();
        if($this->pageNumber){
            $products =  $this->getProductsDetail();
            $this->updateRecordInDataBase($products);
        }else{
            $this->dispatchJobs();
        }
    }

    public function login(){
        //Go to the login page
        $page = $this->client->request('GET', 'https://www.argseguridad.com/distribuidores/login');
        // Get the login form
        $form = $page->selectButton('Identificarse')->form();
        //Fill and submit the login Form and get the dashbord view
        $this->client->submit($form, ['username' => 'batepreciosproo', 'password' => 'batepreciosproo']);
    }

    public function dispatchJobs(){
        $productPage = $this->client->request('GET', 'https://www.argseguridad.com/distribuidores/index.php?page=productos');
        $pagination =  explode(' ', $productPage->filterXPath('//*[@id="content"]/p[4]')->text());
        $paginationSize = ceil($pagination[count($pagination)-2] / 100);
        for($i = 0; $i < $paginationSize; $i++){
            ScrapDataFromArgseguridadWebsite::dispatch($i+1);
        }
    }

    public function getProductsDetail(){
        $productPage = $this->client->request('GET', 'https://www.argseguridad.com/distribuidores/index.php?_pagi_pg='.$this->pageNumber.'&page=productos');

        return $productPage->filter('tr')->each(function($node, $index)  {
            $nested_node =  $node->first('td');
                if($nested_node->filter('a')->count()){
                    $sku = $nested_node->filter('a > h3')->text();
                    $qty =  $nested_node->filter('span > .stockvalorr')->attr('value');
                    return $result[$index] = ['sku' => $sku, 'quantity' => $qty != '' ? $qty : 0 ];
                }
                return null;
        });
    }

    public function updateRecordInDataBase($products){
        foreach($products as $product){
            if($product){
                VendorProductService::updateAndEvaluate(Constants::VendorArgSeguridadId, $product['sku'],  $product);
            }
        }
    }

}
