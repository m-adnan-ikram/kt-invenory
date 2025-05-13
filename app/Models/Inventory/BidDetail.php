<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BidDetail extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $fillable = [
        'bid_id', 'product_id', 'qty', 'rate', 'total', 'discount',
        'delivery_charges', 'tax', 'net_amount'
    ];
    // Define the relationship with BidSummary
    public function bidSummary()
    {
        return $this->belongsTo(BidSummary::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
    public function bids()
    {
        return $this->belongsTo(BidSummary::class, 'bid_id');
    }
    

}

