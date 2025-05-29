<?php

namespace App\Http\Controllers;

use App\Models\Inventory\GoodReceiveNote;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    //
    public function received()
    {
         $inward = GoodReceiveNote::with([
            'supplier',
            'purchaseOrder',
            'details.product'
        ])->get();
    
        return response()->json([
            'inward' => $inward
        ]);
    }
    

}
