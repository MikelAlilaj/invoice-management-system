<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use \Exception;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:products',
            'price' => 'required|numeric|min:0.01|max:999999.99',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        Product::create([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
        ]);

        return response()->json(['message' => 'Product created successfully'], 201);
    }

    public function show(Product $product)
    {
        return response()->json($product, 200);
    }


    public function update(Request $request, Product $product)
    {
        $rules = [
            'name' => 'required|unique:products,name,' . $product->id,
            'price' => 'required|numeric|min:0.01|max:999999.99',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        $product->update([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
        ]);

        return response()->json(['message' => 'Product updated successfully'], 200);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully'], 200);
    }

    public function index()
    {
        $products = Product::all();
        return response()->json($products, 200);
    }
}
