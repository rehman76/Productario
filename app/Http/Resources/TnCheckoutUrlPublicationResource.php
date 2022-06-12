<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TnCheckoutUrlPublicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'variant_id' => $this->tiendanube_variant_id,
            'quantity' => $this->qty,
        ];
    }
}
