<?php

namespace App\Rules;

use App\Connectors\MercadolibreConnector;
use App\Publication;
use App\PublicationPremiumProduct;
use Illuminate\Contracts\Validation\Rule;

class MlaValidation implements Rule
{
    protected $publicationId;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($publicationId)
    {
        $this->publicationId = $publicationId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if(isset($value)) {

            $mercadoLibreConnector = new MercadolibreConnector();
            $isPublicationWithThisMlaExist = Publication::where('mla', '=', $value)->exists(); //Check Publication exist against input MLA
            $isPublicationPremiumProductWithThisMlaExist = PublicationPremiumProduct::where('mla_id', '=', $value)->exists();  //Check Premium Publication exist against input MLA

            if (!$this->publicationId) {
                $response = $this->getProduct($mercadoLibreConnector, $value);
                if (!$isPublicationWithThisMlaExist && !$isPublicationPremiumProductWithThisMlaExist && $response->successful()) {
                    return true;
                }

                return false;

            } else {
                $publicationHasMla = Publication::where('id', '=', $this->publicationId)
                    ->where('mla', '=', $value)
                    ->exists(); //If publication not exist then call then only we do api call else we don't api call

                if (!$publicationHasMla) {
                    $response = $this->getProduct($mercadoLibreConnector, $value);
                    if (!$isPublicationWithThisMlaExist && !$isPublicationPremiumProductWithThisMlaExist && $response->successful()) {
                        return true;
                    }

                    return false;
                }
                return true;
            }
        }

        return true;
    }

    public function getProduct($mercadoLibreConnector, $mla)
    {
        return $mercadoLibreConnector->getProduct($mla); //Here we validate that MLA is correct

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'MLA is not correct or already exist in the system';
    }
}
