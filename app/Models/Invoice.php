<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\DiscountType;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['date', 'total_amount', 'discount_id'];
    protected $hidden = ['deleted_at'];
    protected $with = ['lineItems'];


    public function lineItems()
    {
        return $this->hasMany(InvoiceLineItem::class);
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function getTotalAmount()
    {
        return $this->lineItems()->sum('total_price');
    }

    public function getTotalAmountDiscount($discount, $totalAmount)
    {
        if ($discount->type === DiscountType::PERCENTAGE) {
            $discountAmount = ($totalAmount * $discount->value) / 100;
        } else {
            if($discount->value >= $totalAmount){
                return 0;
            }
            $discountAmount = $discount->value;
        }

        $totalAmount -= $discountAmount;
        return $totalAmount;
    }
}
