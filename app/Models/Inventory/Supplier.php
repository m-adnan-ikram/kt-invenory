<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $fillable = ['name', 'contact', 'address', 'cnic','added_by'];
    public function bidDetails()
    {
        return $this->hasMany(BidSummary::class, 'supplier_id');
    }

}
