<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class TnCheckoutUrlResource extends JsonResource
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
            'url' => $this->url,
            'discount_percentage' => $this->discount_percentage,
            'is_active' => $this->is_active,
            'contact_name' => $this->contact_name,
            'contact_last_name' => $this->contact_last_name,
            'contact_email' => $this->contact_email,
            'params' => $this->params,
            'products' => TnCheckoutUrlPublicationResource::collection($this->tnCheckoutUrlPublications)
        ];
    }
}
