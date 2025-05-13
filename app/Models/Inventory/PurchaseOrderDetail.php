<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetail extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $fillable = [
        'po_id', 'product_id', 'qty', 'rate', 'sub_total',
        'tax', 'delivery', 'discount', 'net_amount',
        'gate_receive_note', 'store_received'
    ];
    

}
