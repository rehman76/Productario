<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'sku' => 'required',
            'name' => 'required',
            'price' => 'required|numeric',
            'quantity' => 'required|numeric',
            'currency'=> ['required',
                 Rule::in(['USD', 'ARS'])
            ],
            'description' => 'sometimes|nullable',
            'iva' => 'sometimes|nullable|numeric',
            'other_taxes' => 'sometimes|nullable|numeric',
            'ean'=>'sometimes|nullable',
            'link' => 'sometimes|nullable',
            'dollar_rate' =>'sometimes|nullable|numeric',
        ];
    }
}
