<?php

namespace App\Models\Inventory;

use App\Models\Inventory\ProductCategory;
use App\Models\Inventory\ProductUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $fillable = ['name', 'unit_id', 'category_id', 'qty', 'avg_price'];


    public function unit()
    {
        return $this->belongsTo(ProductUnit::class);
    }
    
    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }
    public function materialRequestDetails()
    {
        return $this->hasMany(MaterialRequestDetail::class);
    }
    public function prnDetails()
    {
        return $this->hasMany(PurchaseRequisitionNoteDetail::class);
    }

}


