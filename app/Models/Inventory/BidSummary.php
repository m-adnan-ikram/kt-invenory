<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BidSummary extends Model
{
    use HasFactory;
    protected $guarded = [];
   
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function prn()
    {
        return $this->belongsTo(PurchaseRequisitionNote::class, 'prn_id');
    }
    public function products() {
        return $this->hasMany(Product::class); // or whatever your model is
    }
    public function details() {
        return $this->hasMany(BidDetail::class, 'bid_id');
    }
}
