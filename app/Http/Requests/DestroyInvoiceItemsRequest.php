<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DestroyInvoiceItemsRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $invoiceLineItemIds = collect($this->invoice->lineItems)->pluck('id')->toArray();
        return [
            'line_items' => 'required|array|min:1',
            'line_items.*.id' =>  ['integer', 'min:1', Rule::in($invoiceLineItemIds)],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['message' => $validator->errors()], 422));
    }

}
