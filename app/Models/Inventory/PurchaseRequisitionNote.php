<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequisitionNote extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function details()
    {
        return $this->hasMany(PurchaseRequisitionNoteDetail::class, 'prn_id');
    }
    
    public function mr()
    {
        return $this->belongsTo(MaterialRequest::class);
    }

}
