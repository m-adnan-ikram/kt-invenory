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


    public function unit()
    {
        return $this->belongsTo(ProductUnit::class);
    }
    
    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }
    
}


