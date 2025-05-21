<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialRequestDetail extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $fillable = ['mr_id', 'product_id', 'qty','store_issued_qty', 'reason'];

    // MaterialRequestDetail.php
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }


}
