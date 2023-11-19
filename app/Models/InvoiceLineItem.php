<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceLineItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['invoice_id', 'product_id', 'quantity', 'total_price'];
    protected $hidden = ['deleted_at'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}