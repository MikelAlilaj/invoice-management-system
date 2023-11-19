<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Product;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreInvoiceItemsRequest extends FormRequest
{
    protected $products;

    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'line_items' => 'required|array|min:1',
            'line_items.*.product_id' => ['required', 'distinct', Rule::in($this->getProductIds()), Rule::notIn($this->getExistedProductIds())],
            'line_items.*.quantity' => 'required|numeric|min:1',
        ];
    }

    protected function getProductIds()
    {
        $lineItemProductIds = collect($this->input('line_items'))->pluck('product_id')->toArray();
        $this->products = Product::whereIn('id', $lineItemProductIds)->select('id', 'price')->get();
        return $this->products->pluck('id')->toArray();
    }

    protected function getExistedProductIds()
    {
        $lineItemIds = collect($this->input('line_items'))->pluck('id')->toArray();
        $existedProductIds = $this->invoice->lineItems->whereNotIn('id', $lineItemIds)->pluck('product_id')->toArray();
        return $existedProductIds;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['message' => $validator->errors()], 422));
    }

    public function getProducts()
    {
        return $this->products;
    }
}
