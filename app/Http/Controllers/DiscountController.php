<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;
use App\Models\Discount;
use Illuminate\Validation\Rule;
use App\Enums\DiscountType;

class DiscountController extends Controller
{
    public function store(Request $request)
    {
        $rules = [
            'code' => 'required|unique:discounts',
            'type' => ['required', Rule::in([DiscountType::PERCENTAGE, DiscountType::FLAT])],
            'value' => [
                'required',
                'numeric',
                Rule::when($request->input('type') === DiscountType::PERCENTAGE, ['min:0.01', 'max:99.99']),
                Rule::when( $request->input('type') === DiscountType::FLAT,  ['min:0.01', 'max:99999999.99']),
            ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        Discount::create($validatedData);

        return response()->json(['message' => 'Discount created successfully'], 201);
    }
}
