<?php


namespace App\Services;

use App\Connectors\MercadolibreConnector;
use App\PublicationPremiumProduct;
use Illuminate\Http\Client\RequestException;

class ClonePremiumProductService
{
    /**
     * Create a new job instance.
     * @param $mercadolibreConnector
     * @return void
     */

    protected $mercadolibreConnector, $mlaCategoryAttributesConfig = [];

    public function __construct()
    {
        $this->mercadolibreConnector = new MercadolibreConnector();
    }


    public function clonePremiumProduct($publication)
    {
        $mercadoLibreProduct = $this->get($publication);

        if (!isset($mercadoLibreProduct['error'])) {
            $premiumPublicationData = $this->newPremiumPublicationDataStructure($mercadoLibreProduct, $publication);
            $response = $this->mercadolibreConnector->createProduct($premiumPublicationData);

            if ($response->successful()) {
                PublicationPremiumProduct::create([
                    'publication_id' => $publication->id,
                    'mla_id' => $response['id'],
                    'price' => $response['price']
                ]);
                return $this->setPremiumProductDescription($publication->mla, $response['id']);

            } else {

                return ['error' => true, 'message' => $this->getErrorMessage($response)];
            }
        }else{
            return ['error' => true, 'message' => $mercadoLibreProduct['message']];
        }
    }

    public function get($publication)
    {
        // Get product details from mercadolibre
        return json_decode($this->mercadolibreConnector->getProduct($publication->mla), true);
    }

    public function newPremiumPublicationDataStructure($classicPublication, $publication)
    {
        $winnerVendorProduct = $publication->vendorproductwinner()->first();
        $premiumProductPrice = round((nova_get_setting('markup_percentage_premium_product') / 100) *
            $winnerVendorProduct->price  + $winnerVendorProduct->price);

        $premiumPublicationData = [
            "title" => $classicPublication['title'],
            "category_id" => $classicPublication['category_id'],
            "price" => $premiumProductPrice,
            "currency_id" => $classicPublication['currency_id'],
            "available_quantity" => $classicPublication['available_quantity'],
            "buying_mode" => $classicPublication['buying_mode'],
            "shipping" => $classicPublication['shipping'],
            "condition" => $classicPublication['condition'],
            "listing_type_id" => 'gold_pro',
        ];

        $premiumPublicationData['sale_terms'] = [];
        foreach ($classicPublication['sale_terms'] as $key => $sale_term)
        {
            if ($sale_term['id'] !=='INVOICE' )
            {
                array_push($premiumPublicationData['sale_terms'], [
                    'id' => $sale_term['id'],
                    'value_name' => $sale_term['value_name'],
                ]);
            }

        }


        foreach ($classicPublication['pictures'] as $key => $picture)
        {
            $premiumPublicationData['pictures'][$key]['source'] = $picture['secure_url'];
        }

        $this->getMlaCategoryAttributesConfig($classicPublication['category_id']);
        $premiumPublicationData['attributes'] = [];
        foreach ($this->mlaCategoryAttributesConfig as $key => $attributeConfig)
        {
                $mlaCategoryArrayIndexById =  array_search($attributeConfig['id'], array_column($classicPublication['attributes'], 'id'));

                array_push($premiumPublicationData['attributes'], $mlaCategoryArrayIndexById!==false ? [
                    'id' => $classicPublication['attributes'][$mlaCategoryArrayIndexById]['id'],
                    'value_name' => $classicPublication['attributes'][$mlaCategoryArrayIndexById]['value_name'],
                    'value_id' => $classicPublication['attributes'][$mlaCategoryArrayIndexById]['value_id'],
                ] : [
                    'id' => $attributeConfig['id'],
                    'value_id' => null,
                ]);
        }

        return $premiumPublicationData;
    }

    public function getMlaCategoryAttributesConfig($categoryId)
    {
        $response = $this->mercadolibreConnector->getCategoryAttributes($categoryId);

        if ($response->successful())
        {
            $attributes = json_decode($response, true);

            foreach ($attributes as $attribute)
            {
                array_push($this->mlaCategoryAttributesConfig, [
                    'id' => $attribute['id'],
                    'name' => $attribute['name'],
                    'tags' => $attribute['tags']
                ]);
            }
        }
    }

    public function getErrorMessage($response)
    {
        $messages = [];

        foreach ($response['cause'] as $error)
        {
            array_push($messages, $error['message']);
        }
        return implode(',', $messages);
    }

    public function setPremiumProductDescription($classProductMlaID, $premiumProductMlaId)
    {
        try {

            $classProductDescription = $this->mercadolibreConnector->getProductDescription($classProductMlaID)->throw();
            $this->mercadolibreConnector->setProductDescription($premiumProductMlaId, $classProductDescription['plain_text'])->throw();

            return ['error' => false, 'message' => ''];
        }catch (RequestException $exception) {
            return ['error' => true, 'message' => 'Premium Publication Created but issue in cloning description, Error is: '.
                                    $exception->response['message']];
        }
    }

}
