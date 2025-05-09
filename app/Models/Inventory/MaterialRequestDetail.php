<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialRequestDetail extends Model
{
    use HasFactory;
    protected $guarded = [];
    // MaterialRequestDetail.php
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }


}
