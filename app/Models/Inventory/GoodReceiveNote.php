<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodReceiveNote extends Model
{
    use HasFactory;
    protected $fillable = ['po_id', 'supplier_id', 'received_by'];

}
