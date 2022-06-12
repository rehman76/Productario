<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'id' => $this->id,
            'status' => $this->status ? true : false,
            'name' => $this->name,
            'sku' => $this->sku,
            'ean' => $this->ean,
            'description' => $this->description,
            'markup' => $this->markup,
            'discount' => $this->discount,
            'woo_id' => $this->woo_id,
            'woo_product_url' => $this->woo_product_url,
            'mla' => $this->mla,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'stock' => $this->quantity,
            'other_taxes' => $this->other_taxes,
            'notes' => $this->notes,
            'attributes' => $this->attributes,
            'avatar' => !$this->getMedia('avatar')->isEmpty() ? $this->getMedia('avatar')[0]->getFullUrl() : null,
            'categories' => CategoryResource::collection($this->categories),
        ];
    }
}
