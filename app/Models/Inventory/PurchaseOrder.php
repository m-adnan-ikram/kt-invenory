<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
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
    
    public function mr()
    {
        return $this->belongsTo(MaterialRequest::class, 'mr_id');
    }
    
}
