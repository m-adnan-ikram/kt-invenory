<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $fillable = [
        'bid_id', 'mr_id', 'prn_id', 'supplier_id',
        'total', 'remaining', 'status'
    ];
    
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function prn()
    {
        return $this->belongsTo(PurchaseRequisitionNote::class, 'prn_id');
    }

    public function mr()
    {
        return $this->belongsTo(MaterialRequest::class, 'mr_id');
    }

    public function bid()
    {
        return $this->belongsTo(BidSummary::class, 'bid_id');
    }
}

