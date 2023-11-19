<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use \Exception;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\StoreInvoiceItemsRequest;
use App\Http\Requests\DestroyInvoiceItemsRequest;
use App\Models\Discount;

class InvoiceController extends Controller
{
    public function store(StoreInvoiceRequest $request)
    {
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
            $invoice = Invoice::create($validatedData);
            foreach ($validatedData['line_items'] as $item) {
                $product = $request->getProducts()->firstWhere('id', $item['product_id']);

                $lineItems[] = [
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'total_price' => $product->price * $item['quantity'],
                ];
            }

            $invoice->lineItems()->upsert(
                $lineItems,
                ['id'],
                ['product_id', 'quantity', 'total_price']
            );

            $validatedData['total_amount'] = $invoice->getTotalAmount();
            if(isset($validatedData['discount_code'] )){
                $discount = Discount::where('code', $validatedData['discount_code'])->first();
                $validatedData['total_amount'] = $invoice->getTotalAmountDiscount($discount, $validatedData['total_amount']);
                $validatedData['discount_id'] = $discount->id;
            }

            $invoice->update($validatedData);
            DB::commit();

            return response()->json(['message' => 'Invoice created successfully'], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Something went wrong.'], 500);
        }
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('lineItems.product', 'discount');
        return response()->json($invoice, 200);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        try {
            $validatedData = $request->validated();
            $updateValues = [];
            foreach ($validatedData['line_items'] ?? [] as $item) {
                $product = $request->getProducts()->firstWhere('id', $item['product_id']);

                $updateValues[] = [
                    'id' => $item['id'],
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'total_price' => $product->price * $item['quantity'],
                ];
            }

            DB::beginTransaction();
            if (!empty($updateValues)) {
                $invoice->lineItems()->upsert(
                    $updateValues,
                    ['id'],
                    ['product_id', 'quantity', 'total_price']
                );
            }

            $validatedData['total_amount'] = $invoice->getTotalAmount();
            if(isset($validatedData['discount_code'] )){
                $discount = Discount::where('code', $validatedData['discount_code'])->first();
                $validatedData['total_amount'] = $invoice->getTotalAmountDiscount($discount, $validatedData['total_amount']);
                $validatedData['discount_id'] = $discount->id;
            } else if(isset($invoice->discount)){
                $validatedData['total_amount'] = $invoice->getTotalAmountDiscount($invoice->discount, $validatedData['total_amount']);
            }

            $invoice->update($validatedData);
            DB::commit();

            return response()->json(['message' => 'Updated successfully'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Something went wrong.'], 500);
        }
    }

    public function storeLineItems(StoreInvoiceItemsRequest $request, Invoice $invoice)
    {
        try {
            $validatedData = $request->validated();
            foreach ($validatedData['line_items'] as $item) {
                $product = $request->getProducts()->firstWhere('id', $item['product_id']);

                $storeValues[] = [
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'total_price' => $product->price * $item['quantity'],
                ];
            }

            DB::beginTransaction();
            $invoice->lineItems()->upsert(
                $storeValues,
                ['id'],
                ['product_id', 'quantity', 'total_price']
            );

            $validatedData['total_amount'] = $invoice->getTotalAmount();
            if(isset($invoice->discount)){
                $validatedData['total_amount'] = $invoice->getTotalAmountDiscount($invoice->discount, $validatedData['total_amount']);
            }
            $invoice->update($validatedData);
            DB::commit();

            return response()->json(['message' => 'Created successfully'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Something went wrong.'], 500);
        }
    }

    public function destroyLineItems(DestroyInvoiceItemsRequest $request, Invoice $invoice)
    {
        try {
            DB::beginTransaction();
            $validatedData = $request->validated();
            $requestLineItemIds = collect($validatedData['line_items'])->pluck('id')->toArray();
            $invoice->lineItems()->whereIn('id', $requestLineItemIds)->delete();

            $validatedData['total_amount'] = $invoice->getTotalAmount();
            if(isset($invoice->discount)){
                $validatedData['total_amount'] = $invoice->getTotalAmountDiscount($invoice->discount, $validatedData['total_amount']);
            }
            $invoice->update($validatedData);
            DB::commit();

            return response()->json(['message' => 'Deleted successfully'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Something went wrong.'], 500);
        }
    }

    public function destroy(Invoice $invoice)
    {
        try {
            DB::beginTransaction();
            $invoice->lineItems()->delete();
            $invoice->delete();
            DB::commit();
            return response()->json(['message' => 'Invoice deleted successfully'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Something went wrong.'], 500);
        }
    }

    public function index()
    {
        $invoices = Invoice::with('lineItems.product', 'discount')->get();
        return response()->json($invoices, 200);
    }
}
