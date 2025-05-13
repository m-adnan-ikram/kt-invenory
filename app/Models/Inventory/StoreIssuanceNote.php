<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreIssuanceNote extends Model
{
    use HasFactory;
    protected $fillable = ['requested_by', 'reason', 'mr_id'];

}
