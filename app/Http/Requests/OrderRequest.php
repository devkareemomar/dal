<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
        // dd($this->all());
        return [
            'customer_id'          => 'required|exists:users,id',
            'address_id'          => 'required|exists:addresses,id',
            'additional_info'      => 'nullable|string',
            'shipping_cost'       => 'required',
            'grand_total'       => 'required',
            'payment_status'       => 'required',
            'items'       => ['required', 'array', 'min:1'],
            'items.*.id'    => 'required',
            'items.*.color'    => 'required',
            'items.*.attribute'    => 'nullable',
            'items.*.price'    => 'required',
            'items.*.quantity'    => 'required',
            'items.*.total'    => 'required',
        ];
    }
}
